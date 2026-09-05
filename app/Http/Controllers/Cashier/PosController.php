<?php

namespace App\Http\Controllers\Cashier;

use App\Http\Controllers\Controller;
use App\Models\Member;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

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
                'payment_status' => 'paid',
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

            return response()->json([
                'success' => true,
                'message' => 'Transaksi berhasil diproses!',
                'order' => $order->load('items'),
                'receipt_url' => route('cashier.pos.receipt', $order->id),
            ]);
        });
    }

    public function receipt(Order $order)
    {
        $order->load(['items', 'cashier', 'member.user']);
        return view('admin.pos.receipt', compact('order'));
    }
}
