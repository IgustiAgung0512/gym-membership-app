@extends('layouts.admin')
@section('title', 'Kartu RFID')

@section('content')
<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    <div class="lg:col-span-2">
        
        {{-- MOBILE CARDS VIEW (Responsif di HP) --}}
        <div class="grid gap-3 lg:hidden">
            @forelse ($cards as $c)
                <div class="bg-white border border-slate-200 rounded-2xl p-4 shadow-sm">
                    <div class="flex items-start justify-between gap-2">
                        <div>
                            <span class="text-[10px] uppercase font-semibold text-slate-400">UID KARTU</span>
                            <p class="font-mono font-bold text-slate-900 text-base">{{ $c->uid }}</p>
                        </div>
                        @php $badge = ['assigned'=>'bg-lime-100 text-lime-800 font-semibold','unassigned'=>'bg-slate-100 text-slate-600 font-semibold','blocked'=>'bg-rose-100 text-rose-800 font-semibold'][$c->status]; @endphp
                        <span class="text-xs px-2.5 py-0.5 rounded-full {{ $badge }} shrink-0">
                            {{ ucfirst($c->status) }}
                        </span>
                    </div>

                    <div class="mt-2.5 pt-2.5 border-t border-slate-100 flex items-center justify-between text-xs">
                        <span class="text-slate-500">Pemilik:</span>
                        <strong class="text-slate-800">{{ $c->member->user->name ?? 'Belum terhubung' }}</strong>
                    </div>

                    <div class="mt-3 pt-2.5 border-t border-slate-100 flex items-center justify-end gap-2">
                        <form method="POST" action="{{ route('admin.rfid.toggle-block', $c) }}" class="flex-1">
                            @csrf
                            <button class="w-full text-xs font-semibold py-2 px-3 rounded-lg border {{ $c->status=='blocked' ? 'bg-lime-50 text-lime-800 border-lime-200 hover:bg-lime-100' : 'bg-rose-50 text-rose-700 border-rose-200 hover:bg-rose-100' }} transition">
                                {{ $c->status=='blocked' ? 'Buka Blokir' : 'Blokir' }}
                            </button>
                        </form>
                        <form method="POST" action="{{ route('admin.rfid.destroy', $c) }}" onsubmit="return confirm('Hapus kartu ini?')">
                            @csrf @method('DELETE')
                            <button class="text-xs font-semibold py-2 px-3.5 rounded-lg border border-slate-200 bg-white text-slate-600 hover:text-rose-600 hover:bg-rose-50 transition">
                                Hapus
                            </button>
                        </form>
                    </div>
                </div>
            @empty
                <div class="bg-white border border-slate-200 rounded-2xl p-8 text-center text-slate-500 text-sm shadow-sm">
                    Belum ada kartu RFID terdaftar.
                </div>
            @endforelse
        </div>

        {{-- DESKTOP TABLE VIEW (Layar Laptop / PC) --}}
        <div class="hidden lg:block bg-white border border-slate-200 rounded-2xl overflow-hidden shadow-sm">
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="text-left bg-slate-50 text-slate-600 border-b border-slate-200">
                            <th class="px-5 py-3.5 font-semibold text-xs uppercase tracking-wider">UID Kartu</th>
                            <th class="px-5 py-3.5 font-semibold text-xs uppercase tracking-wider">Pemilik</th>
                            <th class="px-5 py-3.5 font-semibold text-xs uppercase tracking-wider">Status</th>
                            <th class="px-5 py-3.5 font-semibold text-xs uppercase tracking-wider text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                    @forelse ($cards as $c)
                        <tr class="hover:bg-slate-50/80 transition">
                            <td class="px-5 py-3.5 font-mono font-bold text-slate-900">{{ $c->uid }}</td>
                            <td class="px-5 py-3.5 text-slate-700 font-medium">{{ $c->member->user->name ?? '—' }}</td>
                            <td class="px-5 py-3.5">
                                @php $badge = ['assigned'=>'bg-lime-100 text-lime-800 font-semibold','unassigned'=>'bg-slate-100 text-slate-600 font-semibold','blocked'=>'bg-rose-100 text-rose-800 font-semibold'][$c->status]; @endphp
                                <span class="text-xs px-2.5 py-0.5 rounded-full {{ $badge }}">{{ ucfirst($c->status) }}</span>
                            </td>
                            <td class="px-5 py-3.5">
                                <div class="flex items-center justify-end gap-3 text-xs">
                                    <form method="POST" action="{{ route('admin.rfid.toggle-block', $c) }}">
                                        @csrf
                                        <button class="font-semibold {{ $c->status=='blocked' ? 'text-lime-700' : 'text-rose-600' }} hover:underline">
                                            {{ $c->status=='blocked' ? 'Buka Blokir' : 'Blokir' }}
                                        </button>
                                    </form>
                                    <form method="POST" action="{{ route('admin.rfid.destroy', $c) }}" onsubmit="return confirm('Hapus kartu ini?')">
                                        @csrf @method('DELETE')
                                        <button class="font-semibold text-slate-500 hover:underline">Hapus</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="4" class="px-5 py-10 text-center text-slate-500">Belum ada kartu RFID terdaftar.</td></tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <div class="mt-5">{{ $cards->links() }}</div>
    </div>

    {{-- SIDEBAR FORM & INTEGRASI --}}
    <div class="space-y-6">
        <div class="bg-white border border-slate-200 rounded-2xl p-5 lg:p-6 shadow-sm">
            <div class="flex items-center justify-between mb-1">
                <h3 class="font-display font-bold text-slate-900 text-lg">Daftarkan Kartu Baru</h3>
                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[11px] font-semibold bg-lime-50 text-lime-700 border border-lime-200">
                    <span class="w-2 h-2 rounded-full bg-lime-500 animate-pulse"></span>
                    Live Scanner
                </span>
            </div>
            <p class="text-xs text-slate-500 mb-4">Tempelkan kartu RFID baru ke alat reader untuk scan otomatis, atau ketik manual.</p>

            {{-- Scanner Status Box --}}
            <div id="scanner-box" class="mb-4 p-3.5 rounded-xl border border-dashed border-slate-200 bg-slate-50 text-center transition-all duration-200">
                <div id="scanner-idle" class="flex items-center justify-center gap-2 text-xs text-slate-500">
                    <svg class="w-4 h-4 text-lime-600 animate-spin" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path>
                    </svg>
                    <span>Menunggu kartu di-tap ke reader...</span>
                </div>
                <div id="scanner-detected" class="hidden text-xs">
                    <div class="flex items-center justify-center gap-1.5 font-bold text-slate-800">
                        <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
                        <span>Kartu Terdeteksi: <span id="detected-uid" class="font-mono text-lime-700 font-bold bg-lime-100 px-2 py-0.5 rounded"></span></span>
                    </div>
                    <p id="detected-status-text" class="mt-1 text-[11px] text-slate-600"></p>
                </div>
            </div>

            <form method="POST" action="{{ route('admin.rfid.store') }}" id="rfid-form" class="space-y-3">
                @csrf
                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">UID Kartu</label>
                    <div class="relative">
                        <input type="text" name="uid" id="uid-input" required placeholder="Contoh: A1B2C3D4" class="w-full rounded-xl bg-slate-50 border border-slate-300 px-4 py-2.5 text-slate-900 font-mono text-sm focus:outline-none focus:bg-white focus:border-lime-600 focus:ring-2 focus:ring-lime-100 pr-16">
                        <button type="button" id="btn-clear-uid" class="hidden absolute right-3 top-1/2 -translate-y-1/2 text-xs font-medium text-slate-400 hover:text-rose-600 transition">
                            Reset
                        </button>
                    </div>
                    <p id="uid-feedback" class="text-xs mt-1 font-medium hidden"></p>
                    @error('uid')<p class="text-xs text-rose-600 font-medium mt-1">{{ $message }}</p>@enderror
                </div>

                <button type="submit" id="btn-submit-card" class="w-full rounded-xl bg-slate-900 text-white font-display font-bold text-sm py-2.5 hover:bg-slate-800 transition shadow-sm mt-2 flex items-center justify-center gap-2">
                    <span>Simpan Kartu</span>
                </button>
            </form>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const uidInput = document.getElementById('uid-input');
        const scannerIdle = document.getElementById('scanner-idle');
        const scannerDetected = document.getElementById('scanner-detected');
        const scannerBox = document.getElementById('scanner-box');
        const detectedUid = document.getElementById('detected-uid');
        const detectedStatusText = document.getElementById('detected-status-text');
        const uidFeedback = document.getElementById('uid-feedback');
        const btnSubmit = document.getElementById('btn-submit-card');
        const btnClear = document.getElementById('btn-clear-uid');

        let lastPolledUid = '';
        let checkingUid = false;

        async function pollLatestRfid() {
            if (document.hidden) return;
            try {
                const response = await fetch("{{ route('admin.rfid.latest') }}");
                if (!response.ok) return;
                const data = await response.json();

                if (data.uid && data.uid !== lastPolledUid) {
                    lastPolledUid = data.uid;
                    uidInput.value = data.uid;
                    onUidChanged(data.uid, true);
                }
            } catch (error) {
                console.error("Gagal polling RFID:", error);
            }
        }

        async function onUidChanged(uid, isFromScanner = false) {
            uid = uid.trim();
            if (!uid) {
                scannerIdle.classList.remove('hidden');
                scannerDetected.classList.add('hidden');
                scannerBox.className = "mb-4 p-3.5 rounded-xl border border-dashed border-slate-200 bg-slate-50 text-center transition-all duration-200";
                uidFeedback.classList.add('hidden');
                btnClear.classList.add('hidden');
                btnSubmit.disabled = false;
                btnSubmit.classList.remove('opacity-50', 'cursor-not-allowed');
                return;
            }

            btnClear.classList.remove('hidden');

            if (checkingUid) return;
            checkingUid = true;

            try {
                const res = await fetch(`{{ route('admin.rfid.check') }}?uid=${encodeURIComponent(uid)}`);
                const data = await res.json();

                scannerIdle.classList.add('hidden');
                scannerDetected.classList.remove('hidden');
                detectedUid.innerText = uid;

                if (data.exists) {
                    scannerBox.className = "mb-4 p-3.5 rounded-xl border border-rose-200 bg-rose-50 text-center transition-all duration-200";
                    detectedStatusText.innerHTML = `<span class="text-rose-700 font-semibold">${data.message}</span>`;
                    
                    uidFeedback.className = "text-xs mt-1 font-medium text-rose-600 block";
                    uidFeedback.innerText = data.message;
                    btnSubmit.disabled = true;
                    btnSubmit.classList.add('opacity-50', 'cursor-not-allowed');
                } else {
                    scannerBox.className = "mb-4 p-3.5 rounded-xl border border-emerald-200 bg-emerald-50 text-center transition-all duration-200";
                    detectedStatusText.innerHTML = `<span class="text-emerald-700 font-semibold">${data.message}</span>`;
                    
                    uidFeedback.className = "text-xs mt-1 font-medium text-emerald-600 block";
                    uidFeedback.innerText = "Kartu belum terdaftar. Siap disimpan!";
                    btnSubmit.disabled = false;
                    btnSubmit.classList.remove('opacity-50', 'cursor-not-allowed');
                }
            } catch (err) {
                console.error("Error check RFID:", err);
            } finally {
                checkingUid = false;
            }
        }

        uidInput.addEventListener('input', function() {
            onUidChanged(this.value, false);
        });

        btnClear.addEventListener('click', function() {
            uidInput.value = '';
            lastPolledUid = '';
            onUidChanged('', false);
            uidInput.focus();
        });

        // Polling setiap 1 detik untuk mendeteksi tap kartu baru dari reader
        setInterval(pollLatestRfid, 1000);
    });
</script>
@endsection
