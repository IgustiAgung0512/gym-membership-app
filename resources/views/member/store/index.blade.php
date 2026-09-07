@extends('layouts.member')
@section('title', 'Gym Store & Suplemen')

@section('content')
<div 
    x-data="memberStoreApp()" 
    x-init="init()"
    class="space-y-4 sm:space-y-6 relative pb-20 sm:pb-12"
>

    {{-- HEADER CARD --}}
    <div class="rounded-2xl sm:rounded-3xl p-4 sm:p-6 bg-gradient-to-br from-slate-900 via-slate-850 to-slate-950 text-white border border-slate-800 shadow-xl relative overflow-hidden">
        <div class="absolute -right-12 -bottom-12 w-64 h-64 rounded-full bg-lime-500/10 blur-3xl pointer-events-none"></div>

        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 sm:gap-4 relative z-10">
            <div>
                <div class="flex items-center gap-1.5 sm:gap-2 mb-1">
                    <span class="px-2 py-0.5 rounded-full bg-lime-400 text-slate-950 font-black text-[9px] sm:text-[10px] tracking-wider uppercase">Self-Service QRIS</span>
                    <span class="text-[9px] sm:text-[10px] font-bold uppercase tracking-widest text-slate-400">STOK SINKRON KASIR</span>
                </div>
                <h2 class="font-display font-extrabold text-xl sm:text-2xl lg:text-3xl text-white">
                    Katalog Produk Gym Store
                </h2>
                <p class="text-xs sm:text-sm text-slate-400 mt-0.5 sm:mt-1 max-w-2xl leading-relaxed">
                    Pesan suplemen, minuman dingin & aksesoris langsung via QRIS. Tunjukkan bukti pembayaran ke kasir saat mengambil barang.
                </p>
            </div>

            <div class="flex items-center gap-2 shrink-0 pt-1 sm:pt-0">
                <button 
                    type="button" 
                    @click="myOrdersModal = true"
                    class="inline-flex items-center gap-1.5 px-3 sm:px-4 py-2 sm:py-2.5 rounded-xl bg-slate-800 hover:bg-slate-700 text-white text-xs font-bold border border-slate-700 transition shadow-sm active:scale-95"
                >
                    <span>🧾</span>
                    <span>Bukti Pesanan</span>
                    @if (count($myOrders) > 0)
                        <span class="w-4 h-4 sm:w-5 sm:h-5 rounded-full bg-lime-400 text-slate-950 text-[9px] sm:text-[10px] font-black flex items-center justify-center">{{ count($myOrders) }}</span>
                    @endif
                </button>

                <a 
                    href="https://wa.me/?text={{ urlencode('Halo Kasir GymPulse, saya member ' . $member->user->name . ' (' . $member->member_code . ') ingin bertanya ketersediaan produk gym store. Terima kasih!') }}" 
                    target="_blank"
                    class="inline-flex items-center gap-1 px-3 py-2 sm:py-2.5 rounded-xl bg-slate-800/80 hover:bg-slate-700 text-slate-300 hover:text-white text-xs font-semibold border border-slate-700/60 transition"
                >
                    <span>💬 Tanya WA</span>
                </a>
            </div>
        </div>
    </div>

    {{-- FILTER CATEGORIES & SEARCH BAR --}}
    <div class="bg-white border border-slate-200 rounded-2xl p-3 sm:p-4 shadow-sm space-y-2.5 sm:space-y-3">
        <form method="GET" action="{{ route('member.store.index') }}" class="flex flex-col sm:flex-row gap-2.5 sm:gap-3">
            <div class="flex-1 relative">
                <input 
                    type="text" 
                    name="search" 
                    value="{{ request('search') }}" 
                    placeholder="Cari suplemen, minuman, snack, aksesoris..." 
                    class="w-full pl-9 pr-4 py-2 rounded-xl border border-slate-200 bg-slate-50 focus:bg-white text-xs focus:outline-none focus:ring-2 focus:ring-lime-500 transition"
                >
                <svg class="w-4 h-4 text-slate-400 absolute left-3 top-2.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
            </div>

            <div class="flex items-center gap-1.5 overflow-x-auto pb-1 text-xs no-scrollbar">
                @foreach ($categories as $catKey => $cat)
                    <a 
                        href="{{ route('member.store.index', ['category' => $catKey, 'search' => request('search')]) }}"
                        class="px-3 py-1.5 sm:py-2 rounded-xl transition whitespace-nowrap font-bold flex items-center gap-1.5 text-xs {{ (request('category', 'all') == $catKey) ? 'bg-slate-900 text-white shadow-sm' : 'bg-slate-100 text-slate-600 hover:bg-slate-200' }}"
                    >
                        <span>{{ $cat['icon'] }}</span>
                        <span>{{ $cat['label'] }}</span>
                    </a>
                @endforeach
            </div>
        </form>
    </div>

    {{-- PRODUCTS GRID (Mobile: 2 cols, Tablet: 3 cols, Desktop: 4 cols) --}}
    <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 gap-3 sm:gap-4.5">
        @forelse ($products as $product)
            @php
                $catLower = strtolower($product->category ?? '');
                $categoryBg = match(true) {
                    str_starts_with($catLower, 'drink') => 'from-sky-50 via-cyan-50 to-blue-100 border-sky-200',
                    str_starts_with($catLower, 'supp') => 'from-amber-50 via-orange-50 to-amber-100 border-amber-200',
                    str_starts_with($catLower, 'snack') => 'from-emerald-50 via-teal-50 to-lime-100 border-emerald-200',
                    str_starts_with($catLower, 'gear') || str_starts_with($catLower, 'appar') => 'from-purple-50 via-violet-50 to-indigo-100 border-indigo-200',
                    default => 'from-slate-50 to-slate-100 border-slate-200',
                };
                $imgUrl = $product->image_url ?: ($product->image ? (str_starts_with($product->image, '/') ? $product->image : '/storage/' . $product->image) : null);
            @endphp
            <div class="bg-white border border-slate-200/90 hover:border-slate-300 rounded-2xl sm:rounded-3xl p-3 sm:p-4 shadow-xs hover:shadow-md transition-all flex flex-col justify-between group">
                <div>
                    {{-- Product Image with Stock Badge (Aspect Square 1:1) --}}
                    <div class="relative w-full aspect-square rounded-xl sm:rounded-2xl overflow-hidden bg-gradient-to-br {{ $categoryBg }} mb-2 sm:mb-3 border shadow-inner flex items-center justify-center">
                        @if ($imgUrl)
                            <img 
                                src="{{ $imgUrl }}" 
                                alt="{{ $product->name }}" 
                                class="w-full h-full object-cover object-center group-hover:scale-105 transition duration-300"
                                loading="lazy"
                                onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';"
                            >
                            <div class="hidden w-full h-full flex-col items-center justify-center text-slate-400">
                                <span class="text-3xl sm:text-4xl mb-1">{{ $product->category_icon }}</span>
                                <span class="text-[9px] uppercase font-bold tracking-wider">GymPulse</span>
                            </div>
                        @else
                            <div class="w-full h-full flex flex-col items-center justify-center text-slate-400">
                                <span class="text-3xl sm:text-4xl mb-1">{{ $product->category_icon }}</span>
                                <span class="text-[9px] uppercase font-bold tracking-wider">GymPulse</span>
                            </div>
                        @endif

                        {{-- Stock Status Tag --}}
                        <div class="absolute top-2 left-2">
                            @if ($product->stock <= 0)
                                <span class="px-2 py-0.5 rounded-full bg-rose-600 text-white font-black text-[8px] sm:text-[9px] shadow-sm">
                                    Stok Habis
                                </span>
                            @elseif ($product->isLowStock())
                                <span class="px-2 py-0.5 rounded-full bg-amber-400 text-slate-950 font-black text-[8px] sm:text-[9px] shadow-sm">
                                    Sisa {{ $product->stock }}
                                </span>
                            @else
                                <span class="px-2 py-0.5 rounded-full bg-emerald-600 text-white font-bold text-[8px] sm:text-[9px] shadow-sm">
                                    Tersedia {{ $product->stock }}
                                </span>
                            @endif
                        </div>

                        {{-- Category Tag --}}
                        <div class="absolute bottom-2 left-2">
                            <span class="px-1.5 sm:px-2 py-0.5 rounded-md bg-slate-950/75 backdrop-blur-sm text-white font-semibold text-[8px] sm:text-[9px] uppercase tracking-wider">
                                {{ $product->category_label }}
                            </span>
                        </div>
                    </div>

                    {{-- Product Title (High-contrast, dark slate) --}}
                    <h3 class="font-display font-bold text-xs sm:text-sm text-slate-900 group-hover:text-lime-700 transition leading-snug line-clamp-2">
                        {{ $product->name }}
                    </h3>

                    @if ($product->description)
                        <p class="text-[10px] sm:text-[11px] text-slate-500 mt-1 line-clamp-2 leading-relaxed hidden sm:block">
                            {{ $product->description }}
                        </p>
                    @endif
                </div>

                {{-- Price & Action Buttons --}}
                <div class="mt-2.5 sm:mt-4 pt-2 sm:pt-3 border-t border-slate-100 space-y-2">
                    <div class="flex items-baseline justify-between">
                        <span class="text-[9px] sm:text-[10px] text-slate-400 font-semibold uppercase">Harga</span>
                        <span class="font-display font-black text-xs sm:text-base text-slate-900 font-mono">
                            Rp{{ number_format($product->price, 0, ',', '.') }}
                            <span class="text-[9px] sm:text-[10px] text-slate-400 font-normal font-sans">/ {{ $product->unit }}</span>
                        </span>
                    </div>

                    @if ($product->stock > 0)
                        <div class="flex items-center gap-1 sm:gap-1.5">
                            <button 
                                type="button" 
                                @click="addToCart({{ json_encode($product) }})"
                                class="p-2 sm:px-2.5 sm:py-2 rounded-xl border border-slate-200 hover:bg-slate-100 active:scale-95 text-slate-700 text-xs font-bold transition flex items-center justify-center"
                                title="Tambah ke keranjang"
                            >
                                <span>🛒</span>
                                <span class="hidden sm:inline ml-1 text-xs">Keranjang</span>
                            </button>
                            <button 
                                type="button" 
                                @click="quickBuy({{ json_encode($product) }})"
                                class="flex-1 py-2 px-2 sm:px-3 rounded-xl bg-lime-500 hover:bg-lime-400 active:scale-95 text-slate-950 text-[11px] sm:text-xs font-display font-extrabold transition flex items-center justify-center gap-1 shadow-sm"
                            >
                                <span>📱</span>
                                <span>Beli QRIS</span>
                            </button>
                        </div>
                    @else
                        <button disabled class="w-full py-2 rounded-xl bg-slate-100 text-slate-400 text-xs font-bold cursor-not-allowed text-center">
                            Stok Habis
                        </button>
                    @endif
                </div>
            </div>
        @empty
            <div class="col-span-full py-16 text-center text-slate-400 text-xs bg-white rounded-3xl border border-slate-200">
                <p class="text-4xl mb-2">🔍</p>
                <p class="font-bold text-slate-700 text-sm">Produk tidak ditemukan</p>
                <p class="mt-1">Silakan coba pilih kategori lain atau ubah kata kunci pencarian.</p>
            </div>
        @endforelse
    </div>

    {{-- FLOATING CART BAR (IF CART HAS ITEMS) --}}
    <div 
        x-show="cart.length > 0" 
        x-cloak 
        x-transition:enter="transition ease-out duration-300"
        x-transition:enter-start="translate-y-full opacity-0"
        x-transition:enter-end="translate-y-0 opacity-100"
        x-transition:leave="transition ease-in duration-200"
        x-transition:leave-start="translate-y-0 opacity-100"
        x-transition:leave-end="translate-y-full opacity-0"
        class="fixed bottom-3 left-3 right-3 sm:left-auto sm:right-6 sm:w-96 z-40"
    >
        <div class="bg-slate-950 text-white rounded-2xl p-3 sm:p-4 shadow-2xl border border-slate-800 flex items-center justify-between gap-2.5 sm:gap-3">
            <div class="flex items-center gap-2.5 sm:gap-3 min-w-0">
                <div class="relative shrink-0">
                    <span class="w-9 h-9 sm:w-10 sm:h-10 rounded-xl bg-lime-400 text-slate-950 font-black flex items-center justify-center text-base sm:text-lg shadow-md">
                        🛒
                    </span>
                    <span class="absolute -top-1.5 -right-1.5 px-1.5 py-0.2 rounded-full bg-rose-500 text-white text-[9px] sm:text-[10px] font-bold font-mono" x-text="cartTotalQty"></span>
                </div>
                <div class="truncate">
                    <p class="text-[10px] sm:text-[11px] text-slate-400 font-semibold truncate">Total Tagihan Keranjang</p>
                    <p class="font-display font-black text-sm sm:text-base text-lime-400 font-mono">
                        Rp<span x-text="cartTotalAmount.toLocaleString('id-ID')"></span>
                    </p>
                </div>
            </div>

            <button 
                type="button" 
                @click="openCartModal()" 
                class="py-2 sm:py-2.5 px-3 sm:px-4 rounded-xl bg-lime-500 hover:bg-lime-400 text-slate-950 text-xs font-display font-extrabold transition shadow-md flex items-center gap-1 shrink-0 active:scale-95"
            >
                <span>Bayar QRIS</span>
                <span>➔</span>
            </button>
        </div>
    </div>

    {{-- MODAL CART & CHECKOUT CONFIRMATION --}}
    <div 
        x-show="cartModal" 
        x-cloak 
        class="fixed inset-0 z-50 flex items-center justify-center p-3 sm:p-4 bg-slate-950/70 backdrop-blur-sm overflow-y-auto"
    >
        <div 
            @click.away="cartModal = false"
            class="bg-white rounded-2xl sm:rounded-3xl max-w-sm sm:max-w-md w-full p-4 sm:p-6 shadow-2xl border border-slate-100 relative my-auto max-h-[92vh] overflow-y-auto"
        >
            <div class="flex items-center justify-between pb-3 border-b border-slate-100">
                <div class="flex items-center gap-2">
                    <span class="w-8 h-8 rounded-xl bg-lime-100 text-slate-900 flex items-center justify-center text-base font-bold">🛒</span>
                    <div>
                        <h3 class="font-display font-bold text-sm sm:text-base text-slate-900">Keranjang Belanja</h3>
                        <p class="text-[10px] sm:text-[11px] text-slate-500">Checkout mandiri via QRIS Dinamis</p>
                    </div>
                </div>
                <button type="button" @click="cartModal = false" class="text-slate-400 hover:text-slate-600 text-xl font-bold">&times;</button>
            </div>

            {{-- ERROR MESSAGE --}}
            <template x-if="errorMessage">
                <div class="mt-3 p-2.5 rounded-xl bg-rose-50 border border-rose-200 text-rose-700 text-xs font-semibold" x-text="errorMessage"></div>
            </template>

            {{-- CART ITEMS LIST --}}
            <div class="mt-3.5 space-y-2 divide-y divide-slate-100">
                <template x-for="(item, index) in cart" :key="item.product_id">
                    <div class="pt-2 first:pt-0 flex items-center justify-between gap-2.5 text-xs">
                        <div class="flex items-center gap-2 flex-1 min-w-0">
                            <template x-if="item.image_url">
                                <img :src="item.image_url" :alt="item.name" class="w-9 h-9 sm:w-10 sm:h-10 rounded-xl object-cover border border-slate-200 shrink-0">
                            </template>
                            <template x-if="!item.image_url">
                                <span class="w-9 h-9 sm:w-10 sm:h-10 rounded-xl bg-slate-100 flex items-center justify-center text-base shrink-0">📦</span>
                            </template>
                            <div class="truncate">
                                <p class="font-bold text-slate-900 truncate text-[11px] sm:text-xs" x-text="item.name"></p>
                                <p class="text-slate-500 text-[10px] sm:text-[11px] font-mono">Rp<span x-text="Number(item.price).toLocaleString('id-ID')"></span> / <span x-text="item.unit"></span></p>
                            </div>
                        </div>

                        {{-- QTY CONTROLLER --}}
                        <div class="flex items-center gap-1 shrink-0">
                            <button 
                                type="button" 
                                @click="decreaseQty(index)"
                                class="w-6 h-6 sm:w-7 sm:h-7 rounded-lg bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold flex items-center justify-center transition"
                            >-</button>
                            <span class="w-5 sm:w-6 text-center font-bold font-mono text-slate-900 text-xs" x-text="item.quantity"></span>
                            <button 
                                type="button" 
                                @click="increaseQty(index)"
                                class="w-6 h-6 sm:w-7 sm:h-7 rounded-lg bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold flex items-center justify-center transition"
                            >+</button>
                            <button 
                                type="button" 
                                @click="removeItem(index)"
                                class="w-6 h-6 sm:w-7 sm:h-7 rounded-lg bg-rose-50 hover:bg-rose-100 text-rose-600 font-bold flex items-center justify-center ml-0.5 transition"
                                title="Hapus item"
                            >&times;</button>
                        </div>
                    </div>
                </template>
            </div>

            {{-- NOTES INPUT --}}
            <div class="mt-3.5 pt-2.5 border-t border-slate-100">
                <label class="block text-[10px] sm:text-[11px] font-bold text-slate-700 uppercase tracking-wider mb-1">Catatan Tambahan (Opsional)</label>
                <input 
                    type="text" 
                    x-model="orderNotes" 
                    placeholder="Contoh: Suplemen scoop rasa vanila / botol dingin"
                    class="w-full px-3 py-1.5 sm:py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs focus:outline-none focus:ring-2 focus:ring-lime-500"
                >
            </div>

            {{-- TOTAL & SUBMIT BUTTON --}}
            <div class="mt-3.5 p-3 bg-slate-950 text-white rounded-xl sm:rounded-2xl">
                <div class="flex items-center justify-between text-[11px] text-slate-400">
                    <span>Metode Pembayaran</span>
                    <span class="font-bold text-lime-400">📱 QRIS Dinamis</span>
                </div>
                <div class="mt-1.5 pt-1.5 border-t border-slate-800 flex items-center justify-between">
                    <span class="text-xs text-slate-300 font-semibold uppercase">Total Bayar</span>
                    <span class="font-display font-black text-lg sm:text-xl text-lime-400 font-mono">
                        Rp<span x-text="cartTotalAmount.toLocaleString('id-ID')"></span>
                    </span>
                </div>
            </div>

            <div class="mt-3.5 grid grid-cols-2 gap-2">
                <button 
                    type="button" 
                    @click="cartModal = false" 
                    class="py-2 rounded-xl border border-slate-200 text-slate-600 text-xs font-bold hover:bg-slate-50 transition"
                >
                    Tambah Lain
                </button>
                <button 
                    type="button" 
                    @click="submitCheckout()" 
                    :disabled="loading || cart.length === 0"
                    class="py-2 rounded-xl bg-lime-500 hover:bg-lime-400 active:scale-95 text-slate-950 text-xs font-display font-extrabold transition shadow-md flex items-center justify-center gap-1.5"
                >
                    <span x-show="!loading">Bayar via QRIS</span>
                    <span x-show="loading">Membuat QRIS...</span>
                </button>
            </div>
        </div>
    </div>

    {{-- MODAL QRIS DINAMIS MEMBER --}}
    <div 
        x-show="qrisModal" 
        x-cloak 
        class="fixed inset-0 z-50 flex items-center justify-center p-3 sm:p-4 bg-slate-950/75 backdrop-blur-md overflow-y-auto"
    >
        <div 
            class="bg-white rounded-2xl sm:rounded-3xl max-w-sm w-full p-4 sm:p-5 shadow-2xl border border-slate-100 text-center relative my-auto max-h-[92vh] overflow-y-auto"
        >
            {{-- Top Header --}}
            <div class="flex items-center justify-between pb-2 border-b border-slate-100 mb-2.5">
                <div class="flex items-center gap-1.5">
                    <span class="px-2 py-0.5 rounded-md bg-slate-900 text-white font-black text-[11px] tracking-wider">QRIS</span>
                    <span class="text-[11px] font-bold text-slate-500 uppercase tracking-wide">Dinamis Otomatis</span>
                </div>
                <div class="flex items-center gap-1 text-[11px] font-mono font-bold bg-amber-50 text-amber-800 px-2 py-0.5 rounded-lg border border-amber-200">
                    <svg class="w-3 h-3 text-amber-600 animate-spin" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                    <span x-text="qrisFormattedTimer"></span>
                </div>
            </div>

            {{-- QR Code Container --}}
            <div class="bg-gradient-to-b from-slate-50 to-slate-100/90 p-2.5 sm:p-3 rounded-2xl border border-slate-200 shadow-inner inline-block mx-auto">
                <div class="w-40 h-40 sm:w-44 sm:h-44 bg-white rounded-xl p-2 shadow-sm flex items-center justify-center mx-auto border border-slate-200">
                    <template x-if="qrisData && qrisData.qr_url">
                        <img :src="qrisData.qr_url" alt="QRIS Code" class="w-full h-full object-contain">
                    </template>
                </div>
                <p class="font-mono text-[11px] text-slate-500 mt-1 font-semibold" x-text="lastOrder ? lastOrder.invoice_number : ''"></p>
            </div>

            {{-- Total Tagihan Display --}}
            <div class="mt-2 p-2.5 bg-slate-900 text-white rounded-xl">
                <p class="text-[10px] text-slate-400 font-semibold uppercase tracking-wider">Total Tagihan QRIS</p>
                <p class="font-display font-black text-xl text-lime-400 mt-0.5 font-mono">
                    Rp<span x-text="lastOrder ? Number(lastOrder.total_amount).toLocaleString('id-ID') : '0'"></span>
                </p>
                <p class="text-[10px] text-slate-300 mt-0.5" x-text="'Member: ' + (lastOrder ? lastOrder.customer_name : '')"></p>
            </div>

            {{-- Live Status Indicator --}}
            <div class="my-2 py-1.5 px-2.5 rounded-lg bg-emerald-50 border border-emerald-200 flex items-center justify-center gap-1.5 text-[11px] font-bold text-emerald-800">
                <span class="w-2 h-2 rounded-full bg-emerald-500 animate-ping"></span>
                <span>Menunggu Pembayaran Anda...</span>
            </div>

            {{-- SIMULATOR TEST ACTION BOX --}}
            <div class="p-2.5 bg-gradient-to-br from-indigo-50 via-purple-50 to-blue-50 border border-indigo-200 rounded-xl text-left space-y-1.5">
                <div class="flex items-center justify-between">
                    <span class="text-[10px] font-bold text-indigo-950 uppercase tracking-wider flex items-center gap-1">
                        <span>🧪</span> Mode Uji Coba (Simulator)
                    </span>
                    <span class="text-[9px] bg-indigo-200 text-indigo-900 px-1.5 py-0.5 rounded-full font-bold">Midtrans Ready</span>
                </div>
                <p class="text-[10px] text-indigo-800 leading-snug">
                    Klik tombol di bawah untuk simulasi telah bayar via BCA Mobile / GoPay / OVO:
                </p>
                <button 
                    type="button" 
                    @click="simulateQrisPayment()"
                    :disabled="qrisSimulating"
                    class="w-full py-2 px-3 bg-indigo-600 hover:bg-indigo-700 active:scale-95 text-white rounded-lg text-xs font-bold transition shadow-sm flex items-center justify-center gap-1.5"
                >
                    <span x-show="!qrisSimulating">⚡ Simulasi: Bayar QRIS Sukses</span>
                    <span x-show="qrisSimulating">Memverifikasi Pembayaran...</span>
                </button>
            </div>

            {{-- Cancel / Close --}}
            <div class="mt-2.5">
                <button 
                    type="button" 
                    @click="cancelQrisOrder()"
                    class="text-xs text-rose-600 hover:text-rose-800 font-bold hover:underline"
                >
                    ✕ Batalkan Transaksi
                </button>
            </div>
        </div>
    </div>

    {{-- MODAL SUCCESS & INSTANT PROOF OF PAYMENT --}}
    <div 
        x-show="successModal" 
        x-cloak 
        class="fixed inset-0 z-50 flex items-center justify-center p-3 sm:p-4 bg-slate-950/75 backdrop-blur-md overflow-y-auto"
    >
        <div 
            class="bg-white rounded-2xl sm:rounded-3xl max-w-sm w-full p-4 sm:p-6 shadow-2xl border border-slate-100 text-center relative my-auto max-h-[92vh] overflow-y-auto"
        >
            <div class="w-12 h-12 sm:w-14 sm:h-14 mx-auto rounded-2xl bg-emerald-100 text-emerald-600 flex items-center justify-center text-xl sm:text-2xl mb-2 shadow-inner">
                ✓
            </div>

            <span class="px-2.5 py-0.5 rounded-full bg-emerald-100 text-emerald-800 font-black text-[10px] uppercase tracking-wider">
                Lunas via QRIS
            </span>

            <h3 class="font-display font-extrabold text-base sm:text-lg text-slate-900 mt-1">Pembayaran Berhasil!</h3>
            <p class="font-mono text-xs text-slate-500 font-bold" x-text="lastOrder ? lastOrder.invoice_number : ''"></p>

            {{-- PICKUP INSTRUCTION CARD --}}
            <div class="mt-2.5 sm:mt-3 p-3 rounded-xl sm:rounded-2xl bg-lime-50 border border-lime-200 text-left space-y-1">
                <div class="flex items-center gap-1.5 text-slate-950 font-bold text-xs">
                    <span>📦</span>
                    <span>Siap Diambil di Kasir</span>
                </div>
                <p class="text-[11px] text-slate-600 leading-snug">
                    Tunjukkan bukti ini atau sebutkan nama Anda kepada kasir front-desk gym untuk mengambil pesanan.
                </p>
            </div>

            {{-- ITEMS SUMMARY --}}
            <div class="mt-2.5 sm:mt-3 p-3 rounded-xl sm:rounded-2xl bg-slate-50 border border-slate-100 text-left text-xs space-y-1.5">
                <p class="text-[10px] uppercase font-bold text-slate-400 tracking-wider">Ringkasan Barang</p>
                <template x-if="lastOrder && lastOrder.items">
                    <div class="space-y-1 divide-y divide-slate-100">
                        <template x-for="item in lastOrder.items" :key="item.id">
                            <div class="pt-1 first:pt-0 flex justify-between items-center text-[11px]">
                                <span class="font-semibold text-slate-800" x-text="item.quantity + 'x ' + item.product_name"></span>
                                <span class="font-mono text-slate-600 font-bold">Rp<span x-text="Number(item.subtotal).toLocaleString('id-ID')"></span></span>
                            </div>
                        </template>
                    </div>
                </template>
                <div class="pt-2 border-t border-slate-200 flex justify-between items-center font-bold text-xs">
                    <span class="text-slate-700">Total Dibayar:</span>
                    <span class="text-emerald-700 font-mono font-black">
                        Rp<span x-text="lastOrder ? Number(lastOrder.total_amount).toLocaleString('id-ID') : '0'"></span>
                    </span>
                </div>
            </div>

            <div class="mt-3.5 sm:mt-4 space-y-2">
                <template x-if="receiptUrl">
                    <a 
                        :href="receiptUrl" 
                        class="w-full py-2.5 px-4 bg-slate-900 hover:bg-slate-800 text-white rounded-xl text-xs font-bold transition flex items-center justify-center gap-1.5 shadow-sm"
                    >
                        <span>📄</span>
                        <span>Buka Bukti Pembayaran Penuh</span>
                    </a>
                </template>
                <button 
                    type="button" 
                    @click="closeSuccessModal()" 
                    class="w-full py-2 px-4 rounded-xl border border-slate-200 text-slate-700 text-xs font-semibold hover:bg-slate-100 transition"
                >
                    Selesai & Belanja Lagi
                </button>
            </div>
        </div>
    </div>

    {{-- MODAL MY ORDERS / RIWAYAT PESANAN MEMBER --}}
    <div 
        x-show="myOrdersModal" 
        x-cloak 
        class="fixed inset-0 z-50 flex items-center justify-center p-3 sm:p-4 bg-slate-950/75 backdrop-blur-md overflow-y-auto"
    >
        <div 
            @click.away="myOrdersModal = false"
            class="bg-white rounded-2xl sm:rounded-3xl max-w-lg w-full p-4 sm:p-6 shadow-2xl border border-slate-100 relative my-auto max-h-[92vh] overflow-y-auto"
        >
            <div class="flex items-center justify-between pb-3 border-b border-slate-100">
                <div class="flex items-center gap-2">
                    <span class="w-8 h-8 rounded-xl bg-slate-900 text-white flex items-center justify-center text-sm font-bold">🧾</span>
                    <div>
                        <h3 class="font-display font-bold text-sm sm:text-base text-slate-900">Riwayat Pesanan & Bukti Bayar</h3>
                        <p class="text-[10px] sm:text-[11px] text-slate-500">Tunjukkan bukti bayar ini ke kasir saat mengambil barang</p>
                    </div>
                </div>
                <button type="button" @click="myOrdersModal = false" class="text-slate-400 hover:text-slate-600 text-xl font-bold">&times;</button>
            </div>

            <div class="mt-3.5 space-y-2.5">
                @forelse ($myOrders as $order)
                    <div class="p-3 sm:p-3.5 rounded-xl sm:rounded-2xl bg-slate-50 border border-slate-200/80 hover:border-slate-300 transition space-y-2">
                        <div class="flex items-center justify-between">
                            <div>
                                <span class="font-mono font-bold text-xs text-slate-900">{{ $order->invoice_number }}</span>
                                <p class="text-[10px] text-slate-500">{{ $order->created_at->translatedFormat('d M Y, H:i') }}</p>
                            </div>
                            <div>
                                @if ($order->pickup_status === 'picked_up')
                                    <span class="px-2 py-0.5 rounded-full bg-slate-200 text-slate-700 font-bold text-[9px] sm:text-[10px]">
                                        ✓ Sudah Diambil
                                    </span>
                                @else
                                    <span class="px-2 py-0.5 rounded-full bg-lime-400 text-slate-950 font-black text-[9px] sm:text-[10px] shadow-sm animate-pulse">
                                        📦 Siap Diambil
                                    </span>
                                @endif
                            </div>
                        </div>

                        {{-- Items list --}}
                        <div class="text-[11px] text-slate-600 space-y-0.5 pt-1 border-t border-slate-200/60">
                            @foreach ($order->items as $item)
                                <div class="flex justify-between">
                                    <span>{{ $item->quantity }}x {{ $item->product_name }}</span>
                                    <span class="font-mono text-slate-700">Rp{{ number_format($item->subtotal, 0, ',', '.') }}</span>
                                </div>
                            @endforeach
                        </div>

                        {{-- Total & Receipt Link --}}
                        <div class="pt-2 border-t border-slate-200/60 flex items-center justify-between text-xs">
                            <span class="font-bold text-slate-800">
                                Total: <span class="text-emerald-700 font-mono">Rp{{ number_format($order->total_amount, 0, ',', '.') }}</span>
                            </span>
                            <a 
                                href="{{ route('member.store.receipt', $order->id) }}" 
                                class="px-3 py-1.5 rounded-xl bg-slate-900 hover:bg-slate-800 text-white font-bold text-[11px] transition inline-flex items-center gap-1 shadow-sm"
                            >
                                <span>Lihat Bukti Bayar</span>
                                <span>➔</span>
                            </a>
                        </div>
                    </div>
                @empty
                    <div class="py-12 text-center text-slate-400 text-xs">
                        <p class="text-3xl mb-1">🛒</p>
                        <p class="font-bold text-slate-700">Belum ada riwayat pesanan</p>
                        <p class="mt-0.5">Pesanan yang Anda bayar akan tercatat di sini dan bukti bayarnya dapat ditunjukkan ke kasir.</p>
                    </div>
                @endforelse
            </div>
        </div>
    </div>

