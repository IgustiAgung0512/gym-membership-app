@extends('layouts.admin')
@section('title', 'Absensi / Check-in')

@section('content')
<div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 mb-6">
    <form method="GET" class="flex flex-wrap items-center gap-3">
        <input type="date" name="date" value="{{ request('date', today()->format('Y-m-d')) }}" onchange="this.form.submit()"
               class="rounded-xl bg-white border border-slate-300 px-4 py-2.5 text-sm text-slate-900 focus:outline-none focus:ring-2 focus:ring-lime-500 shadow-sm">
        <span class="text-xs font-semibold text-slate-600 bg-white border border-slate-200 px-3.5 py-2.5 rounded-xl shadow-2xs">
            Total: <strong>{{ $attendances->total() }}</strong> check-in
        </span>
    </form>
</div>

{{-- MOBILE CARDS VIEW (Responsif di HP) --}}
<div class="grid gap-3 lg:hidden">
    @forelse ($attendances as $a)
        <div class="bg-white border border-slate-200 rounded-2xl p-4 shadow-sm">
            <div class="flex items-start justify-between gap-2">
                <div class="flex items-center gap-3 min-w-0">
                    <div class="w-10 h-10 rounded-xl bg-lime-100 text-lime-800 flex items-center justify-center font-bold text-sm shrink-0">
                        {{ substr($a->member->user->name, 0, 1) }}
                    </div>
                    <div class="min-w-0">
                        <p class="text-slate-900 font-semibold text-sm truncate">{{ $a->member->user->name }}</p>
                        <p class="text-xs text-slate-500 font-mono">{{ $a->member->member_code }}</p>
                    </div>
                </div>
                <span class="text-xs font-mono font-bold px-2.5 py-1 rounded-lg bg-slate-100 text-slate-800 shrink-0">
                    {{ $a->check_in_at->format('H:i') }} WIB
                </span>
            </div>

            <div class="mt-3 pt-2.5 border-t border-slate-100 flex items-center justify-between text-xs">
                <div>
                    <span class="text-slate-500">Metode: </span>
                    <span class="font-semibold {{ $a->method=='rfid' ? 'text-lime-700' : 'text-slate-700' }}">
                        {{ $a->method == 'rfid' ? '⚡ RFID Tap' : '✍️ Manual' }}
                    </span>
                </div>
                <div>
                    <span class="text-slate-500">UID: </span>
                    <span class="font-mono text-slate-700 font-medium">{{ $a->rfidCard->uid ?? '—' }}</span>
                </div>
            </div>
        </div>
    @empty
        <div class="bg-white border border-slate-200 rounded-2xl p-8 text-center text-slate-500 text-sm shadow-sm">
            Belum ada data check-in pada tanggal ini.
        </div>
    @endforelse
</div>

{{-- DESKTOP TABLE VIEW (Layar Laptop / PC) --}}
<div class="hidden lg:block bg-white border border-slate-200 rounded-2xl overflow-hidden shadow-sm">
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead>
                <tr class="text-left bg-slate-50 text-slate-600 border-b border-slate-200">
                    <th class="px-5 py-3.5 font-semibold text-xs uppercase tracking-wider">Member</th>
                    <th class="px-5 py-3.5 font-semibold text-xs uppercase tracking-wider">Kartu RFID</th>
                    <th class="px-5 py-3.5 font-semibold text-xs uppercase tracking-wider">Jam Masuk</th>
                    <th class="px-5 py-3.5 font-semibold text-xs uppercase tracking-wider">Jam Keluar</th>
                    <th class="px-5 py-3.5 font-semibold text-xs uppercase tracking-wider">Durasi Sesi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
            @forelse ($attendances as $a)
                <tr class="hover:bg-slate-50/80 transition">
                    <td class="px-5 py-3.5">
                        <p class="text-slate-900 font-semibold">{{ $a->member->user->name }}</p>
                        <p class="text-xs text-slate-500 font-mono">{{ $a->member->member_code }}</p>
                    </td>
                    <td class="px-5 py-3.5">
                        <span class="text-xs px-2.5 py-0.5 rounded-full font-semibold {{ $a->method=='rfid' ? 'bg-lime-100 text-lime-800' : 'bg-slate-100 text-slate-600' }}">{{ $a->method == 'rfid' ? '⚡ ' . ($a->rfidCard->uid ?? 'RFID') : '✍️ Manual' }}</span>
                    </td>
                    <td class="px-5 py-3.5 text-slate-800 font-mono font-bold">{{ $a->check_in_at->format('H:i:s') }} WIB</td>
                    <td class="px-5 py-3.5 text-slate-600 font-mono">
                        @if ($a->check_out_at)
                            {{ $a->check_out_at->format('H:i:s') }} WIB
                        @else
                            <span class="px-2 py-0.5 rounded-full bg-emerald-100 text-emerald-800 font-bold text-[10px]">
                                SEDANG LATIHAN
                            </span>
                        @endif
                    </td>
                    <td class="px-5 py-3.5 font-medium text-slate-700">
                        <span class="font-semibold text-slate-900">{{ $a->duration_formatted }}</span>
                        @if (!$a->check_out_at)
                            <span class="text-[10px] text-emerald-600 font-bold ml-1">(Berjalan)</span>
                        @endif
                    </td>
                </tr>
            @empty
                <tr><td colspan="5" class="px-5 py-10 text-center text-slate-500">Belum ada check-in di tanggal ini.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
</div>

<div class="mt-5">{{ $attendances->links() }}</div>
@endsection
