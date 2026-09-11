@extends('layouts.admin')
@section('title', 'Edit Member')

@section('content')
<div class="max-w-2xl bg-white border border-slate-200 rounded-2xl p-6 lg:p-8 shadow-sm">
    <h2 class="font-display font-bold text-slate-900 text-xl mb-1">Edit Member — {{ $member->member_code }}</h2>
    <p class="text-xs sm:text-sm text-slate-500 mb-6">Ubah data profil, paket, atau tautan kartu RFID member.</p>

    <form method="POST" action="{{ route('admin.members.update', $member) }}" class="space-y-4">
        @csrf @method('PUT')

        <div class="grid sm:grid-cols-2 gap-4">
            <div>
                <label class="block text-xs font-semibold text-slate-700 mb-1.5">Nama Lengkap</label>
                <input type="text" name="name" value="{{ old('name', $member->user->name) }}" required class="w-full rounded-xl bg-slate-50 border border-slate-300 px-4 py-2.5 text-slate-900 text-sm focus:outline-none focus:bg-white focus:border-lime-600 focus:ring-2 focus:ring-lime-100">
            </div>
            <div>
                <label class="block text-xs font-semibold text-slate-700 mb-1.5">No. WhatsApp</label>
                <input type="text" name="phone" value="{{ old('phone', $member->user->phone) }}" required class="w-full rounded-xl bg-slate-50 border border-slate-300 px-4 py-2.5 text-slate-900 text-sm focus:outline-none focus:bg-white focus:border-lime-600 focus:ring-2 focus:ring-lime-100">
            </div>
        </div>

        <div>
            <label class="block text-xs font-semibold text-slate-700 mb-1.5">Alamat</label>
            <textarea name="address" rows="2" class="w-full rounded-xl bg-slate-50 border border-slate-300 px-4 py-2.5 text-slate-900 text-sm focus:outline-none focus:bg-white focus:border-lime-600 focus:ring-2 focus:ring-lime-100">{{ old('address', $member->address) }}</textarea>
        </div>

        <div class="grid sm:grid-cols-2 gap-4">
            <div>
                <label class="block text-xs font-semibold text-slate-700 mb-1.5">Paket</label>
                <select name="membership_package_id" required class="w-full rounded-xl bg-slate-50 border border-slate-300 px-4 py-2.5 text-slate-900 text-sm focus:outline-none focus:bg-white focus:border-lime-600 focus:ring-2 focus:ring-lime-100">
                    @foreach ($packages as $p)
                        <option value="{{ $p->id }}" @selected(old('membership_package_id', $member->membership_package_id)==$p->id)>
                            {{ $p->name }} — Rp{{ number_format($p->price,0,',','.') }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-xs font-semibold text-slate-700 mb-1.5">Status</label>
                <select name="status" class="w-full rounded-xl bg-slate-50 border border-slate-300 px-4 py-2.5 text-slate-900 text-sm focus:outline-none focus:bg-white focus:border-lime-600 focus:ring-2 focus:ring-lime-100">
                    <option value="active" @selected(old('status', $member->status)=='active')>Aktif</option>
                    <option value="inactive" @selected(old('status', $member->status)=='inactive')>Nonaktif</option>
                    <option value="expired" @selected(old('status', $member->status)=='expired')>Expired</option>
                </select>
            </div>
        </div>

        <div class="grid sm:grid-cols-2 gap-4">
            <div>
                <label class="block text-xs font-semibold text-slate-700 mb-1.5">Tanggal Berakhir</label>
                <input type="date" name="expire_date" value="{{ old('expire_date', optional($member->expire_date)->format('Y-m-d')) }}" class="w-full rounded-xl bg-slate-50 border border-slate-300 px-4 py-2.5 text-slate-900 text-sm focus:outline-none focus:bg-white focus:border-lime-600 focus:ring-2 focus:ring-lime-100">
            </div>
            <div>
                <div class="flex items-center justify-between mb-1.5">
                    <label class="block text-xs font-semibold text-slate-700">Kartu RFID</label>
                    <span class="inline-flex items-center gap-1 text-[10px] font-semibold text-lime-700 bg-lime-50 px-2 py-0.5 rounded-full border border-lime-200">
                        <span class="w-1.5 h-1.5 rounded-full bg-lime-500 animate-pulse"></span>
                        Scan / Pilih
                    </span>
                </div>
                @php
                    $currentUid = old('rfid_uid', $member->rfidCard->uid ?? $member->rfid_uid ?? '');
                @endphp
                <select name="rfid_uid" id="rfid_uid_select" class="w-full rounded-xl bg-slate-50 border border-slate-300 px-4 py-2.5 text-slate-900 font-mono text-sm focus:outline-none focus:bg-white focus:border-lime-600 focus:ring-2 focus:ring-lime-100">
                    <option value="">-- Tanpa Kartu RFID (Kosongkan) --</option>
                    @foreach ($unassignedCards as $c)
                        @php
                            $isCurrent = ($c->member_id == $member->id || $c->uid == $member->rfid_uid || $currentUid == $c->uid);
                        @endphp
                        <option value="{{ $c->uid }}" @selected($currentUid == $c->uid)>
                            {{ $c->uid }} {{ $isCurrent ? '• [Kartu Saat Ini]' : '• [Tersedia / Unassigned]' }}
                        </option>
                    @endforeach
                    @if ($currentUid && !$unassignedCards->contains('uid', $currentUid))
                        <option value="{{ $currentUid }}" selected>{{ $currentUid }} • [Kartu Saat Ini]</option>
                    @endif
                </select>

                {{-- Status Scan Real-Time --}}
                <div id="rfid-scan-indicator" class="hidden mt-1.5 text-xs font-medium"></div>
                @error('rfid_uid')<p class="text-xs text-rose-600 mt-1 font-medium">{{ $message }}</p>@enderror
                <p class="text-[11px] text-slate-500 mt-1">Pilih dari dropdown <strong>atau tempelkan kartu ke reader</strong> untuk scan otomatis.</p>
            </div>
        </div>

        <div class="flex gap-3 pt-3">
            <button type="submit" id="btn-submit-edit" class="flex-1 rounded-xl bg-slate-900 text-white font-display font-bold text-sm py-3 hover:bg-slate-800 transition shadow-sm">
                Simpan Perubahan
            </button>
            <a href="{{ route('admin.members.index') }}" class="rounded-xl border border-slate-300 px-5 py-3 text-slate-700 font-medium text-sm hover:bg-slate-50 transition">
                Batal
            </a>
        </div>
    </form>

    <div class="mt-8 pt-6 border-t border-slate-200 flex items-center justify-between">
        <div>
            <h3 class="font-display font-bold text-slate-900 text-base mb-0.5">Reset Password Login Member</h3>
            <p class="text-xs text-slate-500">Kirim password sementara baru ke WhatsApp member.</p>
        </div>
        <form method="POST" action="{{ route('admin.members.reset-password', $member) }}" onsubmit="return confirm('Kirim password baru via WhatsApp?')">
            @csrf
            <button class="rounded-xl bg-slate-100 border border-slate-300 px-4 py-2 text-xs font-semibold text-slate-700 hover:bg-slate-200 transition">
                Reset Password
            </button>
        </form>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const rfidSelect = document.getElementById('rfid_uid_select');
        const scanIndicator = document.getElementById('rfid-scan-indicator');
        const submitBtn = document.getElementById('btn-submit-edit');
        const memberId = '{{ $member->id }}';

        let lastDetectedUid = '';
        let isChecking = false;

        async function pollLatestRfid() {
            if (document.hidden) return;
            try {
                const response = await fetch("{{ route('admin.members.latest-rfid') }}");
                if (!response.ok) return;
                const data = await response.json();

                if (data.uid && data.uid !== lastDetectedUid) {
                    lastDetectedUid = data.uid;
                    handleScannedUid(data.uid);
                }
            } catch (error) {
                console.error("Gagal polling RFID:", error);
            }
        }

        async function handleScannedUid(uid) {
            if (isChecking) return;
            isChecking = true;

            scanIndicator.classList.remove('hidden');
            scanIndicator.className = "mt-1.5 text-xs font-medium text-blue-600";
            scanIndicator.innerText = `📡 Memeriksa kartu terdeteksi: ${uid}...`;

            try {
                const response = await fetch(`{{ route('admin.members.check-rfid') }}?uid=${encodeURIComponent(uid)}&member_id=${memberId}`);
                const result = await response.json();

                // Cari apakah UID sudah ada di option dropdown
                let optionExists = false;
                for (let i = 0; i < rfidSelect.options.length; i++) {
                    if (rfidSelect.options[i].value === uid) {
                        rfidSelect.selectedIndex = i;
                        optionExists = true;
                        break;
                    }
                }

                // Jika kartu baru yang belum ada di dropdown, tambahkan option baru
                if (!optionExists) {
                    const newOption = document.createElement('option');
                    newOption.value = uid;
                    newOption.text = `${uid} • [Scan Baru / Tersedia]`;
                    newOption.selected = true;
                    rfidSelect.appendChild(newOption);
                }

                if (result.valid === false) {
                    scanIndicator.className = "mt-1.5 text-xs font-medium text-rose-600";
                    scanIndicator.innerText = `⚠️ ${result.message}`;
                    submitBtn.disabled = true;
                    submitBtn.classList.add('opacity-50', 'cursor-not-allowed');
                } else {
                    scanIndicator.className = "mt-1.5 text-xs font-medium text-emerald-600";
                    scanIndicator.innerText = `🟢 Kartu terdeteksi dari scan: ${uid} (Siap digunakan)`;
                    submitBtn.disabled = false;
                    submitBtn.classList.remove('opacity-50', 'cursor-not-allowed');
                }
            } catch (err) {
                console.error("Error check RFID:", err);
            } finally {
                isChecking = false;
            }
        }

        rfidSelect.addEventListener('change', function () {
            const selectedVal = this.value;
            if (!selectedVal) {
                scanIndicator.classList.add('hidden');
                submitBtn.disabled = false;
                submitBtn.classList.remove('opacity-50', 'cursor-not-allowed');
                return;
            }

            // Validasi kartu yang dipilih dari dropdown
            fetch(`{{ route('admin.members.check-rfid') }}?uid=${encodeURIComponent(selectedVal)}&member_id=${memberId}`)
                .then(res => res.json())
                .then(result => {
                    if (result.valid === false) {
                        scanIndicator.classList.remove('hidden');
                        scanIndicator.className = "mt-1.5 text-xs font-medium text-rose-600";
                        scanIndicator.innerText = `⚠️ ${result.message}`;
                        submitBtn.disabled = true;
                        submitBtn.classList.add('opacity-50', 'cursor-not-allowed');
                    } else {
                        scanIndicator.classList.remove('hidden');
                        scanIndicator.className = "mt-1.5 text-xs font-medium text-slate-600";
                        scanIndicator.innerText = `Pilihan aktif: ${selectedVal}`;
                        submitBtn.disabled = false;
                        submitBtn.classList.remove('opacity-50', 'cursor-not-allowed');
                    }
                })
                .catch(err => console.error(err));
        });

        // Polling scan kartu setiap 500ms agar respon instan
        setInterval(pollLatestRfid, 500);
    });
</script>
@endsection
