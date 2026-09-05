@extends('layouts.admin')
@section('title', 'Kasir POS (Point of Sale)')

@section('content')
<div 
    x-data="{
        search: '',
        category: 'all',
        cart: [],
        memberId: '',
        customerName: 'Tamu / Walk-in',
        discount: 0,
        paymentMethod: 'cash',
        cashReceived: '',
        notes: '',
        checkoutModal: false,
        successModal: false,
        lastOrder: null,
        loading: false,
        errorMessage: '',

        products: {{ Js::from($products) }},
        members: {{ Js::from($members) }},

        get filteredProducts() {
            return this.products.filter(p => {
                const cat = (p.category || '').toLowerCase();
                const matchCategory = this.category === 'all' || 
                    cat === this.category || 
                    cat.startsWith(this.category.replace(/s$/, '')) ||
                    this.category.startsWith(cat.replace(/s$/, ''));
                const matchSearch = this.search === '' || 
                    p.name.toLowerCase().includes(this.search.toLowerCase()) || 
                    (p.sku && p.sku.toLowerCase().includes(this.search.toLowerCase()));
                return matchCategory && matchSearch;
            });
        },

        getCategoryLabel(cat) {
            if (!cat) return 'Produk';
            const c = cat.toLowerCase();
            if (c.startsWith('drink')) return '🥤 Minuman';
            if (c.startsWith('supp')) return '⚡ Suplemen';
            if (c.startsWith('snack')) return '🍫 Snack';
            if (c.startsWith('gear') || c.startsWith('appar')) return '🎽 Aksesoris';
            return cat.toUpperCase();
        },

        getCategoryBg(cat) {
            if (!cat) return 'from-slate-50 to-slate-100 border-slate-200';
            const c = cat.toLowerCase();
            if (c.startsWith('drink')) return 'from-sky-50 via-cyan-50 to-blue-100 border-sky-200';
            if (c.startsWith('supp')) return 'from-amber-50 via-orange-50 to-amber-100 border-amber-200';
            if (c.startsWith('snack')) return 'from-emerald-50 via-teal-50 to-lime-100 border-emerald-200';
            if (c.startsWith('gear') || c.startsWith('appar')) return 'from-purple-50 via-violet-50 to-indigo-100 border-indigo-200';
            return 'from-slate-50 to-slate-100 border-slate-200';
        },

        getCategoryEmoji(cat) {
            if (!cat) return '📦';
            const c = cat.toLowerCase();
            if (c.startsWith('drink')) return '🥤';
            if (c.startsWith('supp')) return '⚡';
            if (c.startsWith('snack')) return '🍫';
            if (c.startsWith('gear') || c.startsWith('appar')) return '🎽';
            return '📦';
        },

        addToCart(product) {
            if (product.stock <= 0) {
                alert('Stok produk ini habis!');
                return;
            }

            const existing = this.cart.find(item => item.product_id === product.id);
            if (existing) {
                if (existing.quantity >= product.stock) {
                    alert('Jumlah melebihi stok yang tersedia (' + product.stock + ')');
                    return;
                }
                existing.quantity++;
            } else {
                this.cart.push({
                    product_id: product.id,
                    name: product.name,
                    price: parseFloat(product.price),
                    stock: product.stock,
                    quantity: 1,
                    unit: product.unit
                });
            }
        },

        updateQuantity(item, delta) {
            const newQty = item.quantity + delta;
            if (newQty <= 0) {
                this.removeFromCart(item);
            } else if (newQty > item.stock) {
                alert('Jumlah melebihi stok yang tersedia (' + item.stock + ')');
            } else {
                item.quantity = newQty;
            }
        },

        removeFromCart(item) {
            this.cart = this.cart.filter(i => i.product_id !== item.product_id);
        },

        clearCart() {
            if (this.cart.length > 0 && confirm('Kosongkan keranjang belanja?')) {
                this.cart = [];
                this.discount = 0;
            }
        },

        get subtotal() {
            return this.cart.reduce((sum, item) => sum + (item.price * item.quantity), 0);
        },

        get totalAmount() {
            return Math.max(0, this.subtotal - (parseFloat(this.discount) || 0));
        },

        get cashChange() {
            const received = parseFloat(this.cashReceived) || 0;
            return Math.max(0, received - this.totalAmount);
        },

        setQuickCash(amount) {
            this.cashReceived = amount;
        },

        setExactCash() {
            this.cashReceived = this.totalAmount;
        },

        onMemberChange() {
            if (this.memberId) {
                const m = this.members.find(m => m.id == this.memberId);
                this.customerName = m ? m.user.name + ' (' + m.member_code + ')' : 'Member';
            } else {
                this.customerName = 'Tamu / Walk-in';
            }
        },

        openCheckout() {
            if (this.cart.length === 0) {
                alert('Keranjang belanja masih kosong!');
                return;
            }
            this.cashReceived = this.paymentMethod === 'cash' ? this.totalAmount : '';
            this.errorMessage = '';
            this.checkoutModal = true;
        },

        async submitCheckout() {
            if (this.paymentMethod === 'cash' && (parseFloat(this.cashReceived) || 0) < this.totalAmount) {
                this.errorMessage = 'Uang tunai yang diterima kurang dari total belanja!';
                return;
            }

            this.loading = true;
            this.errorMessage = '';

            try {
                const response = await fetch('{{ route('admin.pos.checkout') }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({
                        items: this.cart,
                        member_id: this.memberId || null,
                        customer_name: this.customerName,
                        discount: parseFloat(this.discount) || 0,
                        payment_method: this.paymentMethod,
                        cash_received: parseFloat(this.cashReceived) || 0,
                        notes: this.notes
                    })
                });

                const data = await response.json();

                if (response.ok && data.success) {
                    // Update local product stock
                    this.cart.forEach(cartItem => {
                        const prod = this.products.find(p => p.id === cartItem.product_id);
                        if (prod) {
                            prod.stock -= cartItem.quantity;
                        }
                    });

                    this.lastOrder = data.order;
                    this.lastOrder.receipt_url = data.receipt_url;
                    this.checkoutModal = false;
                    this.successModal = true;
                    this.cart = [];
                    this.discount = 0;
                    this.notes = '';
                } else {
                    this.errorMessage = data.message || 'Gagal memproses transaksi.';
                }
            } catch (err) {
                this.errorMessage = 'Terjadi kesalahan sistem atau jaringan.';
            } finally {
                this.loading = false;
            }
        },

        formatRupiah(num) {
            return new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', maximumFractionDigits: 0 }).format(num);
        },

        printReceipt(url) {
            const win = window.open(url, '_blank', 'width=420,height=650');
            if (win) {
                win.focus();
            }
        }
    }"
    class="space-y-6"
    @keydown.window.f4.prevent="openCheckout()"
