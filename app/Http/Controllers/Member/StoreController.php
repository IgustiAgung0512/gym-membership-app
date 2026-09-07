<?php

namespace App\Http\Controllers\Member;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Services\QrisService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class StoreController extends Controller
{
    /**
     * Tampilan katalog toko member
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        $member = $user->member;

        $query = Product::where('is_active', true);

        if ($request->filled('category') && $request->category !== 'all') {
            $query->where('category', $request->category);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%");
            });
        }

        $products = $query->orderBy('category')->orderBy('name')->get();

        $categories = [
            'all' => ['label' => 'Semua Produk', 'icon' => '🏷️'],
            'drinks' => ['label' => 'Minuman & Elektrolit', 'icon' => '🥤'],
            'supplements' => ['label' => 'Suplemen & Whey', 'icon' => '💪'],
            'snacks' => ['label' => 'Camilan Sehat', 'icon' => '🥑'],
            'gear' => ['label' => 'Aksesoris Gym', 'icon' => '🏋️'],
        ];

        // Ambil riwayat pesanan aktif & terbaru member
        $myOrders = Order::where('member_id', $member->id)
            ->whereIn('payment_status', ['paid', 'pending'])
            ->with(['items.product'])
            ->latest()
            ->take(10)
            ->get();

        return view('member.store.index', compact('products', 'categories', 'member', 'myOrders'));
    }

    /**
     * Checkout pesanan via QRIS Dinamis
     */
    public function checkout(Request $request)
    {
        $user = Auth::user();
        $member = $user->member;

        $request->validate([
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.quantity' => 'required|integer|min:1',
            'notes' => 'nullable|string|max:255',
        ]);

        return DB::transaction(function () use ($request, $user, $member) {
            $subtotal = 0;
            $costTotal = 0;
            $itemsData = [];

            // 1. Kunci baris produk & validasi ketersediaan stok
            foreach ($request->items as $item) {
                $product = Product::lockForUpdate()->find($item['product_id']);

                if (!$product || !$product->is_active) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Produk tidak ditemukan atau sedang dinonaktifkan.',
                    ], 422);
                }

                if ($product->stock < $item['quantity']) {
                    return response()->json([
                        'success' => false,
                        'message' => "Stok produk '{$product->name}' tidak mencukupi (Tersedia: {$product->stock} {$product->unit}).",
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

            // 2. Generate Invoice Number: MBR-YYMMDD-XXXX
            $todayCount = Order::whereDate('created_at', today())->count() + 1;
            $invoiceNumber = 'MBR-' . now()->format('ymd') . '-' . str_pad($todayCount, 4, '0', STR_PAD_LEFT);
            $customerName = "{$user->name} ({$member->member_code})";

            // 3. Buat pesanan baru
            $order = Order::create([
                'invoice_number' => $invoiceNumber,
                'member_id' => $member->id,
                'customer_name' => $customerName,
                'subtotal' => $subtotal,
                'discount' => 0,
                'total_amount' => $subtotal,
                'cost_total' => $costTotal,
                'payment_method' => 'qris',
                'cash_received' => $subtotal,
                'cash_change' => 0,
                'payment_status' => 'pending',
                'pickup_status' => 'ready_for_pickup',
                'notes' => $request->notes ?? 'Pesanan Mandiri via Portal Member',
                'created_by' => $user->id,
            ]);

            // 4. Simpan Order Items & potong stok di database secara langsung
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

                // Kurangi stok di kasir secara atomik
                $data['product']->decrement('stock', $data['quantity']);
            }

            // 5. Generate QRIS Payload
            $qrisData = QrisService::generate($order);

            return response()->json([
                'success' => true,
                'message' => 'QRIS pembayaran berhasil dibuat. Silakan scan untuk membayar.',
                'order' => $order->load('items.product'),
                'qris' => $qrisData,
                'status_url' => route('member.store.order-status', $order->id),
                'simulate_url' => route('member.store.simulate-qris', $order->id),
                'cancel_url' => route('member.store.cancel-order', $order->id),
                'receipt_url' => route('member.store.receipt', $order->id),
            ]);
        });
    }

