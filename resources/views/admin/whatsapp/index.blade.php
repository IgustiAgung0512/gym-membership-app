@extends('layouts.admin')
@section('title', 'Log Notifikasi WhatsApp')

@section('content')
{{-- ALERT STATUS TOKEN WHATSAPP --}}
@if (!$hasToken)
    <div class="mb-6 p-4 rounded-2xl bg-amber-50 border border-amber-200 text-amber-900 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-3 shadow-xs">
        <div class="flex items-start gap-3">
            <span class="text-2xl">⚠️</span>
            <div>
                <h4 class="font-bold text-sm text-amber-950">Token Gateway WhatsApp Belum Diatur</h4>
                <p class="text-xs text-amber-800 mt-0.5 leading-relaxed">
                    Pesan otomatis belum bisa terkirim keluar karena <code>WHATSAPP_API_TOKEN</code> belum diisi di Environment Variables (Vercel / .env). Silakan isi token dari akun <a href="https://fonnte.com" target="_blank" class="font-bold underline text-amber-950">Fonnte.com</a> Anda.
                </p>
            </div>
        </div>
        <a href="https://fonnte.com" target="_blank" class="px-3.5 py-1.5 rounded-xl bg-amber-900 hover:bg-amber-800 text-white text-xs font-bold shrink-0 transition">
            Dapatkan Token di Fonnte &rarr;
        </a>
    </div>
@endif

<div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-6">
    <div class="flex items-center gap-2">
        <span class="text-xs font-semibold px-3 py-1.5 rounded-xl bg-white border border-slate-200 text-slate-700 shadow-2xs">
            Total: <strong>{{ $logs->total() }}</strong> log
        </span>
        <span class="text-xs font-semibold px-3 py-1.5 rounded-xl bg-emerald-50 border border-emerald-200 text-emerald-800">
            Terkirim: <strong>{{ $totalSent ?? 0 }}</strong>
        </span>
        @if (($totalFailed ?? 0) > 0)
            <span class="text-xs font-semibold px-3 py-1.5 rounded-xl bg-rose-50 border border-rose-200 text-rose-800">
                Gagal: <strong>{{ $totalFailed }}</strong>
            </span>
        @endif
    </div>

    <div class="flex items-center gap-2">
        @if ($logs->total() > 0)
            {{-- Hapus Semua yang Terkirim --}}
            <form method="POST" action="{{ route('admin.whatsapp.clear-sent') }}" onsubmit="return confirm('Hapus semua log WhatsApp yang berstatus TERKIRIM?')">
                @csrf @method('DELETE')
                <button type="submit" class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-xl bg-white border border-slate-300 text-slate-700 hover:text-rose-600 hover:border-rose-300 hover:bg-rose-50 text-xs font-semibold transition shadow-2xs">
                    <svg class="w-3.5 h-3.5 text-rose-500" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                    <span>Bersihkan Log Terkirim</span>
                </button>
            </form>

            {{-- Hapus Seluruh Log --}}
            <form method="POST" action="{{ route('admin.whatsapp.clear-all') }}" onsubmit="return confirm('PERINGATAN: Hapus SELURUH riwayat log notifikasi WhatsApp?')">
                @csrf @method('DELETE')
                <button type="submit" class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-xl bg-rose-50 border border-rose-200 text-rose-700 hover:bg-rose-100 text-xs font-semibold transition">
                    <span>Hapus Semua</span>
                </button>
            </form>
        @endif
    </div>
</div>

{{-- CARD TEST KIRIM WHATSAPP --}}
<div class="mb-6 p-4 rounded-2xl bg-white border border-slate-200 shadow-sm" x-data="{ openTest: false }">
    <div class="flex items-center justify-between">
        <div class="flex items-center gap-2.5">
            <span class="w-8 h-8 rounded-xl bg-emerald-100 text-emerald-700 flex items-center justify-center text-sm font-bold">📲</span>
            <div>
                <h4 class="font-bold text-slate-900 text-xs sm:text-sm">Uji Coba Kirim Pesan WhatsApp</h4>
                <p class="text-slate-500 text-[11px]">Tes apakah nomor gateway dan token Fonnte aktif & terhubung.</p>
            </div>
        </div>
        <button type="button" @click="openTest = !openTest" class="px-3 py-1.5 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold text-xs transition">
            <span x-text="openTest ? 'Tutup Form' : 'Buka Form Tes'"></span>
        </button>
    </div>

    <form x-show="openTest" x-cloak method="POST" action="{{ route('admin.whatsapp.test-send') }}" class="mt-4 pt-4 border-t border-slate-100 space-y-3">
        @csrf
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
            <div>
                <label class="block text-xs font-bold text-slate-700 mb-1">Nomor WhatsApp Tujuan</label>
                <input type="text" name="phone" placeholder="08xxxxxxxxxx" required class="w-full px-3 py-2 rounded-xl border border-slate-300 text-xs font-mono focus:ring-2 focus:ring-emerald-500">
            </div>
            <div class="sm:col-span-2">
                <label class="block text-xs font-bold text-slate-700 mb-1">Isi Pesan Uji Coba</label>
                <input type="text" name="message" value="Halo! Ini adalah pesan uji coba dari GymPulse Gateway WhatsApp. ✅" required class="w-full px-3 py-2 rounded-xl border border-slate-300 text-xs focus:ring-2 focus:ring-emerald-500">
            </div>
        </div>
        <div class="flex justify-end">
            <button type="submit" class="px-4 py-2 rounded-xl bg-emerald-600 hover:bg-emerald-500 text-white font-bold text-xs shadow-md transition flex items-center gap-1.5">
                <span>⚡ Kirim Pesan Uji Coba</span>
            </button>
        </div>
    </form>
