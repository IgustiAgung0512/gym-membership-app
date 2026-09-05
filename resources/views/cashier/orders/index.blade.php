@extends('layouts.cashier')
@section('title', 'Riwayat Shift Transaksi Kasir')

@section('content')
<div class="space-y-6">

    {{-- HEADER & CASH DRAWER RECONCILIATION --}}
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h1 class="text-xl font-display font-bold text-slate-900">Rekap Kas & Riwayat Transaksi Shift</h1>
            <p class="text-xs text-slate-500 mt-0.5">Pantau uang kas di laci kasir dan riwayat pesanan yang Anda layani hari ini.</p>
        </div>
        <a href="{{ route('cashier.pos.index') }}" class="inline-flex items-center gap-2 px-4 py-2 rounded-xl bg-lime-500 hover:bg-lime-400 text-slate-950 text-xs font-bold transition shadow-sm self-start">
            <span>🛒 Buka Kasir POS</span>
        </a>
    </div>

    {{-- RECAP CARDS --}}
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
        <div class="bg-white border border-slate-200 rounded-2xl p-5 shadow-sm">
            <div class="w-10 h-10 rounded-xl bg-lime-100 text-lime-800 flex items-center justify-center mb-3">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V6m0 10v2m9-8a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            </div>
            <p class="text-xl font-display font-extrabold text-slate-900">Rp{{ number_format($todayCashInDrawer, 0, ',', '.') }}</p>
            <p class="text-xs text-slate-500 mt-0.5 font-medium">Uang Tunai di Laci Kasir (Hari Ini)</p>
        </div>

        <div class="bg-white border border-slate-200 rounded-2xl p-5 shadow-sm">
            <div class="w-10 h-10 rounded-xl bg-blue-100 text-blue-800 flex items-center justify-center mb-3">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
            </div>
            <p class="text-xl font-display font-extrabold text-blue-900">Rp{{ number_format($todayQris, 0, ',', '.') }}</p>
            <p class="text-xs text-slate-500 mt-0.5 font-medium">Pembayaran QRIS (Hari Ini)</p>
        </div>

        <div class="bg-white border border-slate-200 rounded-2xl p-5 shadow-sm">
            <div class="w-10 h-10 rounded-xl bg-indigo-100 text-indigo-800 flex items-center justify-center mb-3">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 10h18M7 15h1m4 0h1m-7 4h16a1 1 0 001-1V6a1 1 0 00-1-1H4a1 1 0 00-1 1v12a1 1 0 001 1z"/></svg>
            </div>
            <p class="text-xl font-display font-extrabold text-indigo-900">Rp{{ number_format($todayTransfer, 0, ',', '.') }}</p>
            <p class="text-xs text-slate-500 mt-0.5 font-medium">Transfer Bank (Hari Ini)</p>
        </div>

        <div class="bg-white border border-slate-200 rounded-2xl p-5 shadow-sm">
            <div class="w-10 h-10 rounded-xl bg-emerald-100 text-emerald-800 flex items-center justify-center mb-3">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            </div>
            <p class="text-xl font-display font-extrabold text-emerald-700">Rp{{ number_format($todayTotal, 0, ',', '.') }}</p>
            <p class="text-xs text-slate-500 mt-0.5 font-medium">Total Shift ({{ $todayCount }} Transaksi)</p>
        </div>
    </div>

    {{-- FILTER BAR --}}
    <div class="bg-white p-4 rounded-2xl border border-slate-200 shadow-sm">
        <form method="GET" action="{{ route('cashier.orders.index') }}" class="grid grid-cols-1 sm:grid-cols-4 gap-3 items-end">
            <div>
                <label class="block text-xs font-semibold text-slate-500 mb-1">Dari Tanggal</label>
                <input type="date" name="from" value="{{ request('from', today()->format('Y-m-d')) }}" class="w-full bg-slate-50 border border-slate-200 rounded-xl px-3 py-2 text-xs focus:outline-none focus:ring-2 focus:ring-lime-500">
            </div>
            <div>
                <label class="block text-xs font-semibold text-slate-500 mb-1">Sampai Tanggal</label>
                <input type="date" name="to" value="{{ request('to', today()->format('Y-m-d')) }}" class="w-full bg-slate-50 border border-slate-200 rounded-xl px-3 py-2 text-xs focus:outline-none focus:ring-2 focus:ring-lime-500">
            </div>
            <div>
                <label class="block text-xs font-semibold text-slate-500 mb-1">Metode Pembayaran</label>
                <select name="payment_method" class="w-full bg-slate-50 border border-slate-200 rounded-xl px-3 py-2 text-xs focus:outline-none focus:ring-2 focus:ring-lime-500">
                    <option value="">Semua Metode</option>
                    <option value="cash" {{ request('payment_method') === 'cash' ? 'selected' : '' }}>Tunai (Cash)</option>
                    <option value="qris" {{ request('payment_method') === 'qris' ? 'selected' : '' }}>QRIS</option>
                    <option value="transfer" {{ request('payment_method') === 'transfer' ? 'selected' : '' }}>Transfer Bank</option>
                </select>
            </div>
            <div class="flex gap-2">
                <button type="submit" class="flex-1 py-2 rounded-xl bg-slate-900 text-white font-bold text-xs hover:bg-slate-800 transition">
                    Filter
                </button>
                <a href="{{ route('cashier.orders.index') }}" class="px-3 py-2 rounded-xl border border-slate-200 text-slate-600 font-bold text-xs hover:bg-slate-50 transition flex items-center justify-center">
                    Reset
                </a>
            </div>
        </form>
    </div>

    {{-- ORDERS TABLE --}}
    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs text-slate-600">
                <thead class="bg-slate-50 border-b border-slate-200 text-[11px] uppercase font-bold text-slate-400">
                    <tr>
                        <th class="py-3 px-4">No. Faktur</th>
                        <th class="py-3 px-4">Waktu</th>
                        <th class="py-3 px-4">Pelanggan</th>
                        <th class="py-3 px-4">Item Produk</th>
                        <th class="py-3 px-4">Total Bayar</th>
                        <th class="py-3 px-4">Metode</th>
                        <th class="py-3 px-4 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse ($orders as $o)
                        <tr class="hover:bg-slate-50 transition">
                            <td class="py-3 px-4 font-mono font-bold text-slate-900">
                                {{ $o->order_number }}
                            </td>
                            <td class="py-3 px-4 text-slate-500">
                                {{ $o->created_at->format('d/m/Y H:i') }}
                            </td>
                            <td class="py-3 px-4 font-semibold text-slate-800">
                                {{ $o->customer_name }}
                            </td>
                            <td class="py-3 px-4">
                                <span class="bg-slate-100 text-slate-700 px-2 py-0.5 rounded font-bold">
                                    {{ $o->items->sum('quantity') }} pcs
                                </span>
                                <span class="text-[11px] text-slate-400 ml-1 truncate max-w-xs inline-block align-middle">
                                    ({{ $o->items->pluck('product_name')->implode(', ') }})
                                </span>
                            </td>
                            <td class="py-3 px-4 font-mono font-extrabold text-slate-900">
                                Rp{{ number_format($o->total_amount, 0, ',', '.') }}
                            </td>
                            <td class="py-3 px-4">
                                @if ($o->payment_method === 'cash')
                                    <span class="px-2 py-0.5 rounded-full bg-lime-100 text-lime-800 font-bold text-[10px]">💵 TUNAI</span>
                                @elseif ($o->payment_method === 'qris')
                                    <span class="px-2 py-0.5 rounded-full bg-blue-100 text-blue-800 font-bold text-[10px]">📱 QRIS</span>
                                @else
                                    <span class="px-2 py-0.5 rounded-full bg-indigo-100 text-indigo-800 font-bold text-[10px]">🏦 TRANSFER</span>
                                @endif
                            </td>
                            <td class="py-3 px-4 text-right">
                                <a 
                                    href="javascript:void(0)" 
                                    onclick="window.open('{{ route('cashier.pos.receipt', $o->id) }}', '_blank', 'width=400,height=600')"
                                    class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg bg-slate-100 hover:bg-slate-200 text-slate-800 text-[11px] font-bold transition"
                                >
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
                                    Cetak Struk
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="py-10 text-center text-slate-400">
                                Belum ada transaksi yang diproses pada rentang tanggal ini.
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
