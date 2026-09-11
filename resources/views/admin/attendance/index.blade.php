@extends('layouts.admin')
@section('title', 'Absensi / Check-in')

@section('content')
<div class="space-y-6" x-data="adminQuickAttendanceApp()">

    {{-- TOP ROW: QUICK SEARCH FALLBACK CHECKIN & DATE FILTER --}}
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-5">
        
        {{-- QUICK SEARCH CHECK-IN CARD (2 COLS) --}}
        <div class="lg:col-span-2 bg-white p-5 rounded-2xl border border-slate-200 shadow-sm relative" @click.outside="showDropdown = false">
            <div class="flex items-center justify-between mb-3">
                <div class="flex items-center gap-2">
                    <span class="w-8 h-8 rounded-xl bg-lime-100 text-lime-800 flex items-center justify-center font-bold text-sm">⚡</span>
                    <div>
                        <h3 class="font-display font-bold text-slate-900 text-sm" style="color: #0f172a !important;">Quick Check-in Fallback (Lupa Kartu)</h3>
                        <p class="text-[11px] text-slate-500">Klik kolom pencarian untuk memilih member aktif atau ketik Nama / No. HP / Kode Member.</p>
                    </div>
                </div>
                <span class="text-[11px] font-bold px-2.5 py-1 rounded-full bg-slate-100 text-slate-700 font-mono">
                    {{ count($activeMembers) }} Member Aktif
                </span>
            </div>

            <form method="POST" action="{{ route('attendance.manual') }}" @submit.prevent="submitDirect()">
                @csrf
                <div class="relative">
                    <input 
                        type="text" 
                        x-model="searchQuery" 
                        @focus="showDropdown = true"
                        @input="showDropdown = true"
                        @keydown.escape="showDropdown = false"
                        @keydown.enter.prevent="submitSelectedOrFirst()"
                        placeholder="Klik disini / ketik Nama Member, No. HP, atau Kode Member..."
                        class="w-full bg-slate-50 border border-slate-300 rounded-xl pl-10 pr-28 py-3 text-xs font-medium focus:outline-none focus:ring-2 focus:ring-lime-500 focus:bg-white text-slate-900 shadow-inner"
                    >
                    <span class="absolute left-3.5 top-3.5 text-slate-400 text-sm">🔍</span>

                    <button 
                        type="button"
                        x-show="searchQuery"
                        @click="searchQuery = ''; showDropdown = true"
                        class="absolute right-24 top-3 text-slate-400 hover:text-slate-600 text-xs px-1"
                        title="Bersihkan pencarian"
                    >
                        ✕
                    </button>
                    
                    <button 
                        type="button"
                        @click="submitSelectedOrFirst()"
                        :disabled="!searchQuery && !selectedMember"
                        class="absolute right-1.5 top-1.5 bottom-1.5 px-3.5 rounded-lg bg-slate-900 hover:bg-slate-800 disabled:opacity-40 disabled:cursor-not-allowed text-white text-xs font-bold transition flex items-center gap-1 shadow-sm"
                    >
                        <span>Presensi</span>
                        <span class="text-[10px] opacity-75 font-mono">↵</span>
                    </button>
                </div>
            </form>

            {{-- LIVE SEARCH RESULTS DROPDOWN / LIST (MUNCUL INSTAN SAAT DIKLIK) --}}
            <div 
                x-show="showDropdown && filteredMembers.length > 0" 
                x-cloak 
                class="absolute left-5 right-5 z-30 mt-1.5 bg-white border border-slate-200 rounded-2xl shadow-xl max-h-64 overflow-y-auto divide-y divide-slate-100"
            >
                <div class="px-3.5 py-2 bg-slate-50 border-b border-slate-100 flex items-center justify-between text-[10px] font-bold uppercase text-slate-400 tracking-wider">
                    <span x-text="searchQuery ? 'Hasil Pencarian Member' : 'Pilih Member Cepat (Aktif)'"></span>
                    <span x-text="filteredMembers.length + ' ditampilkan'"></span>
                </div>

                <template x-for="m in filteredMembers" :key="m.id">
                    <div 
                        @click="selectAndCheckin(m)"
                        class="p-3 hover:bg-lime-50/70 cursor-pointer transition flex items-center justify-between gap-3 text-xs group"
                    >
                        <div class="flex items-center gap-3 min-w-0">
                            <div class="w-8 h-8 rounded-xl bg-slate-100 group-hover:bg-lime-100 flex items-center justify-center font-bold text-slate-700 group-hover:text-lime-900 shrink-0 overflow-hidden border border-slate-200 transition">
                                <span x-text="(m.user?.name || '?').charAt(0).toUpperCase()"></span>
                            </div>
                            <div class="min-w-0">
                                <p class="font-bold text-slate-900 truncate group-hover:text-lime-950" style="color: #0f172a !important;" x-text="m.user?.name || '-'"></p>
                                <p class="text-[11px] text-slate-500 font-mono" x-text="(m.member_code || '-') + ' · ' + (m.user?.phone || '-')"></p>
                            </div>
                        </div>
                        <div class="flex items-center gap-2 shrink-0">
                            <span class="text-[10px] font-semibold px-2 py-0.5 rounded-full bg-slate-100 text-slate-700" x-text="m.package ? m.package.name : 'Paket Gym'"></span>
                            <span class="text-xs font-bold text-lime-900 bg-lime-200 group-hover:bg-lime-300 px-2.5 py-1 rounded-lg transition shadow-xs">
                                ⚡ Check-in
                            </span>
                        </div>
                    </div>
                </template>
            </div>

            {{-- JIKA PENCARIAN TIDAK DITEMUKAN --}}
            <div 
                x-show="showDropdown && filteredMembers.length === 0 && searchQuery.length > 0" 
                x-cloak 
                class="absolute left-5 right-5 z-30 mt-1.5 bg-white border border-slate-200 rounded-2xl shadow-xl p-4 text-center text-xs text-slate-500"
            >
                <p class="font-bold text-slate-800">Tidak ada member aktif yang cocok dengan "<span x-text="searchQuery"></span>"</p>
                <p class="text-[11px] text-slate-400 mt-1">Tekan tombol <strong>Presensi (Enter)</strong> untuk mencoba verifikasi langsung ke database.</p>
            </div>

            {{-- SELECTED MEMBER CARD PREVIEW --}}
            <template x-if="selectedMember">
                <div class="mt-3.5 p-3 rounded-xl bg-slate-50 border border-slate-200 flex items-center justify-between gap-3 text-xs">
                    <div class="flex items-center gap-3 min-w-0">
                        <div class="w-10 h-10 rounded-xl bg-slate-900 text-lime-400 flex items-center justify-center font-bold text-sm shrink-0">
                            <span x-text="(selectedMember.user?.name || '?').charAt(0).toUpperCase()"></span>
                        </div>
                        <div class="min-w-0">
                            <p class="font-bold text-slate-900 text-sm truncate" style="color: #0f172a !important;" x-text="selectedMember.user?.name"></p>
                            <p class="text-[11px] text-slate-500 font-mono" x-text="'Kode: ' + selectedMember.member_code + ' | Telp: ' + (selectedMember.user?.phone || '-')"></p>
                        </div>
                    </div>
                    <form method="POST" action="{{ route('attendance.manual') }}">
                        @csrf
                        <input type="hidden" name="member_id" :value="selectedMember.id">
                        <button type="submit" class="px-4 py-2 rounded-xl bg-slate-900 hover:bg-slate-800 text-white font-bold text-xs shadow-sm transition">
                            Catat Kehadiran (WA Notif) &rarr;
                        </button>
                    </form>
                </div>
            </template>
        </div>

        {{-- DATE FILTER & STATS --}}
        <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-sm flex flex-col justify-between">
            <div>
                <div class="flex items-center gap-2 mb-1">
                    <span class="text-base">📅</span>
                    <h3 class="font-display font-bold text-slate-900 text-sm" style="color: #0f172a !important;">Filter Tanggal Absensi</h3>
                </div>
                <p class="text-xs text-slate-500">Total: <strong class="text-slate-900">{{ $attendances->total() }}</strong> sesi presensi tercatat.</p>
            </div>
            <form method="GET" action="{{ route('attendance.index') }}" class="flex items-center gap-2.5 mt-4">
                <input type="date" name="date" value="{{ $date }}" class="flex-1 bg-slate-50 border border-slate-300 rounded-xl px-3 py-2 text-xs text-slate-900 focus:outline-none focus:ring-2 focus:ring-lime-500">
                <button type="submit" class="px-4 py-2 rounded-xl bg-slate-900 text-white font-bold text-xs hover:bg-slate-800 transition shadow-sm shrink-0">
                    Tampilkan
                </button>
            </form>
        </div>

    </div>

    {{-- MOBILE CARDS VIEW (Responsif di HP) --}}
    <div class="grid gap-3 lg:hidden">
        @forelse ($attendances as $a)
            <div class="bg-white border border-slate-200 rounded-2xl p-4 shadow-sm">
                <div class="flex items-start justify-between gap-2">
                    <div class="flex items-center gap-3 min-w-0">
                        @if ($a->member?->photo)
                            <img src="{{ route('admin.members.photo', $a->member) }}" alt="{{ $a->member->user->name }}" class="w-10 h-10 rounded-xl object-cover shrink-0" onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';">
                            <div class="w-10 h-10 rounded-xl bg-lime-100 text-lime-800 hidden items-center justify-center font-bold text-sm shrink-0">
                                {{ strtoupper(substr($a->member->user->name ?? '?', 0, 1)) }}
                            </div>
                        @else
                            <div class="w-10 h-10 rounded-xl bg-lime-100 text-lime-800 flex items-center justify-center font-bold text-sm shrink-0">
                                {{ strtoupper(substr($a->member->user->name ?? '?', 0, 1)) }}
                            </div>
                        @endif
                        <div class="min-w-0">
                            <p class="text-slate-900 font-semibold text-sm truncate" style="color: #0f172a !important;">{{ $a->member->user->name ?? 'Non-Member' }}</p>
                            <p class="text-xs text-slate-500 font-mono">{{ $a->member->member_code ?? '-' }}</p>
                        </div>
                    </div>
                    <span class="text-xs font-mono font-bold px-2.5 py-1 rounded-lg bg-slate-100 text-slate-800 shrink-0">
                        {{ $a->check_in_at->format('H:i') }} WIB
                    </span>
                </div>

                <div class="mt-3 pt-2.5 border-t border-slate-100 flex items-center justify-between text-xs">
                    <div>
                        <span class="text-slate-500">Metode: </span>
                        @if ($a->method === 'rfid')
                            <span class="font-bold text-lime-700">⚡ RFID Tap</span>
                        @elseif ($a->method === 'qr')
                            <span class="font-bold text-lime-700">📱 QR E-Card</span>
                        @else
                            <span class="font-semibold text-slate-700">✍️ Manual</span>
                        @endif
                    </div>
                    <div>
                        <span class="text-slate-500">UID/Ref: </span>
                        <span class="font-mono text-slate-700 font-medium">{{ $a->rfidCard->uid ?? 'Manual' }}</span>
                    </div>
                </div>
            </div>
        @empty
            <div class="bg-white border border-slate-200 rounded-2xl p-8 text-center text-slate-500 text-sm shadow-sm">
                Belum ada data check-in pada tanggal ini.
            </div>
        @endforelse
    </div>

    {{-- DESKTOP TABLE VIEW (Layar Laptop / PC) --}}
    <div class="hidden lg:block bg-white border border-slate-200 rounded-2xl overflow-hidden shadow-sm">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="text-left bg-slate-50 text-slate-600 border-b border-slate-200">
                        <th class="px-5 py-3.5 font-semibold text-xs uppercase tracking-wider">Member</th>
                        <th class="px-5 py-3.5 font-semibold text-xs uppercase tracking-wider">Metode Presensi</th>
                        <th class="px-5 py-3.5 font-semibold text-xs uppercase tracking-wider">Jam Masuk</th>
                        <th class="px-5 py-3.5 font-semibold text-xs uppercase tracking-wider">Jam Keluar</th>
                        <th class="px-5 py-3.5 font-semibold text-xs uppercase tracking-wider">Durasi Sesi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                @forelse ($attendances as $a)
                    <tr class="hover:bg-slate-50/80 transition">
                        <td class="px-5 py-3.5">
                            <div class="flex items-center gap-3">
                                @if ($a->member?->photo)
                                    <img src="{{ route('admin.members.photo', $a->member) }}" alt="{{ $a->member->user->name }}" class="w-9 h-9 rounded-full object-cover shrink-0" onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';">
                                    <div class="w-9 h-9 rounded-full bg-lime-100 text-lime-800 hidden items-center justify-center font-bold text-sm shrink-0">
                                        {{ strtoupper(substr($a->member->user->name ?? '?', 0, 1)) }}
                                    </div>
                                @else
                                    <div class="w-9 h-9 rounded-full bg-lime-100 text-lime-800 flex items-center justify-center font-bold text-sm shrink-0">
                                        {{ strtoupper(substr($a->member->user->name ?? '?', 0, 1)) }}
                                    </div>
                                @endif
                                <div>
                                    <p class="text-slate-900 font-semibold" style="color: #0f172a !important;">{{ $a->member->user->name ?? 'Non-Member' }}</p>
                                    <p class="text-xs text-slate-500 font-mono">{{ $a->member->member_code ?? '-' }}</p>
                                </div>
                            </div>
                        </td>
                        <td class="px-5 py-3.5">
                            @if ($a->method === 'rfid')
                                <span class="text-xs px-2.5 py-0.5 rounded-full font-semibold bg-lime-100 text-lime-800">⚡ RFID: {{ $a->rfidCard->uid ?? 'Card' }}</span>
                            @elseif ($a->method === 'qr')
                                <span class="text-xs px-2.5 py-0.5 rounded-full font-semibold bg-lime-100 text-lime-800">📱 QR E-Card</span>
                            @else
                                <span class="text-xs px-2.5 py-0.5 rounded-full font-semibold bg-slate-100 text-slate-600">✍️ Manual</span>
                            @endif
                        </td>
                        <td class="px-5 py-3.5 text-slate-900 font-mono font-bold" style="color: #0f172a !important;">{{ $a->check_in_at->format('H:i:s') }} WIB</td>
                        <td class="px-5 py-3.5 text-slate-600 font-mono">
                            @if ($a->check_out_at)
                                <span class="text-slate-800 font-bold" style="color: #0f172a !important;">{{ $a->check_out_at->format('H:i:s') }} WIB</span>
                            @else
                                <span class="px-2 py-0.5 rounded-full bg-emerald-100 text-emerald-800 font-bold text-[10px]">
                                    SEDANG LATIHAN
                                </span>
                            @endif
                        </td>
                        <td class="px-5 py-3.5 font-medium text-slate-700">
                            <span class="font-semibold text-slate-900" style="color: #0f172a !important;">{{ $a->duration_formatted }}</span>
                            @if (!$a->check_out_at)
                                <span class="text-[10px] text-emerald-600 font-bold ml-1">(Berjalan)</span>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="px-5 py-10 text-center text-slate-500">Belum ada check-in di tanggal ini.</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="mt-5">{{ $attendances->links() }}</div>