>

    {{-- HEADER BAR --}}
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <div class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-xs font-bold bg-lime-100 text-lime-800 uppercase tracking-wider mb-1">
                <span>🛒</span>
                <span>Gym Store POS</span>
            </div>
            <h2 class="font-display font-bold text-2xl sm:text-3xl text-slate-900 tracking-tight">
                Kasir Penjualan Produk
            </h2>
            <p class="text-xs sm:text-sm text-slate-500 mt-0.5">
                Layani pembelian suplemen, minuman, makanan sehat, dan merchandise dengan cepat.
            </p>
        </div>

        <div class="flex items-center gap-2">
            <a href="{{ route('admin.orders.index') }}" class="px-3.5 py-2 rounded-xl bg-white border border-slate-200 text-slate-700 hover:bg-slate-50 text-xs font-bold transition shadow-sm inline-flex items-center gap-1.5">
                <svg class="w-4 h-4 text-slate-500" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                <span>Riwayat Penjualan</span>
            </a>
            <a href="{{ route('admin.products.index') }}" class="px-3.5 py-2 rounded-xl bg-slate-900 text-white hover:bg-slate-800 text-xs font-bold transition shadow-sm inline-flex items-center gap-1.5">
                <svg class="w-4 h-4 text-lime-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
                <span>Kelola Produk</span>
            </a>
        </div>
    </div>

    {{-- MAIN 2-PANEL POS GRID --}}
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 items-start">
        
        {{-- =========================================================
            PANEL KIRI (8 COL): KATALOG PRODUK
        ========================================================= --}}
        <div class="lg:col-span-7 xl:col-span-8 space-y-4">
            
            {{-- Filter & Search Bar --}}
            <div class="bg-white border border-slate-200 rounded-2xl p-4 shadow-sm space-y-3">
                <div class="relative">
                    <input 
                        type="text" 
                        x-model="search" 
                        placeholder="Cari produk berdasarkan nama / kode SKU... (Tekan ketik)" 
                        class="w-full pl-10 pr-4 py-2.5 rounded-xl border border-slate-200 bg-slate-50 focus:bg-white focus:outline-none focus:ring-2 focus:ring-lime-500/20 focus:border-lime-500 text-sm transition"
                    >
                    <svg class="w-5 h-5 text-slate-400 absolute left-3.5 top-3" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                    <button x-show="search" @click="search = ''" class="absolute right-3 top-3 text-slate-400 hover:text-slate-600 text-xs">✕</button>
                </div>

                {{-- Category Tabs --}}
                <div class="flex items-center gap-1.5 overflow-x-auto pb-1 text-xs">
                    <button 
                        @click="category = 'all'" 
                        :class="category === 'all' ? 'bg-slate-900 text-white font-bold' : 'bg-slate-100 text-slate-600 hover:bg-slate-200'"
                        class="px-3 py-1.5 rounded-xl transition shrink-0"
                    >
                        🏷️ Semua Produk
                    </button>
                    <button 
                        @click="category = 'drinks'" 
                        :class="category === 'drinks' ? 'bg-slate-900 text-white font-bold' : 'bg-slate-100 text-slate-600 hover:bg-slate-200'"
                        class="px-3 py-1.5 rounded-xl transition shrink-0"
                    >
                        🥤 Minuman & Elektrolit
                    </button>
                    <button 
                        @click="category = 'supplements'" 
                        :class="category === 'supplements' ? 'bg-slate-900 text-white font-bold' : 'bg-slate-100 text-slate-600 hover:bg-slate-200'"
                        class="px-3 py-1.5 rounded-xl transition shrink-0"
                    >
                        💪 Suplemen & Whey
                    </button>
                    <button 
                        @click="category = 'snacks'" 
                        :class="category === 'snacks' ? 'bg-slate-900 text-white font-bold' : 'bg-slate-100 text-slate-600 hover:bg-slate-200'"
                        class="px-3 py-1.5 rounded-xl transition shrink-0"
                    >
                        🥑 Camilan Sehat
                    </button>
                    <button 
                        @click="category = 'gear'" 
                        :class="category === 'gear' ? 'bg-slate-900 text-white font-bold' : 'bg-slate-100 text-slate-600 hover:bg-slate-200'"
                        class="px-3 py-1.5 rounded-xl transition shrink-0"
                    >
                        🏋️ Aksesoris Gym
                    </button>
                </div>
            </div>

            {{-- Products Grid --}}
            <div class="grid grid-cols-2 sm:grid-cols-3 xl:grid-cols-4 gap-3.5">
                <template x-for="p in filteredProducts" :key="p.id">
                    <div 
                        @click="addToCart(p)"
                        class="bg-white border-2 rounded-2xl p-3.5 shadow-sm hover:border-lime-500 hover:shadow-lg cursor-pointer transition-all duration-150 flex flex-col justify-between relative group select-none"
                        :class="p.stock <= 0 ? 'opacity-50 bg-slate-50 border-slate-200 cursor-not-allowed' : (p.stock <= p.min_stock_alert ? 'border-amber-300 bg-amber-50/10' : 'border-slate-200/90')"
                    >
                        <div>
                            {{-- Top Row: Category Pill & High-Contrast Stock Badge --}}
                            <div class="flex items-center justify-between gap-1.5 mb-2.5">
                                <span 
                                    class="text-[10px] font-bold uppercase tracking-wider px-2 py-0.5 rounded-lg bg-slate-100 text-slate-700 truncate"
                                    x-text="getCategoryLabel(p.category)"
                                ></span>

                                {{-- HIGH CONTRAST STOCK BADGE --}}
                                <template x-if="p.stock <= 0">
                                    <span class="inline-flex items-center gap-1 text-[11px] font-black px-2.5 py-0.5 rounded-full bg-rose-600 text-white shadow-sm shrink-0">
                                        <span>✕</span>
                                        <span>Habis</span>
                                    </span>
                                </template>
                                <template x-if="p.stock > 0 && p.stock <= p.min_stock_alert">
                                    <span class="inline-flex items-center gap-1 text-[11px] font-black px-2.5 py-0.5 rounded-full bg-amber-400 text-slate-950 border border-amber-500 shadow-sm shrink-0 animate-pulse">
                                        <span>⚠️</span>
                                        <span>Sisa <strong x-text="p.stock"></strong></span>
                                    </span>
                                </template>
                                <template x-if="p.stock > p.min_stock_alert">
                                    <span class="inline-flex items-center gap-1.5 text-[11px] font-black px-2.5 py-0.5 rounded-full bg-emerald-500 text-white shadow-sm shrink-0">
                                        <span class="w-1.5 h-1.5 rounded-full bg-white"></span>
                                        <span>Stok: <strong x-text="p.stock"></strong></span>
                                    </span>
                                </template>
                            </div>

                            {{-- Image / Visual Icon Container --}}
                            <div 
                                class="w-full h-24 rounded-xl border overflow-hidden mb-3 flex items-center justify-center relative bg-gradient-to-br transition group-hover:scale-[1.02]"
                                :class="getCategoryBg(p.category)"
                            >
                                <template x-if="p.image">
                                    <img :src="'/storage/' + p.image" :alt="p.name" class="w-full h-full object-cover">
                                </template>
                                <template x-if="!p.image">
                                    <span class="text-4xl filter drop-shadow-sm select-none" x-text="getCategoryEmoji(p.category)"></span>
                                </template>
                            </div>

                            <h4 class="font-display font-bold text-xs sm:text-sm text-slate-900 line-clamp-2 leading-snug group-hover:text-lime-700 transition" x-text="p.name"></h4>
                            <p class="text-[11px] text-slate-400 font-mono mt-0.5" x-text="p.sku ? p.sku + ' · ' + (p.unit || 'pcs') : (p.unit || 'pcs')"></p>
                        </div>

                        {{-- Bottom Row: Big Bold Price & Prominent Lime Add Button --}}
                        <div class="mt-3.5 pt-3 border-t border-slate-100 flex items-center justify-between">
                            <div>
                                <span class="text-[10px] text-slate-400 uppercase font-bold block leading-none mb-0.5">Harga</span>
                                <span class="font-display font-black text-base lg:text-lg text-slate-900" x-text="formatRupiah(p.price)"></span>
                            </div>
                            
                            <button 
                                type="button" 
                                class="w-8 h-8 rounded-xl bg-lime-500 text-slate-950 font-black text-base flex items-center justify-center shadow-sm group-hover:bg-lime-400 group-hover:scale-110 active:scale-95 transition"
                                :disabled="p.stock <= 0"
                            >
                                +
                            </button>
                        </div>
                    </div>
                </template>

                <template x-if="filteredProducts.length === 0">
                    <div class="col-span-full py-12 text-center text-slate-400 text-xs">
                        <p class="text-3xl mb-2">🔍</p>
                        <p class="font-bold text-slate-600">Tidak ada produk ditemukan</p>
                        <p class="text-slate-400 mt-1">Coba gunakan kata kunci pencarian lain atau pilih kategori Semua.</p>
                    </div>
                </template>
            </div>

        </div>

        {{-- =========================================================
            PANEL KANAN (4 COL): KERANJANG & CHECKOUT
        ========================================================= --}}
        <div class="lg:col-span-5 xl:col-span-4 sticky top-20">
            <div class="bg-white border border-slate-200 rounded-3xl p-5 sm:p-6 shadow-sm flex flex-col justify-between min-h-[580px]">
                
                <div>
                    {{-- Cart Header --}}
                    <div class="flex items-center justify-between pb-4 border-b border-slate-100">
                        <div class="flex items-center gap-2">
                            <span class="text-lg">🛒</span>
                            <h3 class="font-display font-bold text-base text-slate-900">Keranjang Kasir</h3>
                        </div>
                        <button 
                            x-show="cart.length > 0"
                            @click="clearCart()" 
                            class="text-xs text-rose-500 hover:text-rose-700 font-semibold"
                        >
                            Kosongkan
                        </button>
                    </div>

                    {{-- Customer Selector --}}
                    <div class="py-3 border-b border-slate-100">
                        <label class="block text-[11px] font-bold text-slate-500 uppercase tracking-wider mb-1.5">Customer / Pembeli</label>
                        <select 
                            x-model="memberId" 
                            @change="onMemberChange()"
                            class="w-full text-xs rounded-xl border border-slate-200 py-2 px-3 bg-slate-50 focus:bg-white focus:outline-none focus:ring-2 focus:ring-lime-500/20"
                        >
                            <option value="">👤 Tamu / Non-Member (Walk-in)</option>
                            <template x-for="m in members" :key="m.id">
                                <option :value="m.id" x-text="m.user.name + ' (' + m.member_code + ')'"></option>
                            </template>
                        </select>
                    </div>

                    {{-- Cart Items List --}}
                    <div class="py-3 max-h-72 overflow-y-auto space-y-2.5">
                        <template x-for="item in cart" :key="item.product_id">
                            <div class="p-3 rounded-2xl bg-slate-50 border border-slate-100 flex items-center justify-between gap-3 text-xs">
                                <div class="flex-1 min-w-0">
                                    <h5 class="font-bold text-slate-900 truncate" x-text="item.name"></h5>
                                    <p class="text-slate-500 mt-0.5" x-text="formatRupiah(item.price)"></p>
                                </div>

                                {{-- Quantity Controls --}}
                                <div class="flex items-center gap-1.5 shrink-0 bg-white border border-slate-200 rounded-xl p-1 shadow-2xs">
                                    <button 
                                        @click="updateQuantity(item, -1)" 
                                        class="w-5 h-5 rounded-lg bg-slate-100 text-slate-600 hover:bg-slate-200 flex items-center justify-center font-bold"
                                    >
                                        -
                                    </button>
                                    <span class="w-6 text-center font-bold font-mono text-slate-900" x-text="item.quantity"></span>
                                    <button 
                                        @click="updateQuantity(item, 1)" 
                                        class="w-5 h-5 rounded-lg bg-slate-100 text-slate-600 hover:bg-slate-200 flex items-center justify-center font-bold"
                                    >
                                        +
                                    </button>
                                </div>

                                <div class="text-right shrink-0 min-w-[70px]">
                                    <p class="font-bold font-mono text-slate-900" x-text="formatRupiah(item.price * item.quantity)"></p>
                                    <button @click="removeFromCart(item)" class="text-[10px] text-slate-400 hover:text-rose-500 mt-0.5">Hapus</button>
                                </div>
                            </div>
                        </template>

                        <template x-if="cart.length === 0">
                            <div class="py-12 text-center text-slate-400 text-xs">
                                <p class="text-2xl mb-1">🛍️</p>
                                <p>Keranjang masih kosong</p>
                                <p class="text-[11px] text-slate-400 mt-0.5">Klik produk di panel sebelah kiri untuk menambahkan.</p>
                            </div>
                        </template>
                    </div>
                </div>

                {{-- Cart Summary & Action Buttons --}}
                <div class="pt-4 border-t border-slate-100 space-y-3">
                    <div class="space-y-1.5 text-xs">
                        <div class="flex justify-between text-slate-500">
                            <span>Subtotal</span>
                            <span class="font-mono font-bold text-slate-800" x-text="formatRupiah(subtotal)"></span>
                        </div>
                        <div class="flex justify-between items-center text-slate-500">
                            <span>Diskon (Rp)</span>
                            <input 
                                type="number" 
                                min="0" 
                                step="1000" 
                                x-model="discount" 
                                placeholder="0" 
                                class="w-24 text-right py-1 px-2 rounded-lg border border-slate-200 bg-slate-50 text-xs font-mono font-bold text-rose-600 focus:outline-none"
                            >
                        </div>
                        <div class="flex justify-between text-base pt-2 border-t border-slate-100">
                            <span class="font-display font-bold text-slate-900">Total Tagihan</span>
                            <span class="font-display font-extrabold text-xl text-slate-900" x-text="formatRupiah(totalAmount)"></span>
                        </div>
                    </div>

                    <button 
                        @click="openCheckout()"
                        :disabled="cart.length === 0"
                        :class="cart.length === 0 ? 'bg-slate-200 text-slate-400 cursor-not-allowed' : 'bg-lime-500 hover:bg-lime-400 text-slate-950 shadow-md'"
                        class="w-full py-3.5 rounded-2xl font-display font-bold text-sm transition flex items-center justify-center gap-2"
                    >
                        <span>💳 Proses Pembayaran</span>
                        <span class="text-xs px-2 py-0.5 rounded-md bg-slate-950/15 font-mono">F4</span>
                    </button>
                </div>

            </div>
        </div>

    </div>

    {{-- =========================================================
        MODAL 1: CHECKOUT & MULTI-PAYMENT
    ========================================================= --}}
    <div x-show="checkoutModal" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-950/75 backdrop-blur-xs" @keydown.escape.window="checkoutModal = false">
        <div @click.outside="checkoutModal = false" class="bg-white border border-slate-200 rounded-3xl w-full max-w-lg p-6 sm:p-7 shadow-2xl relative max-h-[90vh] overflow-y-auto">
            
            <button @click="checkoutModal = false" class="absolute top-4 right-4 text-slate-400 hover:text-slate-700 p-1">✕</button>

            <div class="text-center pb-4 border-b border-slate-100">
                <span class="text-[10px] font-bold uppercase tracking-wider text-slate-400">TOTAL PEMBAYARAN KASIR</span>
                <p class="font-display font-extrabold text-3xl sm:text-4xl text-slate-900 mt-1" x-text="formatRupiah(totalAmount)"></p>
                <p class="text-xs text-slate-500 mt-1">Pembeli: <strong class="text-slate-800" x-text="customerName"></strong></p>
            </div>

            {{-- Error Message Alert --}}
            <div x-show="errorMessage" class="mt-4 p-3 rounded-xl bg-rose-50 border border-rose-200 text-rose-700 text-xs font-semibold" x-text="errorMessage"></div>

            {{-- Payment Method Selection --}}
            <div class="mt-5 space-y-4">
                <label class="block text-xs font-bold text-slate-700">Pilih Metode Pembayaran</label>
                
                <div class="grid grid-cols-3 gap-2.5">
                    <button 
                        type="button" 
                        @click="paymentMethod = 'cash'; cashReceived = totalAmount" 
                        :class="paymentMethod === 'cash' ? 'bg-slate-900 text-white font-bold border-slate-900 shadow-sm' : 'bg-slate-50 text-slate-700 border-slate-200 hover:bg-slate-100'"
                        class="p-3 rounded-2xl border text-xs flex flex-col items-center gap-1.5 transition"
                    >
                        <span class="text-xl">💵</span>
                        <span>Tunai (Cash)</span>
                    </button>

                    <button 
                        type="button" 
                        @click="paymentMethod = 'qris'" 
                        :class="paymentMethod === 'qris' ? 'bg-slate-900 text-white font-bold border-slate-900 shadow-sm' : 'bg-slate-50 text-slate-700 border-slate-200 hover:bg-slate-100'"
                        class="p-3 rounded-2xl border text-xs flex flex-col items-center gap-1.5 transition"
                    >
                        <span class="text-xl">📱</span>
                        <span>QRIS Toko</span>
                    </button>

                    <button 
                        type="button" 
                        @click="paymentMethod = 'transfer'" 
                        :class="paymentMethod === 'transfer' ? 'bg-slate-900 text-white font-bold border-slate-900 shadow-sm' : 'bg-slate-50 text-slate-700 border-slate-200 hover:bg-slate-100'"
                        class="p-3 rounded-2xl border text-xs flex flex-col items-center gap-1.5 transition"
                    >
                        <span class="text-xl">🏦</span>
                        <span>Transfer Bank</span>
                    </button>
                </div>

                {{-- OPSI 1: TUNAI (CASH) --}}
                <div x-show="paymentMethod === 'cash'" class="p-4 rounded-2xl bg-slate-50 border border-slate-200 space-y-3">
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Uang Tunai Diterima (Rp)</label>
                        <input 
                            type="number" 
                            x-model="cashReceived" 
                            class="w-full text-base font-mono font-bold py-2.5 px-3 rounded-xl border border-slate-300 bg-white focus:outline-none focus:ring-2 focus:ring-lime-500"
                            placeholder="Contoh: 50000"
                        >
                    </div>

                    {{-- Quick Cash Buttons --}}
                    <div class="flex flex-wrap gap-1.5">
                        <button type="button" @click="setExactCash()" class="px-2.5 py-1 rounded-lg bg-white border border-slate-200 text-xs font-bold text-slate-700 hover:bg-slate-100">Uang Pas</button>
                        <button type="button" @click="setQuickCash(20000)" class="px-2.5 py-1 rounded-lg bg-white border border-slate-200 text-xs font-mono font-bold text-slate-700 hover:bg-slate-100">20.000</button>
                        <button type="button" @click="setQuickCash(50000)" class="px-2.5 py-1 rounded-lg bg-white border border-slate-200 text-xs font-mono font-bold text-slate-700 hover:bg-slate-100">50.000</button>
                        <button type="button" @click="setQuickCash(100000)" class="px-2.5 py-1 rounded-lg bg-white border border-slate-200 text-xs font-mono font-bold text-slate-700 hover:bg-slate-100">100.000</button>
                        <button type="button" @click="setQuickCash(200000)" class="px-2.5 py-1 rounded-lg bg-white border border-slate-200 text-xs font-mono font-bold text-slate-700 hover:bg-slate-100">200.000</button>
                    </div>

                    {{-- Cash Change Box --}}
                    <div class="p-3 rounded-xl bg-white border border-slate-200 flex justify-between items-center text-xs">
                        <span class="text-slate-500">Uang Kembalian:</span>
                        <span class="font-display font-extrabold text-lg text-emerald-600" x-text="formatRupiah(cashChange)"></span>
                    </div>
                </div>

                {{-- OPSI 2: QRIS --}}
                <div x-show="paymentMethod === 'qris'" class="p-4 rounded-2xl bg-slate-50 border border-slate-200 text-center space-y-3">
                    <div class="w-44 h-44 mx-auto bg-white p-2 rounded-2xl border-2 border-slate-900 shadow-sm flex flex-col items-center justify-center">
                        {{-- QR Code Image/Placeholder --}}
                        <svg class="w-32 h-32 text-slate-900" viewBox="0 0 24 24" fill="currentColor">
                            <path d="M2 2h8v8H2V2zm2 2v4h4V4H4zm8-2h8v8h-8V2zm2 2v4h4V4h-4zM2 14h8v8H2v-8zm2 2v4h4v-4H4zm13-2h3v3h-3v-3zm0 5h3v3h-3v-3zm-5-5h3v3h-3v-3zm0 5h3v3h-3v-3zm5-2h3v2h-3v-2zM5 5h2v2H5V5zm10 0h2v2h-2V5zM5 17h2v2H5v-2z"/>
                        </svg>
                        <span class="text-[9px] font-bold text-slate-500 tracking-wider uppercase">QRIS STANDAR INDONESIA</span>
                    </div>
                    <p class="text-xs text-slate-600">Arahkan customer untuk scan QRIS menggunakan aplikasi GoPay / OVO / Dana / BCA / Mobile Banking apa saja.</p>
                </div>

                {{-- OPSI 3: TRANSFER BANK --}}
                <div x-show="paymentMethod === 'transfer'" class="p-4 rounded-2xl bg-slate-50 border border-slate-200 space-y-2.5 text-xs">
                    <p class="font-bold text-slate-800">Rekening Tujuan Toko GymPulse:</p>
                    <div class="p-2.5 rounded-xl bg-white border border-slate-200 flex justify-between items-center">
                        <div>
                            <p class="font-bold text-slate-900">BCA: 845-019-2831</p>
                            <p class="text-[11px] text-slate-500">a.n GymPulse Fitness Center</p>
                        </div>
                        <span class="text-[10px] font-bold px-2 py-0.5 rounded bg-blue-100 text-blue-800">BCA</span>
                    </div>
                    <div class="p-2.5 rounded-xl bg-white border border-slate-200 flex justify-between items-center">
                        <div>
                            <p class="font-bold text-slate-900">Mandiri: 142-00-19283-11</p>
                            <p class="text-[11px] text-slate-500">a.n GymPulse Fitness Center</p>
                        </div>
                        <span class="text-[10px] font-bold px-2 py-0.5 rounded bg-amber-100 text-amber-800">Mandiri</span>
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Catatan Tambahan (Opsional)</label>
                    <input type="text" x-model="notes" placeholder="Contoh: Titip di loker / sudah lunas" class="w-full text-xs py-2 px-3 rounded-xl border border-slate-200 bg-white focus:outline-none">
                </div>
            </div>

            <div class="mt-6 pt-4 border-t border-slate-100 flex gap-3">
                <button 
                    type="button" 
                    @click="checkoutModal = false" 
                    class="w-1/3 py-3 rounded-2xl bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold text-xs transition"
                >
                    Batal
                </button>
                <button 
                    type="button" 
                    @click="submitCheckout()" 
                    :disabled="loading"
                    class="w-2/3 py-3 rounded-2xl bg-lime-500 hover:bg-lime-400 text-slate-950 font-display font-bold text-sm transition flex items-center justify-center gap-2 shadow-md"
                >
                    <span x-show="!loading">✅ Konfirmasi & Bayar</span>
                    <span x-show="loading">Memproses...</span>
                </button>
            </div>

        </div>
    </div>

    {{-- =========================================================
        MODAL 2: SUCCESS & PRINT RECEIPT
    ========================================================= --}}
    <div x-show="successModal" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-950/75 backdrop-blur-xs">
        <div class="bg-white border border-slate-200 rounded-3xl w-full max-w-md p-6 sm:p-7 shadow-2xl text-center space-y-4">
            
            <div class="w-16 h-16 rounded-full bg-emerald-100 text-emerald-600 flex items-center justify-center mx-auto text-3xl">
                ✓
            </div>

            <div>
                <h3 class="font-display font-bold text-xl text-slate-900">Transaksi Berhasil!</h3>
                <p class="text-xs text-slate-500 mt-1" x-text="'No. Invoice: ' + (lastOrder ? lastOrder.invoice_number : '-')"></p>
                <p class="font-display font-extrabold text-2xl text-slate-900 mt-2" x-text="formatRupiah(lastOrder ? lastOrder.total_amount : 0)"></p>
            </div>

            <template x-if="lastOrder && lastOrder.payment_method === 'cash'">
                <div class="p-3 rounded-xl bg-slate-50 border border-slate-200 text-xs space-y-1">
                    <div class="flex justify-between">
                        <span class="text-slate-500">Tunai Diterima:</span>
                        <strong class="font-mono text-slate-800" x-text="formatRupiah(lastOrder.cash_received)"></strong>
                    </div>
                    <div class="flex justify-between text-emerald-700 font-bold">
                        <span>Kembalian:</span>
                        <span class="font-mono" x-text="formatRupiah(lastOrder.cash_change)"></span>
                    </div>
                </div>
            </template>

            <div class="pt-3 border-t border-slate-100 space-y-2">
                <button 
                    @click="printReceipt(lastOrder.receipt_url)" 
                    class="w-full py-3 rounded-xl bg-slate-900 text-white text-xs font-bold hover:bg-slate-800 transition flex items-center justify-center gap-2 shadow-sm"
                >
                    <span>🖨️ Cetak Struk Nota</span>
                </button>
                <button 
                    @click="successModal = false" 
                    class="w-full py-2.5 rounded-xl bg-slate-100 text-slate-700 text-xs font-bold hover:bg-slate-200 transition"
                >
                    ➕ Transaksi Baru
                </button>
            </div>

        </div>
    </div>

</div>
@endsection
