@extends('layouts.cashier')
@section('title', 'Kasir POS (Point of Sale)')

@section('content')
<script>
document.addEventListener('alpine:init', () => {
    Alpine.data('cashierPosApp', cashierPosApp);
});

function cashierPosApp() {
    return {
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
        qrisModal: false,
        successModal: false,
        lastOrder: null,
        loading: false,
        errorMessage: '',

        // QRIS Dinamis & Real-time Listener state
        qrisData: null,
        qrisOrderId: null,
        qrisTimerSeconds: 300,
        qrisTimerInterval: null,
        qrisPollInterval: null,
        qrisSimulating: false,
        qrisExpired: false,

        // Member Pickups state
        pickupModal: false,
        pickupOrders: [],
        pickupSearch: '',
        pickupFilter: 'ready',
        pickupPendingCount: 0,
        pickupLoading: false,
        pickupHandingOver: null,
        pickupPollInterval: null,

        products: @json($products),
        members: @json($members),

        init() {
            this.loadPickups();
            this.pickupPollInterval = setInterval(() => this.loadPickups(), 10000);
        },

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

        getProductImage(p) {
            if (!p) return '';
            let img = p.image_url || p.image;
            if (!img) return '';
            if (img.startsWith('http://') || img.startsWith('https://') || img.startsWith('/')) return img;
            if (img.startsWith('storage/')) return '/' + img;
            return '/storage/' + img;
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

        openCheckout() {
            if (this.cart.length === 0) {
                alert('Keranjang belanja masih kosong!');
                return;
            }
            this.errorMessage = '';
            this.paymentMethod = 'cash';
            this.cashReceived = this.totalAmount;
            this.checkoutModal = true;
        },

        selectMember(e) {
            const mId = e.target.value;
            this.memberId = mId;
            if (mId) {
                const found = this.members.find(m => m.id == mId);
                this.customerName = (found && found.user) ? found.user.name + ' (' + found.member_code + ')' : 'Member';
            } else {
                this.customerName = 'Tamu / Walk-in';
            }
        },

        submitCheckout() {
            if (this.paymentMethod === 'cash') {
                const received = parseFloat(this.cashReceived) || 0;
                if (received < this.totalAmount) {
                    this.errorMessage = 'Uang tunai yang diterima kurang dari total belanja!';
                    return;
                }
            }

            this.loading = true;
            this.errorMessage = '';

            fetch('{{ route('cashier.pos.checkout') }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json'
                },
                body: JSON.stringify({
                    items: this.cart,
                    payment_method: this.paymentMethod,
                    cash_received: this.cashReceived,
                    discount: this.discount,
                    member_id: this.memberId || null,
                    customer_name: this.customerName,
                    notes: this.notes
                })
            })
            .then(res => res.json().then(data => ({ status: res.status, body: data })))
            .then(res => {
                this.loading = false;
                if (res.status === 200 && res.body.success) {
                    if (res.body.is_qris) {
                        this.startQrisFlow(res.body);
                    } else {
                        this.lastOrder = res.body.order;
                        this.lastOrder.receipt_url = res.body.receipt_url;
                        
                        // Kurangi stok di UI lokal
                        this.cart.forEach(item => {
                            const prod = this.products.find(p => p.id === item.product_id);
                            if (prod) prod.stock -= item.quantity;
                        });

                        this.cart = [];
                        this.discount = 0;
                        this.notes = '';
                        this.checkoutModal = false;
                        this.successModal = true;
                    }
                } else {
                    this.errorMessage = res.body.message || 'Terjadi kesalahan saat memproses transaksi.';
                }
            })
            .catch(err => {
                this.loading = false;
                this.errorMessage = 'Gagal menghubungi server. Silakan coba lagi.';
                console.error(err);
            });
        },

        startQrisFlow(data) {
            this.qrisData = data.qris;
            this.qrisOrderId = data.order.id;
            this.lastOrder = data.order;
            this.lastOrder.receipt_url = data.receipt_url;
            this.checkoutModal = false;
            this.qrisModal = true;
            this.qrisExpired = false;
            this.qrisTimerSeconds = 300;

            // Timer countdown
            clearInterval(this.qrisTimerInterval);
            this.qrisTimerInterval = setInterval(() => {
                if (this.qrisTimerSeconds > 0) {
                    this.qrisTimerSeconds--;
                } else {
                    this.qrisExpired = true;
                    this.stopQrisPolling();
                }
            }, 1000);

            // Polling realtime status every 2 seconds
            clearInterval(this.qrisPollInterval);
            this.qrisPollInterval = setInterval(() => {
                this.checkQrisStatus(data.status_url);
            }, 2000);
        },

        get qrisFormattedTimer() {
            const mins = Math.floor(this.qrisTimerSeconds / 60);
            const secs = this.qrisTimerSeconds % 60;
            return String(mins).padStart(2, '0') + ':' + String(secs).padStart(2, '0');
        },

        checkQrisStatus(url) {
            if (!url || this.qrisExpired || !this.qrisModal) return;

            fetch(url, {
                headers: { 'Accept': 'application/json' }
            })
            .then(r => r.json())
            .then(res => {
                if (res.success && res.payment_status === 'paid') {
                    this.handleQrisSuccess(res.order, res.receipt_url);
                }
            })
            .catch(err => console.error('Status check error:', err));
        },

        simulateQrisPayment() {
            if (!this.qrisOrderId) return;
            this.qrisSimulating = true;
            
            fetch('/cashier/pos/orders/' + this.qrisOrderId + '/simulate-qris', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json'
                }
            })
            .then(r => r.json())
            .then(res => {
                this.qrisSimulating = false;
                if (res.success) {
                    this.handleQrisSuccess(res.order, res.receipt_url);
                }
            })
            .catch(err => {
                this.qrisSimulating = false;
                console.error('Simulate payment error:', err);
            });
        },

        handleQrisSuccess(order, receiptUrl) {
            this.stopQrisPolling();
            this.qrisModal = false;
            this.lastOrder = order;
            if (receiptUrl) this.lastOrder.receipt_url = receiptUrl;

            // Kurangi stok di UI lokal
            this.cart.forEach(item => {
                const prod = this.products.find(p => p.id === item.product_id);
                if (prod) prod.stock -= item.quantity;
            });

            this.cart = [];
            this.discount = 0;
            this.notes = '';
            this.successModal = true;
        },

        cancelQrisOrder() {
            if (!confirm('Batalkan transaksi QRIS ini?')) return;
            this.stopQrisPolling();
            
            if (this.qrisOrderId) {
                fetch('/cashier/pos/orders/' + this.qrisOrderId + '/cancel', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Accept': 'application/json'
                    }
                });
            }

            this.qrisModal = false;
            this.checkoutModal = true;
        },

        stopQrisPolling() {
            if (this.qrisPollInterval) clearInterval(this.qrisPollInterval);
            if (this.qrisTimerInterval) clearInterval(this.qrisTimerInterval);
        },

        printReceipt(url) {
            const win = window.open(url, '_blank', 'width=400,height=600');
            if (win) {
                win.focus();
            }
        },

        async loadPickups() {
            try {
                const params = new URLSearchParams();
                if (this.pickupSearch) params.append('search', this.pickupSearch);
                if (this.pickupFilter !== 'all') params.append('status', this.pickupFilter);

                const res = await fetch("{{ route('cashier.pos.member-pickups') }}?" + params.toString(), {
                    headers: { 'Accept': 'application/json' }
                });
                const data = await res.json();
                if (data.success) {
                    this.pickupOrders = data.orders;
                    this.pickupPendingCount = data.pending_count;
                }
            } catch (e) {}
        },

        async markOrderPickedUp(order) {
            if (!confirm('Serahkan barang untuk pesanan ' + order.invoice_number + ' (' + order.customer_name + ')?')) return;

            this.pickupHandingOver = order.id;
            try {
                const res = await fetch("{{ url('/cashier/pos/orders') }}/" + order.id + "/pickup", {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Accept': 'application/json'
                    }
                });
                const data = await res.json();
                if (data.success) {
                    await this.loadPickups();
                } else {
                    alert(data.message || 'Gagal menyerahkan pesanan');
                }
            } catch (err) {
                alert('Gagal menghubungi server');
            } finally {
                this.pickupHandingOver = null;
            }
        }
    };
}
window.cashierPosApp = cashierPosApp;
</script>

