<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Product;
use App\Services\QrisService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class PaymentWebhookController extends Controller
{
    /**
     * Handle Official Midtrans & Payment Gateway Webhook Notification with Defense-in-Depth Security
     */
    public function handle(Request $request)
    {
        $payload = $request->all();
        $orderId = $request->input('order_id') ?? $request->input('invoice_number');
        $statusCode = (string) ($request->input('status_code') ?? '200');
        $grossAmount = (string) ($request->input('gross_amount') ?? '0');
        $transactionStatus = $request->input('transaction_status') ?? $request->input('status');
        $fraudStatus = $request->input('fraud_status') ?? 'accept';
        $receivedSignature = $request->input('signature_key');

        Log::info("Payment Webhook Notification received for Order: {$orderId}", [
            'status' => $transactionStatus,
            'amount' => $grossAmount,
            'ip' => $request->ip(),
        ]);

        if (empty($orderId)) {
            Log::warning("Payment Webhook rejected: missing order_id from IP: {$request->ip()}");
            return response()->json([
                'success' => false,
                'message' => 'Missing order_id or invoice_number',
            ], 400);
        }

        // 1. Cari Order di Database
        $order = Order::where('invoice_number', $orderId)->first();
        if (!$order) {
            Log::warning("Payment Webhook order not found: {$orderId}");
            return response()->json([
                'success' => false,
                'message' => 'Order not found',
            ], 404);
        }

        $serverKey = config('services.midtrans.server_key', env('MIDTRANS_SERVER_KEY'));
        $isProduction = config('services.midtrans.is_production', env('MIDTRANS_IS_PRODUCTION', false));

        // 2. LAYER 1: Verifikasi Tanda Tangan Kriptografis (Cryptographic Signature Verification)
        if (!empty($serverKey) && !empty($receivedSignature)) {
            $isValidSignature = QrisService::verifyMidtransNotification(
                $orderId,
                $statusCode,
                $grossAmount,
                $receivedSignature,
                $serverKey
            );

            if (!$isValidSignature) {
                Log::critical("SECURITY ALERT: Invalid Midtrans Signature detected on Order {$orderId} from IP {$request->ip()}! Potential tampering attempt.", [
                    'received_sig' => $receivedSignature,
                    'order_amount' => $order->total_amount,
                ]);

                return response()->json([
                    'success' => false,
                    'message' => 'Security Error: Invalid cryptographic signature',
                ], 403);
            }
        }

        // 3. LAYER 2: Validasi Kesesuaian Nominal (Amount Tampering Prevention)
        if ($grossAmount !== '0' && (int) round((float) $grossAmount) !== (int) $order->total_amount) {
            Log::critical("SECURITY ALERT: Payment amount mismatch for Order {$orderId}! DB: {$order->total_amount}, Paid: {$grossAmount}");
            return response()->json([
                'success' => false,
                'message' => 'Security Error: Gross amount mismatch with order total',
            ], 422);
        }

        // 4. LAYER 3: Active Server-to-Server Double Verification (Active Callback to Midtrans API)
        if (!empty($serverKey) && !empty($request->input('transaction_id'))) {
            try {
                $statusEndpoint = $isProduction
                    ? "https://api.midtrans.com/v2/{$orderId}/status"
                    : "https://api.sandbox.midtrans.com/v2/{$orderId}/status";

                $verifyResponse = Http::withBasicAuth($serverKey, '')
                    ->timeout(10)
                    ->get($statusEndpoint);

                if ($verifyResponse->successful()) {
                    $verifyData = $verifyResponse->json();
                    $verifiedStatus = $verifyData['transaction_status'] ?? null;
                    $verifiedGross = (int) round((float) ($verifyData['gross_amount'] ?? 0));

                    if ($verifiedGross !== (int) $order->total_amount) {
                        Log::critical("SECURITY ALERT: Active Midtrans inquiry amount mismatch on {$orderId}!");
                        return response()->json(['success' => false, 'message' => 'Amount verification failed with Payment Gateway'], 422);
                    }

                    // Sinkronkan status resmi dari Payment Gateway
                    $transactionStatus = $verifiedStatus ?: $transactionStatus;
                }
            } catch (\Throwable $e) {
                Log::error("Active payment verification connection exception: " . $e->getMessage());
            }
        }

        // 5. LAYER 4: State Machine & Idempotent Order Updating
        return DB::transaction(function () use ($order, $transactionStatus, $fraudStatus) {
            // Jika transaksi sudah lunas sebelumnya, cukup return 200 OK (Idempotency)
            if ($order->payment_status === 'paid') {
                return response()->json([
                    'success' => true,
                    'message' => 'Order was already marked as paid (Idempotent OK)',
                ]);
            }

            // Pembayaran Berhasil (Settlement / Capture / Paid)
            if (in_array($transactionStatus, ['settlement', 'paid', 'success']) || 
               ($transactionStatus === 'capture' && $fraudStatus === 'accept')) {
                
                $order->update([
                    'payment_status' => 'paid',
                    'cash_received' => $order->total_amount,
                    'cash_change' => 0,
                    'pickup_status' => $order->pickup_status ?: 'ready_for_pickup',
                ]);

                Log::info("Order {$order->invoice_number} successfully SETTLED via QRIS payment gateway.");

                return response()->json([
                    'success' => true,
                    'message' => 'Payment successfully verified and settled',
                ]);
            }

            // Pembayaran Dibatalkan / Kedaluwarsa / Ditolak (Cancel, Expire, Deny) -> Kembalikan Stok
            if (in_array($transactionStatus, ['cancel', 'deny', 'expire', 'cancelled'])) {
                if ($order->payment_status === 'pending') {
                    foreach ($order->items as $item) {
                        if ($item->product_id) {
                            Product::where('id', $item->product_id)->increment('stock', $item->quantity);
                        }
                    }

                    $order->update([
                        'payment_status' => 'cancelled',
                        'notes' => ($order->notes ? $order->notes . ' | ' : '') . "Dibatalkan oleh Payment Gateway (status: {$transactionStatus})",
                    ]);

                    Log::info("Order {$order->invoice_number} expired/cancelled by Gateway. Inventory stock restored.");
                }

                return response()->json([
                    'success' => true,
                    'message' => "Order marked as cancelled ({$transactionStatus}) and stock restored",
                ]);
            }

            // Status Pending / Menunggu Pembayaran
            return response()->json([
                'success' => true,
                'message' => 'Payment status received: ' . $transactionStatus,
            ]);
        });
    }
}