</div>

{{-- MOBILE CARDS VIEW (Responsif di HP) --}}
<div class="grid gap-3 lg:hidden">
    @forelse ($logs as $log)
        <div class="bg-white border border-slate-200 rounded-2xl p-4 shadow-sm">
            <div class="flex items-start justify-between gap-2">
                <div>
                    <p class="text-slate-900 font-semibold text-sm">{{ $log->member->user->name ?? 'Tujuan Bebas' }}</p>
                    <p class="text-xs text-slate-500 font-mono mt-0.5">{{ $log->phone }}</p>
                </div>
                <span class="text-xs px-2.5 py-0.5 rounded-full font-semibold {{ $log->status=='sent' ? 'bg-emerald-100 text-emerald-800' : 'bg-rose-100 text-rose-800' }} shrink-0">
                    {{ ucfirst($log->status) }}
                </span>
            </div>

            <div class="mt-2.5 pt-2 border-t border-slate-100 text-xs space-y-1">
                <p class="text-[11px] font-bold uppercase text-slate-400 mb-1">{{ str($log->type)->headline() }} · {{ $log->created_at->format('d M Y H:i') }} WIB</p>
                <p class="text-slate-700 bg-slate-50 p-2.5 rounded-xl border border-slate-100 leading-relaxed font-sans whitespace-pre-line">{{ $log->message }}</p>
                @if ($log->status === 'failed' && $log->response)
                    <p class="text-[11px] text-rose-700 bg-rose-50 p-2 rounded-lg border border-rose-200 font-mono">
                        <strong>Keterangan Error:</strong> {{ $log->response }}
                    </p>
                @endif
            </div>

            <div class="mt-3 pt-2.5 border-t border-slate-100 flex items-center justify-end">
                <form method="POST" action="{{ route('admin.whatsapp.destroy', $log) }}" onsubmit="return confirm('Hapus log notifikasi ini?')">
                    @csrf @method('DELETE')
                    <button type="submit" class="text-xs font-semibold text-rose-600 hover:underline inline-flex items-center gap-1">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                        <span>Hapus Log</span>
                    </button>
                </form>
            </div>
        </div>
    @empty
        <div class="bg-white border border-slate-200 rounded-2xl p-8 text-center text-slate-500 text-sm shadow-sm">
            Belum ada data log WhatsApp.
        </div>
    @endforelse
</div>

{{-- DESKTOP TABLE VIEW (Layar Laptop / PC) --}}
<div class="hidden lg:block bg-white border border-slate-200 rounded-2xl overflow-hidden shadow-sm">
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead>
                <tr class="text-left bg-slate-50 text-slate-600 border-b border-slate-200">
                    <th class="px-5 py-3.5 font-semibold text-xs uppercase tracking-wider">Waktu</th>
                    <th class="px-5 py-3.5 font-semibold text-xs uppercase tracking-wider">Tujuan</th>
                    <th class="px-5 py-3.5 font-semibold text-xs uppercase tracking-wider">Tipe</th>
                    <th class="px-5 py-3.5 font-semibold text-xs uppercase tracking-wider">Isi Pesan</th>
                    <th class="px-5 py-3.5 font-semibold text-xs uppercase tracking-wider">Status & Diagnostik</th>
                    <th class="px-5 py-3.5 font-semibold text-xs uppercase tracking-wider text-right">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
            @forelse ($logs as $log)
                <tr class="hover:bg-slate-50/80 transition align-top">
                    <td class="px-5 py-3.5 text-slate-500 whitespace-nowrap text-xs font-mono">{{ $log->created_at->format('d M H:i') }}</td>
                    <td class="px-5 py-3.5">
                        <p class="text-slate-900 font-semibold">{{ $log->member->user->name ?? '-' }}</p>
                        <p class="text-xs text-slate-500 font-mono">{{ $log->phone }}</p>
                    </td>
                    <td class="px-5 py-3.5 text-slate-700 font-medium whitespace-nowrap">{{ str($log->type)->headline() }}</td>
                    <td class="px-5 py-3.5 text-slate-600 max-w-sm text-xs leading-relaxed whitespace-pre-line">{{ str($log->message)->limit(90) }}</td>
                    <td class="px-5 py-3.5">
                        <span class="text-xs px-2.5 py-0.5 rounded-full font-semibold {{ $log->status=='sent' ? 'bg-emerald-100 text-emerald-800' : 'bg-rose-100 text-rose-800' }}">
                            {{ ucfirst($log->status) }}
                        </span>
                        @if ($log->status === 'failed' && $log->response)
                            <p class="text-[10px] text-rose-600 font-mono mt-1 max-w-xs truncate" title="{{ $log->response }}">
                                {{ $log->response }}
                            </p>
                        @endif
                    </td>
                    <td class="px-5 py-3.5 text-right whitespace-nowrap">
                        <form method="POST" action="{{ route('admin.whatsapp.destroy', $log) }}" onsubmit="return confirm('Hapus log notifikasi ini?')">
                            @csrf @method('DELETE')
                            <button type="submit" class="text-xs font-semibold text-slate-400 hover:text-rose-600 hover:underline">
                                Hapus
                            </button>
                        </form>
                    </td>
                </tr>
            @empty
                <tr><td colspan="6" class="px-5 py-10 text-center text-slate-500">Belum ada riwayat notifikasi WhatsApp.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
</div>

<div class="mt-5">{{ $logs->links() }}</div>
@endsection
