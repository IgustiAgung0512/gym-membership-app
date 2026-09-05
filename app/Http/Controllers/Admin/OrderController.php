<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use Illuminate\Http\Request;

class OrderController extends Controller
{
    public function index(Request $request)
    {
        $query = Order::with(['items', 'cashier', 'member.user']);

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('invoice_number', 'like', "%{$search}%")
                    ->orWhere('customer_name', 'like', "%{$search}%");
            });
        }

        if ($request->filled('payment_method') && $request->payment_method !== 'all') {
            $query->where('payment_method', $request->payment_method);
        }

        if ($request->filled('start_date')) {
            $query->whereDate('created_at', '>=', $request->start_date);
        }

        if ($request->filled('end_date')) {
            $query->whereDate('created_at', '<=', $request->end_date);
        }

        $orders = $query->latest()->paginate(15)->withQueryString();

        // Summary statistics
        $totalRevenue = Order::where('payment_status', 'paid')->sum('total_amount');
        $totalCost = Order::where('payment_status', 'paid')->sum('cost_total');
        $totalProfit = $totalRevenue - $totalCost;
        $totalTransactions = Order::where('payment_status', 'paid')->count();

        return view('admin.orders.index', compact(
            'orders',
            'totalRevenue',
            'totalProfit',
            'totalTransactions'
        ));
    }

    public function show(Order $order)
    {
        $order->load(['items.product', 'cashier', 'member.user']);
        return view('admin.orders.show', compact('order'));
    }

    public function destroy(Order $order)
    {
        // Opsional: kembalikan stok saat pesanan dibatalkan/dihapus
        foreach ($order->items as $item) {
            if ($item->product) {
                $item->product->increment('stock', $item->quantity);
            }
        }

        $order->delete();

        return redirect()->route('admin.orders.index')
            ->with('success', 'Transaksi berhasil dibatalkan dan stok produk telah dikembalikan.');
    }
}
