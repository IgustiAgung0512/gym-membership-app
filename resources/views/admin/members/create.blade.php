@extends('layouts.admin')
@section('title', 'Daftarkan Member')

@section('content')
<div class="max-w-2xl bg-base-card border border-base-line rounded-2xl p-5 lg:p-8">
    <h2 class="font-display font-semibold text-white text-lg mb-1">Daftarkan Member Baru</h2>
    <p class="text-sm text-slate-400 mb-6">Setelah disimpan, notifikasi WhatsApp otomatis dikirim ke nomor member.</p>

    <form method="POST" action="{{ route('admin.members.store') }}" class="space-y-4">
        @csrf
        <div class="grid sm:grid-cols-2 gap-4">
            <div>
                <label class="block text-sm text-slate-400 mb-1.5">Nama Lengkap</label>
                <input type="text" name="name" value="{{ old('name') }}" required class="w-full rounded-xl bg-base border border-base-line px-4 py-2.5 text-white focus:outline-none focus:ring-2 focus:ring-volt/50">
                @error('name')<p class="text-xs text-coral mt-1">{{ $message }}</p>@enderror
            </div>
            <div>
                <label class="block text-sm text-slate-400 mb-1.5">No. WhatsApp</label>
                <input type="text" name="phone" value="{{ old('phone') }}" required placeholder="0812xxxxxxx" class="w-full rounded-xl bg-base border border-base-line px-4 py-2.5 text-white focus:outline-none focus:ring-2 focus:ring-volt/50">
                @error('phone')<p class="text-xs text-coral mt-1">{{ $message }}</p>@enderror
            </div>
        </div>

        <div>
            <label class="block text-sm text-slate-400 mb-1.5">Email (untuk login member)</label>
            <input type="email" name="email" value="{{ old('email') }}" required class="w-full rounded-xl bg-base border border-base-line px-4 py-2.5 text-white focus:outline-none focus:ring-2 focus:ring-volt/50">
            @error('email')<p class="text-xs text-coral mt-1">{{ $message }}</p>@enderror
        </div>

        <div class="grid sm:grid-cols-2 gap-4">
            <div>
                <label class="block text-sm text-slate-400 mb-1.5">Tanggal Lahir</label>
                <input type="date" name="birth_date" value="{{ old('birth_date') }}" class="w-full rounded-xl bg-base border border-base-line px-4 py-2.5 text-white focus:outline-none focus:ring-2 focus:ring-volt/50">
            </div>
            <div>
                <label class="block text-sm text-slate-400 mb-1.5">Jenis Kelamin</label>
                <select name="gender" class="w-full rounded-xl bg-base border border-base-line px-4 py-2.5 text-white focus:outline-none focus:ring-2 focus:ring-volt/50">
                    <option value="">Pilih</option>
                    <option value="L">Laki-laki</option>
                    <option value="P">Perempuan</option>
                </select>
            </div>
        </div>

        <div>
            <label class="block text-sm text-slate-400 mb-1.5">Alamat</label>
            <textarea name="address" rows="2" class="w-full rounded-xl bg-base border border-base-line px-4 py-2.5 text-white focus:outline-none focus:ring-2 focus:ring-volt/50">{{ old('address') }}</textarea>
        </div>

        <div>
            <label class="block text-sm text-slate-400 mb-1.5">Paket Membership</label>
            <select name="membership_package_id" required class="w-full rounded-xl bg-base border border-base-line px-4 py-2.5 text-white focus:outline-none focus:ring-2 focus:ring-volt/50">
                <option value="">Pilih paket</option>
                @foreach ($packages as $p)
                    <option value="{{ $p->id }}" @selected(old('membership_package_id')==$p->id)>{{ $p->name }} — Rp{{ number_format($p->price,0,',','.') }} / {{ $p->duration_months }} bln</option>
                @endforeach
            </select>
            @error('membership_package_id')<p class="text-xs text-coral mt-1">{{ $message }}</p>@enderror
        </div>

        <div>
            <label class="block text-sm text-slate-400 mb-1.5">
                Kartu RFID
            </label>
        
            <div class="flex gap-2">
                <input
                    type="text"
                    name="rfid_uid"
                    id="rfid_uid"
                    placeholder="Silakan tap kartu RFID..."
                    readonly
                    class="flex-1 rounded-xl bg-base border border-base-line px-4 py-2.5 text-white focus:outline-none focus:ring-2 focus:ring-volt/50"
                >
            </div>
        
            <p id="rfid-status" class="text-xs text-slate-500 mt-1">
                Menunggu kartu RFID...
            </p>

            <p id="rfid-warning" class="hidden text-xs text-coral mt-1"></p>
        </div>
        

        <div class="flex gap-3 pt-2">
            <button type="submit" class="flex-1 rounded-xl bg-volt text-base font-semibold py-2.5 hover:brightness-95 transition">Simpan & Kirim Notifikasi WA</button>
            <a href="{{ route('admin.members.index') }}" class="rounded-xl border border-base-line px-5 py-2.5 text-slate-300 hover:bg-white/5 transition">Batal</a>
        </div>
    </form>