</div>

{{-- ALPINE.JS MEMBER STORE SCRIPT --}}
<script>
function memberStoreApp() {
    return {
        cart: [],
        cartModal: false,
        myOrdersModal: false,
        orderNotes: '',
        loading: false,
        errorMessage: '',

        // QRIS State
        qrisModal: false,
        qrisData: null,
        lastOrder: null,
        receiptUrl: '',
        qrisTimer: 300,
        qrisTimerInterval: null,
        qrisPollingInterval: null,
        qrisSimulating: false,
        successModal: false,

        init() {
            // Load saved cart if any
            const savedCart = localStorage.getItem('gympulse_member_cart');
            if (savedCart) {
                try {
                    this.cart = JSON.parse(savedCart);
                } catch (e) {
                    this.cart = [];
                }
            }
        },

        saveCart() {
            localStorage.setItem('gympulse_member_cart', JSON.stringify(this.cart));
        },

        get cartTotalQty() {
            return this.cart.reduce((sum, item) => sum + item.quantity, 0);
        },

        get cartTotalAmount() {
            return this.cart.reduce((sum, item) => sum + (item.price * item.quantity), 0);
        },

        get qrisFormattedTimer() {
            const minutes = Math.floor(this.qrisTimer / 60);
            const seconds = this.qrisTimer % 60;
            return `${String(minutes).padStart(2, '0')}:${String(seconds).padStart(2, '0')}`;
        },

        addToCart(product) {
            const existing = this.cart.find(i => i.product_id === product.id);
            if (existing) {
                if (existing.quantity < product.stock) {
                    existing.quantity++;
                } else {
                    alert(`Maksimal pesanan untuk '${product.name}' adalah ${product.stock} ${product.unit} (sesuai stok kasir).`);
                }
            } else {
                this.cart.push({
                    product_id: product.id,
                    name: product.name,
                    price: product.price,
                    unit: product.unit,
                    stock: product.stock,
                    image_url: product.image_url || product.image,
                    quantity: 1,
                });
            }
            this.saveCart();
        },

        quickBuy(product) {
            this.cart = [{
                product_id: product.id,
                name: product.name,
                price: product.price,
                unit: product.unit,
                stock: product.stock,
                image_url: product.image_url || product.image,
                quantity: 1,
            }];
            this.saveCart();
            this.cartModal = true;
        },

        openCartModal() {
            this.errorMessage = '';
            this.cartModal = true;
        },

        increaseQty(index) {
            const item = this.cart[index];
            if (item.quantity < item.stock) {
                item.quantity++;
                this.saveCart();
            } else {
                alert(`Maksimal stok tersedia adalah ${item.stock} ${item.unit}.`);
            }
        },

        decreaseQty(index) {
            if (this.cart[index].quantity > 1) {
                this.cart[index].quantity--;
            } else {
                this.cart.splice(index, 1);
            }
            this.saveCart();
            if (this.cart.length === 0) {
                this.cartModal = false;
            }
        },

        removeItem(index) {
            this.cart.splice(index, 1);
            this.saveCart();
            if (this.cart.length === 0) {
                this.cartModal = false;
            }
        },

        async submitCheckout() {
            if (this.cart.length === 0) return;

            this.loading = true;
            this.errorMessage = '';

            try {
                const response = await fetch("{{ route('member.store.checkout') }}", {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Accept': 'application/json',
                    },
                    body: JSON.stringify({
                        items: this.cart.map(i => ({ product_id: i.product_id, quantity: i.quantity })),
                        notes: this.orderNotes,
                    })
                });

                const data = await response.json();

                if (!response.ok || !data.success) {
                    this.errorMessage = data.message || 'Terjadi kesalahan saat membuat pesanan.';
                    this.loading = false;
                    return;
                }

                // Sukses inisiasi QRIS
                this.cartModal = false;
                this.cart = [];
                this.saveCart();
                this.orderNotes = '';

                this.lastOrder = data.order;
                this.qrisData = data.qris;
                this.receiptUrl = data.receipt_url;

                this.openQrisModal(data);

            } catch (err) {
                this.errorMessage = 'Gagal menghubungi server. Periksa koneksi internet Anda.';
            } finally {
                this.loading = false;
            }
        },

        openQrisModal(data) {
            this.qrisModal = true;
            this.qrisTimer = 300;
            this.qrisSimulating = false;

            // Timer countdown
            clearInterval(this.qrisTimerInterval);
            this.qrisTimerInterval = setInterval(() => {
                if (this.qrisTimer > 0) {
                    this.qrisTimer--;
                } else {
                    this.cancelQrisOrder(true);
                }
            }, 1000);

            // Polling order status
            clearInterval(this.qrisPollingInterval);
            this.qrisPollingInterval = setInterval(() => {
                this.checkQrisStatus(data.status_url);
            }, 2500);
        },

        async checkQrisStatus(statusUrl) {
            if (!this.qrisModal) return;

            try {
                const res = await fetch(statusUrl, {
                    headers: { 'Accept': 'application/json' }
                });
                const data = await res.json();

                if (data.success && data.is_paid) {
                    this.handlePaymentSuccess(data.order, data.receipt_url);
                }
            } catch (e) {
                // Polling error silently handled
            }
        },

        async simulateQrisPayment() {
            if (!this.lastOrder || this.qrisSimulating) return;

            this.qrisSimulating = true;

            try {
                const res = await fetch("{{ url('/member/store/orders') }}/" + this.lastOrder.id + "/simulate", {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Accept': 'application/json',
                    }
                });

                const data = await res.json();
                if (data.success) {
                    this.handlePaymentSuccess(data.order, data.receipt_url);
                } else {
                    alert(data.message || 'Gagal simulasi');
                }
            } catch (err) {
                alert('Gagal menghubungi server');
            } finally {
                this.qrisSimulating = false;
            }
        },

        async cancelQrisOrder(isExpired = false) {
            if (!this.lastOrder) {
                this.closeQrisModal();
                return;
            }

            if (!isExpired && !confirm('Apakah Anda yakin ingin membatalkan transaksi QRIS ini?')) {
                return;
            }

            try {
                await fetch("{{ url('/member/store/orders') }}/" + this.lastOrder.id + "/cancel", {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Accept': 'application/json',
                    }
                });
            } catch (e) {}

            this.closeQrisModal();
            if (isExpired) {
                alert('Waktu pembayaran QRIS telah habis. Pesanan dibatalkan.');
            }
            // Refresh halaman agar stok kembali terupdate
            window.location.reload();
        },

        closeQrisModal() {
            clearInterval(this.qrisTimerInterval);
            clearInterval(this.qrisPollingInterval);
            this.qrisModal = false;
        },

        handlePaymentSuccess(order, receiptUrl) {
            this.closeQrisModal();
            this.lastOrder = order;
            this.receiptUrl = receiptUrl || ("{{ url('/member/store/receipt') }}/" + order.id);
            this.successModal = true;
        },

        closeSuccessModal() {
            this.successModal = false;
            window.location.reload();
        }
    };
}
</script>
@endsection
