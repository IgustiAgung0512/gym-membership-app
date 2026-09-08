@extends('layouts.member')
@section('title', 'Ganti Password')

@section('content')
<div class="max-w-md mx-auto">
    @if ($forceChange)
        <div class="mb-5 rounded-xl border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-900 shadow-sm flex items-start gap-2">
            <span class="text-amber-600 font-bold">⚠️</span>
            <span>Ini pertama kalinya Anda masuk. Demi keamanan akun, silakan perbarui password sementara yang diberikan admin sebelum melanjutkan.</span>
        </div>
    @endif

    @if ($errors->any())
        <div class="mb-5 rounded-xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-900 shadow-sm flex items-start gap-2">
            <span class="text-rose-600 font-bold">!</span>
            <span>{{ $errors->first() }}</span>
        </div>
    @endif

    <div class="bg-white border border-slate-200 rounded-2xl p-6 sm:p-7 shadow-sm">
        <h2 class="font-display font-bold text-slate-900 text-xl mb-1">Ganti Password</h2>
        <p class="text-xs sm:text-sm text-slate-500 mb-6">Gunakan password yang kuat dengan kombinasi huruf dan angka.</p>

        <form method="POST" action="{{ route('member.password.update') }}" class="space-y-4">
            @csrf
            @method('PUT')

            @unless ($forceChange)
                <div class="space-y-1">
                    <label class="block text-xs font-semibold text-slate-700">Password Saat Ini</label>
                    <input type="password" name="current_password" required
                           class="w-full rounded-xl bg-slate-50 border border-slate-300 px-4 py-2.5 text-slate-900 text-sm focus:outline-none focus:bg-white focus:border-lime-600 focus:ring-2 focus:ring-lime-100">
                </div>
            @endunless

            <div class="space-y-1">
                <label class="block text-xs font-semibold text-slate-700">Password Baru</label>
                <input type="password" name="password" required minlength="8"
                       class="w-full rounded-xl bg-slate-50 border border-slate-300 px-4 py-2.5 text-slate-900 text-sm focus:outline-none focus:bg-white focus:border-lime-600 focus:ring-2 focus:ring-lime-100">
                <p class="text-[11px] text-slate-400">Minimal 8 karakter.</p>
            </div>

            <div class="space-y-1">
                <label class="block text-xs font-semibold text-slate-700">Konfirmasi Password Baru</label>
                <input type="password" name="password_confirmation" required minlength="8"
                       class="w-full rounded-xl bg-slate-50 border border-slate-300 px-4 py-2.5 text-slate-900 text-sm focus:outline-none focus:bg-white focus:border-lime-600 focus:ring-2 focus:ring-lime-100">
            </div>

            <button type="submit" class="w-full rounded-xl bg-slate-900 text-white font-display font-bold text-sm py-3 hover:bg-slate-800 transition shadow-sm mt-2">
                Simpan Password Baru
            </button>

            @unless ($forceChange)
                <a href="{{ route('member.dashboard') }}" class="block text-center text-xs font-medium text-slate-500 hover:text-slate-800 transition pt-1">
                    ← Batal, kembali ke dashboard
                </a>
            @endunless
        </form>
    </div>
</div>
@endsection