    /**
     * Polling status pembayaran QRIS pesanan member
     */
    public function orderStatus(Order $order)
    {
        $member = Auth::user()->member;
        if ($order->member_id !== $member->id) {
            return response()->json(['success' => false, 'message' => 'Akses ditolak.'], 403);
        }

        return response()->json([
            'success' => true,
            'payment_status' => $order->payment_status,
            'pickup_status' => $order->pickup_status,
            'is_paid' => $order->payment_status === 'paid',
            'order' => $order->load('items.product'),
            'receipt_url' => route('member.store.receipt', $order->id),
        ]);
    }

    /**
     * Simulator pembayaran sukses untuk pengujian (Hanya aktif di Mode Non-Produksi)
     */
    public function simulateQris(Order $order)
    {
        $member = Auth::user()->member;
        if ($order->member_id !== $member->id) {
            return response()->json(['success' => false, 'message' => 'Akses ditolak: Anda bukan pemilik pesanan ini.'], 403);
        }

        // Keamanan: Tolak pembayaran simulasi jika berjalan di environment produksi
        if (app()->isProduction() || config('services.midtrans.is_production', false)) {
            Log::warning("Blocked simulation attempt in production for Order: {$order->invoice_number} by Member: {$member->id}");
            return response()->json([
                'success' => false,
                'message' => 'Mode simulasi dinonaktifkan di lingkungan produksi (Production). Silakan lakukan pembayaran menggunakan QRIS resmi.',
            ], 403);
        }

        if ($order->payment_status === 'pending') {
            $order->update([
                'payment_status' => 'paid',
                'cash_received' => $order->total_amount,
                'cash_change' => 0,
                'pickup_status' => 'ready_for_pickup',
            ]);

            Log::info("Simulated payment SUCCESS for Order {$order->invoice_number} by Member ID {$member->id}");
        }

        return response()->json([
            'success' => true,
            'message' => 'Simulasi pembayaran QRIS berhasil! Pesanan siap diambil di kasir.',
            'order' => $order->fresh()->load('items.product'),
            'receipt_url' => route('member.store.receipt', $order->id),
        ]);
    }

    /**
     * Batalkan pesanan QRIS yang belum dibayar & kembalikan stok
     */
    public function cancelOrder(Order $order)
    {
        $member = Auth::user()->member;
        if ($order->member_id !== $member->id) {
            return response()->json(['success' => false, 'message' => 'Akses ditolak.'], 403);
        }

        if ($order->payment_status !== 'pending') {
            return response()->json([
                'success' => false,
                'message' => 'Pesanan yang sudah dibayar tidak dapat dibatalkan mandiri. Hubungi kasir.',
            ], 422);
        }

        return DB::transaction(function () use ($order) {
            // Kembalikan stok produk
            foreach ($order->items as $item) {
                if ($item->product) {
                    $item->product->increment('stock', $item->quantity);
                }
            }

            $order->update([
                'payment_status' => 'cancelled',
                'notes' => ($order->notes ? $order->notes . ' | ' : '') . 'Dibatalkan oleh member',
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Pesanan dibatalkan dan stok produk telah dikembalikan.',
            ]);
        });
    }

    /**
     * Halaman bukti pembayaran digital resmi member
     */
    public function receipt(Order $order)
    {
        $member = Auth::user()->member;
        if ($order->member_id !== $member->id) {
            abort(403, 'Anda tidak memiliki akses ke bukti pembayaran ini.');
        }

        $order->load(['items.product', 'member.user', 'picker']);
        return view('member.store.receipt', compact('order', 'member'));
    }

    /**
     * Riwayat pesanan & bukti pembayaran member
     */
    public function myOrders()
    {
        $member = Auth::user()->member;

        $orders = Order::where('member_id', $member->id)
            ->with(['items.product', 'picker'])
            ->latest()
            ->paginate(15);

        return response()->json([
            'success' => true,
            'orders' => $orders,
        ]);
    }
}
