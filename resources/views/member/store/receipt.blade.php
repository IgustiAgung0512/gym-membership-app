@extends('layouts.member')
@section('title', 'Bukti Pembayaran - ' . $order->invoice_number)

@section('content')
<div class="max-w-2xl mx-auto space-y-6">

    {{-- TOP NAVIGATION --}}
    <div class="flex items-center justify-between">
        <a 
            href="{{ route('member.store.index') }}" 
            class="inline-flex items-center gap-2 text-xs font-bold text-slate-600 hover:text-slate-900 bg-white border border-slate-200 px-3.5 py-2 rounded-xl transition shadow-sm hover:shadow"
        >
            <span>←</span>
            <span>Kembali ke Katalog Toko</span>
        </a>

        <div class="flex items-center gap-2">
            <button 
                type="button" 
                onclick="window.print()" 
                class="inline-flex items-center gap-1.5 text-xs font-bold text-slate-800 bg-slate-100 hover:bg-slate-200 px-3.5 py-2 rounded-xl transition"
            >
                <span>🖨️</span>
                <span>Cetak Bukti</span>
            </button>
            <a 
                href="https://wa.me/?text={{ urlencode('Halo Kasir GymPulse, saya ' . $member->user->name . ' (' . $member->member_code . ') telah menyelesaikan pembayaran QRIS untuk invoice: ' . $order->invoice_number . ' senilai Rp' . number_format($order->total_amount, 0, ',', '.') . '. Mohon disiapkan pesanannya. Terima kasih!') }}" 
                target="_blank"
                class="inline-flex items-center gap-1.5 text-xs font-bold text-white bg-emerald-600 hover:bg-emerald-500 px-3.5 py-2 rounded-xl transition shadow-sm"
            >
                <span>💬</span>
                <span>Konfirmasi WA</span>
            </a>
        </div>
    </div>

    {{-- OFFICIAL RECEIPT CARD --}}
    <div class="bg-white border border-slate-200 rounded-3xl shadow-xl overflow-hidden relative">
        
        {{-- HEADER BANNER --}}
        <div class="bg-gradient-to-br from-slate-950 via-slate-900 to-slate-850 text-white p-6 sm:p-7 relative">
            <div class="absolute -right-8 -bottom-8 w-40 h-40 bg-lime-500/15 rounded-full blur-2xl pointer-events-none"></div>

            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 relative z-10">
                <div>
                    <div class="flex items-center gap-2 mb-1.5">
                        <span class="w-6 h-6 rounded-lg bg-lime-400 text-slate-950 font-black flex items-center justify-center text-xs">G</span>
                        <span class="font-display font-extrabold text-sm tracking-wider text-lime-400 uppercase">GymPulse Official Receipt</span>
                    </div>
                    <h1 class="font-display font-black text-2xl text-white">Bukti Pembayaran Digital</h1>
                    <p class="text-xs text-slate-400 mt-0.5 font-mono">Invoice: {{ $order->invoice_number }}</p>
                </div>

                <div class="sm:text-right shrink-0">
                    @if ($order->pickup_status === 'picked_up')
                        <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl bg-emerald-500/20 text-emerald-400 font-bold text-xs border border-emerald-500/30">
                            <span>✓</span>
                            <span>Barang Sudah Diambil</span>
                        </span>
                    @else
                        <span class="inline-flex items-center gap-1.5 px-3.5 py-1.5 rounded-xl bg-lime-400 text-slate-950 font-black text-xs shadow-md animate-pulse">
                            <span>📦</span>
                            <span>Siap Diambil di Kasir</span>
                        </span>
                    @endif
                    <p class="text-[11px] text-slate-400 mt-1">Status Pembayaran: <span class="text-emerald-400 font-bold uppercase">{{ $order->payment_status }} (QRIS)</span></p>
                </div>
            </div>
        </div>

        {{-- INSTRUCTION BANNER FOR FRONTDESK PICKUP --}}
        <div class="p-4 bg-lime-50 border-b border-lime-100 flex items-start gap-3">
            <span class="text-2xl shrink-0">ℹ️</span>
            <div class="text-xs text-slate-700">
                <p class="font-bold text-slate-900 mb-0.5">Petunjuk Pengambilan Barang:</p>
                <p class="leading-relaxed">
                    Tunjukkan layar bukti pembayaran ini atau sebutkan <strong>Nama Member ({{ $member->user->name }})</strong> dan <strong>Nomor Invoice ({{ $order->invoice_number }})</strong> kepada kasir front-desk gym untuk mengambil barang pesanan Anda.
                </p>
            </div>
        </div>

        {{-- RECEIPT DETAILS --}}
        <div class="p-6 sm:p-7 space-y-6">
            
            {{-- MEMBER & TRANSACTION INFO GRID --}}
            <div class="grid grid-cols-2 sm:grid-cols-4 gap-4 p-4 rounded-2xl bg-slate-50 border border-slate-200/80 text-xs">
                <div>
                    <span class="text-slate-400 block text-[11px] font-semibold">Nama Pembeli</span>
                    <span class="font-bold text-slate-900 mt-0.5 block">{{ $member->user->name }}</span>
                </div>
                <div>
                    <span class="text-slate-400 block text-[11px] font-semibold">Kode Member</span>
                    <span class="font-mono font-bold text-slate-800 mt-0.5 block">{{ $member->member_code }}</span>
                </div>
                <div>
                    <span class="text-slate-400 block text-[11px] font-semibold">Tanggal & Waktu</span>
                    <span class="font-medium text-slate-800 mt-0.5 block">{{ $order->created_at->translatedFormat('d M Y, H:i') }}</span>
                </div>
                <div>
                    <span class="text-slate-400 block text-[11px] font-semibold">Metode Bayar</span>
                    <span class="font-bold text-emerald-700 mt-0.5 flex items-center gap-1">
                        <span>📱</span> QRIS Dinamis
                    </span>
                </div>
            </div>

            {{-- PURCHASED ITEMS TABLE --}}
            <div>
                <h3 class="font-display font-bold text-sm text-slate-900 mb-3 flex items-center gap-1.5">
                    <span>🛒</span>
                    <span>Rincian Barang yang Dibeli</span>
                </h3>

                <div class="border border-slate-200 rounded-2xl overflow-hidden">
                    <table class="w-full text-left text-xs border-collapse">
                        <thead class="bg-slate-100/80 text-slate-600 font-bold uppercase text-[10px] tracking-wider border-b border-slate-200">
                            <tr>
                                <th class="py-2.5 px-4">Produk</th>
                                <th class="py-2.5 px-4 text-center">Qty</th>
                                <th class="py-2.5 px-4 text-right">Harga Satuan</th>
                                <th class="py-2.5 px-4 text-right">Subtotal</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @foreach ($order->items as $item)
                                <tr class="hover:bg-slate-50/50">
                                    <td class="py-3 px-4 font-semibold text-slate-900">
                                        <div class="flex items-center gap-2.5">
                                            @if ($item->product && $item->product->image_url)
                                                <img src="{{ $item->product->image_url }}" alt="{{ $item->product_name }}" class="w-8 h-8 rounded-lg object-cover border border-slate-200">
                                            @else
                                                <span class="w-8 h-8 rounded-lg bg-slate-100 flex items-center justify-center text-sm">📦</span>
                                            @endif
                                            <span>{{ $item->product_name }}</span>
                                        </div>
                                    </td>
                                    <td class="py-3 px-4 text-center font-mono font-bold text-slate-700">
                                        {{ $item->quantity }}
                                    </td>
                                    <td class="py-3 px-4 text-right text-slate-600 font-mono">
                                        Rp{{ number_format($item->unit_price, 0, ',', '.') }}
                                    </td>
                                    <td class="py-3 px-4 text-right font-bold font-mono text-slate-900">
                                        Rp{{ number_format($item->subtotal, 0, ',', '.') }}
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                        <tfoot class="bg-slate-900 text-white font-bold border-t-2 border-slate-900">
                            <tr>
                                <td colspan="3" class="py-3.5 px-4 text-right text-slate-300 uppercase tracking-wider text-xs">
                                    Total Lunas (QRIS)
                                </td>
                                <td class="py-3.5 px-4 text-right font-display font-black text-lime-400 text-base font-mono">
                                    Rp{{ number_format($order->total_amount, 0, ',', '.') }}
                                </td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>

            {{-- VERIFICATION QR & FOOTER --}}
            <div class="pt-4 border-t border-slate-100 flex flex-col sm:flex-row items-center justify-between gap-4">
                <div class="flex items-center gap-3">
                    <div class="w-16 h-16 bg-slate-100 rounded-xl p-1 border border-slate-200 flex items-center justify-center">
                        <img 
                            src="https://api.qrserver.com/v1/create-qr-code/?size=120x120&data={{ urlencode($order->invoice_number) }}" 
                            alt="Invoice QR" 
                            class="w-full h-full object-contain"
                        >
                    </div>
                    <div class="text-xs text-slate-500">
                        <p class="font-mono font-bold text-slate-800">{{ $order->invoice_number }}</p>
                        <p class="text-[11px]">Scan QR di atas untuk verifikasi keaslian kasir</p>
                    </div>
                </div>

                <div class="text-right">
                    <p class="text-[11px] text-slate-400">GymPulse Smart Management System</p>
                    <p class="text-xs font-semibold text-slate-700">Terima kasih atas pesanan Anda!</p>
                </div>
            </div>

        </div>
    </div>

</div>
@endsection
