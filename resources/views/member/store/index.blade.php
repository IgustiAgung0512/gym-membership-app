@extends('layouts.member')
@section('title', 'Gym Store & Suplemen')

@section('content')
<div class="space-y-6">

    {{-- HEADER CARD --}}
    <div class="rounded-3xl p-6 sm:p-7 bg-gradient-to-br from-slate-900 via-slate-850 to-slate-950 text-white border border-slate-800 shadow-xl relative overflow-hidden">
        <div class="absolute -right-12 -bottom-12 w-64 h-64 rounded-full bg-lime-500/10 blur-3xl pointer-events-none"></div>

        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 relative z-10">
            <div>
                <span class="text-[10px] font-bold uppercase tracking-widest text-lime-400">GYMPULSE STORE & SUPPLEMENTS</span>
                <h2 class="font-display font-extrabold text-2xl sm:text-3xl text-white mt-1">
                    Katalog Produk Gym Store
                </h2>
                <p class="text-xs sm:text-sm text-slate-400 mt-1">
                    Dapatkan suplemen per scoop, minuman dingin, protein bar, dan apparel resmi di kasir front-desk.
                </p>
            </div>

            <a 
                href="https://wa.me/?text={{ urlencode('Halo Admin GymPulse, saya ' . $member->user->name . ' (' . $member->member_code . ') ingin bertanya ketersediaan produk store. Terima kasih!') }}" 
                target="_blank"
                class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-lime-500 hover:bg-lime-400 text-slate-950 text-xs font-bold transition shadow-sm shrink-0 self-start sm:self-auto"
            >
                <span>💬 Chat Kasir via WA</span>
            </a>
        </div>
    </div>

    {{-- FILTER CATEGORIES & SEARCH --}}
    <div class="bg-white border border-slate-200 rounded-2xl p-4 shadow-sm space-y-3">
        <form method="GET" action="{{ route('member.store.index') }}" class="flex flex-col sm:flex-row gap-3">
            <div class="flex-1 relative">
                <input 
                    type="text" 
                    name="search" 
                    value="{{ request('search') }}" 
                    placeholder="Cari suplemen, minuman, protein bar..." 
                    class="w-full pl-9 pr-4 py-2 rounded-xl border border-slate-200 bg-slate-50 focus:bg-white text-xs focus:outline-none"
                >
                <svg class="w-4 h-4 text-slate-400 absolute left-3 top-2.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
            </div>

            <div class="flex items-center gap-1.5 overflow-x-auto pb-1 text-xs">
                @foreach ($categories as $catKey => $cat)
                    <a 
                        href="{{ route('member.store.index', ['category' => $catKey, 'search' => request('search')]) }}"
                        class="px-3 py-2 rounded-xl transition whitespace-nowrap font-bold {{ (request('category', 'all') == $catKey) ? 'bg-slate-900 text-white' : 'bg-slate-100 text-slate-600 hover:bg-slate-200' }}"
                    >
                        {{ $cat['icon'] }} {{ $cat['label'] }}
                    </a>
                @endforeach
            </div>
        </form>
    </div>

    {{-- PRODUCTS GRID --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-4">
        @forelse ($products as $product)
            @php
                $orderWaUrl = "https://wa.me/?text=" . urlencode("Halo Admin GymPulse, saya member " . $member->user->name . " (" . $member->member_code . ") ingin memesan produk: " . $product->name . " (Rp" . number_format($product->price, 0, ',', '.') . "). Mohon disiapkan saat saya tiba di gym. Terima kasih!");
            @endphp
            <div class="bg-white border border-slate-200 rounded-3xl p-5 shadow-sm hover:border-slate-300 hover:shadow-md transition flex flex-col justify-between group">
                <div>
                    {{-- Badge & Stock --}}
                    <div class="flex items-center justify-between mb-3">
                        <span class="text-2xl">{{ $product->category_icon }}</span>
                        @if ($product->stock <= 0)
                            <span class="text-[10px] font-bold px-2.5 py-0.5 rounded-full bg-rose-100 text-rose-700">Stok Habis</span>
                        @elseif ($product->isLowStock())
                            <span class="text-[10px] font-bold px-2.5 py-0.5 rounded-full bg-amber-100 text-amber-800">Sisa Sedikit</span>
                        @else
                            <span class="text-[10px] font-bold px-2.5 py-0.5 rounded-full bg-emerald-100 text-emerald-800">Ready di Kasir</span>
                        @endif
                    </div>

                    <span class="text-[10px] font-bold uppercase tracking-wider text-slate-400">{{ $product->category_label }}</span>
                    <h3 class="font-display font-bold text-base text-slate-900 mt-0.5 group-hover:text-lime-600 transition">
                        {{ $product->name }}
                    </h3>

                    @if ($product->description)
                        <p class="text-xs text-slate-500 mt-1.5 leading-relaxed">
                            {{ $product->description }}
                        </p>
                    @endif
                </div>

                <div class="mt-4 pt-3 border-t border-slate-100 space-y-3">
                    <div class="flex items-center justify-between">
                        <span class="text-xs text-slate-400">Harga / {{ $product->unit }}</span>
                        <span class="font-display font-extrabold text-lg text-slate-900">
                            Rp{{ number_format($product->price, 0, ',', '.') }}
                        </span>
                    </div>

                    @if ($product->stock > 0)
                        <a 
                            href="{{ $orderWaUrl }}" 
                            target="_blank"
                            class="w-full py-2.5 rounded-xl bg-slate-900 hover:bg-slate-800 text-white text-xs font-bold transition flex items-center justify-center gap-1.5 shadow-sm"
                        >
                            <span>🛒 Pesan / Tanya Kasir</span>
                        </a>
                    @else
                        <button disabled class="w-full py-2.5 rounded-xl bg-slate-100 text-slate-400 text-xs font-bold cursor-not-allowed">
                            Stok Belum Tersedia
                        </button>
                    @endif
                </div>
            </div>
        @empty
            <div class="col-span-full py-16 text-center text-slate-400 text-xs bg-white rounded-3xl border border-slate-200">
                <p class="text-4xl mb-2">🔍</p>
                <p class="font-bold text-slate-700 text-sm">Produk tidak ditemukan</p>
                <p class="mt-1">Silakan coba pilih kategori lain atau hapus kata kunci pencarian.</p>
            </div>
        @endforelse
    </div>

</div>
@endsection
