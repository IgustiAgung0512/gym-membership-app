@extends('layouts.cashier')
@section('title', 'Monitor RFID & Absensi')

@section('content')
<div class="space-y-6">

    {{-- HEADER --}}
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h1 class="text-xl font-display font-bold text-slate-900">Monitor Check-In & Absensi RFID</h1>
            <p class="text-xs text-slate-500 mt-0.5">Pantau kedatangan member dan proses check-in manual jika member tidak membawa kartu.</p>
        </div>
        <a href="{{ route('cashier.pos.index') }}" class="inline-flex items-center gap-2 px-4 py-2 rounded-xl bg-lime-500 hover:bg-lime-400 text-slate-950 text-xs font-bold transition shadow-sm self-start">
            <span>🛒 Kembali ke Kasir POS</span>
        </a>
    </div>

    {{-- MANUAL CHECKIN & DATE FILTER --}}
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-4">
        
        {{-- MANUAL FORM --}}
        <div class="bg-white p-4 rounded-2xl border border-slate-200 shadow-sm">
            <h3 class="font-display font-bold text-slate-900 text-sm mb-2">Check-in / Check-out Manual</h3>
            <form method="POST" action="{{ route('cashier.attendance.manual') }}" class="space-y-3">
                @csrf
                <div>
                    <label class="block text-[11px] font-bold text-slate-500 uppercase tracking-wider mb-1">Pilih Member</label>
                    <select name="member_id" required class="w-full bg-slate-50 border border-slate-300 rounded-xl px-3 py-2 text-xs focus:outline-none focus:ring-2 focus:ring-lime-500">
                        <option value="">-- Pilih Member Aktif --</option>
                        @foreach ($activeMembers as $m)
                            <option value="{{ $m->id }}">{{ $m->user->name }} ({{ $m->member_code }})</option>
                        @endforeach
                    </select>
                </div>
                <button type="submit" class="w-full py-2.5 rounded-xl bg-slate-900 hover:bg-slate-800 text-white font-bold text-xs transition shadow-sm">
                    Catat Kehadiran Manual
                </button>
            </form>
        </div>

        {{-- DATE FILTER --}}
        <div class="lg:col-span-2 bg-white p-4 rounded-2xl border border-slate-200 shadow-sm flex flex-col justify-between">
            <div>
                <h3 class="font-display font-bold text-slate-900 text-sm mb-1">Filter Tanggal Kunjungan</h3>
                <p class="text-xs text-slate-500">Lihat data log kehadiran member pada tanggal tertentu.</p>
            </div>
            <form method="GET" action="{{ route('cashier.attendance.index') }}" class="flex items-center gap-3 mt-3">
                <input type="date" name="date" value="{{ $date }}" class="bg-slate-50 border border-slate-300 rounded-xl px-3 py-2 text-xs focus:outline-none focus:ring-2 focus:ring-lime-500">
                <button type="submit" class="px-4 py-2 rounded-xl bg-slate-900 text-white font-bold text-xs hover:bg-slate-800 transition">
                    Tampilkan
                </button>
            </form>
        </div>

    </div>

    {{-- ATTENDANCE TABLE --}}
    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs text-slate-600">
                <thead class="bg-slate-50 border-b border-slate-200 text-[11px] uppercase font-bold text-slate-400">
                    <tr>
                        <th class="py-3 px-4">Member</th>
                        <th class="py-3 px-4">Kartu RFID</th>
                        <th class="py-3 px-4">Jam Masuk (Check-In)</th>
                        <th class="py-3 px-4">Jam Keluar (Check-Out)</th>
                        <th class="py-3 px-4">Durasi Sesi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse ($attendances as $a)
                        <tr class="hover:bg-slate-50 transition">
                            <td class="py-3 px-4 font-semibold text-slate-900">
                                {{ $a->member->user->name ?? 'Non-Member' }}
                                <span class="text-[11px] text-slate-400 font-mono block">{{ $a->member->member_code ?? '-' }}</span>
                            </td>
                            <td class="py-3 px-4">
                                @if ($a->rfidCard)
                                    <span class="font-mono text-[11px] px-2 py-0.5 rounded bg-slate-100 text-slate-700 font-bold">
                                        {{ $a->rfidCard->uid }}
                                    </span>
                                @else
                                    <span class="text-[11px] text-slate-400 italic">Manual</span>
                                @endif
                            </td>
                            <td class="py-3 px-4 font-mono font-bold text-slate-800">
                                {{ $a->check_in_at->format('H:i:s') }} WIB
                            </td>
                            <td class="py-3 px-4 font-mono text-slate-600">
                                @if ($a->check_out_at)
                                    {{ $a->check_out_at->format('H:i:s') }} WIB
                                @else
                                    <span class="px-2 py-0.5 rounded-full bg-emerald-100 text-emerald-800 font-bold text-[10px]">
                                        SEDANG LATIHAN
                                    </span>
                                @endif
                            </td>
                            <td class="py-3 px-4 font-medium text-slate-700">
                                <span class="font-semibold text-slate-900">{{ $a->duration_formatted }}</span>
                                @if (!$a->check_out_at)
                                    <span class="text-[10px] text-emerald-600 font-bold ml-1">(Berjalan)</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="py-10 text-center text-slate-400">
                                Belum ada riwayat check-in pada tanggal {{ \Carbon\Carbon::parse($date)->translatedFormat('d F Y') }}.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($attendances->hasPages())
            <div class="p-4 border-t border-slate-100">
                {{ $attendances->links() }}
            </div>
        @endif
    </div>

</div>
@endsection
