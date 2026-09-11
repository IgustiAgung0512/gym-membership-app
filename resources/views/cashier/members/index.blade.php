@extends('layouts.cashier')
@section('title', 'Member & Pendaftaran Front Desk')

@section('content')
<div x-data="{
    renewModal: false,
    assignModal: false,
    selectedMember: null,
    packageId: '',
    paymentMethod: 'cash',
    rfidUid: '',
    pollInterval: null,
    isScanning: false,

    openRenew(member) {
        this.selectedMember = member;
        this.packageId = member.membership_package_id || '';
        this.paymentMethod = 'cash';
        this.renewModal = true;
    },

    openAssign(member) {
        this.selectedMember = member;
        this.rfidUid = '';
        this.assignModal = true;
        this.startRfidPolling();
    },

    closeAssign() {
        this.assignModal = false;
        this.selectedMember = null;
        this.stopRfidPolling();
    },

    startRfidPolling() {
        this.stopRfidPolling();
        this.isScanning = true;
        this.pollInterval = setInterval(async () => {
            if (!this.assignModal) return;
            try {
                const res = await fetch('{{ route('admin.members.latest-rfid') }}');
                if (!res.ok) return;
                const data = await res.json();
                if (data && data.uid) {
                    this.rfidUid = data.uid;
                }
            } catch (e) {}
        }, 1000);
    },

    stopRfidPolling() {
        if (this.pollInterval) {
            clearInterval(this.pollInterval);
            this.pollInterval = null;
        }
        this.isScanning = false;
    }
}" class="space-y-6">

    {{-- HEADER --}}
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h1 class="text-xl font-display font-bold text-slate-900">Member & Layanan Resepsionis</h1>
            <p class="text-xs text-slate-500 mt-0.5">Daftarkan member baru, tautkan kartu Smart RFID, atau perpanjang paket membership di meja kasir.</p>
        </div>
        <a href="{{ route('cashier.members.create') }}" class="inline-flex items-center gap-2 px-4 py-2 rounded-xl bg-lime-500 hover:bg-lime-400 text-slate-950 text-xs font-bold transition shadow-sm self-start">
            <span>+ Daftar Member Baru</span>
        </a>
    </div>

    {{-- SEARCH & FILTER --}}
    <div class="bg-white p-4 rounded-2xl border border-slate-200 shadow-sm">
        <form method="GET" action="{{ route('cashier.members.index') }}" class="flex flex-col sm:flex-row gap-3">
            <div class="relative flex-1">
                <svg class="w-4 h-4 text-slate-400 absolute left-3.5 top-1/2 -translate-y-1/2" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari nama member, no. HP, atau kode GYM-..." class="w-full pl-10 pr-4 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs focus:outline-none focus:ring-2 focus:ring-lime-500">
            </div>
            <select name="status" class="bg-slate-50 border border-slate-200 rounded-xl px-3 py-2 text-xs focus:outline-none focus:ring-2 focus:ring-lime-500">
                <option value="">Semua Status</option>
                <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>Aktif</option>
                <option value="expired" {{ request('status') === 'expired' ? 'selected' : '' }}>Expired</option>
                <option value="inactive" {{ request('status') === 'inactive' ? 'selected' : '' }}>Nonaktif</option>
            </select>
            <button type="submit" class="px-4 py-2 rounded-xl bg-slate-900 text-white text-xs font-bold hover:bg-slate-800 transition">
                Cari
            </button>
        </form>
    </div>

    {{-- MEMBERS TABLE --}}
    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs text-slate-600">
                <thead class="bg-slate-50 border-b border-slate-200 text-[11px] uppercase font-bold text-slate-400">
                    <tr>
                        <th class="py-3 px-4">Member</th>
                        <th class="py-3 px-4">Kontak</th>
                        <th class="py-3 px-4">Paket</th>
                        <th class="py-3 px-4">Masa Berlaku</th>
                        <th class="py-3 px-4">Kartu RFID</th>
                        <th class="py-3 px-4">Status</th>
                        <th class="py-3 px-4 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse ($members as $m)
                        <tr class="hover:bg-slate-50 transition">
                            <td class="py-3 px-4">
                                <div class="flex items-center gap-3">
                                    <div class="w-9 h-9 rounded-xl bg-slate-100 border border-slate-200 overflow-hidden flex items-center justify-center font-bold text-slate-700 shrink-0 relative">
                                        @if ($m->photo)
                                            <img src="{{ route('cashier.members.photo', $m) }}" alt="{{ $m->user->name }}" class="w-full h-full object-cover" onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';">
                                            <span class="hidden items-center justify-center w-full h-full text-slate-700 font-bold text-sm bg-lime-100 text-lime-800">
                                                {{ strtoupper(substr($m->user->name, 0, 1)) }}
                                            </span>
                                        @else
                                            <span class="flex items-center justify-center w-full h-full bg-slate-100 text-slate-700 font-bold text-sm">
                                                {{ strtoupper(substr($m->user->name, 0, 1)) }}
                                            </span>
                                        @endif
                                    </div>
                                    <div>
                                        <p class="font-bold text-slate-900">{{ $m->user->name }}</p>
                                        <p class="text-[11px] text-slate-400 font-mono">{{ $m->member_code }}</p>
                                    </div>
                                </div>
                            </td>
                            <td class="py-3 px-4">
                                <p class="text-slate-800 font-semibold">{{ $m->user->phone ?? '-' }}</p>
                                <p class="text-[11px] text-slate-400">{{ $m->user->email }}</p>
                            </td>
                            <td class="py-3 px-4 font-bold text-slate-800">
                                {{ $m->package->name ?? '-' }}
                            </td>
                            <td class="py-3 px-4">
                                @if ($m->expire_date)
                                    <span class="font-mono {{ $m->expire_date->isPast() ? 'text-rose-600 font-bold' : 'text-slate-700' }}">
                                        {{ $m->expire_date->format('d/m/Y') }}
                                    </span>
                                @else
                                    -
                                @endif
                            </td>
                            <td class="py-3 px-4">
                                @if ($m->rfidCard)
                                    <span class="font-mono text-[11px] px-2 py-0.5 rounded bg-slate-100 text-slate-700 font-bold">
                                        {{ $m->rfidCard->uid }}
                                    </span>
                                @else
                                    <button type="button" 
                                            @click="openAssign({{ Js::from(['id' => $m->id, 'member_code' => $m->member_code, 'name' => $m->user->name]) }})" 
                                            class="inline-flex items-center gap-1 px-2 py-0.5 rounded-lg bg-amber-100 text-amber-800 hover:bg-amber-200 font-bold text-[10px] transition border border-amber-300">
                                        <span>⚠️ Tautkan RFID</span>
                                    </button>
                                @endif
                            </td>
                            <td class="py-3 px-4">
                                @if ($m->status === 'active')
                                    <span class="px-2 py-0.5 rounded-full bg-lime-100 text-lime-800 font-bold text-[10px]">AKTIF</span>
                                @elseif ($m->status === 'expired')
                                    <span class="px-2 py-0.5 rounded-full bg-rose-100 text-rose-800 font-bold text-[10px]">EXPIRED</span>
                                @else
                                    <span class="px-2 py-0.5 rounded-full bg-slate-100 text-slate-600 font-bold text-[10px]">NONAKTIF</span>
                                @endif
                            </td>
                            <td class="py-3 px-4 text-right space-x-1">
                                @if (!$m->rfidCard)
                                    <button 
                                        type="button" 
                                        @click="openAssign({{ Js::from(['id' => $m->id, 'member_code' => $m->member_code, 'name' => $m->user->name]) }})"
                                        class="px-2.5 py-1 rounded-lg bg-amber-500 hover:bg-amber-400 text-slate-950 font-bold text-[11px] transition shadow-sm"
                                    >
                                        Tautkan Kartu
                                    </button>
                                @endif
                                <button 
                                    type="button" 
                                    @click="openRenew({{ Js::from($m) }})"
                                    class="px-2.5 py-1 rounded-lg bg-lime-500 hover:bg-lime-400 text-slate-950 font-bold text-[11px] transition shadow-sm"
                                >
                                    Perpanjang
                                </button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="py-10 text-center text-slate-400">
                                Tidak ada data member yang ditemukan.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($members->hasPages())
            <div class="p-4 border-t border-slate-100">
                {{ $members->links() }}
            </div>
        @endif
    </div>

    {{-- MODAL PERPANJANGAN MEMBER --}}
    <div 
        x-show="renewModal" 
        x-cloak 
        class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-sm"
    >
        <div 
            @click.away="renewModal = false"
            class="bg-white rounded-3xl max-w-md w-full p-6 shadow-2xl border border-slate-100 relative"
        >
            <div class="flex items-center justify-between pb-3 border-b border-slate-100">
                <h3 class="font-display font-bold text-base text-slate-900">Perpanjang Paket Membership</h3>
                <button type="button" @click="renewModal = false" class="text-slate-400 hover:text-slate-600">&times;</button>
            </div>

            <form :action="'/cashier/members/' + (selectedMember ? selectedMember.id : '') + '/renew'" method="POST" class="mt-4 space-y-4">
                @csrf

                <div class="p-3.5 rounded-xl bg-slate-50 border border-slate-200">
                    <p class="text-xs text-slate-400 uppercase font-bold">Member Terpilih</p>
                    <p class="font-bold text-sm text-slate-900 mt-0.5" x-text="selectedMember ? selectedMember.user.name : ''"></p>
                    <p class="text-[11px] text-slate-500 font-mono" x-text="selectedMember ? selectedMember.member_code : ''"></p>
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">Pilih Paket Perpanjangan</label>
                    <select name="membership_package_id" x-model="packageId" required class="w-full bg-slate-50 border border-slate-300 rounded-xl px-3 py-2 text-xs focus:outline-none focus:ring-2 focus:ring-lime-500 font-medium">
                        @foreach ($packages as $pkg)
                            <option value="{{ $pkg->id }}">
                                {{ $pkg->name }} ({{ $pkg->duration_months }} Bln) - Rp{{ number_format($pkg->price, 0, ',', '.') }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">Metode Pembayaran</label>
                    <select name="payment_method" x-model="paymentMethod" required class="w-full bg-slate-50 border border-slate-300 rounded-xl px-3 py-2 text-xs focus:outline-none focus:ring-2 focus:ring-lime-500 font-medium">
                        <option value="cash">💵 Tunai (Cash)</option>
                        <option value="qris">📱 QRIS</option>
                        <option value="transfer">🏦 Transfer Bank</option>
                    </select>
                </div>

                <div class="pt-3 grid grid-cols-2 gap-2">
                    <button type="button" @click="renewModal = false" class="py-2.5 rounded-xl border border-slate-200 text-slate-600 text-xs font-bold hover:bg-slate-50 transition">
                        Batal
                    </button>
                    <button type="submit" class="py-2.5 rounded-xl bg-lime-500 hover:bg-lime-400 text-slate-950 text-xs font-bold transition shadow-sm">
                        Proses Pembayaran
                    </button>
                </div>
            </form>
        </div>
    </div>

    {{-- MODAL TAUTKAN KARTU RFID KASIR --}}
    <div x-show="assignModal" 
         x-cloak 
         class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-sm"
         style="display: none;">
        <div @click.away="closeAssign()" class="bg-white rounded-3xl max-w-md w-full p-6 shadow-2xl border border-slate-100 relative space-y-4">
            <div class="flex items-center justify-between pb-3 border-b border-slate-100">
                <div class="flex items-center gap-2">
                    <div class="w-8 h-8 rounded-xl bg-amber-100 text-amber-700 flex items-center justify-center font-bold text-sm">
                        🪪
                    </div>
                    <div>
                        <h3 class="font-display font-bold text-sm text-slate-900">Tautkan Kartu RFID Member</h3>
                        <p class="text-[11px] text-slate-500">Serahkan kartu fisik Smart RFID ke member baru</p>
                    </div>
                </div>
                <button type="button" @click="closeAssign()" class="text-slate-400 hover:text-slate-600">✕</button>
            </div>

            <div class="bg-slate-50 p-3.5 rounded-xl border border-slate-200 text-xs space-y-1" x-show="selectedMember">
                <p class="text-slate-400 uppercase font-bold text-[10px]">Member Terpilih</p>
                <p class="font-bold text-sm text-slate-900" x-text="selectedMember?.name"></p>
                <p class="text-[11px] text-slate-500 font-mono" x-text="selectedMember?.member_code"></p>
            </div>

            <form method="POST" :action="'/cashier/members/' + (selectedMember ? selectedMember.id : '') + '/assign-rfid'" class="space-y-4">
                @csrf
                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">UID Kartu RFID <span class="text-rose-500">*</span></label>
                    <div class="relative">
                        <input type="text" 
                               name="rfid_uid" 
                               x-model="rfidUid" 
                               required 
                               placeholder="Contoh: C1:B8:C8:A3"
                               class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 font-mono text-sm focus:outline-none focus:ring-2 focus:ring-lime-500">
                    </div>
                    <div class="mt-2 flex items-center gap-2 text-[11px] text-slate-500">
                        <span class="w-2 h-2 rounded-full bg-lime-500 animate-ping"></span>
                        <span>Tempelkan kartu pada scanner RFID USB/ESP untuk auto-fill.</span>
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-2 pt-2">
                    <button type="button" @click="closeAssign()" class="py-2.5 rounded-xl border border-slate-200 text-slate-600 text-xs font-bold hover:bg-slate-50 transition">
                        Batal
                    </button>
                    <button type="submit" class="py-2.5 rounded-xl bg-slate-900 text-white font-bold text-xs hover:bg-slate-800 shadow-sm transition">
                        Simpan & Tautkan
                    </button>
                </div>
            </form>
        </div>
    </div>

</div>
@endsection
