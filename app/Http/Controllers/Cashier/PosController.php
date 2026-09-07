<?php

namespace App\Http\Controllers\Cashier;

use App\Http\Controllers\Controller;
use App\Models\Member;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Services\QrisService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class PosController extends Controller
{
    public function index()
    {
        $products = Product::where('is_active', true)
            ->orderBy('category')
            ->orderBy('name')
            ->get();

        $members = Member::with('user')
            ->where('status', 'active')
            ->orderBy('member_code')
            ->get();

        $recentOrders = Order::with('items')
            ->where('created_by', Auth::id())
            ->whereDate('created_at', today())
            ->latest()
            ->take(5)
            ->get();

        // Shift metrics for current cashier today
        $shiftOrdersToday = Order::where('created_by', Auth::id())
            ->whereDate('created_at', today())
            ->where('payment_status', 'paid')
            ->get();

        $shiftCashInDrawer = $shiftOrdersToday->where('payment_method', 'cash')->sum('total_amount');
        $shiftNonCashTotal = $shiftOrdersToday->whereIn('payment_method', ['qris', 'transfer', 'other'])->sum('total_amount');
        $shiftTotalSales = $shiftOrdersToday->sum('total_amount');
        $shiftTransactionsCount = $shiftOrdersToday->count();

        return view('cashier.pos.index', compact(
            'products',
            'members',
            'recentOrders',
            'shiftCashInDrawer',
            'shiftNonCashTotal',
            'shiftTotalSales',
            'shiftTransactionsCount'
        ));
    }

    public function checkout(Request $request)
    {
        $request->validate([
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.quantity' => 'required|integer|min:1',
            'payment_method' => 'required|in:cash,qris,transfer,other',
            'cash_received' => 'nullable|numeric|min:0',
            'discount' => 'nullable|numeric|min:0',
            'member_id' => 'nullable|exists:members,id',
            'customer_name' => 'nullable|string|max:100',
            'notes' => 'nullable|string',
        ]);

        return DB::transaction(function () use ($request) {
            $subtotal = 0;
            $costTotal = 0;
            $itemsData = [];

            // 1. Validasi stok dan hitung total
            foreach ($request->items as $item) {
                $product = Product::lockForUpdate()->find($item['product_id']);

                if ($product->stock < $item['quantity']) {
                    return response()->json([
                        'success' => false,
                        'message' => "Stok untuk produk '{$product->name}' tidak mencukupi (sisa: {$product->stock}).",
                    ], 422);
                }

                $itemSubtotal = $product->price * $item['quantity'];
                $itemCost = $product->cost_price * $item['quantity'];

                $subtotal += $itemSubtotal;
                $costTotal += $itemCost;

                $itemsData[] = [
                    'product' => $product,
                    'quantity' => $item['quantity'],
                    'unit_price' => $product->price,
                    'cost_price' => $product->cost_price,
                    'subtotal' => $itemSubtotal,
                ];
            }

            $discount = (float) ($request->discount ?? 0);
            $totalAmount = max($subtotal - $discount, 0);

            // 2. Tentukan nama customer
            $customerName = 'Tamu / Walk-in';
            if ($request->filled('member_id')) {
                $member = Member::with('user')->find($request->member_id);
                $customerName = $member ? "{$member->user->name} ({$member->member_code})" : 'Member';
            } elseif ($request->filled('customer_name')) {
                $customerName = $request->customer_name;
            }

            // 3. Kalkulasi uang kembalian
            $cashReceived = $request->payment_method === 'cash' ? (float) $request->cash_received : $totalAmount;
            $cashChange = $request->payment_method === 'cash' ? max($cashReceived - $totalAmount, 0) : 0;

            if ($request->payment_method === 'cash' && $cashReceived < $totalAmount) {
                return response()->json([
                    'success' => false,
                    'message' => 'Uang tunai yang diterima kurang dari total pembayaran!',
                ], 422);
            }

            // 4. Generate Invoice Number: POS-YYMMDD-XXXX
            $todayCount = Order::whereDate('created_at', today())->count() + 1;
            $invoiceNumber = 'POS-' . now()->format('ymd') . '-' . str_pad($todayCount, 4, '0', STR_PAD_LEFT);

            // Initial status: QRIS starts as 'pending', others as 'paid'
            $initialPaymentStatus = $request->payment_method === 'qris' ? 'pending' : 'paid';

            // 5. Simpan Order
            $order = Order::create([
                'invoice_number' => $invoiceNumber,
                'member_id' => $request->member_id,
                'customer_name' => $customerName,
                'subtotal' => $subtotal,
                'discount' => $discount,
                'total_amount' => $totalAmount,
                'cost_total' => $costTotal,
                'payment_method' => $request->payment_method,
                'cash_received' => $cashReceived,
                'cash_change' => $cashChange,
                'payment_status' => $initialPaymentStatus,
                'notes' => $request->notes,
                'created_by' => Auth::id(),
            ]);

            // 6. Simpan Items & Potong Stok
            foreach ($itemsData as $data) {
                OrderItem::create([
                    'order_id' => $order->id,
                    'product_id' => $data['product']->id,
                    'product_name' => $data['product']->name,
                    'unit_price' => $data['unit_price'],
                    'cost_price' => $data['cost_price'],
                    'quantity' => $data['quantity'],
                    'subtotal' => $data['subtotal'],
                ]);

                $data['product']->decrement('stock', $data['quantity']);
            }

            // If QRIS: Generate Dynamic QRIS & Return URLs
            if ($request->payment_method === 'qris') {
                $qrisData = QrisService::generate($order);

                return response()->json([
                    'success' => true,
                    'is_qris' => true,
                    'message' => 'QRIS Dinamis berhasil dibuat. Menunggu pembayaran...',
                    'order' => $order->load('items'),
                    'qris' => $qrisData,
                    'status_url' => route('cashier.pos.order-status', $order->id),
                    'simulate_url' => route('cashier.pos.simulate-qris', $order->id),
                    'cancel_url' => route('cashier.pos.cancel-order', $order->id),
                    'receipt_url' => route('cashier.pos.receipt', $order->id),
                ]);
            }

            return response()->json([
                'success' => true,
                'is_qris' => false,
                'message' => 'Transaksi berhasil diproses!',
                'order' => $order->load('items'),
                'receipt_url' => route('cashier.pos.receipt', $order->id),
            ]);
        });
    }

    /**
     * Check real-time payment status of an order
     */
    public function orderStatus(Order $order)
    {
        return response()->json([
            'success' => true,
            'order_id' => $order->id,
            'invoice_number' => $order->invoice_number,
            'payment_status' => $order->payment_status,
            'total_amount' => $order->total_amount,
            'is_paid' => $order->payment_status === 'paid',
            'order' => $order->load('items'),
            'receipt_url' => route('cashier.pos.receipt', $order->id),
        ]);
    }

    /**
     * Simulate buyer scanning and paying QRIS successfully (Development/Testing Only)
     */
    public function simulateQris(Order $order)
    {
        if (app()->isProduction() || config('services.midtrans.is_production', false)) {
            Log::warning("Blocked POS simulation attempt in production for Order: {$order->invoice_number}");
            return response()->json([
                'success' => false,
                'message' => 'Mode simulasi kasir dinonaktifkan di lingkungan produksi (Production).',
            ], 403);
        }

        if ($order->payment_status === 'pending') {
            $order->update([
                'payment_status' => 'paid',
                'cash_received' => $order->total_amount,
                'cash_change' => 0,
            ]);

            Log::info("Cashier simulated QRIS payment for Order {$order->invoice_number}");
        }

        return response()->json([
            'success' => true,
            'message' => 'Pembayaran QRIS berhasil diselesaikan oleh pembeli!',
            'order' => $order->fresh()->load('items'),
            'receipt_url' => route('cashier.pos.receipt', $order->id),
        ]);
    }

    /**
     * Cancel a pending QRIS order and restore product stock
     */
    public function cancelOrder(Order $order)
    {
        if ($order->payment_status === 'pending') {
            DB::transaction(function () use ($order) {
                // Restore stock
                foreach ($order->items as $item) {
                    if ($item->product_id) {
                        Product::where('id', $item->product_id)->increment('stock', $item->quantity);
                    }
                }
                $order->update(['payment_status' => 'cancelled']);
            });

            return response()->json([
                'success' => true,
                'message' => 'Pesanan QRIS berhasil dibatalkan dan stok dikembalikan.',
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => 'Pesanan ini tidak dapat dibatalkan.',
        ], 400);
    }

    public function receipt(Order $order)
    {
        $order->load(['items', 'cashier', 'member.user']);
        return view('admin.pos.receipt', compact('order'));
    }

    /**
     * Get member orders ready for pickup / handed over
     */
    public function getMemberPickups(Request $request)
    {
        $query = Order::with(['items.product', 'member.user', 'picker'])
            ->where('payment_status', 'paid');

        if ($request->filled('status')) {
            if ($request->status === 'ready') {
                $query->where('pickup_status', 'ready_for_pickup');
            } elseif ($request->status === 'picked_up') {
                $query->where('pickup_status', 'picked_up');
            }
        } else {
            // Default: show ready_for_pickup or today's orders
            $query->where(function ($q) {
                $q->where('pickup_status', 'ready_for_pickup')
                  ->orWhereDate('created_at', today());
            });
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('invoice_number', 'like', "%{$search}%")
                  ->orWhere('customer_name', 'like', "%{$search}%");
            });
        }

        $orders = $query->latest()->take(30)->get();
        $pendingCount = Order::where('payment_status', 'paid')
            ->where('pickup_status', 'ready_for_pickup')
            ->count();

        return response()->json([
            'success' => true,
            'pending_count' => $pendingCount,
            'orders' => $orders,
        ]);
    }

    /**
     * Mark member order as picked up by member
     */
    public function markAsPickedUp(Order $order)
    {
        $order->update([
            'pickup_status' => 'picked_up',
            'picked_up_at' => now(),
            'picked_up_by' => Auth::id(),
        ]);

        return response()->json([
            'success' => true,
            'message' => "Pesanan {$order->invoice_number} berhasil diserahkan kepada member.",
            'order' => $order->fresh()->load(['items.product', 'member.user', 'picker']),
        ]);
    }
}
