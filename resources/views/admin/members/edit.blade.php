@extends('layouts.admin')
@section('title', 'Edit Member')

@section('content')
<div class="max-w-2xl bg-base-card border border-base-line rounded-2xl p-5 lg:p-8">
    <h2 class="font-display font-semibold text-white text-lg mb-1">Edit Member — {{ $member->member_code }}</h2>
    <p class="text-sm text-slate-400 mb-6">Ubah data, status, atau tautkan ulang kartu RFID.</p>

    <form method="POST" action="{{ route('admin.members.update', $member) }}" class="space-y-4">
        @csrf @method('PUT')
        <div class="grid sm:grid-cols-2 gap-4">
            <div>
                <label class="block text-sm text-slate-400 mb-1.5">Nama Lengkap</label>
                <input type="text" name="name" value="{{ old('name', $member->user->name) }}" required class="w-full rounded-xl bg-base border border-base-line px-4 py-2.5 text-white focus:outline-none focus:ring-2 focus:ring-volt/50">
            </div>
            <div>
                <label class="block text-sm text-slate-400 mb-1.5">No. WhatsApp</label>
                <input type="text" name="phone" value="{{ old('phone', $member->user->phone) }}" required class="w-full rounded-xl bg-base border border-base-line px-4 py-2.5 text-white focus:outline-none focus:ring-2 focus:ring-volt/50">
            </div>
        </div>

        <div>
            <label class="block text-sm text-slate-400 mb-1.5">Alamat</label>
            <textarea name="address" rows="2" class="w-full rounded-xl bg-base border border-base-line px-4 py-2.5 text-white focus:outline-none focus:ring-2 focus:ring-volt/50">{{ old('address', $member->address) }}</textarea>
        </div>

        <div class="grid sm:grid-cols-2 gap-4">
            <div>
                <label class="block text-sm text-slate-400 mb-1.5">Paket Membership</label>
                <select name="membership_package_id" required class="w-full rounded-xl bg-base border border-base-line px-4 py-2.5 text-white focus:outline-none focus:ring-2 focus:ring-volt/50">
                    @foreach ($packages as $p)
                        <option value="{{ $p->id }}" @selected($member->membership_package_id==$p->id)>{{ $p->name }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-sm text-slate-400 mb-1.5">Status</label>
                <select name="status" class="w-full rounded-xl bg-base border border-base-line px-4 py-2.5 text-white focus:outline-none focus:ring-2 focus:ring-volt/50">
                    <option value="active" @selected($member->status=='active')>Aktif</option>
                    <option value="inactive" @selected($member->status=='inactive')>Nonaktif</option>
                    <option value="expired" @selected($member->status=='expired')>Expired</option>
                </select>
            </div>
        </div>

        <div class="grid sm:grid-cols-2 gap-4">
            <div>
                <label class="block text-sm text-slate-400 mb-1.5">Tanggal Berakhir</label>
                <input type="date" name="expire_date" value="{{ old('expire_date', optional($member->expire_date)->format('Y-m-d')) }}" class="w-full rounded-xl bg-base border border-base-line px-4 py-2.5 text-white focus:outline-none focus:ring-2 focus:ring-volt/50">
            </div>
            <div>
                <label class="block text-sm text-slate-400 mb-1.5">UID Kartu RFID</label>
                <input type="text" name="rfid_uid" value="{{ old('rfid_uid', $member->rfidCard->uid ?? '') }}" list="cardlist" class="w-full rounded-xl bg-base border border-base-line px-4 py-2.5 text-white focus:outline-none focus:ring-2 focus:ring-volt/50">
                <datalist id="cardlist">@foreach ($unassignedCards as $c)<option value="{{ $c->uid }}">@endforeach</datalist>
            </div>
        </div>

        <div class="flex gap-3 pt-2">
            <button type="submit" class="flex-1 rounded-xl bg-volt text-base font-semibold py-2.5 hover:brightness-95 transition">Simpan Perubahan</button>
            <a href="{{ route('admin.members.index') }}" class="rounded-xl border border-base-line px-5 py-2.5 text-slate-300 hover:bg-white/5 transition">Batal</a>
        </div>
    </form>
</div>

<div class="max-w-2xl bg-base-card border border-base-line rounded-2xl p-5 lg:p-8 mt-6">
    <h3 class="font-display font-semibold text-white mb-1">Reset Password Login Member</h3>
    <p class="text-sm text-slate-400 mb-4">
        Gunakan ini kalau member lupa password. Sistem akan membuat password sementara baru,
        lalu member akan diminta menggantinya sendiri saat login berikutnya.
    </p>
    <form method="POST" action="{{ route('admin.members.reset-password', $member) }}"
          onsubmit="return confirm('Reset password login untuk {{ $member->user->name }}? Password lama akan langsung tidak berlaku.')">
        @csrf
        <button type="submit" class="rounded-xl border border-coral/40 text-coral px-5 py-2.5 text-sm font-medium hover:bg-coral/10 transition">
            Reset Password Member
        </button>
    </form>
</div>
@endsection