</div>
<script>
    document.addEventListener('DOMContentLoaded', function () {
    
        const rfidInput = document.getElementById('rfid_uid');
        const rfidStatus = document.getElementById('rfid-status');
        const rfidWarning = document.getElementById('rfid-warning');
        const submitButton = document.querySelector('button[type="submit"]');
    
        let currentUid = '';
        let checkingUid = false;
    
        function resetRfidStatus() {
    
            rfidWarning.classList.add('hidden');
            rfidWarning.textContent = '';
    
            rfidStatus.textContent = 'Menunggu kartu RFID...';
    
            rfidStatus.classList.remove('text-volt');
            rfidStatus.classList.remove('text-coral');
            rfidStatus.classList.add('text-slate-500');
    
            submitButton.disabled = false;
            submitButton.classList.remove('opacity-50', 'cursor-not-allowed');
        }
    
        function setButtonDisabled(disabled) {
    
            submitButton.disabled = disabled;
    
            if (disabled) {
                submitButton.classList.add('opacity-50', 'cursor-not-allowed');
            } else {
                submitButton.classList.remove('opacity-50', 'cursor-not-allowed');
            }
        }
    
        async function checkRfid(uid) {
    
            if (!uid) {
                resetRfidStatus();
                return;
            }
    
            checkingUid = true;
    
            try {
    
                const response = await fetch(
                    '{{ route('admin.members.check-rfid') }}?uid=' +
                    encodeURIComponent(uid)
                );
    
                const data = await response.json();
    
                if (data.exists) {
    
                    // ==============================
                    // RFID SUDAH DIGUNAKAN
                    // ==============================
    
                    rfidStatus.textContent = 'Kartu terdeteksi: ' + uid;
    
                    rfidStatus.classList.remove('text-slate-500');
                    rfidStatus.classList.remove('text-volt');
                    rfidStatus.classList.add('text-coral');
    
                    rfidWarning.textContent = '⚠ ' + data.message;
                    rfidWarning.classList.remove('hidden');
    
                    setButtonDisabled(true);
    
                } else {
    
                    // ==============================
                    // RFID BELUM DIGUNAKAN
                    // ==============================
    
                    rfidStatus.textContent = '✓ Kartu terdeteksi: ' + uid;
    
                    rfidStatus.classList.remove('text-slate-500');
                    rfidStatus.classList.remove('text-coral');
                    rfidStatus.classList.add('text-volt');
    
                    rfidWarning.textContent = '✓ Kartu RFID tersedia';
                    rfidWarning.classList.remove('hidden');
    
                    rfidWarning.classList.remove('text-coral');
                    rfidWarning.classList.add('text-volt');
    
                    setButtonDisabled(false);
                }
    
            } catch (error) {
    
                console.error('RFID Check Error:', error);
    
                rfidWarning.textContent =
                    'Gagal mengecek kartu RFID.';
    
                rfidWarning.classList.remove('hidden');
    
                setButtonDisabled(true);
    
            } finally {
    
                checkingUid = false;
            }
        }
    
        function checkLatestRfid() {
    
            fetch('{{ route('admin.members.latest-rfid') }}')
                .then(response => response.json())
                .then(data => {
    
                    if (data.uid && data.uid !== currentUid) {
    
                        currentUid = data.uid;
    
                        rfidInput.value = data.uid;
    
                        // langsung cek ke database
                        checkRfid(data.uid);
                    }
    
                })
                .catch(error => {
    
                    console.error('RFID Error:', error);
    
                });
        }
    
        // Cek RFID setiap 1 detik
        setInterval(checkLatestRfid, 1000);
    
        // Cek langsung ketika halaman dibuka
        checkLatestRfid();
    
    });
    </script>
@endsection
