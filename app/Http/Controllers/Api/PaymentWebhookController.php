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

        // 1. Cari Order, Renewal Payment, atau Pending Registration di Database
        $order = Order::where('invoice_number', $orderId)->first();
        if (!$order) {
            $payment = \App\Models\Payment::with(['member.user', 'package'])->where('invoice_number', $orderId)->first();
            if ($payment) {
                return $this->handleRenewalPayment($payment, $orderId, $statusCode, $grossAmount, $transactionStatus, $fraudStatus, $receivedSignature, $request);
            }

            $pendingReg = \App\Models\PendingRegistration::with('package')->where('invoice_number', $orderId)->first();
            if ($pendingReg) {
                return $this->handleRegistrationPayment($pendingReg, $orderId, $statusCode, $grossAmount, $transactionStatus, $fraudStatus, $receivedSignature, $request);
            }

            Log::warning("Payment Webhook record not found: {$orderId}");
            return response()->json([
                'success' => false,
                'message' => 'Order, Payment, or Registration record not found',
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

    /**
     * Handle Official Webhook Notification for Membership Renewal Payments
     */
    protected function handleRenewalPayment($payment, $orderId, $statusCode, $grossAmount, $transactionStatus, $fraudStatus, $receivedSignature, Request $request)
    {
        $serverKey = config('services.midtrans.server_key', env('MIDTRANS_SERVER_KEY'));

        // 1. Verifikasi Tanda Tangan Kriptografis
        if (!empty($serverKey) && !empty($receivedSignature)) {
            $isValidSignature = QrisService::verifyMidtransNotification(
                $orderId,
                $statusCode,
                $grossAmount,
                $receivedSignature,
                $serverKey
            );

            if (!$isValidSignature) {
                Log::critical("SECURITY ALERT: Invalid Midtrans Signature on Renewal {$orderId}!");
                return response()->json([
                    'success' => false,
                    'message' => 'Security Error: Invalid cryptographic signature',
                ], 403);
            }
        }

        // 2. Validasi Nominal
        if ($grossAmount !== '0' && (int) round((float) $grossAmount) !== (int) $payment->amount) {
            Log::critical("SECURITY ALERT: Amount mismatch on Renewal {$orderId}! DB: {$payment->amount}, Paid: {$grossAmount}");
            return response()->json([
                'success' => false,
                'message' => 'Security Error: Gross amount mismatch with renewal total',
            ], 422);
        }

        // 3. State Machine & Idempotent Renewal Updating
        return DB::transaction(function () use ($payment, $transactionStatus, $fraudStatus) {
            if ($payment->status === 'paid') {
                return response()->json([
                    'success' => true,
                    'message' => 'Renewal payment was already marked as paid (Idempotent OK)',
                ]);
            }

            // Pembayaran Berhasil
            if (in_array($transactionStatus, ['settlement', 'paid', 'success']) ||
                ($transactionStatus === 'capture' && $fraudStatus === 'accept')) {

                $payment->update([
                    'status' => 'paid',
                    'payment_date' => now(),
                ]);

                $member = $payment->member;
                if ($member && $payment->package) {
                    $currentExpire = $member->expire_date && $member->expire_date->isFuture()
                        ? $member->expire_date
                        : now();
                    $newExpire = (clone $currentExpire)->addMonths($payment->package->duration_months);

                    $member->update([
                        'membership_package_id' => $payment->package->id,
                        'expire_date' => $newExpire,
                        'status' => 'active',
                    ]);

                    try {
                        app(\App\Services\WhatsAppService::class)->sendRenewalSuccess($member->fresh(['package', 'user']));
                    } catch (\Throwable $e) {
                        Log::warning("Failed sending renewal WhatsApp from Webhook: " . $e->getMessage());
                    }
                }

                Log::info("Renewal {$payment->invoice_number} successfully SETTLED and membership extended.");

                return response()->json([
                    'success' => true,
                    'message' => 'Renewal payment successfully verified and membership extended',
                ]);
            }

            // Pembayaran Dibatalkan / Kedaluwarsa
            if (in_array($transactionStatus, ['cancel', 'deny', 'expire', 'cancelled'])) {
                $payment->update([
                    'status' => 'cancelled',
                    'notes' => ($payment->notes ? $payment->notes . ' | ' : '') . "Dibatalkan oleh Gateway (status: {$transactionStatus})",
                ]);

                return response()->json([
                    'success' => true,
                    'message' => "Renewal marked as cancelled ({$transactionStatus})",
                ]);
            }

            return response()->json([
                'success' => true,
                'message' => 'Renewal payment status received: ' . $transactionStatus,
            ]);
        });
    }

    /**
     * Handle Webhook Verification & Settlement for Online Member Registration
     */
    protected function handleRegistrationPayment(\App\Models\PendingRegistration $pending, string $orderId, string $statusCode, string $grossAmount, string $transactionStatus, string $fraudStatus, ?string $receivedSignature, Request $request)
    {
        $serverKey = config('services.midtrans.server_key', env('MIDTRANS_SERVER_KEY'));
        $isProduction = config('services.midtrans.is_production', env('MIDTRANS_IS_PRODUCTION', false));

        // 1. Verifikasi Tanda Tangan Kriptografis
        if (!empty($serverKey) && !empty($receivedSignature)) {
            $isValidSignature = QrisService::verifyMidtransNotification(
                $orderId,
                $statusCode,
                $grossAmount,
                $receivedSignature,
                $serverKey
            );

            if (!$isValidSignature) {
                Log::critical("SECURITY ALERT: Invalid Midtrans Signature on Online Registration {$orderId}!");
                return response()->json([
                    'success' => false,
                    'message' => 'Security Error: Invalid cryptographic signature',
                ], 403);
            }
        }

        // 2. Validasi Nominal
        if ($grossAmount !== '0' && (int) round((float) $grossAmount) !== (int) $pending->amount) {
            Log::critical("SECURITY ALERT: Amount mismatch on Registration {$orderId}! DB: {$pending->amount}, Paid: {$grossAmount}");
            return response()->json([
                'success' => false,
                'message' => 'Security Error: Gross amount mismatch with registration total',
            ], 422);
        }

        // 3. State Machine
        if ($pending->status === 'paid') {
            return response()->json([
                'success' => true,
                'message' => 'Registration payment was already marked as paid (Idempotent OK)',
            ]);
        }

        if (in_array($transactionStatus, ['settlement', 'paid', 'success']) ||
            ($transactionStatus === 'capture' && $fraudStatus === 'accept')) {

            $member = \App\Http\Controllers\OnlineRegistrationController::settleRegistration($pending);

            Log::info("Online Registration {$pending->invoice_number} successfully SETTLED for Member {$member->member_code}.");

            return response()->json([
                'success' => true,
                'message' => 'Online registration payment verified and member account activated',
                'member_code' => $member->member_code,
            ]);
        }

        if (in_array($transactionStatus, ['cancel', 'deny', 'expire', 'cancelled'])) {
            $pending->update(['status' => 'cancelled']);
            return response()->json([
                'success' => true,
                'message' => "Online registration marked as cancelled ({$transactionStatus})",
            ]);
        }

        return response()->json([
            'success' => true,
            'message' => 'Registration payment status received: ' . $transactionStatus,
        ]);
    }
}