</div>

<script>
function adminQuickAttendanceApp() {
    return {
        searchQuery: '',
        showDropdown: false,
        selectedMember: null,
        members: @json($activeMembers),

        get filteredMembers() {
            if (!this.searchQuery || !this.searchQuery.trim()) {
                return this.members.slice(0, 10);
            }
            const q = this.searchQuery.toLowerCase().trim();
            return this.members.filter(m => {
                const name = (m.user?.name || '').toLowerCase();
                const code = (m.member_code || '').toLowerCase();
                const phone = (m.user?.phone || '').toLowerCase();
                const uid = (m.rfid_card?.uid || '').toLowerCase();
                return name.includes(q) || code.includes(q) || phone.includes(q) || uid.includes(q);
            }).slice(0, 15);
        },

        selectAndCheckin(member) {
            this.selectedMember = member;
            this.searchQuery = member.user?.name || member.member_code;
            this.showDropdown = false;
            
            const form = document.createElement('form');
            form.method = 'POST';
            form.action = "{{ route('attendance.manual') }}";
            
            const csrf = document.createElement('input');
            csrf.type = 'hidden';
            csrf.name = '_token';
            csrf.value = "{{ csrf_token() }}";
            form.appendChild(csrf);

            const input = document.createElement('input');
            input.type = 'hidden';
            input.name = 'member_id';
            input.value = member.id;
            form.appendChild(input);

            document.body.appendChild(form);
            form.submit();
        },

        submitSelectedOrFirst() {
            if (this.selectedMember) {
                this.selectAndCheckin(this.selectedMember);
                return;
            }
            if (this.filteredMembers.length > 0 && this.searchQuery.trim()) {
                this.selectAndCheckin(this.filteredMembers[0]);
                return;
            }
            if (this.searchQuery.trim()) {
                this.submitDirect();
            }
        },

        submitDirect() {
            if (!this.searchQuery.trim()) return;
            const form = document.createElement('form');
            form.method = 'POST';
            form.action = "{{ route('attendance.manual') }}";
            
            const csrf = document.createElement('input');
            csrf.type = 'hidden';
            csrf.name = '_token';
            csrf.value = "{{ csrf_token() }}";
            form.appendChild(csrf);

            const input = document.createElement('input');
            input.type = 'hidden';
            input.name = 'identifier';
            input.value = this.searchQuery.trim();
            form.appendChild(input);

            document.body.appendChild(form);
            form.submit();
        }
    };
}
</script>
@endsection
