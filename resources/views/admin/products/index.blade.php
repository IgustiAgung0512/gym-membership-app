@extends('layouts.admin')
@section('title', 'Manajemen Produk & Stok')

@section('content')
<div 
    x-data="{
        createModal: false,
        editModal: false,
        restockModal: false,
        selectedProduct: {},
        restockProduct: {},

        openEdit(product) {
            this.selectedProduct = Object.assign({}, product);
            this.editModal = true;
        },

        openRestock(product) {
            this.restockProduct = product;
            this.restockModal = true;
        }
    }" 
    class="space-y-6"
>

    {{-- FLASH MESSAGES --}}
    @if (session('success'))
        <div class="rounded-2xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800 flex items-center gap-2 shadow-sm">
            <svg class="w-4 h-4 shrink-0 text-emerald-600" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
            <span class="font-medium">{{ session('success') }}</span>
        </div>
    @endif

    @if ($errors->any())
        <div class="rounded-2xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-800 flex items-center gap-2 shadow-sm">
            <svg class="w-4 h-4 shrink-0 text-rose-600" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
            <span class="font-medium">{{ $errors->first() }}</span>
        </div>
    @endif

    {{-- HEADER & ACTIONS --}}
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <div class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-xs font-bold bg-lime-100 text-lime-800 uppercase tracking-wider mb-1">
                <span>📦</span>
                <span>Inventaris Toko</span>
            </div>
            <h2 class="font-display font-bold text-2xl sm:text-3xl text-slate-900 tracking-tight">
                Produk & Stok Toko
            </h2>
            <p class="text-xs sm:text-sm text-slate-500 mt-0.5">
                Kelola suplemen, minuman, camilan sehat, dan aksesoris gym.
            </p>
        </div>

        <div class="flex items-center gap-2">
            <a href="{{ route('admin.pos.index') }}" class="px-4 py-2.5 rounded-xl bg-slate-900 text-white hover:bg-slate-800 text-xs font-bold transition shadow-sm inline-flex items-center gap-1.5">
                <span>🛒 Buka Kasir POS</span>
            </a>
            <button 
                @click="createModal = true" 
                class="px-4 py-2.5 rounded-xl bg-lime-500 hover:bg-lime-400 text-slate-950 text-xs font-bold transition shadow-sm inline-flex items-center gap-1.5"
            >
                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/></svg>
                <span>Tambah Produk</span>
            </button>
        </div>
    </div>

    {{-- SUMMARY KPI CARDS --}}
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
        <div class="bg-white border border-slate-200 rounded-2xl p-4 sm:p-5 shadow-sm">
            <span class="text-xs font-semibold text-slate-400 uppercase tracking-wider">Total Item Produk</span>
            <p class="font-display text-2xl sm:text-3xl font-extrabold text-slate-900 mt-1">{{ $totalProducts }}</p>
        </div>

        <div class="bg-white border border-slate-200 rounded-2xl p-4 sm:p-5 shadow-sm">
            <span class="text-xs font-semibold text-slate-400 uppercase tracking-wider">Nilai Aset Stok</span>
            <p class="font-display text-2xl sm:text-3xl font-extrabold text-slate-900 mt-1">Rp{{ number_format($totalStockValue, 0, ',', '.') }}</p>
        </div>

        <div class="bg-white border border-slate-200 rounded-2xl p-4 sm:p-5 shadow-sm">
            <span class="text-xs font-semibold text-slate-400 uppercase tracking-wider">Stok Menipis</span>
            <p class="font-display text-2xl sm:text-3xl font-extrabold text-amber-600 mt-1">{{ $lowStockCount }}</p>
        </div>

        <div class="bg-white border border-slate-200 rounded-2xl p-4 sm:p-5 shadow-sm">
            <span class="text-xs font-semibold text-slate-400 uppercase tracking-wider">Stok Habis</span>
            <p class="font-display text-2xl sm:text-3xl font-extrabold text-rose-600 mt-1">{{ $outOfStockCount }}</p>
        </div>
    </div>

    {{-- FILTER & SEARCH TOOLBAR --}}
    <div class="bg-white border border-slate-200 rounded-2xl p-4 shadow-sm">
        <form method="GET" action="{{ route('admin.products.index') }}" class="grid grid-cols-1 sm:grid-cols-12 gap-3">
            <div class="sm:col-span-5 relative">
                <input 
                    type="text" 
                    name="search" 
                    value="{{ request('search') }}" 
                    placeholder="Cari nama produk / SKU..." 
                    class="w-full pl-9 pr-4 py-2 rounded-xl border border-slate-200 bg-slate-50 focus:bg-white text-xs focus:outline-none focus:ring-2 focus:ring-lime-500/20"
                >
                <svg class="w-4 h-4 text-slate-400 absolute left-3 top-2.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
            </div>

            <div class="sm:col-span-3">
                <select name="category" onchange="this.form.submit()" class="w-full py-2 px-3 rounded-xl border border-slate-200 bg-slate-50 text-xs focus:outline-none">
                    <option value="all">Semua Kategori</option>
                    <option value="drinks" {{ request('category') == 'drinks' ? 'selected' : '' }}>🥤 Minuman & Elektrolit</option>
                    <option value="supplements" {{ request('category') == 'supplements' ? 'selected' : '' }}>💪 Suplemen & Whey</option>
                    <option value="snacks" {{ request('category') == 'snacks' ? 'selected' : '' }}>🥑 Camilan Sehat</option>
                    <option value="gear" {{ request('category') == 'gear' ? 'selected' : '' }}>🏋️ Aksesoris Gym</option>
                    <option value="other" {{ request('category') == 'other' ? 'selected' : '' }}>📦 Lainnya</option>
                </select>
            </div>

            <div class="sm:col-span-3">
                <select name="stock_status" onchange="this.form.submit()" class="w-full py-2 px-3 rounded-xl border border-slate-200 bg-slate-50 text-xs focus:outline-none">
                    <option value="">Semua Status Stok</option>
                    <option value="low" {{ request('stock_status') == 'low' ? 'selected' : '' }}>⚠️ Stok Menipis</option>
                    <option value="out" {{ request('stock_status') == 'out' ? 'selected' : '' }}>❌ Stok Habis</option>
                </select>
            </div>

            <div class="sm:col-span-1 flex gap-1.5">
                <button type="submit" class="w-full py-2 bg-slate-900 text-white rounded-xl text-xs font-bold hover:bg-slate-800 transition">
                    Cari
                </button>
            </div>
        </form>
    </div>

    {{-- PRODUCTS TABLE --}}
    <div class="bg-white border border-slate-200 rounded-3xl overflow-hidden shadow-sm">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead class="bg-slate-50 border-b border-slate-200 text-slate-500 font-bold uppercase tracking-wider">
                    <tr>
                        <th class="py-3.5 px-4">Produk</th>
                        <th class="py-3.5 px-4">Kategori</th>
                        <th class="py-3.5 px-4 text-right">Harga Beli (HPP)</th>
                        <th class="py-3.5 px-4 text-right">Harga Jual</th>
                        <th class="py-3.5 px-4 text-center">Stok</th>
                        <th class="py-3.5 px-4 text-center">Status</th>
                        <th class="py-3.5 px-4 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-slate-700">
                    @forelse ($products as $p)
                        <tr class="hover:bg-slate-50/80 transition">
                            <td class="py-3.5 px-4">
                                <div class="flex items-center gap-3">
                                    <div class="w-9 h-9 rounded-xl bg-slate-100 flex items-center justify-center text-lg shrink-0">
                                        {{ $p->category_icon }}
                                    </div>
                                    <div>
                                        <p class="font-bold text-slate-900 text-sm">{{ $p->name }}</p>
                                        <p class="text-[11px] text-slate-400 font-mono">{{ $p->sku ?? '-' }} · Satuan: {{ $p->unit }}</p>
                                    </div>
                                </div>
                            </td>
                            <td class="py-3.5 px-4">
                                <span class="px-2.5 py-1 rounded-md bg-slate-100 text-slate-700 font-medium text-[11px]">
                                    {{ $p->category_label }}
                                </span>
                            </td>
                            <td class="py-3.5 px-4 text-right font-mono text-slate-500">
                                Rp{{ number_format($p->cost_price, 0, ',', '.') }}
                            </td>
                            <td class="py-3.5 px-4 text-right font-mono font-bold text-slate-900">
                                Rp{{ number_format($p->price, 0, ',', '.') }}
                            </td>
                            <td class="py-3.5 px-4 text-center">
                                <span class="font-mono font-extrabold px-2.5 py-1 rounded-full text-xs {{ $p->stock <= 0 ? 'bg-rose-100 text-rose-700' : ($p->stock <= $p->min_stock_alert ? 'bg-amber-100 text-amber-800' : 'bg-lime-100 text-lime-800') }}">
                                    {{ $p->stock }} {{ $p->unit }}
                                </span>
                            </td>
                            <td class="py-3.5 px-4 text-center">
                                @if ($p->is_active)
                                    <span class="inline-flex items-center gap-1 text-[11px] font-bold text-emerald-700 bg-emerald-50 border border-emerald-200 px-2 py-0.5 rounded-full">
                                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span> Aktif
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1 text-[11px] font-bold text-slate-500 bg-slate-100 px-2 py-0.5 rounded-full">
                                        Nonaktif
                                    </span>
                                @endif
                            </td>
                            <td class="py-3.5 px-4 text-right">
                                <div class="flex items-center justify-end gap-1.5">
                                    <button 
                                        @click="openRestock({{ Js::from($p) }})"
                                        class="px-2.5 py-1 rounded-lg bg-lime-50 text-lime-800 hover:bg-lime-100 border border-lime-200 font-bold transition text-[11px]"
                                        title="Tambah Stok"
                                    >
                                        + Stok
                                    </button>

                                    <button 
                                        @click="openEdit({{ Js::from($p) }})"
                                        class="p-1.5 rounded-lg bg-slate-100 hover:bg-slate-200 text-slate-700 transition"
                                        title="Edit Produk"
                                    >
                                        ✏️
                                    </button>

                                    <form method="POST" action="{{ route('admin.products.destroy', $p->id) }}" onsubmit="return confirm('Hapus produk {{ $p->name }}?')" class="inline">
                                        @csrf
                                        @method('DELETE')
                                        <button class="p-1.5 rounded-lg bg-rose-50 hover:bg-rose-100 text-rose-600 border border-rose-200 transition" title="Hapus Produk">
                                            🗑️
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="py-12 text-center text-slate-400">
                                <p class="text-3xl mb-1">📦</p>
                                <p class="font-bold text-slate-600">Belum ada data produk.</p>
                                <p class="text-xs text-slate-400 mt-1">Klik tombol "+ Tambah Produk" untuk mulai memasukkan item gym store.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($products->hasPages())
            <div class="p-4 border-t border-slate-100">
                {{ $products->links() }}
            </div>
        @endif
    </div>

    {{-- =========================================================
        MODAL 1: TAMBAH PRODUK BARU
    ========================================================= --}}
    <div x-show="createModal" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-950/75 backdrop-blur-xs" @keydown.escape.window="createModal = false">
        <div @click.outside="createModal = false" class="bg-white border border-slate-200 rounded-3xl w-full max-w-lg p-6 sm:p-7 shadow-2xl relative max-h-[90vh] overflow-y-auto">
            <button @click="createModal = false" class="absolute top-4 right-4 text-slate-400 hover:text-slate-700 p-1">✕</button>

            <h3 class="font-display font-bold text-xl text-slate-900">Tambah Produk Baru</h3>
            <p class="text-xs text-slate-500 mt-0.5">Lengkapi form di bawah untuk mendaftarkan barang baru ke Gym Store.</p>

            <form method="POST" action="{{ route('admin.products.store') }}" enctype="multipart/form-data" class="mt-5 space-y-4 text-xs">
                @csrf

                <div>
                    <label class="block font-bold text-slate-700 mb-1">Nama Produk <span class="text-rose-500">*</span></label>
                    <input type="text" name="name" required placeholder="Contoh: Whey Protein Isolate (1 Scoop)" class="w-full py-2 px-3 rounded-xl border border-slate-200 focus:outline-none focus:ring-2 focus:ring-lime-500/20">
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block font-bold text-slate-700 mb-1">Kategori <span class="text-rose-500">*</span></label>
                        <select name="category" required class="w-full py-2 px-3 rounded-xl border border-slate-200 focus:outline-none">
                            <option value="drinks">🥤 Minuman & Elektrolit</option>
                            <option value="supplements">💪 Suplemen & Whey</option>
                            <option value="snacks">🥑 Camilan Sehat</option>
                            <option value="gear">🏋️ Aksesoris Gym</option>
                            <option value="other">📦 Lainnya</option>
                        </select>
                    </div>
                    <div>
                        <label class="block font-bold text-slate-700 mb-1">Kode SKU / Barcode</label>
                        <input type="text" name="sku" placeholder="Contoh: SUP-WHEY-01" class="w-full py-2 px-3 rounded-xl border border-slate-200 font-mono focus:outline-none">
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block font-bold text-slate-700 mb-1">Harga Beli / HPP (Rp)</label>
                        <input type="number" name="cost_price" min="0" step="500" placeholder="12000" class="w-full py-2 px-3 rounded-xl border border-slate-200 font-mono focus:outline-none">
                    </div>
                    <div>
                        <label class="block font-bold text-slate-700 mb-1">Harga Jual (Rp) <span class="text-rose-500">*</span></label>
                        <input type="number" name="price" min="0" step="500" required placeholder="20000" class="w-full py-2 px-3 rounded-xl border border-slate-200 font-mono font-bold text-slate-900 focus:outline-none">
                    </div>
                </div>

                <div class="grid grid-cols-3 gap-3">
                    <div>
                        <label class="block font-bold text-slate-700 mb-1">Stok Awal <span class="text-rose-500">*</span></label>
                        <input type="number" name="stock" min="0" required placeholder="50" class="w-full py-2 px-3 rounded-xl border border-slate-200 font-mono focus:outline-none">
                    </div>
                    <div>
                        <label class="block font-bold text-slate-700 mb-1">Satuan <span class="text-rose-500">*</span></label>
                        <input type="text" name="unit" required placeholder="scoop / botol / pcs" class="w-full py-2 px-3 rounded-xl border border-slate-200 focus:outline-none">
                    </div>
                    <div>
                        <label class="block font-bold text-slate-700 mb-1">Alert Stok Minimum</label>
                        <input type="number" name="min_stock_alert" min="0" value="5" class="w-full py-2 px-3 rounded-xl border border-slate-200 font-mono focus:outline-none">
                    </div>
                </div>

                <div>
                    <label class="block font-bold text-slate-700 mb-1">Deskripsi / Catatan Produk</label>
                    <textarea name="description" rows="2" placeholder="Keterangan singkat manfaat atau cara konsumsi produk..." class="w-full py-2 px-3 rounded-xl border border-slate-200 focus:outline-none"></textarea>
                </div>

                <div class="flex items-center gap-2 pt-2">
                    <input type="checkbox" name="is_active" value="1" id="create_active" checked class="rounded border-slate-300 text-lime-600 focus:ring-lime-500">
                    <label for="create_active" class="font-bold text-slate-700 cursor-pointer">Status Produk Aktif (Tampil di Kasir & Toko Member)</label>
                </div>

                <div class="pt-4 border-t border-slate-100 flex gap-3">
                    <button type="button" @click="createModal = false" class="w-1/3 py-2.5 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold transition">Batal</button>
                    <button type="submit" class="w-2/3 py-2.5 rounded-xl bg-lime-500 hover:bg-lime-400 text-slate-950 font-bold transition shadow-sm">Simpan Produk</button>
                </div>
            </form>
        </div>
    </div>

    {{-- =========================================================
        MODAL 2: EDIT PRODUK
    ========================================================= --}}
    <div x-show="editModal" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-950/75 backdrop-blur-xs" @keydown.escape.window="editModal = false">
        <div @click.outside="editModal = false" class="bg-white border border-slate-200 rounded-3xl w-full max-w-lg p-6 sm:p-7 shadow-2xl relative max-h-[90vh] overflow-y-auto">
            <button @click="editModal = false" class="absolute top-4 right-4 text-slate-400 hover:text-slate-700 p-1">✕</button>

            <h3 class="font-display font-bold text-xl text-slate-900">Edit Data Produk</h3>
            <p class="text-xs text-slate-500 mt-0.5" x-text="selectedProduct.name"></p>

            <form :action="'{{ url('admin/products') }}/' + selectedProduct.id" method="POST" enctype="multipart/form-data" class="mt-5 space-y-4 text-xs">
                @csrf
                @method('PUT')

                <div>
                    <label class="block font-bold text-slate-700 mb-1">Nama Produk <span class="text-rose-500">*</span></label>
                    <input type="text" name="name" x-model="selectedProduct.name" required class="w-full py-2 px-3 rounded-xl border border-slate-200 focus:outline-none">
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block font-bold text-slate-700 mb-1">Kategori <span class="text-rose-500">*</span></label>
                        <select name="category" x-model="selectedProduct.category" required class="w-full py-2 px-3 rounded-xl border border-slate-200 focus:outline-none">
                            <option value="drinks">🥤 Minuman & Elektrolit</option>
                            <option value="supplements">💪 Suplemen & Whey</option>
                            <option value="snacks">🥑 Camilan Sehat</option>
                            <option value="gear">🏋️ Aksesoris Gym</option>
                            <option value="other">📦 Lainnya</option>
                        </select>
                    </div>
                    <div>
                        <label class="block font-bold text-slate-700 mb-1">Kode SKU / Barcode</label>
                        <input type="text" name="sku" x-model="selectedProduct.sku" class="w-full py-2 px-3 rounded-xl border border-slate-200 font-mono focus:outline-none">
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block font-bold text-slate-700 mb-1">Harga Beli / HPP (Rp)</label>
                        <input type="number" name="cost_price" x-model="selectedProduct.cost_price" min="0" step="500" class="w-full py-2 px-3 rounded-xl border border-slate-200 font-mono focus:outline-none">
                    </div>
                    <div>
                        <label class="block font-bold text-slate-700 mb-1">Harga Jual (Rp) <span class="text-rose-500">*</span></label>
                        <input type="number" name="price" x-model="selectedProduct.price" min="0" step="500" required class="w-full py-2 px-3 rounded-xl border border-slate-200 font-mono font-bold text-slate-900 focus:outline-none">
                    </div>
                </div>

                <div class="grid grid-cols-3 gap-3">
                    <div>
                        <label class="block font-bold text-slate-700 mb-1">Stok Saat Ini <span class="text-rose-500">*</span></label>
                        <input type="number" name="stock" x-model="selectedProduct.stock" min="0" required class="w-full py-2 px-3 rounded-xl border border-slate-200 font-mono focus:outline-none">
                    </div>
                    <div>
                        <label class="block font-bold text-slate-700 mb-1">Satuan <span class="text-rose-500">*</span></label>
                        <input type="text" name="unit" x-model="selectedProduct.unit" required class="w-full py-2 px-3 rounded-xl border border-slate-200 focus:outline-none">
                    </div>
                    <div>
                        <label class="block font-bold text-slate-700 mb-1">Alert Stok Minimum</label>
                        <input type="number" name="min_stock_alert" x-model="selectedProduct.min_stock_alert" min="0" class="w-full py-2 px-3 rounded-xl border border-slate-200 font-mono focus:outline-none">
                    </div>
                </div>

                <div>
                    <label class="block font-bold text-slate-700 mb-1">Deskripsi Produk</label>
                    <textarea name="description" x-model="selectedProduct.description" rows="2" class="w-full py-2 px-3 rounded-xl border border-slate-200 focus:outline-none"></textarea>
                </div>

                <div class="flex items-center gap-2 pt-2">
                    <input type="checkbox" name="is_active" value="1" id="edit_active" :checked="selectedProduct.is_active" class="rounded border-slate-300 text-lime-600 focus:ring-lime-500">
                    <label for="edit_active" class="font-bold text-slate-700 cursor-pointer">Status Produk Aktif</label>
                </div>

                <div class="pt-4 border-t border-slate-100 flex gap-3">
                    <button type="button" @click="editModal = false" class="w-1/3 py-2.5 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold transition">Batal</button>
                    <button type="submit" class="w-2/3 py-2.5 rounded-xl bg-lime-500 hover:bg-lime-400 text-slate-950 font-bold transition shadow-sm">Simpan Perubahan</button>
                </div>
            </form>
        </div>
    </div>

    {{-- =========================================================
        MODAL 3: QUICK RESTOCK
    ========================================================= --}}
    <div x-show="restockModal" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-950/75 backdrop-blur-xs" @keydown.escape.window="restockModal = false">
        <div @click.outside="restockModal = false" class="bg-white border border-slate-200 rounded-3xl w-full max-w-sm p-6 shadow-2xl relative text-center">
            <button @click="restockModal = false" class="absolute top-4 right-4 text-slate-400 hover:text-slate-700 p-1">✕</button>

            <span class="text-3xl">📥</span>
            <h3 class="font-display font-bold text-lg text-slate-900 mt-2">Tambah Stok Barang</h3>
            <p class="text-xs text-slate-500 mt-0.5" x-text="restockProduct.name"></p>
            <p class="text-[11px] text-slate-400 mt-1">Stok saat ini: <strong class="text-slate-800" x-text="restockProduct.stock + ' ' + restockProduct.unit"></strong></p>

            <form :action="'{{ url('admin/products') }}/' + restockProduct.id + '/restock'" method="POST" class="mt-4 space-y-4 text-xs">
                @csrf
                <div>
                    <label class="block font-bold text-slate-700 mb-1 text-left">Jumlah Stok yang Masuk (+)</label>
                    <input type="number" name="added_stock" min="1" value="10" required class="w-full text-center font-mono font-extrabold text-xl py-2 px-3 rounded-xl border border-slate-300 focus:outline-none focus:ring-2 focus:ring-lime-500">
                </div>

                <div class="flex gap-2">
                    <button type="button" @click="restockModal = false" class="w-1/2 py-2.5 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold transition">Batal</button>
                    <button type="submit" class="w-1/2 py-2.5 rounded-xl bg-lime-500 hover:bg-lime-400 text-slate-950 font-bold transition shadow-sm">+ Tambah</button>
                </div>
            </form>
        </div>
    </div>

</div>
@endsection
