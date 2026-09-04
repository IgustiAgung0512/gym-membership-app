@extends('layouts.admin')
@section('title', 'Daftarkan Member')

@section('content')
<div class="max-w-2xl bg-white border border-slate-200 rounded-2xl p-6 lg:p-8 shadow-sm">
    <h2 class="font-display font-bold text-slate-900 text-xl mb-1">Daftarkan Member Baru</h2>
    <p class="text-xs sm:text-sm text-slate-500 mb-6">Setelah disimpan, notifikasi WhatsApp otomatis dikirim ke nomor member.</p>

    <form method="POST" action="{{ route('admin.members.store') }}" class="space-y-4">
        @csrf
        <div class="grid sm:grid-cols-2 gap-4">
            <div>
                <label class="block text-xs font-semibold text-slate-700 mb-1.5">Nama Lengkap</label>
                <input type="text" name="name" value="{{ old('name') }}" required class="w-full rounded-xl bg-slate-50 border border-slate-300 px-4 py-2.5 text-slate-900 text-sm focus:outline-none focus:bg-white focus:border-lime-600 focus:ring-2 focus:ring-lime-100">
                @error('name')<p class="text-xs text-rose-600 mt-1">{{ $message }}</p>@enderror
            </div>
            <div>
                <label class="block text-xs font-semibold text-slate-700 mb-1.5">No. WhatsApp</label>
                <input type="text" name="phone" value="{{ old('phone') }}" required placeholder="0812xxxxxxx" class="w-full rounded-xl bg-slate-50 border border-slate-300 px-4 py-2.5 text-slate-900 text-sm focus:outline-none focus:bg-white focus:border-lime-600 focus:ring-2 focus:ring-lime-100">
                @error('phone')<p class="text-xs text-rose-600 mt-1">{{ $message }}</p>@enderror
            </div>
        </div>

        <div>
            <label class="block text-xs font-semibold text-slate-700 mb-1.5">Email (untuk login member)</label>
            <input type="email" name="email" value="{{ old('email') }}" required class="w-full rounded-xl bg-slate-50 border border-slate-300 px-4 py-2.5 text-slate-900 text-sm focus:outline-none focus:bg-white focus:border-lime-600 focus:ring-2 focus:ring-lime-100">
            @error('email')<p class="text-xs text-rose-600 mt-1">{{ $message }}</p>@enderror
        </div>

        <div class="grid sm:grid-cols-2 gap-4">
            <div>
                <label class="block text-xs font-semibold text-slate-700 mb-1.5">Tanggal Lahir</label>
                <input type="date" name="birth_date" value="{{ old('birth_date') }}" class="w-full rounded-xl bg-slate-50 border border-slate-300 px-4 py-2.5 text-slate-900 text-sm focus:outline-none focus:bg-white focus:border-lime-600 focus:ring-2 focus:ring-lime-100">
            </div>
            <div>
                <label class="block text-xs font-semibold text-slate-700 mb-1.5">Jenis Kelamin</label>
                <select name="gender" class="w-full rounded-xl bg-slate-50 border border-slate-300 px-4 py-2.5 text-slate-900 text-sm focus:outline-none focus:bg-white focus:border-lime-600 focus:ring-2 focus:ring-lime-100">
                    <option value="">Pilih</option>
                    <option value="L">Laki-laki</option>
                    <option value="P">Perempuan</option>
                </select>
            </div>
        </div>

        <div>
            <label class="block text-xs font-semibold text-slate-700 mb-1.5">Alamat</label>
            <textarea name="address" rows="2" class="w-full rounded-xl bg-slate-50 border border-slate-300 px-4 py-2.5 text-slate-900 text-sm focus:outline-none focus:bg-white focus:border-lime-600 focus:ring-2 focus:ring-lime-100">{{ old('address') }}</textarea>
        </div>

        <div>
            <label class="block text-xs font-semibold text-slate-700 mb-1.5">Paket Membership</label>
            <select name="membership_package_id" required class="w-full rounded-xl bg-slate-50 border border-slate-300 px-4 py-2.5 text-slate-900 text-sm focus:outline-none focus:bg-white focus:border-lime-600 focus:ring-2 focus:ring-lime-100">
                <option value="">Pilih paket</option>
                @foreach ($packages as $p)
                    <option value="{{ $p->id }}" @selected(old('membership_package_id')==$p->id)>{{ $p->name }} — Rp{{ number_format($p->price,0,',','.') }} / {{ $p->duration_months }} bln</option>
                @endforeach
            </select>
            @error('membership_package_id')<p class="text-xs text-rose-600 mt-1">{{ $message }}</p>@enderror
        </div>

        <div>
            <label class="block text-xs font-semibold text-slate-700 mb-1.5">
                Kartu RFID
            </label>
        
            <div class="flex gap-2">
                <input
                    type="text"
                    name="rfid_uid"
                    id="rfid_uid"
                    placeholder="Silakan tap kartu RFID..."
                    readonly
                    class="flex-1 rounded-xl bg-slate-100 border border-slate-300 px-4 py-2.5 text-slate-900 font-mono text-sm focus:outline-none focus:ring-2 focus:ring-lime-500"
                >
            </div>
        
            <p id="rfid-status" class="text-xs text-slate-500 mt-1">
                Menunggu kartu RFID...
            </p>

            <p id="rfid-warning" class="hidden text-xs text-rose-600 mt-1 font-medium"></p>
        </div>
        
        <div class="flex gap-3 pt-3">
            <button type="submit" class="flex-1 rounded-xl bg-slate-900 text-white font-display font-bold text-sm py-3 hover:bg-slate-800 transition shadow-sm">
                Simpan & Kirim Notifikasi WA
            </button>
            <a href="{{ route('admin.members.index') }}" class="rounded-xl border border-slate-300 px-5 py-3 text-slate-700 font-medium text-sm hover:bg-slate-50 transition">
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
        const submitButton = document.querySelector('button[type="submit"]');
    
        let currentUid = '';
        let checkingUid = false;
    
        async function fetchLatestRfid() {
            try {
                const response = await fetch("{{ route('admin.members.latest-rfid') }}");
                if (!response.ok) return;
                const data = await response.json();
    
                if (data.uid && data.uid !== currentUid) {
                    currentUid = data.uid;
                    rfidInput.value = currentUid;
                    validateRfid(currentUid);
                }
            } catch (error) {
                console.error("Gagal polling RFID:", error);
            }
        }
    
        async function validateRfid(uid) {
            if (checkingUid) return;
            checkingUid = true;
    
            rfidStatus.innerText = "Memeriksa ketersediaan kartu RFID...";
            rfidStatus.className = "text-xs text-blue-600 mt-1";
            rfidWarning.classList.add('hidden');
            rfidWarning.innerText = "";
    
            try {
                const response = await fetch(`{{ route('admin.members.check-rfid') }}?uid=${encodeURIComponent(uid)}`);
                const data = await response.json();
    
                if (data.exists) {
                    rfidStatus.innerText = "Kartu RFID tidak dapat digunakan.";
                    rfidStatus.className = "text-xs text-rose-600 mt-1 font-bold";
                    rfidWarning.innerText = data.message;
                    rfidWarning.classList.remove('hidden');
                    submitButton.disabled = true;
                    submitButton.classList.add('opacity-50', 'cursor-not-allowed');
                } else {
                    rfidStatus.innerText = data.message;
                    rfidStatus.className = "text-xs text-emerald-600 mt-1 font-bold";
                    rfidWarning.classList.add('hidden');
                    submitButton.disabled = false;
                    submitButton.classList.remove('opacity-50', 'cursor-not-allowed');
                }
            } catch (error) {
                console.error("Error checking RFID:", error);
                rfidStatus.innerText = "Gagal memeriksa status kartu RFID.";
                rfidStatus.className = "text-xs text-rose-600 mt-1";
            } finally {
                checkingUid = false;
            }
        }
    
        setInterval(fetchLatestRfid, 1000);
    });
</script>
@endsection