<div 
    x-data="cashierPosApp()"
    x-init="init()"
    class="space-y-4"
>

    {{-- SHIFT SUMMARY BANNER --}}
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-3 bg-white p-4 rounded-2xl border border-slate-200 shadow-sm">
        <div class="flex items-center gap-3">
            <div class="w-10 h-10 rounded-xl bg-lime-100 text-lime-800 flex items-center justify-center shrink-0">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V6m0 10v2m9-8a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            </div>
            <div>
                <p class="text-[11px] text-slate-500 font-semibold uppercase tracking-wider">Uang Fisik di Laci (Cash)</p>
                <p class="text-base lg:text-lg font-display font-extrabold text-slate-900">Rp{{ number_format($shiftCashInDrawer, 0, ',', '.') }}</p>
            </div>
        </div>

        <div class="flex items-center gap-3">
            <div class="w-10 h-10 rounded-xl bg-blue-100 text-blue-800 flex items-center justify-center shrink-0">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
            </div>
            <div>
                <p class="text-[11px] text-slate-500 font-semibold uppercase tracking-wider">QRIS & Transfer</p>
                <p class="text-base lg:text-lg font-display font-extrabold text-blue-900">Rp{{ number_format($shiftNonCashTotal, 0, ',', '.') }}</p>
            </div>
        </div>

        <div class="flex items-center gap-3">
            <div class="w-10 h-10 rounded-xl bg-emerald-100 text-emerald-800 flex items-center justify-center shrink-0">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            </div>
            <div>
                <p class="text-[11px] text-slate-500 font-semibold uppercase tracking-wider">Total Omzet Shift Ini</p>
                <p class="text-base lg:text-lg font-display font-extrabold text-emerald-700">Rp{{ number_format($shiftTotalSales, 0, ',', '.') }}</p>
            </div>
        </div>

        <div class="flex items-center justify-between lg:justify-end gap-2 border-t lg:border-t-0 pt-2 lg:pt-0 col-span-2 lg:col-span-1">
            <button 
                type="button" 
                @click="pickupModal = true; loadPickups()" 
                class="text-xs font-bold text-slate-950 bg-lime-400 hover:bg-lime-300 active:scale-95 px-3 py-1.5 rounded-xl transition shadow-sm flex items-center gap-1.5"
            >
                <span>📦 Pickup Member</span>
                <span 
                    x-show="pickupPendingCount > 0" 
                    x-text="pickupPendingCount"
                    class="px-1.5 py-0.2 rounded-full bg-slate-950 text-lime-400 text-[10px] font-black animate-pulse"
                ></span>
            </button>
            <a href="{{ route('cashier.orders.index') }}" class="text-xs font-bold text-slate-700 hover:text-slate-900 bg-slate-100 hover:bg-slate-200 px-2.5 py-1.5 rounded-xl transition">
                Rekap &rarr;
            </a>
        </div>
    </div>

    {{-- POS WORKBENCH GRID --}}
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-5 items-start">
        
        {{-- LEFT PANEL: PRODUCT CATALOG (8 cols) --}}
        <div class="lg:col-span-7 xl:col-span-8 space-y-4">
            
            {{-- SEARCH & CATEGORY BAR (Sticky for quick access while scrolling products) --}}
            <div class="bg-white p-4 rounded-2xl border border-slate-200 shadow-sm flex flex-col sm:flex-row gap-3 items-center justify-between sticky top-20 z-10">
                <div class="relative w-full sm:w-72">
                    <svg class="w-4 h-4 text-slate-400 absolute left-3.5 top-1/2 -translate-y-1/2" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                    </svg>
                    <input 
                        type="text" 
                        x-model="search" 
                        placeholder="Cari produk / barcode..." 
                        class="w-full pl-10 pr-4 py-2 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-lime-500 focus:bg-white transition"
                    >
                </div>

                {{-- CATEGORY FILTER CHIPS --}}
                <div class="flex items-center gap-1.5 overflow-x-auto w-full sm:w-auto pb-1 sm:pb-0">
                    <button 
                        type="button" 
                        @click="category = 'all'"
                        :class="category === 'all' ? 'bg-slate-900 text-white font-bold' : 'bg-slate-100 text-slate-600 hover:bg-slate-200'"
                        class="px-3.5 py-1.5 rounded-xl text-xs whitespace-nowrap transition"
                    >
                        Semua
                    </button>
                    <button 
                        type="button" 
                        @click="category = 'drinks'"
                        :class="category === 'drinks' ? 'bg-slate-900 text-white font-bold' : 'bg-slate-100 text-slate-600 hover:bg-slate-200'"
                        class="px-3.5 py-1.5 rounded-xl text-xs whitespace-nowrap transition"
                    >
                        🥤 Minuman
                    </button>
                    <button 
                        type="button" 
                        @click="category = 'supplements'"
                        :class="category === 'supplements' ? 'bg-slate-900 text-white font-bold' : 'bg-slate-100 text-slate-600 hover:bg-slate-200'"
                        class="px-3.5 py-1.5 rounded-xl text-xs whitespace-nowrap transition"
                    >
                        ⚡ Suplemen
                    </button>
                    <button 
                        type="button" 
                        @click="category = 'snacks'"
                        :class="category === 'snacks' ? 'bg-slate-900 text-white font-bold' : 'bg-slate-100 text-slate-600 hover:bg-slate-200'"
                        class="px-3.5 py-1.5 rounded-xl text-xs whitespace-nowrap transition"
                    >
                        🍫 Snack Sehat
                    </button>
                    <button 
                        type="button" 
                        @click="category = 'gear'"
                        :class="category === 'gear' ? 'bg-slate-900 text-white font-bold' : 'bg-slate-100 text-slate-600 hover:bg-slate-200'"
                        class="px-3.5 py-1.5 rounded-xl text-xs whitespace-nowrap transition"
                    >
                        🎽 Aksesoris
                    </button>
                </div>
            </div>

            {{-- PRODUCT GRID (Full natural height, smooth page scroll) --}}
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

                            {{-- Image / Visual Container (Aspect-Square for perfect 1:1 photos without cropping) --}}
                            <div 
                                class="w-full aspect-square rounded-xl border overflow-hidden mb-2.5 flex items-center justify-center relative bg-slate-100 transition group-hover:scale-[1.02]"
                                :class="getCategoryBg(p.category)"
                            >
                                <template x-if="p.image">
                                    <img :src="getProductImage(p)" :alt="p.name" class="w-full h-full object-cover object-center" loading="lazy">
                                </template>
                                <template x-if="!p.image">
                                    <span class="text-4xl filter drop-shadow-sm select-none" x-text="getCategoryEmoji(p.category)"></span>
                                </template>
                            </div>

                            <h3 class="font-bold text-slate-900 text-sm line-clamp-2 leading-snug group-hover:text-lime-700 transition" x-text="p.name"></h3>
                            <p class="text-[11px] text-slate-400 font-mono mt-0.5" x-text="p.sku ? p.sku + ' · ' + (p.unit || 'pcs') : (p.unit || 'pcs')"></p>
                        </div>

                        {{-- Bottom Row: Big Bold Price & Prominent Lime Add Button --}}
                        <div class="mt-3.5 pt-3 border-t border-slate-100 flex items-center justify-between">
                            <div>
                                <span class="text-[10px] text-slate-500 uppercase font-bold block leading-none mb-0.5" style="color: #64748b !important;">Harga</span>
                                <span class="pos-price font-display font-black text-base lg:text-lg text-slate-950" style="color: #0f172a !important; font-weight: 900 !important;">
                                    Rp<span x-text="Number(p.price).toLocaleString('id-ID')"></span>
                                </span>
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
                    <div class="col-span-full py-16 text-center bg-white rounded-2xl border border-dashed border-slate-200">
                        <p class="text-slate-400 font-semibold text-sm">Produk tidak ditemukan.</p>
                        <p class="text-slate-400 text-xs mt-1">Coba kata kunci lain atau pilih semua kategori.</p>
                    </div>
                </template>
            </div>

        </div>

        {{-- RIGHT PANEL: SHOPPING CART & CHECKOUT (4-5 cols) --}}
        <div class="lg:col-span-5 xl:col-span-4 bg-white rounded-2xl border border-slate-200 shadow-sm p-4 lg:p-5 flex flex-col justify-between sticky top-16 lg:top-20 h-auto lg:h-[calc(100vh-6rem)] overflow-hidden">
            
            <div class="flex-1 min-h-0 flex flex-col">
                {{-- HEADER CART --}}
                <div class="flex items-center justify-between pb-3 border-b border-slate-100 shrink-0">
                    <div class="flex items-center gap-2">
                        <div class="w-8 h-8 rounded-lg bg-lime-400 text-slate-950 flex items-center justify-center font-bold text-sm">
                            🛒
                        </div>
                        <div>
                            <h2 class="font-display font-bold text-slate-900 text-base" style="color: #0f172a !important;">Keranjang Kasir</h2>
                            <p class="text-[11px] text-slate-400" x-text="cart.length + ' item dipilih'"></p>
                        </div>
                    </div>
                    <button 
                        type="button" 
                        @click="clearCart()" 
                        x-show="cart.length > 0"
                        class="text-xs text-rose-600 hover:text-rose-800 font-bold"
                    >
                        Kosongkan
                    </button>
                </div>

                {{-- MEMBER / CUSTOMER SELECTION --}}
                <div class="mt-3 p-2.5 rounded-xl bg-slate-50 border border-slate-200/80 space-y-1.5 shrink-0">
                    <div class="flex items-center justify-between">
                        <label class="text-[11px] font-bold text-slate-600 uppercase tracking-wider">Pelanggan / Member</label>
                        <span class="text-[10px] text-slate-400">Opsional</span>
                    </div>
                    <select 
                        @change="selectMember($event)"
                        class="w-full bg-white border border-slate-300 rounded-lg px-2.5 py-1.5 text-xs text-slate-800 focus:outline-none focus:ring-2 focus:ring-lime-500 font-medium"
                    >
                        <option value="">-- Tamu Umum (Walk-in) --</option>
                        <template x-for="m in members" :key="m.id">
                            <option :value="m.id" x-text="m.user.name + ' (' + m.member_code + ')'"></option>
                        </template>
                    </select>
                </div>

                {{-- CART ITEMS LIST (Dynamic Scrollable Area) --}}
                <div class="mt-3 space-y-2 flex-1 min-h-0 overflow-y-auto pr-1">
                    <template x-for="item in cart" :key="item.product_id">
                        <div class="flex items-center justify-between p-2 rounded-xl bg-slate-50/80 border border-slate-100 text-xs">
                            <div class="min-w-0 flex-1 pr-2">
                                <p class="font-bold text-slate-900 truncate" style="color: #0f172a !important;" x-text="item.name"></p>
                                <p class="text-[11px] text-slate-600 font-mono" style="color: #475569 !important;">
                                    Rp<span x-text="item.price.toLocaleString('id-ID')"></span> × <span x-text="item.quantity"></span>
                                </p>
                            </div>

                            <div class="flex items-center gap-1.5 shrink-0">
                                <button 
                                    type="button" 
                                    @click="updateQuantity(item, -1)" 
                                    class="w-6 h-6 rounded-md bg-white border border-slate-200 hover:bg-slate-100 font-bold flex items-center justify-center text-slate-700"
                                >
                                    -
                                </button>
                                <span class="font-bold text-xs w-5 text-center text-slate-900" style="color: #0f172a !important;" x-text="item.quantity"></span>
                                <button 
                                    type="button" 
                                    @click="updateQuantity(item, 1)" 
                                    class="w-6 h-6 rounded-md bg-white border border-slate-200 hover:bg-slate-100 font-bold flex items-center justify-center text-slate-700"
                                >
                                    +
                                </button>
                                <button 
                                    type="button" 
                                    @click="removeFromCart(item)" 
                                    class="text-rose-500 hover:text-rose-700 ml-1 p-1"
                                >
                                    &times;
                                </button>
                            </div>
                        </div>
                    </template>

                    <template x-if="cart.length === 0">
                        <div class="py-10 text-center text-slate-400">
                            <span class="text-3xl block mb-1">🛒</span>
                            <p class="text-xs font-semibold">Keranjang masih kosong</p>
                            <p class="text-[11px] text-slate-400">Pilih produk di sebelah kiri untuk mulai transaksi.</p>
                        </div>
                    </template>
                </div>
            </div>

            {{-- PAYMENT SUMMARY & BUTTON (Pinned to bottom) --}}
            <div class="mt-3 pt-3 border-t border-slate-200 space-y-2 shrink-0">
                <div class="flex justify-between text-xs text-slate-600" style="color: #475569 !important;">
                    <span>Subtotal</span>
                    <span class="font-mono font-bold text-slate-900" style="color: #0f172a !important;">
                        Rp<span x-text="subtotal.toLocaleString('id-ID')"></span>
                    </span>
                </div>

                <div class="flex items-center justify-between text-xs text-slate-600" style="color: #475569 !important;">
                    <span>Diskon (Rp)</span>
                    <input 
                        type="number" 
                        x-model="discount" 
                        min="0" 
                        class="w-24 px-2 py-1 text-right bg-slate-50 border border-slate-200 rounded-lg text-xs font-mono font-bold focus:outline-none focus:ring-1 focus:ring-lime-500 text-rose-600"
                        placeholder="0"
                    >
                </div>

                <div class="flex justify-between text-base font-display font-extrabold text-slate-900 pt-1.5 border-t border-slate-100">
                    <span style="color: #0f172a !important;">Total Tagihan</span>
                    <span class="pos-price font-display font-black text-lg text-slate-950" style="color: #0f172a !important; font-weight: 900 !important;">
                        Rp<span x-text="totalAmount.toLocaleString('id-ID')"></span>
                    </span>
                </div>

                <button 
                    type="button" 
                    @click="openCheckout()"
                    :disabled="cart.length === 0"
                    class="w-full py-3 rounded-xl bg-lime-500 hover:bg-lime-400 disabled:opacity-50 disabled:cursor-not-allowed text-slate-950 font-display font-bold text-sm transition shadow-sm flex items-center justify-center gap-2"
                >
                    <span>Bayar Sekarang (F8)</span>
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
                </button>
            </div>

        </div>

    </div>

    {{-- MODAL CHECKOUT & PAYMENT METHOD --}}
    <div 
        x-show="checkoutModal" 
        x-cloak 
        class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-sm"
    >
        <div 
            @click.away="if (!loading) checkoutModal = false"
            class="bg-white rounded-3xl max-w-lg w-full p-6 shadow-2xl border border-slate-100 relative max-h-[90vh] overflow-y-auto"
        >
            <div class="flex items-center justify-between pb-3 border-b border-slate-100">
                <h3 class="font-display font-bold text-lg text-slate-900">Pembayaran Kasir</h3>
                <button type="button" @click="checkoutModal = false" class="text-slate-400 hover:text-slate-600 text-lg font-bold">&times;</button>
            </div>

            {{-- ERROR MESSAGE --}}
            <template x-if="errorMessage">
                <div class="mt-3 p-3 rounded-xl bg-rose-50 border border-rose-200 text-rose-700 text-xs font-semibold" x-text="errorMessage"></div>
            </template>

            <div class="mt-4 space-y-4">
                
                {{-- TOTAL BOX --}}
                <div class="p-4 rounded-2xl bg-slate-900 text-white text-center">
                    <p class="text-xs text-slate-400 uppercase tracking-wider font-semibold">Total Tagihan Belanja</p>
                    <p class="font-display font-black text-2xl lg:text-3xl text-lime-400 mt-1">
                        Rp<span x-text="totalAmount.toLocaleString('id-ID')"></span>
                    </p>
                    <p class="text-[11px] text-slate-300 mt-1" x-text="'Pelanggan: ' + customerName"></p>
                </div>

                {{-- PAYMENT METHOD SELECTION --}}
                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-2">Metode Pembayaran</label>
                    <div class="grid grid-cols-3 gap-2">
                        <button 
                            type="button" 
                            @click="paymentMethod = 'cash'; cashReceived = totalAmount;"
                            :class="paymentMethod === 'cash' ? 'bg-lime-500 text-slate-950 font-bold border-lime-500 shadow-sm' : 'bg-slate-50 text-slate-700 border-slate-200 hover:bg-slate-100'"
                            class="py-2.5 px-3 rounded-xl border text-xs flex flex-col items-center gap-1 transition"
                        >
                            <span class="text-base">💵</span>
                            <span>Tunai (Cash)</span>
                        </button>
                        <button 
                            type="button" 
                            @click="paymentMethod = 'qris'"
                            :class="paymentMethod === 'qris' ? 'bg-lime-500 text-slate-950 font-bold border-lime-500 shadow-sm' : 'bg-slate-50 text-slate-700 border-slate-200 hover:bg-slate-100'"
                            class="py-2.5 px-3 rounded-xl border text-xs flex flex-col items-center gap-1 transition"
                        >
                            <span class="text-base">📱</span>
                            <span>QRIS</span>
                        </button>
                        <button 
                            type="button" 
                            @click="paymentMethod = 'transfer'"
                            :class="paymentMethod === 'transfer' ? 'bg-lime-500 text-slate-950 font-bold border-lime-500 shadow-sm' : 'bg-slate-50 text-slate-700 border-slate-200 hover:bg-slate-100'"
                            class="py-2.5 px-3 rounded-xl border text-xs flex flex-col items-center gap-1 transition"
                        >
                            <span class="text-base">🏦</span>
                            <span>Transfer Bank</span>
                        </button>
                    </div>
                </div>

                {{-- CASH INPUT (IF CASH) --}}
                <div x-show="paymentMethod === 'cash'" class="space-y-2.5 p-3.5 rounded-2xl bg-slate-50 border border-slate-200">
                    <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider">Uang Diterima dari Pelanggan</label>
                    <div class="relative">
                        <span class="absolute left-3.5 top-1/2 -translate-y-1/2 font-bold text-slate-400 text-sm">Rp</span>
                        <input 
                            type="number" 
                            x-model="cashReceived" 
                            class="w-full pl-10 pr-4 py-2.5 bg-white border border-slate-300 rounded-xl text-base font-bold font-mono text-slate-900 focus:outline-none focus:ring-2 focus:ring-lime-500"
                            placeholder="0"
                        >
                    </div>

                    {{-- QUICK CASH BUTTONS --}}
                    <div class="flex flex-wrap gap-1.5 pt-1">
                        <button type="button" @click="setExactCash()" class="px-2.5 py-1 rounded-lg bg-white border border-slate-200 text-[11px] font-bold text-slate-700 hover:bg-slate-100">Uang Pas</button>
                        <button type="button" @click="setQuickCash(20000)" class="px-2.5 py-1 rounded-lg bg-white border border-slate-200 text-[11px] font-mono text-slate-700 hover:bg-slate-100">20rb</button>
                        <button type="button" @click="setQuickCash(50000)" class="px-2.5 py-1 rounded-lg bg-white border border-slate-200 text-[11px] font-mono text-slate-700 hover:bg-slate-100">50rb</button>
                        <button type="button" @click="setQuickCash(100000)" class="px-2.5 py-1 rounded-lg bg-white border border-slate-200 text-[11px] font-mono text-slate-700 hover:bg-slate-100">100rb</button>
                        <button type="button" @click="setQuickCash(200000)" class="px-2.5 py-1 rounded-lg bg-white border border-slate-200 text-[11px] font-mono text-slate-700 hover:bg-slate-100">200rb</button>
                    </div>

                    {{-- KEMBALIAN --}}
                    <div class="mt-2 pt-2 border-t border-slate-200 flex justify-between items-center text-xs">
                        <span class="font-bold text-slate-600">Kembalian:</span>
                        <span 
                            :class="cashChange >= 0 ? 'text-emerald-700 font-bold' : 'text-rose-600 font-bold'"
                            class="font-mono text-sm"
                        >
                            Rp<span x-text="cashChange.toLocaleString('id-ID')"></span>
                        </span>
                    </div>
                </div>

                {{-- QRIS / TRANSFER INSTRUCTIONS --}}
                <div x-show="paymentMethod === 'qris'" class="p-3 rounded-2xl bg-blue-50 border border-blue-200 text-xs text-blue-900 flex items-center gap-3">
                    <span class="text-2xl">📱</span>
                    <div>
                        <p class="font-bold">Scan QRIS Dinamis / Statis</p>
                        <p class="text-[11px] text-blue-700">Pastikan notifikasi berhasil muncul di smartphone/aplikasi kasir sebelum konfirmasi.</p>
                    </div>
                </div>

                <div x-show="paymentMethod === 'transfer'" class="p-3 rounded-2xl bg-indigo-50 border border-indigo-200 text-xs text-indigo-900 flex items-center gap-3">
                    <span class="text-2xl">🏦</span>
                    <div>
                        <p class="font-bold">Transfer Rekening Gym</p>
                        <p class="text-[11px] text-indigo-700">Pastikan bukti transfer telah dicek oleh kasir.</p>
                    </div>
                </div>

                {{-- CATATAN TAMBAHAN --}}
                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">Catatan Tambahan (Opsional)</label>
                    <input 
                        type="text" 
                        x-model="notes" 
                        placeholder="Contoh: Titip di loker, botol dingin, dll" 
                        class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs focus:outline-none focus:ring-2 focus:ring-lime-500"
                    >
                </div>

            </div>

            <div class="mt-6 grid grid-cols-2 gap-3">
                <button 
                    type="button" 
                    @click="checkoutModal = false" 
                    class="py-2.5 rounded-xl border border-slate-200 text-slate-600 text-xs font-bold hover:bg-slate-50 transition"
                >
                    Batal
                </button>
                <button 
                    type="button" 
                    @click="submitCheckout()" 
                    :disabled="loading"
                    class="py-2.5 rounded-xl bg-lime-500 hover:bg-lime-400 text-slate-950 text-xs font-display font-bold transition shadow-sm flex items-center justify-center gap-2"
                >
                    <span x-show="!loading">Konfirmasi Pembayaran</span>
                    <span x-show="loading">Memproses...</span>
                </button>
            </div>
        </div>
    </div>

    {{-- MODAL QRIS DINAMIS LIVE & SIMULATOR --}}
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
                <p class="font-display font-black text-xl text-lime-400 mt-0.5">
                    Rp<span x-text="lastOrder ? Number(lastOrder.total_amount).toLocaleString('id-ID') : '0'"></span>
                </p>
                <p class="text-[10px] text-slate-300 mt-0.5" x-text="'Pelanggan: ' + (lastOrder ? lastOrder.customer_name : '')"></p>
            </div>

            {{-- Live Status Indicator --}}
            <div class="my-2 py-1.5 px-2.5 rounded-lg bg-emerald-50 border border-emerald-200 flex items-center justify-center gap-1.5 text-[11px] font-bold text-emerald-800">
                <span class="w-2 h-2 rounded-full bg-emerald-500 animate-ping"></span>
                <span>Menunggu Pembeli Scan & Membayar...</span>
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
                    Klik tombol di bawah untuk simulasi pembeli telah bayar via BCA/GoPay/OVO:
                </p>
                <button 
                    type="button" 
                    @click="simulateQrisPayment()"
                    :disabled="qrisSimulating"
                    class="w-full py-2 px-3 bg-indigo-600 hover:bg-indigo-700 active:scale-95 text-white rounded-lg text-xs font-bold transition shadow-sm flex items-center justify-center gap-1.5"
                >
                    <span x-show="!qrisSimulating">⚡ Simulasi: Pembeli Scan & Bayar Sukses</span>
                    <span x-show="qrisSimulating">Memverifikasi Pembayaran...</span>
                </button>
            </div>

            {{-- Cancel / Change Payment Method --}}
            <div class="mt-2.5">
                <button 
                    type="button" 
                    @click="cancelQrisOrder()"
                    class="text-xs text-rose-600 hover:text-rose-800 font-bold hover:underline"
                >
                    ✕ Batalkan / Ganti Metode Pembayaran
                </button>
            </div>
        </div>
    </div>

    {{-- MODAL SUCCESS & PRINT RECEIPT --}}
    <div 
        x-show="successModal" 
        x-cloak 
        class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-sm"
    >
        <div class="bg-white rounded-3xl max-w-sm w-full p-6 shadow-2xl border border-slate-100 text-center">
            <div class="w-16 h-16 mx-auto rounded-2xl bg-emerald-100 text-emerald-600 flex items-center justify-center text-2xl mb-3 shadow-inner">
                ✓
            </div>

            <h3 class="font-display font-bold text-lg text-slate-900">Transaksi Berhasil!</h3>
            <p class="text-xs text-slate-500 mt-0.5" x-text="lastOrder ? lastOrder.invoice_number : ''"></p>

            <div class="my-4 p-3.5 rounded-2xl bg-slate-50 border border-slate-200 text-xs space-y-1.5 text-left">
                <div class="flex justify-between">
                    <span class="text-slate-500">Total Bayar:</span>
                    <strong class="text-slate-900 font-mono" x-text="lastOrder ? 'Rp' + Number(lastOrder.total_amount).toLocaleString('id-ID') : ''"></strong>
                </div>
                <div class="flex justify-between">
                    <span class="text-slate-500">Metode:</span>
                    <span class="font-bold uppercase text-slate-700" x-text="lastOrder ? lastOrder.payment_method : ''"></span>
                </div>
                <template x-if="lastOrder && lastOrder.payment_method === 'cash'">
                    <div class="flex justify-between pt-1 border-t border-slate-200 text-emerald-700 font-bold">
                        <span>Kembalian:</span>
                        <span class="font-mono" x-text="'Rp' + Number(lastOrder.cash_change).toLocaleString('id-ID')"></span>
                    </div>
                </template>
            </div>

            <div class="space-y-2">
                <button 
                    type="button" 
                    @click="printReceipt(lastOrder.receipt_url)"
                    class="w-full py-2.5 rounded-xl bg-slate-900 hover:bg-slate-800 text-white text-xs font-bold transition flex items-center justify-center gap-2 shadow-sm"
                >
                    <svg class="w-4 h-4 text-lime-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
                    <span>Cetak Struk Termal</span>
                </button>
                <button 
                    type="button" 
                    @click="successModal = false"
                    class="w-full py-2 rounded-xl text-slate-500 hover:text-slate-800 text-xs font-semibold"
                >
                    Tutup & Transaksi Baru
                </button>
            </div>
        </div>
    </div>

    {{-- MODAL PESANAN MEMBER / PICKUP KASIR --}}
    <div 
        x-show="pickupModal" 
        x-cloak 
        class="fixed inset-0 z-50 flex items-center justify-center p-3 sm:p-4 bg-slate-950/75 backdrop-blur-md overflow-y-auto"
    >
        <div 
            @click.away="pickupModal = false"
            class="bg-white rounded-3xl max-w-2xl w-full p-5 sm:p-6 shadow-2xl border border-slate-100 relative my-auto max-h-[92vh] overflow-y-auto"
        >
            {{-- Header --}}
            <div class="flex items-center justify-between pb-3 border-b border-slate-100">
                <div class="flex items-center gap-2.5">
                    <span class="w-9 h-9 rounded-2xl bg-lime-400 text-slate-950 flex items-center justify-center text-lg font-black shadow-sm">📦</span>
                    <div>
                        <h3 class="font-display font-extrabold text-base sm:text-lg text-slate-900">Pesanan Member (Pickup di Kasir)</h3>
                        <p class="text-[11px] text-slate-500">Cocokkan nama & nomor invoice saat member menunjukkan bukti bayar QRIS</p>
                    </div>
                </div>
                <button type="button" @click="pickupModal = false" class="text-slate-400 hover:text-slate-600 text-2xl font-bold">&times;</button>
            </div>

            {{-- Search & Filter Controls --}}
            <div class="mt-4 flex flex-col sm:flex-row gap-2.5 items-center justify-between">
                <div class="relative w-full sm:flex-1">
                    <input 
                        type="text" 
                        x-model="pickupSearch" 
                        @input.debounce.300ms="loadPickups()"
                        placeholder="Cari nama member / invoice (MBR-... / POS-...)" 
                        class="w-full pl-9 pr-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs focus:outline-none focus:ring-2 focus:ring-lime-500 focus:bg-white"
                    >
                    <svg class="w-4 h-4 text-slate-400 absolute left-3 top-2.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                </div>

                <div class="flex items-center gap-1.5 w-full sm:w-auto text-xs">
                    <button 
                        type="button" 
                        @click="pickupFilter = 'ready'; loadPickups()"
                        :class="pickupFilter === 'ready' ? 'bg-slate-900 text-white font-bold' : 'bg-slate-100 text-slate-600 hover:bg-slate-200'"
                        class="px-3 py-1.5 rounded-xl transition"
                    >
                        Siap Diambil
                    </button>
                    <button 
                        type="button" 
                        @click="pickupFilter = 'all'; loadPickups()"
                        :class="pickupFilter === 'all' ? 'bg-slate-900 text-white font-bold' : 'bg-slate-100 text-slate-600 hover:bg-slate-200'"
                        class="px-3 py-1.5 rounded-xl transition"
                    >
                        Semua Hari Ini
                    </button>
                </div>
            </div>

            {{-- Orders List --}}
            <div class="mt-4 space-y-3">
                <template x-for="order in pickupOrders" :key="order.id">
                    <div class="p-4 rounded-2xl border transition" :class="order.pickup_status === 'ready_for_pickup' ? 'bg-lime-50/40 border-lime-200 shadow-sm' : 'bg-slate-50 border-slate-200/80 opacity-80'">
                        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2 pb-2 border-b border-slate-200/60">
                            <div>
                                <div class="flex items-center gap-2">
                                    <span class="font-mono font-black text-xs text-slate-900" x-text="order.invoice_number"></span>
                                    <span 
                                        x-show="order.pickup_status === 'ready_for_pickup'"
                                        class="px-2 py-0.5 rounded-full bg-lime-400 text-slate-950 font-black text-[10px] animate-pulse"
                                    >
                                        📦 Siap Diambil
                                    </span>
                                    <span 
                                        x-show="order.pickup_status === 'picked_up'"
                                        class="px-2 py-0.5 rounded-full bg-slate-200 text-slate-700 font-bold text-[10px]"
                                    >
                                        ✓ Sudah Diserahkan
                                    </span>
                                </div>
                                <p class="text-xs font-bold text-slate-800 mt-0.5" x-text="'Pembeli: ' + order.customer_name"></p>
                            </div>
                            <div class="text-left sm:text-right">
                                <span class="font-display font-black text-sm text-slate-900 font-mono">
                                    Rp<span x-text="Number(order.total_amount).toLocaleString('id-ID')"></span>
                                </span>
                                <p class="text-[10px] text-slate-500" x-text="new Date(order.created_at).toLocaleTimeString('id-ID', {hour:'2-digit', minute:'2-digit'}) + ' WIB'"></p>
                            </div>
                        </div>

                        {{-- Items to prepare --}}
                        <div class="py-2 space-y-1 text-xs">
                            <p class="text-[10px] uppercase font-bold text-slate-400 tracking-wider">Barang yang Harus Disiapkan:</p>
                            <template x-for="item in order.items" :key="item.id">
                                <div class="flex items-center justify-between font-semibold text-slate-800 pl-2 border-l-2 border-slate-300">
                                    <span>
                                        <span class="text-lime-700 font-bold font-mono" x-text="item.quantity + 'x '"></span>
                                        <span x-text="item.product_name"></span>
                                    </span>
                                    <span class="text-slate-500 font-mono text-[11px]" x-text="'Rp' + Number(item.subtotal).toLocaleString('id-ID')"></span>
                                </div>
                            </template>
                        </div>

                        {{-- Action Button --}}
                        <div class="pt-2 border-t border-slate-200/60 flex items-center justify-between">
                            <p class="text-[11px] text-slate-500 italic" x-text="order.notes ? 'Catatan: ' + order.notes : ''"></p>
                            
                            <template x-if="order.pickup_status === 'ready_for_pickup'">
                                <button 
                                    type="button" 
                                    @click="markOrderPickedUp(order)"
                                    :disabled="pickupHandingOver === order.id"
                                    class="px-4 py-2 bg-slate-900 hover:bg-slate-800 text-lime-400 font-display font-bold text-xs rounded-xl transition flex items-center gap-1.5 shadow-sm active:scale-95"
                                >
                                    <span>✓ Serahkan Barang</span>
                                    <span x-show="pickupHandingOver === order.id" class="animate-spin text-xs">⌛</span>
                                </button>
                            </template>
                            <template x-if="order.pickup_status === 'picked_up'">
                                <span class="text-[11px] text-slate-500">
                                    Diserahkan: <strong x-text="order.picker ? order.picker.name : 'Kasir'"></strong>
                                </span>
                            </template>
                        </div>
                    </div>
                </template>

                <template x-if="pickupOrders.length === 0">
                    <div class="py-12 text-center text-slate-400 text-xs">
                        <p class="text-3xl mb-1">📦</p>
                        <p class="font-bold text-slate-700">Tidak ada pesanan member</p>
                        <p class="mt-0.5">Pesanan yang dibayar oleh member di portal member akan muncul di sini untuk disiapkan kasir.</p>
                    </div>
                </template>
            </div>
        </div>
</div>
@endsection
