@extends('layouts.member')
@section('title', 'Ganti Password')

@section('content')
<div class="max-w-md mx-auto">
    @if ($forceChange)
        <div class="mb-4 rounded-xl border border-coral/30 bg-coral/10 px-4 py-3 text-sm text-coral">
            Ini pertama kalinya kamu masuk. Demi keamanan, silakan ganti password sementara yang diberikan admin sebelum melanjutkan.
        </div>
    @endif

    @if ($errors->any())
        <div class="mb-4 rounded-xl border border-coral/30 bg-coral/10 px-4 py-3 text-sm text-coral">
            {{ $errors->first() }}
        </div>
    @endif

    <div class="bg-base-card border border-base-line rounded-2xl p-6">
        <h2 class="font-display font-semibold text-white text-lg mb-1">Ganti Password</h2>
        <p class="text-sm text-slate-400 mb-6">Gunakan password yang mudah kamu ingat tapi sulit ditebak orang lain.</p>

        <form method="POST" action="{{ route('member.password.update') }}" class="space-y-4">
            @csrf
            @method('PUT')

            @unless ($forceChange)
                <div>
                    <label class="block text-sm text-slate-400 mb-1.5">Password Saat Ini</label>
                    <input type="password" name="current_password" required
                           class="w-full rounded-xl bg-base border border-base-line px-4 py-2.5 text-white focus:outline-none focus:ring-2 focus:ring-volt/50">
                </div>
            @endunless

            <div>
                <label class="block text-sm text-slate-400 mb-1.5">Password Baru</label>
                <input type="password" name="password" required minlength="6"
                       class="w-full rounded-xl bg-base border border-base-line px-4 py-2.5 text-white focus:outline-none focus:ring-2 focus:ring-volt/50">
                <p class="text-xs text-slate-500 mt-1">Minimal 6 karakter.</p>
            </div>

            <div>
                <label class="block text-sm text-slate-400 mb-1.5">Konfirmasi Password Baru</label>
                <input type="password" name="password_confirmation" required minlength="6"
                       class="w-full rounded-xl bg-base border border-base-line px-4 py-2.5 text-white focus:outline-none focus:ring-2 focus:ring-volt/50">
            </div>

            <button type="submit" class="w-full rounded-xl bg-volt text-base font-semibold py-2.5 hover:brightness-95 transition">
                Simpan Password Baru
            </button>

            @unless ($forceChange)
                <a href="{{ route('member.dashboard') }}" class="block text-center text-sm text-slate-400 hover:text-slate-200 transition">Batal, kembali ke dashboard</a>
            @endunless
        </form>
    </div>
</div>
@endsection
