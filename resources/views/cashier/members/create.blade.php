@extends('layouts.cashier')
@section('title', 'Daftar Member Baru - Front Desk')

@section('content')
<div class="max-w-2xl mx-auto bg-white border border-slate-200 rounded-3xl p-6 sm:p-8 shadow-sm">
    <div class="flex items-center justify-between pb-4 mb-6 border-b border-slate-100">
        <div>
            <span class="text-[10px] font-bold uppercase tracking-widest text-lime-600 bg-lime-100 px-2.5 py-0.5 rounded-md">
                RESEPSIONIS / FRONT DESK
            </span>
            <h2 class="font-display font-extrabold text-slate-900 text-2xl mt-1">Daftarkan Member Baru</h2>
            <p class="text-xs text-slate-500 mt-0.5">Pendaftaran member baru dengan sistem scan kartu RFID otomatis.</p>
        </div>
        <a href="{{ route('cashier.members.index') }}" class="text-xs font-bold text-slate-500 hover:text-slate-800 transition">
            &larr; Kembali
        </a>
    </div>

    <form method="POST" action="{{ route('cashier.members.store') }}" class="space-y-4">
        @csrf
        <div class="grid sm:grid-cols-2 gap-4">
            <div>
                <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">Nama Lengkap Member</label>
                <input type="text" name="name" value="{{ old('name') }}" required placeholder="Contoh: I Gusti Agung" class="w-full rounded-xl bg-slate-50 border border-slate-300 px-3.5 py-2.5 text-slate-900 text-xs focus:outline-none focus:bg-white focus:ring-2 focus:ring-lime-500 font-medium">
                @error('name')<p class="text-xs text-rose-600 mt-1">{{ $message }}</p>@enderror
            </div>
            <div>
                <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">No. WhatsApp</label>
                <input type="text" name="phone" value="{{ old('phone') }}" required placeholder="0812xxxxxxx" class="w-full rounded-xl bg-slate-50 border border-slate-300 px-3.5 py-2.5 text-slate-900 text-xs focus:outline-none focus:bg-white focus:ring-2 focus:ring-lime-500 font-medium">
                @error('phone')<p class="text-xs text-rose-600 mt-1">{{ $message }}</p>@enderror
            </div>
        </div>

        <div>
            <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">Email (Untuk Akun Login Member)</label>
            <input type="email" name="email" value="{{ old('email') }}" required placeholder="member@email.com" class="w-full rounded-xl bg-slate-50 border border-slate-300 px-3.5 py-2.5 text-slate-900 text-xs focus:outline-none focus:bg-white focus:ring-2 focus:ring-lime-500 font-medium">
            @error('email')<p class="text-xs text-rose-600 mt-1">{{ $message }}</p>@enderror
        </div>

        <div class="grid sm:grid-cols-2 gap-4">
            <div>
                <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">Pilihan Paket Membership</label>
                <select name="membership_package_id" required class="w-full rounded-xl bg-slate-50 border border-slate-300 px-3 py-2.5 text-slate-900 text-xs focus:outline-none focus:ring-2 focus:ring-lime-500 font-medium">
                    <option value="">-- Pilih Paket --</option>
                    @foreach ($packages as $p)
                        <option value="{{ $p->id }}" @selected(old('membership_package_id')==$p->id)>
                            {{ $p->name }} (Rp{{ number_format($p->price,0,',','.') }} / {{ $p->duration_months }} bln)
                        </option>
                    @endforeach
                </select>
                @error('membership_package_id')<p class="text-xs text-rose-600 mt-1">{{ $message }}</p>@enderror
            </div>

            <div>
                <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">Metode Pembayaran</label>
                <select name="payment_method" required class="w-full rounded-xl bg-slate-50 border border-slate-300 px-3 py-2.5 text-slate-900 text-xs focus:outline-none focus:ring-2 focus:ring-lime-500 font-medium">
                    <option value="cash">💵 Tunai (Cash)</option>
                    <option value="qris">📱 QRIS</option>
                    <option value="transfer">🏦 Transfer Bank</option>
                </select>
            </div>
        </div>

        {{-- RFID SECTION (SCAN OTOMATIS / PILIH KARTU) --}}
        <div x-data="{ mode: 'scan' }" class="p-4 rounded-2xl bg-slate-50 border border-slate-200/90 space-y-3">
            <div class="flex items-center justify-between">
                <div>
                    <label class="block text-xs font-bold text-slate-800 uppercase tracking-wider">
                        Hubungkan Kartu RFID Fisik
                    </label>
                    <p class="text-[11px] text-slate-500">Tempelkan kartu RFID pada scanner reader gate / kasir.</p>
                </div>

                {{-- Toggle Mode Scan / Pilih Manual --}}
                <div class="flex items-center bg-slate-200 p-0.5 rounded-xl text-[11px] font-bold">
                    <button 
                        type="button" 
                        @click="mode = 'scan'" 
                        :class="mode === 'scan' ? 'bg-white text-slate-900 shadow-sm' : 'text-slate-600 hover:text-slate-900'"
                        class="px-2.5 py-1 rounded-lg transition"
                    >
                        📡 Scan Otomatis
                    </button>
                    <button 
                        type="button" 
                        @click="mode = 'select'" 
                        :class="mode === 'select' ? 'bg-white text-slate-900 shadow-sm' : 'text-slate-600 hover:text-slate-900'"
                        class="px-2.5 py-1 rounded-lg transition"
                    >
                        📋 Pilih Kartu
                    </button>
                </div>
            </div>

            {{-- SCAN MODE --}}
            <div x-show="mode === 'scan'" class="space-y-2">
                <div class="relative flex items-center">
                    <input 
                        type="text" 
                        name="rfid_uid" 
                        id="rfid_uid" 
                        placeholder="Silakan tap kartu RFID pada alat reader..." 
                        readonly 
                        class="w-full rounded-xl bg-white border border-slate-300 pl-10 pr-4 py-2.5 text-slate-900 font-mono font-bold text-sm focus:outline-none focus:ring-2 focus:ring-lime-500"
                    >
                    <span class="absolute left-3 text-base">🪪</span>
                </div>

                <div class="flex items-center justify-between text-xs">
                    <p id="rfid-status" class="text-slate-500 font-medium flex items-center gap-1.5">
                        <span class="w-2 h-2 rounded-full bg-amber-400 animate-pulse"></span>
                        <span>Menunggu tap kartu RFID pada alat reader...</span>
                    </p>
                    <p id="rfid-warning" class="hidden text-rose-600 font-bold"></p>
                </div>
            </div>

            {{-- SELECT MODE (DROPDOWN) --}}
            <div x-show="mode === 'select'" class="space-y-2">
                <select name="rfid_card_id" class="w-full rounded-xl bg-white border border-slate-300 px-3 py-2 text-slate-900 text-xs focus:outline-none focus:ring-2 focus:ring-lime-500">
                    <option value="">-- Tanpa Kartu / Hubungkan Nanti --</option>
                    @foreach ($unassignedCards as $c)
                        <option value="{{ $c->id }}">{{ $c->uid }} (Kartu Tersedia)</option>
                    @endforeach
                </select>
                <p class="text-[11px] text-slate-400">Pilih dari stok kartu RFID yang telah didaftarkan sebelumnya.</p>
            </div>
        </div>

        <div class="flex gap-3 pt-4 border-t border-slate-100">
            <button type="submit" id="submit-btn" class="flex-1 rounded-xl bg-lime-500 hover:bg-lime-400 text-slate-950 font-display font-bold text-sm py-3 transition shadow-sm flex items-center justify-center gap-2">
                <span>Simpan & Daftarkan Member</span>
                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
            </button>
            <a href="{{ route('cashier.members.index') }}" class="rounded-xl border border-slate-200 px-5 py-3 text-slate-600 font-bold text-xs hover:bg-slate-50 transition flex items-center justify-center">
                Batal
            </a>
        </div>
    </form>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const rfidInput = document.getElementById('rfid_uid');
        const rfidStatus = document.getElementById('rfid-status');
        const rfidWarning = document.getElementById('rfid-warning');
        const submitBtn = document.getElementById('submit-btn');

        let currentUid = '';
        let checkingUid = false;

        async function fetchLatestRfid() {
            try {
                const response = await fetch("{{ route('admin.members.latest-rfid') }}");
                if (!response.ok) return;
                const data = await response.json();

                if (data.uid && data.uid !== currentUid) {
                    currentUid = data.uid;
                    if (rfidInput) {
                        rfidInput.value = currentUid;
                        validateRfid(currentUid);
                    }
                }
            } catch (error) {
                console.error("Gagal polling RFID:", error);
            }
        }

        async function validateRfid(uid) {
            if (checkingUid) return;
            checkingUid = true;

            rfidStatus.innerHTML = '<span class="w-2 h-2 rounded-full bg-blue-500 animate-pulse"></span> <span class="text-blue-600 font-bold">Memeriksa ketersediaan kartu RFID...</span>';
            rfidWarning.classList.add('hidden');
            rfidWarning.innerText = "";

            try {
                const response = await fetch(`{{ route('admin.members.check-rfid') }}?uid=${encodeURIComponent(uid)}`);
                const data = await response.json();

                if (data.exists) {
                    rfidStatus.innerHTML = '<span class="w-2 h-2 rounded-full bg-rose-500"></span> <span class="text-rose-600 font-bold">Kartu RFID sudah terpakai!</span>';
                    rfidWarning.innerText = data.message;
                    rfidWarning.classList.remove('hidden');
                    if (submitBtn) submitBtn.disabled = true;
                } else {
                    rfidStatus.innerHTML = `<span class="w-2 h-2 rounded-full bg-emerald-500"></span> <span class="text-emerald-700 font-bold">${data.message}</span>`;
                    rfidWarning.classList.add('hidden');
                    if (submitBtn) submitBtn.disabled = false;
                }
            } catch (error) {
                console.error("Error checking RFID:", error);
                rfidStatus.innerHTML = '<span class="text-rose-600">Gagal memeriksa status kartu.</span>';
            } finally {
                checkingUid = false;
            }
        }

        // Polling setiap 500ms agar deteksi tap kartu instan
        setInterval(fetchLatestRfid, 500);
    });
</script>
@endsection
