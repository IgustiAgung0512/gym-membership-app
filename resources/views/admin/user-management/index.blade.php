@extends('layouts.admin')
@section('title', 'Kelola Admin & Kasir')

@section('content')
<div x-data="{
    activeTab: 'admin',
    showAddAdminModal: false,
    showAddCashierModal: false,
    showPasswordModal: false,
    targetUser: { id: null, name: '', email: '', role: '', isSelf: false },
    openPasswordModal(id, name, email, role, isSelf) {
        this.targetUser = { id, name, email, role, isSelf };
        this.showPasswordModal = true;
    }
}" class="space-y-6">

    {{-- ERROR SUMMARY IF ANY --}}
    @if ($errors->any())
        <div class="rounded-2xl border border-rose-200 bg-rose-50 p-4 text-sm text-rose-800 shadow-sm">
            <div class="flex items-center gap-2 font-bold mb-1 text-rose-900">
                <svg class="w-5 h-5 shrink-0 text-rose-600" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                </svg>
                Terjadi kesalahan input:
            </div>
            <ul class="list-disc list-inside space-y-0.5 text-xs text-rose-700">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    {{-- HEADER & TABS --}}
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 bg-white border border-slate-200 p-4 lg:p-5 rounded-2xl shadow-sm">
        <div>
            <h2 class="font-display font-bold text-lg text-slate-900">Manajemen Pengguna Staf</h2>
            <p class="text-xs text-slate-500 mt-0.5">Kelola akun administrator sistem dan kasir front desk (ganti password, tambah, atau hapus).</p>
        </div>
        <div class="flex items-center gap-2 flex-wrap">
            <div class="inline-flex rounded-xl bg-slate-100 p-1 border border-slate-200">
                <button 
                    @click="activeTab = 'admin'" 
                    :class="activeTab === 'admin' ? 'bg-white text-slate-950 shadow-sm font-bold' : 'text-slate-600 hover:text-slate-900 font-medium'"
                    class="px-3.5 py-1.5 rounded-lg text-xs transition">
                    Admin ({{ $admins->count() }})
                </button>
                <button 
                    @click="activeTab = 'cashier'" 
                    :class="activeTab === 'cashier' ? 'bg-white text-slate-950 shadow-sm font-bold' : 'text-slate-600 hover:text-slate-900 font-medium'"
                    class="px-3.5 py-1.5 rounded-lg text-xs transition">
                    Kasir ({{ $cashiers->count() }})
                </button>
            </div>
            
            <button 
                x-show="activeTab === 'admin'"
                @click="showAddAdminModal = true"
                class="flex items-center gap-2 rounded-xl bg-slate-900 text-white text-xs font-bold px-3.5 py-2 hover:bg-slate-800 transition shadow-sm">
                <svg class="w-4 h-4 text-lime-400" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/></svg>
                <span>Tambah Admin</span>
            </button>

            <button 
                x-show="activeTab === 'cashier'"
                x-cloak
                @click="showAddCashierModal = true"
                class="flex items-center gap-2 rounded-xl bg-slate-900 text-white text-xs font-bold px-3.5 py-2 hover:bg-slate-800 transition shadow-sm">
                <svg class="w-4 h-4 text-lime-400" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/></svg>
                <span>Tambah Kasir</span>
            </button>
        </div>
    </div>

    {{-- SECTION: DAFTAR ADMIN --}}
    <div x-show="activeTab === 'admin'" class="space-y-4">
        {{-- Desktop Table --}}
        <div class="hidden lg:block bg-white border border-slate-200 rounded-2xl overflow-hidden shadow-sm">
            <div class="px-5 py-4 border-b border-slate-100 flex items-center justify-between">
                <div class="flex items-center gap-2">
                    <span class="w-2.5 h-2.5 rounded-full bg-lime-500"></span>
                    <h3 class="font-bold text-sm text-slate-800">Daftar Administrator</h3>
                </div>
                <span class="text-xs text-slate-500">Total: {{ $admins->count() }} Akun</span>
            </div>
            <table class="w-full text-sm">
                <thead>
                    <tr class="text-left bg-slate-50 text-slate-600 border-b border-slate-200">
                        <th class="px-5 py-3.5 font-semibold text-xs uppercase tracking-wider">Nama & Email</th>
                        <th class="px-5 py-3.5 font-semibold text-xs uppercase tracking-wider">No. Telepon</th>
                        <th class="px-5 py-3.5 font-semibold text-xs uppercase tracking-wider">Status Akun</th>
                        <th class="px-5 py-3.5 font-semibold text-xs uppercase tracking-wider">Terdaftar</th>
                        <th class="px-5 py-3.5 font-semibold text-xs uppercase tracking-wider text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse ($admins as $admin)
                        @php $isSelf = ($admin->id === auth()->id()); @endphp
                        <tr class="hover:bg-slate-50/80 transition {{ $isSelf ? 'bg-lime-50/40' : '' }}">
                            <td class="px-5 py-3.5">
                                <div class="flex items-center gap-3">
                                    <div class="w-9 h-9 rounded-full bg-slate-900 text-lime-400 font-display font-bold text-sm flex items-center justify-center shrink-0">
                                        {{ substr($admin->name, 0, 1) }}
                                    </div>
                                    <div>
                                        <div class="flex items-center gap-2">
                                            <p class="text-slate-900 font-semibold">{{ $admin->name }}</p>
                                            @if ($isSelf)
                                                <span class="text-[10px] font-bold px-2 py-0.5 rounded-full bg-lime-200 text-lime-900">Anda</span>
                                            @endif
                                        </div>
                                        <p class="text-xs text-slate-500">{{ $admin->email }}</p>
                                    </div>
                                </div>
                            </td>
                            <td class="px-5 py-3.5 text-slate-700 font-mono text-xs">{{ $admin->phone ?: '-' }}</td>
                            <td class="px-5 py-3.5">
                                <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-xs font-semibold bg-emerald-100 text-emerald-800">
                                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                                    Administrator
                                </span>
                            </td>
                            <td class="px-5 py-3.5 text-slate-600 text-xs">{{ $admin->created_at ? $admin->created_at->format('d M Y') : '-' }}</td>
                            <td class="px-5 py-3.5 text-right">
                                <div class="flex items-center justify-end gap-2 text-xs">
                                    <button 
                                        @click="openPasswordModal({{ $admin->id }}, '{{ addslashes($admin->name) }}', '{{ $admin->email }}', 'admin', {{ $isSelf ? 'true' : 'false' }})"
                                        class="px-3 py-1.5 rounded-lg bg-slate-100 text-slate-700 hover:bg-slate-200 font-semibold transition">
                                        Ganti Password
                                    </button>
                                    @if (!$isSelf)
                                        <form method="POST" action="{{ route('admin.user-management.admin.destroy', $admin) }}" onsubmit="return confirm('Hapus admin {{ addslashes($admin->name) }}?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="px-2.5 py-1.5 rounded-lg text-rose-600 hover:bg-rose-50 font-semibold transition">
                                                Hapus
                                            </button>
                                        </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-5 py-8 text-center text-slate-400 text-sm">Tidak ada data admin.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Mobile Cards --}}
        <div class="grid gap-3 lg:hidden">
            @forelse ($admins as $admin)
                @php $isSelf = ($admin->id === auth()->id()); @endphp
                <div class="bg-white border border-slate-200 rounded-2xl p-4 shadow-sm {{ $isSelf ? 'ring-2 ring-lime-400' : '' }}">
                    <div class="flex items-start justify-between gap-2">
                        <div class="flex items-center gap-3 min-w-0">
                            <div class="w-10 h-10 rounded-full bg-slate-900 text-lime-400 font-display font-bold text-sm flex items-center justify-center shrink-0">
                                {{ substr($admin->name, 0, 1) }}
                            </div>
                            <div class="min-w-0">
                                <div class="flex items-center gap-1.5">
                                    <p class="text-slate-900 font-semibold truncate">{{ $admin->name }}</p>
                                    @if ($isSelf)
                                        <span class="text-[10px] font-bold px-2 py-0.5 rounded-full bg-lime-200 text-lime-900 shrink-0">Anda</span>
                                    @endif
                                </div>
                                <p class="text-xs text-slate-500 truncate">{{ $admin->email }}</p>
                            </div>
                        </div>
                        <span class="text-[11px] font-semibold px-2 py-0.5 rounded-full bg-emerald-100 text-emerald-800 shrink-0">Admin</span>
                    </div>

                    <div class="mt-3 text-xs text-slate-500 border-t border-slate-100 pt-2 flex justify-between">
                        <span>Telepon: <strong class="text-slate-800 font-mono">{{ $admin->phone ?: '-' }}</strong></span>
                        <span>Dibuat: <strong class="text-slate-800">{{ $admin->created_at ? $admin->created_at->format('d M Y') : '-' }}</strong></span>
                    </div>

                    <div class="mt-3 flex gap-2">
                        <button 
                            @click="openPasswordModal({{ $admin->id }}, '{{ addslashes($admin->name) }}', '{{ $admin->email }}', 'admin', {{ $isSelf ? 'true' : 'false' }})"
                            class="flex-1 py-2 rounded-xl bg-slate-100 text-slate-800 font-semibold text-xs hover:bg-slate-200 transition text-center">
                            Ganti Password
                        </button>
                        @if (!$isSelf)
                            <form method="POST" action="{{ route('admin.user-management.admin.destroy', $admin) }}" onsubmit="return confirm('Hapus admin {{ addslashes($admin->name) }}?')" class="shrink-0">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="px-3 py-2 rounded-xl bg-rose-50 text-rose-600 font-semibold text-xs hover:bg-rose-100 transition">
                                    Hapus
                                </button>
                            </form>
                        @endif
                    </div>
                </div>
            @empty
                <div class="bg-white border border-slate-200 rounded-2xl p-6 text-center text-slate-400 text-sm">Tidak ada data admin.</div>
            @endforelse
        </div>
    </div>

    {{-- SECTION: DAFTAR KASIR --}}
    <div x-show="activeTab === 'cashier'" x-cloak class="space-y-4">
        {{-- Desktop Table --}}
        <div class="hidden lg:block bg-white border border-slate-200 rounded-2xl overflow-hidden shadow-sm">
            <div class="px-5 py-4 border-b border-slate-100 flex items-center justify-between">
                <div class="flex items-center gap-2">
                    <span class="w-2.5 h-2.5 rounded-full bg-blue-500"></span>
                    <h3 class="font-bold text-sm text-slate-800">Daftar Akun Kasir Front Desk</h3>
                </div>
                <span class="text-xs text-slate-500">Total: {{ $cashiers->count() }} Akun</span>
            </div>
            <table class="w-full text-sm">
                <thead>
                    <tr class="text-left bg-slate-50 text-slate-600 border-b border-slate-200">
                        <th class="px-5 py-3.5 font-semibold text-xs uppercase tracking-wider">Nama & Email</th>
                        <th class="px-5 py-3.5 font-semibold text-xs uppercase tracking-wider">No. Telepon</th>
                        <th class="px-5 py-3.5 font-semibold text-xs uppercase tracking-wider">Peran</th>
                        <th class="px-5 py-3.5 font-semibold text-xs uppercase tracking-wider">Terdaftar</th>
                        <th class="px-5 py-3.5 font-semibold text-xs uppercase tracking-wider text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse ($cashiers as $cashier)
                        <tr class="hover:bg-slate-50/80 transition">
                            <td class="px-5 py-3.5">
                                <div class="flex items-center gap-3">
                                    <div class="w-9 h-9 rounded-full bg-blue-50 text-blue-700 font-display font-bold text-sm flex items-center justify-center shrink-0 border border-blue-200">
                                        {{ substr($cashier->name, 0, 1) }}
                                    </div>
                                    <div>
                                        <p class="text-slate-900 font-semibold">{{ $cashier->name }}</p>
                                        <p class="text-xs text-slate-500">{{ $cashier->email }}</p>
                                    </div>
                                </div>
                            </td>
                            <td class="px-5 py-3.5 text-slate-700 font-mono text-xs">{{ $cashier->phone ?: '-' }}</td>
                            <td class="px-5 py-3.5">
                                <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-xs font-semibold bg-blue-100 text-blue-800">
                                    <span class="w-1.5 h-1.5 rounded-full bg-blue-500"></span>
                                    Kasir / Front Desk
                                </span>
                            </td>
                            <td class="px-5 py-3.5 text-slate-600 text-xs">{{ $cashier->created_at ? $cashier->created_at->format('d M Y') : '-' }}</td>
                            <td class="px-5 py-3.5 text-right">
                                <div class="flex items-center justify-end gap-2 text-xs">
                                    <button 
                                        @click="openPasswordModal({{ $cashier->id }}, '{{ addslashes($cashier->name) }}', '{{ $cashier->email }}', 'cashier', false)"
                                        class="px-3 py-1.5 rounded-lg bg-slate-100 text-slate-700 hover:bg-slate-200 font-semibold transition">
                                        Ganti Password
                                    </button>
                                    <form method="POST" action="{{ route('admin.user-management.cashier.destroy', $cashier) }}" onsubmit="return confirm('Hapus akun kasir {{ addslashes($cashier->name) }}?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="px-2.5 py-1.5 rounded-lg text-rose-600 hover:bg-rose-50 font-semibold transition">
                                            Hapus
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-5 py-8 text-center text-slate-400 text-sm">Belum ada akun kasir terdaftar.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Mobile Cards --}}
        <div class="grid gap-3 lg:hidden">
            @forelse ($cashiers as $cashier)
                <div class="bg-white border border-slate-200 rounded-2xl p-4 shadow-sm">
                    <div class="flex items-start justify-between gap-2">
                        <div class="flex items-center gap-3 min-w-0">
                            <div class="w-10 h-10 rounded-full bg-blue-50 text-blue-700 font-display font-bold text-sm flex items-center justify-center shrink-0 border border-blue-200">
                                {{ substr($cashier->name, 0, 1) }}
                            </div>
                            <div class="min-w-0">
                                <p class="text-slate-900 font-semibold truncate">{{ $cashier->name }}</p>
                                <p class="text-xs text-slate-500 truncate">{{ $cashier->email }}</p>
                            </div>
                        </div>
                        <span class="text-[11px] font-semibold px-2 py-0.5 rounded-full bg-blue-100 text-blue-800 shrink-0">Kasir</span>
                    </div>

                    <div class="mt-3 text-xs text-slate-500 border-t border-slate-100 pt-2 flex justify-between">
                        <span>Telepon: <strong class="text-slate-800 font-mono">{{ $cashier->phone ?: '-' }}</strong></span>
                        <span>Dibuat: <strong class="text-slate-800">{{ $cashier->created_at ? $cashier->created_at->format('d M Y') : '-' }}</strong></span>
                    </div>

                    <div class="mt-3 flex gap-2">
                        <button 
                            @click="openPasswordModal({{ $cashier->id }}, '{{ addslashes($cashier->name) }}', '{{ $cashier->email }}', 'cashier', false)"
                            class="flex-1 py-2 rounded-xl bg-slate-100 text-slate-800 font-semibold text-xs hover:bg-slate-200 transition text-center">
                            Ganti Password
                        </button>
                        <form method="POST" action="{{ route('admin.user-management.cashier.destroy', $cashier) }}" onsubmit="return confirm('Hapus kasir {{ addslashes($cashier->name) }}?')" class="shrink-0">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="px-3 py-2 rounded-xl bg-rose-50 text-rose-600 font-semibold text-xs hover:bg-rose-100 transition">
                                Hapus
                            </button>
                        </form>
                    </div>
                </div>
            @empty
                <div class="bg-white border border-slate-200 rounded-2xl p-6 text-center text-slate-400 text-sm">Belum ada akun kasir terdaftar.</div>
            @endforelse
        </div>
    </div>

    {{-- MODAL: TAMBAH ADMIN --}}
    <div 
        x-show="showAddAdminModal" 
        x-cloak 
        class="fixed inset-0 z-50 overflow-y-auto flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-sm"
        @keydown.escape.window="showAddAdminModal = false">
        
        <div 
            @click.outside="showAddAdminModal = false"
            class="w-full max-w-lg bg-white rounded-2xl shadow-xl border border-slate-200 overflow-hidden"
            x-transition>
            
            <div class="px-6 py-4 border-b border-slate-100 flex items-center justify-between bg-slate-50/50">
                <div class="flex items-center gap-2.5">
                    <div class="w-8 h-8 rounded-lg bg-slate-900 text-lime-400 flex items-center justify-center font-bold">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/></svg>
                    </div>
                    <div>
                        <h3 class="font-display font-bold text-base text-slate-900">Tambah Admin Baru</h3>
                        <p class="text-xs text-slate-500">Berikan akses kontrol penuh sistem ke pengguna baru.</p>
                    </div>
                </div>
                <button @click="showAddAdminModal = false" class="p-1 rounded-lg text-slate-400 hover:text-slate-600 hover:bg-slate-100">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>

            <form method="POST" action="{{ route('admin.user-management.admin.store') }}" class="p-6 space-y-4">
                @csrf
                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">Nama Lengkap *</label>
                    <input type="text" name="name" required placeholder="Contoh: Budi Pratama"
                           class="w-full rounded-xl border border-slate-300 px-3.5 py-2.5 text-sm text-slate-900 focus:outline-none focus:ring-2 focus:ring-lime-500">
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">Email *</label>
                        <input type="email" name="email" required placeholder="admin2@gym.test"
                               class="w-full rounded-xl border border-slate-300 px-3.5 py-2.5 text-sm text-slate-900 focus:outline-none focus:ring-2 focus:ring-lime-500">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">No. Telepon / WA *</label>
                        <input type="text" name="phone" required placeholder="08123456789"
                               class="w-full rounded-xl border border-slate-300 px-3.5 py-2.5 text-sm text-slate-900 focus:outline-none focus:ring-2 focus:ring-lime-500">
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">Password *</label>
                        <input type="password" name="password" required minlength="8" placeholder="Minimal 8 karakter"
                               class="w-full rounded-xl border border-slate-300 px-3.5 py-2.5 text-sm text-slate-900 focus:outline-none focus:ring-2 focus:ring-lime-500">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">Konfirmasi Password *</label>
                        <input type="password" name="password_confirmation" required minlength="8" placeholder="Ulangi password"
                               class="w-full rounded-xl border border-slate-300 px-3.5 py-2.5 text-sm text-slate-900 focus:outline-none focus:ring-2 focus:ring-lime-500">
                    </div>
                </div>

                <div class="pt-3 border-t border-slate-100 flex items-center justify-end gap-2">
                    <button type="button" @click="showAddAdminModal = false" class="px-4 py-2.5 rounded-xl border border-slate-200 text-slate-700 text-xs font-bold hover:bg-slate-50 transition">
                        Batal
                    </button>
                    <button type="submit" class="px-5 py-2.5 rounded-xl bg-slate-900 text-white text-xs font-bold hover:bg-slate-800 transition">
                        Simpan Admin
                    </button>
                </div>
            </form>
        </div>
    </div>

    {{-- MODAL: TAMBAH KASIR --}}
    <div 
        x-show="showAddCashierModal" 
        x-cloak 
        class="fixed inset-0 z-50 overflow-y-auto flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-sm"
        @keydown.escape.window="showAddCashierModal = false">
        
        <div 
            @click.outside="showAddCashierModal = false"
            class="w-full max-w-lg bg-white rounded-2xl shadow-xl border border-slate-200 overflow-hidden"
            x-transition>
            
            <div class="px-6 py-4 border-b border-slate-100 flex items-center justify-between bg-blue-50/40">
                <div class="flex items-center gap-2.5">
                    <div class="w-8 h-8 rounded-lg bg-blue-600 text-white flex items-center justify-center font-bold">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/></svg>
                    </div>
                    <div>
                        <h3 class="font-display font-bold text-base text-slate-900">Tambah Akun Kasir Baru</h3>
                        <p class="text-xs text-slate-500">Buat akun staf kasir untuk mengelola POS & member.</p>
                    </div>
                </div>
                <button @click="showAddCashierModal = false" class="p-1 rounded-lg text-slate-400 hover:text-slate-600 hover:bg-slate-100">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>

            <form method="POST" action="{{ route('admin.user-management.cashier.store') }}" class="p-6 space-y-4">
                @csrf
                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">Nama Kasir *</label>
                    <input type="text" name="name" required placeholder="Contoh: Kasir Front Desk 2"
                           class="w-full rounded-xl border border-slate-300 px-3.5 py-2.5 text-sm text-slate-900 focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">Email *</label>
                        <input type="email" name="email" required placeholder="kasir2@gym.test"
                               class="w-full rounded-xl border border-slate-300 px-3.5 py-2.5 text-sm text-slate-900 focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">No. Telepon / WA *</label>
                        <input type="text" name="phone" required placeholder="08123456780"
                               class="w-full rounded-xl border border-slate-300 px-3.5 py-2.5 text-sm text-slate-900 focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">Password *</label>
                        <input type="password" name="password" required minlength="8" placeholder="Minimal 8 karakter"
                               class="w-full rounded-xl border border-slate-300 px-3.5 py-2.5 text-sm text-slate-900 focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">Konfirmasi Password *</label>
                        <input type="password" name="password_confirmation" required minlength="8" placeholder="Ulangi password"
                               class="w-full rounded-xl border border-slate-300 px-3.5 py-2.5 text-sm text-slate-900 focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                </div>

                <div class="pt-3 border-t border-slate-100 flex items-center justify-end gap-2">
                    <button type="button" @click="showAddCashierModal = false" class="px-4 py-2.5 rounded-xl border border-slate-200 text-slate-700 text-xs font-bold hover:bg-slate-50 transition">
                        Batal
                    </button>
                    <button type="submit" class="px-5 py-2.5 rounded-xl bg-blue-600 text-white text-xs font-bold hover:bg-blue-700 transition">
                        Simpan Kasir
                    </button>
                </div>
            </form>
        </div>
    </div>

    {{-- MODAL: GANTI PASSWORD (ADMIN & KASIR) --}}
    <div 
        x-show="showPasswordModal" 
        x-cloak 
        class="fixed inset-0 z-50 overflow-y-auto flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-sm"
        @keydown.escape.window="showPasswordModal = false">
        
        <div 
            @click.outside="showPasswordModal = false"
            class="w-full max-w-md bg-white rounded-2xl shadow-xl border border-slate-200 overflow-hidden"
            x-transition>
            
            <div class="px-6 py-4 border-b border-slate-100 flex items-center justify-between bg-slate-50/50">
                <div class="flex items-center gap-2.5">
                    <div class="w-8 h-8 rounded-lg bg-amber-500 text-white flex items-center justify-center font-bold">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z"/>
                        </svg>
                    </div>
                    <div>
                        <h3 class="font-display font-bold text-base text-slate-900">Ganti Password</h3>
                        <p class="text-xs text-slate-500" x-text="targetUser.name + ' (' + targetUser.email + ')'"></p>
                    </div>
                </div>
                <button @click="showPasswordModal = false" class="p-1 rounded-lg text-slate-400 hover:text-slate-600 hover:bg-slate-100">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>

            <form :action="'{{ url('/admin/user-management') }}/' + targetUser.id + '/password'" method="POST" class="p-6 space-y-4">
                @csrf
                
                {{-- Jika ganti password diri sendiri, minta password saat ini --}}
                <template x-if="targetUser.isSelf">
                    <div>
                        <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">Password Lama (Saat Ini) *</label>
                        <input type="password" name="current_password" required placeholder="Masukkan password saat ini"
                               class="w-full rounded-xl border border-slate-300 px-3.5 py-2.5 text-sm text-slate-900 focus:outline-none focus:ring-2 focus:ring-lime-500">
                    </div>
                </template>

                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">Password Baru *</label>
                    <input type="password" name="password" required minlength="8" placeholder="Minimal 8 karakter"
                           class="w-full rounded-xl border border-slate-300 px-3.5 py-2.5 text-sm text-slate-900 focus:outline-none focus:ring-2 focus:ring-lime-500">
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">Konfirmasi Password Baru *</label>
                    <input type="password" name="password_confirmation" required minlength="8" placeholder="Ulangi password baru"
                           class="w-full rounded-xl border border-slate-300 px-3.5 py-2.5 text-sm text-slate-900 focus:outline-none focus:ring-2 focus:ring-lime-500">
                </div>

                <div class="pt-3 border-t border-slate-100 flex items-center justify-end gap-2">
                    <button type="button" @click="showPasswordModal = false" class="px-4 py-2.5 rounded-xl border border-slate-200 text-slate-700 text-xs font-bold hover:bg-slate-50 transition">
                        Batal
                    </button>
                    <button type="submit" class="px-5 py-2.5 rounded-xl bg-slate-900 text-white text-xs font-bold hover:bg-slate-800 transition">
                        Simpan Password
                    </button>
                </div>
            </form>
        </div>
    </div>

</div>
@endsection
