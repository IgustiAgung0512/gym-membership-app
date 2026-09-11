@extends('layouts.admin')
@section('title', 'Member')

@section('content')
<div x-data="{
    assignModal: false,
    selectedMember: null,
    rfidUid: '',
    pollInterval: null,
    isScanning: false,

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

    <div class="flex flex-col sm:flex-row sm:items-center gap-3">
        <form method="GET" class="flex-1 flex gap-3">
            <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari nama, telepon, atau kode member..."
                   class="flex-1 rounded-xl bg-white border border-slate-300 px-4 py-2.5 text-sm text-slate-900 placeholder:text-slate-400 focus:outline-none focus:ring-2 focus:ring-lime-500 shadow-sm">
            <select name="status" onchange="this.form.submit()" class="rounded-xl bg-white border border-slate-300 px-3 py-2.5 text-sm text-slate-900 focus:outline-none focus:ring-2 focus:ring-lime-500 shadow-sm">
                <option value="">Semua Status</option>
                <option value="active" @selected(request('status')=='active')>Aktif</option>
                <option value="inactive" @selected(request('status')=='inactive')>Nonaktif</option>
                <option value="expired" @selected(request('status')=='expired')>Expired</option>
            </select>
        </form>
        <a href="{{ route('admin.members.create') }}" class="flex items-center justify-center gap-2 rounded-xl bg-slate-900 text-white text-sm font-bold px-4 py-2.5 hover:bg-slate-800 transition whitespace-nowrap shadow-sm">
            <svg class="w-4 h-4 text-lime-400" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/></svg>
            <span>Member Baru</span>
        </a>
    </div>

    <!-- Mobile cards -->
    <div class="grid gap-3 lg:hidden">
        @forelse ($members as $m)
            <div class="bg-white border border-slate-200 rounded-2xl p-4 shadow-sm">
                <div class="flex items-start justify-between gap-2">
                    <div class="flex items-center gap-3 min-w-0">
                        @if ($m->photo)
                            <img src="{{ route('admin.members.photo', $m) }}" alt="{{ $m->user->name }}" class="w-10 h-10 rounded-full object-cover shrink-0" onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';">
                            <div class="w-10 h-10 rounded-full bg-lime-100 text-lime-800 hidden items-center justify-center font-bold shrink-0">{{ substr($m->user->name,0,1) }}</div>
                        @else
                            <div class="w-10 h-10 rounded-full bg-lime-100 text-lime-800 flex items-center justify-center font-bold shrink-0">{{ substr($m->user->name,0,1) }}</div>
                        @endif
                        <div class="min-w-0">
                            <p class="text-slate-900 font-semibold truncate">{{ $m->user->name }}</p>
                            <p class="text-xs text-slate-500 font-mono">{{ $m->member_code }} &middot; {{ $m->user->phone }}</p>
                        </div>
                    </div>
                    @php $badge = ['active'=>'bg-lime-100 text-lime-800 font-semibold','inactive'=>'bg-slate-100 text-slate-600 font-semibold','expired'=>'bg-rose-100 text-rose-800 font-semibold'][$m->status]; @endphp
                    <span class="text-xs px-2.5 py-0.5 rounded-full {{ $badge }} shrink-0">{{ ucfirst($m->status) }}</span>
                </div>
                <div class="mt-3 grid grid-cols-2 gap-2 text-xs text-slate-500 border-t border-slate-100 pt-2">
                    <p>Paket: <strong class="text-slate-800">{{ $m->package->name ?? '-' }}</strong></p>
                    <p>
                        RFID: 
                        @if ($m->rfidCard)
                            <strong class="text-slate-800 font-mono">{{ $m->rfidCard->uid }}</strong>
                        @else
                            <button type="button" 
                                    @click="openAssign({{ Js::from(['id' => $m->id, 'member_code' => $m->member_code, 'name' => $m->user->name]) }})" 
                                    class="inline-flex items-center gap-1 text-[11px] font-bold text-amber-700 bg-amber-100 hover:bg-amber-200 px-2 py-0.5 rounded">
                                + Tautkan
                            </button>
                        @endif
                    </p>
                    <p>Berakhir: <strong class="text-slate-800">{{ optional($m->expire_date)->format('d M Y') ?? '-' }}</strong></p>
                </div>
                <div class="mt-3 flex gap-2">
                    @if (!$m->rfidCard)
                        <button type="button" 
                                @click="openAssign({{ Js::from(['id' => $m->id, 'member_code' => $m->member_code, 'name' => $m->user->name]) }})" 
                                class="flex-1 text-center text-xs font-bold rounded-lg bg-amber-500 text-slate-950 py-2 hover:bg-amber-400">
                            Tautkan RFID
                        </button>
                    @endif
                    <a href="{{ route('admin.members.edit', $m) }}" class="flex-1 text-center text-xs font-semibold rounded-lg border border-slate-200 py-2 text-slate-700 hover:bg-slate-50">Edit</a>
                    <form method="POST" action="{{ route('admin.members.renew', $m) }}" class="flex-1"><button class="w-full text-xs font-semibold rounded-lg bg-lime-100 text-lime-800 py-2 hover:bg-lime-200">Perpanjang</button>@csrf</form>
                </div>
            </div>
        @empty
            <p class="text-sm text-slate-500 text-center py-10">Belum ada member.</p>
        @endforelse
    </div>

    <!-- Desktop table -->
    <div class="hidden lg:block bg-white border border-slate-200 rounded-2xl overflow-hidden shadow-sm">
        <table class="w-full text-sm">
            <thead>
                <tr class="text-left bg-slate-50 text-slate-600 border-b border-slate-200">
                    <th class="px-5 py-3.5 font-semibold text-xs uppercase tracking-wider">Member</th>
                    <th class="px-5 py-3.5 font-semibold text-xs uppercase tracking-wider">Paket</th>
                    <th class="px-5 py-3.5 font-semibold text-xs uppercase tracking-wider">RFID UID</th>
                    <th class="px-5 py-3.5 font-semibold text-xs uppercase tracking-wider">Berakhir</th>
                    <th class="px-5 py-3.5 font-semibold text-xs uppercase tracking-wider">Status</th>
                    <th class="px-5 py-3.5 font-semibold text-xs uppercase tracking-wider text-right">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
            @forelse ($members as $m)
                <tr class="hover:bg-slate-50/80 transition">
                    <td class="px-5 py-3.5">
                        <div class="flex items-center gap-3">
                            @if ($m->photo)
                                <img src="{{ route('admin.members.photo', $m) }}" alt="{{ $m->user->name }}" class="w-9 h-9 rounded-full object-cover shrink-0" onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';">
                                <div class="w-9 h-9 rounded-full bg-lime-100 text-lime-800 hidden items-center justify-center font-bold text-sm shrink-0">{{ substr($m->user->name,0,1) }}</div>
                            @else
                                <div class="w-9 h-9 rounded-full bg-lime-100 text-lime-800 flex items-center justify-center font-bold text-sm shrink-0">{{ substr($m->user->name,0,1) }}</div>
                            @endif
                            <div>
                                <p class="text-slate-900 font-semibold">{{ $m->user->name }}</p>
                                <p class="text-xs text-slate-500 font-mono">{{ $m->member_code }} &middot; {{ $m->user->phone }}</p>
                            </div>
                        </div>
                    </td>
                    <td class="px-5 py-3.5 text-slate-700 font-medium">{{ $m->package->name ?? '-' }}</td>
                    <td class="px-5 py-3.5">
                        @if ($m->rfidCard)
                            <span class="font-mono text-xs text-slate-700 font-semibold bg-slate-100 px-2 py-0.5 rounded">{{ $m->rfidCard->uid }}</span>
                        @else
                            <button type="button" 
                                    @click="openAssign({{ Js::from(['id' => $m->id, 'member_code' => $m->member_code, 'name' => $m->user->name]) }})" 
                                    class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg bg-amber-100 text-amber-800 hover:bg-amber-200 font-bold text-[11px] transition">
                                <span>⚠️ + Tautkan Kartu</span>
                            </button>
                        @endif
                    </td>
                    <td class="px-5 py-3.5 text-slate-700">{{ optional($m->expire_date)->format('d M Y') ?? '-' }}</td>
                    <td class="px-5 py-3.5">
                        @php $badge = ['active'=>'bg-lime-100 text-lime-800 font-semibold','inactive'=>'bg-slate-100 text-slate-600 font-semibold','expired'=>'bg-rose-100 text-rose-800 font-semibold'][$m->status]; @endphp
                        <span class="text-xs px-2.5 py-0.5 rounded-full {{ $badge }}">{{ ucfirst($m->status) }}</span>
                    </td>
                    <td class="px-5 py-3.5">
                        <div class="flex items-center justify-end gap-3 text-xs">
                            @if (!$m->rfidCard)
                                <button type="button" 
                                        @click="openAssign({{ Js::from(['id' => $m->id, 'member_code' => $m->member_code, 'name' => $m->user->name]) }})" 
                                        class="font-bold text-amber-700 hover:underline">
                                    Tautkan Kartu
                                </button>
                            @endif
                            <form method="POST" action="{{ route('admin.members.renew', $m) }}"><button class="font-semibold text-lime-700 hover:underline">Perpanjang</button>@csrf</form>
                            <a href="{{ route('admin.members.edit', $m) }}" class="font-semibold text-slate-700 hover:underline">Edit</a>
                            <form method="POST" action="{{ route('admin.members.destroy', $m) }}" onsubmit="return confirm('Hapus member ini?')">
                                @csrf @method('DELETE')
                                <button class="font-semibold text-rose-600 hover:underline">Hapus</button>
                            </form>
                        </div>
                    </td>
                </tr>
            @empty
                <tr><td colspan="6" class="px-5 py-10 text-center text-slate-500">Belum ada member.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-6">{{ $members->links() }}</div>

    <!-- MODAL TAUTKAN KARTU RFID -->
    <div x-show="assignModal" 
         x-cloak 
         class="fixed inset-0 z-50 overflow-y-auto" 
         style="display: none;">
        <div class="fixed inset-0 bg-slate-950/70 backdrop-blur-sm transition-opacity" @click="closeAssign()"></div>
        <div class="flex min-h-full items-center justify-center p-4 text-center">
            <div class="relative transform overflow-hidden rounded-2xl bg-white border border-slate-200 text-left shadow-2xl transition-all w-full max-w-md p-6 space-y-4">
                <div class="flex items-center justify-between border-b border-slate-100 pb-3">
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

                <div class="bg-slate-50 p-3 rounded-xl border border-slate-200 text-xs space-y-1" x-show="selectedMember">
                    <p>Nama Member: <strong class="text-slate-900" x-text="selectedMember?.name"></strong></p>
                    <p>Kode Member: <strong class="text-slate-900 font-mono" x-text="selectedMember?.member_code"></strong></p>
                </div>

                <form method="POST" :action="'/admin/members/' + (selectedMember ? selectedMember.id : '') + '/assign-rfid'" class="space-y-4">
                    @csrf
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">UID Kartu RFID <span class="text-rose-500">*</span></label>
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

                    <div class="flex gap-2 pt-2">
                        <button type="button" @click="closeAssign()" class="flex-1 py-2.5 rounded-xl border border-slate-300 text-slate-700 font-semibold text-xs hover:bg-slate-50">
                            Batal
                        </button>
                        <button type="submit" class="flex-1 py-2.5 rounded-xl bg-slate-900 text-white font-bold text-xs hover:bg-slate-800 shadow-sm">
                            Simpan & Tautkan
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

</div>
@endsection
