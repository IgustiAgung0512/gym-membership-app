@extends('layouts.admin')
@section('title', 'Absensi / Check-in')

@section('content')
<form method="GET" class="flex flex-wrap items-center gap-3 mb-6">
    <input type="date" name="date" value="{{ request('date', today()->format('Y-m-d')) }}" onchange="this.form.submit()"
           class="rounded-xl bg-base-card border border-base-line px-4 py-2.5 text-sm text-white focus:outline-none focus:ring-2 focus:ring-volt/50">
    <span class="text-xs text-slate-500">Total: {{ $attendances->total() }} check-in</span>
</form>

<div class="bg-base-card border border-base-line rounded-2xl overflow-hidden">
    <table class="w-full text-sm">
        <thead>
            <tr class="text-left text-slate-400 border-b border-base-line">
                <th class="px-5 py-3 font-medium">Waktu</th>
                <th class="px-5 py-3 font-medium">Member</th>
                <th class="px-5 py-3 font-medium">Metode</th>
                <th class="px-5 py-3 font-medium">Kartu</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-base-line">
        @forelse ($attendances as $a)
            <tr class="hover:bg-white/[0.02]">
                <td class="px-5 py-3 text-slate-300">{{ $a->check_in_at->format('H:i:s') }}</td>
                <td class="px-5 py-3">
                    <p class="text-white">{{ $a->member->user->name }}</p>
                    <p class="text-xs text-slate-500">{{ $a->member->member_code }}</p>
                </td>
                <td class="px-5 py-3">
                    <span class="text-xs px-2 py-1 rounded-full {{ $a->method=='rfid' ? 'bg-volt/10 text-volt' : 'bg-slate-500/10 text-slate-400' }}">{{ $a->method == 'rfid' ? 'RFID' : 'Manual' }}</span>
                </td>
                <td class="px-5 py-3 font-mono text-slate-400">{{ $a->rfidCard->uid ?? '—' }}</td>
            </tr>
        @empty
            <tr><td colspan="4" class="px-5 py-10 text-center text-slate-500">Belum ada check-in di tanggal ini.</td></tr>
        @endforelse
        </tbody>
    </table>
</div>
<div class="mt-6">{{ $attendances->links() }}</div>
@endsection
