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
                <label class="block text-xs font-semibold text-slate-700 mb-1.5">UID Kartu RFID</label>
                <input type="text" name="rfid_uid" value="{{ old('rfid_uid', $member->rfidCard->uid ?? '') }}" list="cardlist" class="w-full rounded-xl bg-slate-50 border border-slate-300 px-4 py-2.5 text-slate-900 font-mono text-sm focus:outline-none focus:bg-white focus:border-lime-600 focus:ring-2 focus:ring-lime-100">
                <datalist id="cardlist">
                    @foreach ($unassignedCards as $c)
                        <option value="{{ $c->uid }}">{{ $c->uid }}</option>
                    @endforeach
                </datalist>
            </div>
        </div>

        <div class="flex gap-3 pt-3">
            <button type="submit" class="flex-1 rounded-xl bg-slate-900 text-white font-display font-bold text-sm py-3 hover:bg-slate-800 transition shadow-sm">
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
@endsection
