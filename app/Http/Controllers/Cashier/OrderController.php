<?php

namespace App\Http\Controllers\Cashier;

use App\Http\Controllers\Controller;
use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class OrderController extends Controller
{
    public function index(Request $request)
    {
        $query = Order::with(['items', 'cashier', 'member.user'])
            ->where('created_by', Auth::id());

        // Default: hari ini, atau sesuai filter tanggal
        if ($request->filled('from') && $request->filled('to')) {
            $query->whereBetween('created_at', [
                $request->from . ' 00:00:00',
                $request->to . ' 23:59:59'
            ]);
        } else {
            $query->whereDate('created_at', today());
        }

        if ($request->filled('payment_method')) {
            $query->where('payment_method', $request->payment_method);
        }

        $orders = $query->latest()->paginate(15)->withQueryString();

        // Rekap kas shift hari ini
        $todayOrders = Order::where('created_by', Auth::id())
            ->whereDate('created_at', today())
            ->where('payment_status', 'paid')
            ->get();

        $todayCashInDrawer = $todayOrders->where('payment_method', 'cash')->sum('total_amount');
        $todayQris = $todayOrders->where('payment_method', 'qris')->sum('total_amount');
        $todayTransfer = $todayOrders->where('payment_method', 'transfer')->sum('total_amount');
        $todayTotal = $todayOrders->sum('total_amount');
        $todayCount = $todayOrders->count();

        return view('cashier.orders.index', compact(
            'orders',
            'todayCashInDrawer',
            'todayQris',
            'todayTransfer',
            'todayTotal',
            'todayCount'
        ));
    }

    public function show(Order $order)
    {
        $order->load(['items', 'cashier', 'member.user']);
        return view('admin.orders.show', compact('order'));
    }
}
