@extends('layouts.admin')
@section('title', 'Riwayat Penjualan Toko')

@section('content')
<div class="space-y-6">

    {{-- FLASH MESSAGES --}}
    @if (session('success'))
        <div class="rounded-2xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800 flex items-center gap-2 shadow-sm">
            <svg class="w-4 h-4 shrink-0 text-emerald-600" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
            <span class="font-medium">{{ session('success') }}</span>
        </div>
    @endif

    {{-- HEADER & ACTIONS --}}
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <div class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-xs font-bold bg-lime-100 text-lime-800 uppercase tracking-wider mb-1">
                <span>🧾</span>
                <span>Laporan Penjualan</span>
            </div>
            <h2 class="font-display font-bold text-2xl sm:text-3xl text-slate-900 tracking-tight">
                Riwayat Transaksi Gym Store
            </h2>
            <p class="text-xs sm:text-sm text-slate-500 mt-0.5">
                Daftar nota transaksi penjualan produk, metode pembayaran, dan rincian item.
            </p>
        </div>

        <div class="flex items-center gap-2">
            <a href="{{ route('admin.pos.index') }}" class="px-4 py-2.5 rounded-xl bg-lime-500 hover:bg-lime-400 text-slate-950 text-xs font-bold transition shadow-sm inline-flex items-center gap-1.5">
                <span>🛒 Buka Kasir POS</span>
            </a>
            <a href="{{ route('admin.products.index') }}" class="px-4 py-2.5 rounded-xl bg-white border border-slate-200 text-slate-700 hover:bg-slate-50 text-xs font-bold transition shadow-sm inline-flex items-center gap-1.5">
                <span>📦 Stok Produk</span>
            </a>
        </div>
    </div>

    {{-- SUMMARY KPI CARDS --}}
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
        <div class="bg-white border border-slate-200 rounded-2xl p-4 sm:p-5 shadow-sm">
            <span class="text-xs font-semibold text-slate-400 uppercase tracking-wider">Total Omzet Penjualan</span>
            <p class="font-display text-2xl sm:text-3xl font-extrabold text-slate-900 mt-1">
                Rp{{ number_format($totalRevenue, 0, ',', '.') }}
            </p>
        </div>

        <div class="bg-white border border-slate-200 rounded-2xl p-4 sm:p-5 shadow-sm">
            <span class="text-xs font-semibold text-slate-400 uppercase tracking-wider">Estimasi Laba Bersih Toko</span>
            <p class="font-display text-2xl sm:text-3xl font-extrabold text-emerald-600 mt-1">
                Rp{{ number_format($totalProfit, 0, ',', '.') }}
            </p>
        </div>

        <div class="bg-white border border-slate-200 rounded-2xl p-4 sm:p-5 shadow-sm">
            <span class="text-xs font-semibold text-slate-400 uppercase tracking-wider">Total Transaksi</span>
            <p class="font-display text-2xl sm:text-3xl font-extrabold text-slate-900 mt-1">
                {{ $totalTransactions }} <span class="text-xs font-sans text-slate-500 font-normal">nota</span>
            </p>
        </div>
    </div>

    {{-- FILTER & SEARCH TOOLBAR --}}
    <div class="bg-white border border-slate-200 rounded-2xl p-4 shadow-sm">
        <form method="GET" action="{{ route('admin.orders.index') }}" class="grid grid-cols-1 sm:grid-cols-12 gap-3 text-xs">
            <div class="sm:col-span-4 relative">
                <input 
                    type="text" 
                    name="search" 
                    value="{{ request('search') }}" 
                    placeholder="Cari nomor invoice / customer..." 
                    class="w-full pl-9 pr-4 py-2 rounded-xl border border-slate-200 bg-slate-50 focus:bg-white text-xs focus:outline-none"
                >
                <svg class="w-4 h-4 text-slate-400 absolute left-3 top-2.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
            </div>

            <div class="sm:col-span-3">
                <select name="payment_method" onchange="this.form.submit()" class="w-full py-2 px-3 rounded-xl border border-slate-200 bg-slate-50 text-xs focus:outline-none">
                    <option value="all">Semua Metode Pembayaran</option>
                    <option value="cash" {{ request('payment_method') == 'cash' ? 'selected' : '' }}>💵 Tunai (Cash)</option>
                    <option value="qris" {{ request('payment_method') == 'qris' ? 'selected' : '' }}>📱 QRIS</option>
                    <option value="transfer" {{ request('payment_method') == 'transfer' ? 'selected' : '' }}>🏦 Transfer Bank</option>
                </select>
            </div>

            <div class="sm:col-span-2">
                <input type="date" name="start_date" value="{{ request('start_date') }}" class="w-full py-2 px-3 rounded-xl border border-slate-200 bg-slate-50 text-xs focus:outline-none" title="Dari Tanggal">
            </div>

            <div class="sm:col-span-2">
                <input type="date" name="end_date" value="{{ request('end_date') }}" class="w-full py-2 px-3 rounded-xl border border-slate-200 bg-slate-50 text-xs focus:outline-none" title="Sampai Tanggal">
            </div>

            <div class="sm:col-span-1">
                <button type="submit" class="w-full py-2 bg-slate-900 text-white rounded-xl font-bold hover:bg-slate-800 transition">
                    Filter
                </button>
            </div>
        </form>
    </div>

    {{-- ORDERS TABLE --}}
    <div class="bg-white border border-slate-200 rounded-3xl overflow-hidden shadow-sm">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead class="bg-slate-50 border-b border-slate-200 text-slate-500 font-bold uppercase tracking-wider">
                    <tr>
                        <th class="py-3.5 px-4">Invoice / Waktu</th>
                        <th class="py-3.5 px-4">Customer</th>
                        <th class="py-3.5 px-4">Item Belanja</th>
                        <th class="py-3.5 px-4">Metode Bayar</th>
                        <th class="py-3.5 px-4 text-right">Total Bayar</th>
                        <th class="py-3.5 px-4 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-slate-700">
                    @forelse ($orders as $order)
                        <tr class="hover:bg-slate-50/80 transition">
                            <td class="py-3.5 px-4">
                                <p class="font-mono font-bold text-slate-900 text-sm">{{ $order->invoice_number }}</p>
                                <p class="text-[11px] text-slate-400 mt-0.5">{{ $order->created_at->translatedFormat('d M Y, H:i') }} WIB</p>
                                <p class="text-[10px] text-slate-400">Kasir: {{ $order->cashier->name ?? 'Admin' }}</p>
                            </td>

                            <td class="py-3.5 px-4">
                                <p class="font-bold text-slate-900">{{ $order->customer_name }}</p>
                                @if ($order->member)
                                    <span class="inline-flex items-center text-[10px] font-bold text-lime-800 bg-lime-100 px-2 py-0.5 rounded-full mt-0.5">
                                        Member: {{ $order->member->member_code }}
                                    </span>
                                @else
                                    <span class="text-[10px] text-slate-400">Non-Member</span>
                                @endif
                            </td>

                            <td class="py-3.5 px-4">
                                <div class="space-y-1">
                                    @foreach ($order->items as $item)
                                        <div class="text-[11px] text-slate-600">
                                            • <strong>{{ $item->quantity }}x</strong> {{ $item->product_name }}
                                        </div>
                                    @endforeach
                                </div>
                            </td>

                            <td class="py-3.5 px-4">
                                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-semibold bg-slate-100 text-slate-700">
                                    <span>{{ $order->payment_method_icon }}</span>
                                    <span>{{ $order->payment_method_label }}</span>
                                </span>
                            </td>

                            <td class="py-3.5 px-4 text-right">
                                <p class="font-mono font-extrabold text-sm text-slate-900">
                                    Rp{{ number_format($order->total_amount, 0, ',', '.') }}
                                </p>
                                <span class="text-[10px] font-bold text-emerald-700 bg-emerald-50 px-2 py-0.5 rounded">
                                    LUNAS
                                </span>
                            </td>

                            <td class="py-3.5 px-4 text-right">
                                <div class="flex items-center justify-end gap-1.5">
                                    <button 
                                        onclick="window.open('{{ route('admin.pos.receipt', $order->id) }}', '_blank', 'width=420,height=650')"
                                        class="px-2.5 py-1 rounded-lg bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold transition text-[11px]"
                                        title="Cetak Ulang Struk Nota"
                                    >
                                        🖨️ Struk
                                    </button>

                                    <form method="POST" action="{{ route('admin.orders.destroy', $order->id) }}" onsubmit="return confirm('Batalkan transaksi ini? Stok barang akan dikembalikan.')" class="inline">
                                        @csrf
                                        @method('DELETE')
                                        <button class="p-1.5 rounded-lg bg-rose-50 hover:bg-rose-100 text-rose-600 border border-rose-200 transition" title="Batalkan Transaksi">
                                            🗑️
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="py-12 text-center text-slate-400">
                                <p class="text-3xl mb-1">🧾</p>
                                <p class="font-bold text-slate-600">Belum ada riwayat penjualan.</p>
                                <p class="text-xs text-slate-400 mt-1">Buka menu Kasir POS untuk mulai melayani transaksi penjualan produk.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($orders->hasPages())
            <div class="p-4 border-t border-slate-100">
                {{ $orders->links() }}
            </div>
        @endif
    </div>

</div>
@endsection
