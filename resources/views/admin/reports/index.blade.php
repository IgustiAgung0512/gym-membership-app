@extends('layouts.admin')
@section('title', 'Laporan')

@section('content')

<p class="text-sm text-slate-500 mb-6 max-w-2xl">
    Pilih jenis laporan dan rentang tanggal, lalu unduh sebagai PDF. Cocok untuk arsip bulanan, laporan ke owner, atau rekap follow-up member.
</p>

{{-- =========================================================
    RINGKASAN CEPAT
========================================================= --}}
<div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-8">
    @php
        $quickStats = [
            ['label' => 'Total Member', 'value' => $totalMembers, 'icon' => 'M17 20h5v-2a4 4 0 00-3-3.87M9 20H4v-2a4 4 0 013-3.87m6-8a4 4 0 11-8 0 4 4 0 018 0zm6 3a4 4 0 11-8 0 4 4 0 018 0z', 'bg' => 'bg-emerald-50', 'text' => 'text-emerald-700'],
            ['label' => 'Member Aktif', 'value' => $activeMembers, 'icon' => 'M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z', 'bg' => 'bg-lime-50', 'text' => 'text-lime-700'],
            ['label' => 'Pendapatan Bulan Ini', 'value' => 'Rp' . number_format($revenueThisMonth, 0, ',', '.'), 'icon' => 'M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V6m0 10v2m9-8a9 9 0 11-18 0 9 9 0 0118 0z', 'bg' => 'bg-blue-50', 'text' => 'text-blue-700'],
            ['label' => 'Segera Berakhir (7hr)', 'value' => $expiringSoonCount, 'icon' => 'M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z', 'bg' => 'bg-rose-50', 'text' => 'text-rose-700'],
        ];
    @endphp
    @foreach ($quickStats as $s)
        <div class="bg-white border border-slate-200 rounded-2xl p-5 shadow-sm">
            <div class="w-10 h-10 rounded-xl {{ $s['bg'] }} {{ $s['text'] }} flex items-center justify-center mb-3">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="{{ $s['icon'] }}"/></svg>
            </div>
            <p class="text-xl lg:text-2xl font-display font-extrabold text-slate-900">{{ $s['value'] }}</p>
            <p class="text-xs lg:text-sm text-slate-500 mt-1 font-medium">{{ $s['label'] }}</p>
        </div>
    @endforeach
</div>

{{-- =========================================================
    GRAFIK
========================================================= --}}
<div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-8">

    <div class="lg:col-span-2 bg-white border border-slate-200 rounded-2xl p-5 lg:p-6 shadow-sm">
        <div class="flex items-center justify-between mb-4">
            <h2 class="font-display font-bold text-slate-900 text-base">Tren Pendapatan 6 Bulan Terakhir</h2>
            <span class="text-xs font-medium text-slate-500 bg-slate-100 px-2.5 py-1 rounded-md">Pembayaran Berstatus Paid</span>
        </div>
        <canvas id="revenueTrendChart" height="130"></canvas>
    </div>

    <div class="bg-white border border-slate-200 rounded-2xl p-5 lg:p-6 shadow-sm">
        <h2 class="font-display font-bold text-slate-900 text-base mb-4">Distribusi Status Member</h2>
        <canvas id="memberStatusChart" height="200"></canvas>
    </div>

</div>

{{-- =========================================================
    DAFTAR LAPORAN
========================================================= --}}
<div x-data="{ from: '{{ $defaultFrom }}', to: '{{ $defaultTo }}' }" class="grid md:grid-cols-2 gap-5">

    {{-- Laporan Data Member --}}
    <div class="bg-white border border-slate-200 rounded-2xl p-5 lg:p-6 shadow-sm">
        <div class="flex items-start gap-3 mb-4">
            <div class="w-10 h-10 rounded-xl bg-lime-100 text-lime-800 flex items-center justify-center shrink-0">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M17 20h5v-2a4 4 0 00-3-3.87M9 20H4v-2a4 4 0 013-3.87m6-8a4 4 0 11-8 0 4 4 0 018 0zm6 3a4 4 0 11-8 0 4 4 0 018 0z"/></svg>
            </div>
            <div>
                <h3 class="font-display font-bold text-slate-900">Laporan Data Member</h3>
                <p class="text-xs text-slate-500 mt-0.5">Daftar lengkap member, paket, status, dan kartu RFID.</p>
            </div>
        </div>
        <form method="GET" action="{{ route('admin.reports.members.pdf') }}" target="_blank" class="flex flex-wrap items-center gap-2">
            <select name="status" class="rounded-xl bg-slate-50 border border-slate-300 px-3 py-2 text-sm text-slate-900 focus:outline-none focus:ring-2 focus:ring-lime-500">
                <option value="">Semua Status</option>
                <option value="active">Aktif</option>
                <option value="inactive">Nonaktif</option>
                <option value="expired">Expired</option>
            </select>
            <button type="submit" class="flex items-center gap-2 rounded-xl bg-slate-900 text-white text-sm font-bold px-4 py-2 hover:bg-slate-800 transition ml-auto shadow-sm">
                <svg class="w-4 h-4 text-lime-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                Unduh PDF
            </button>
        </form>
    </div>

    {{-- Laporan Absensi --}}
    <div class="bg-white border border-slate-200 rounded-2xl p-5 lg:p-6 shadow-sm">
        <div class="flex items-start gap-3 mb-4">
            <div class="w-10 h-10 rounded-xl bg-blue-100 text-blue-800 flex items-center justify-center shrink-0">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            </div>
            <div>
                <h3 class="font-display font-bold text-slate-900">Laporan Absensi / Check-in</h3>
                <p class="text-xs text-slate-500 mt-0.5">Rekap kehadiran member via RFID dalam rentang tanggal.</p>
            </div>
        </div>
        <form method="GET" action="{{ route('admin.reports.attendance.pdf') }}" target="_blank" class="flex flex-wrap items-center gap-2">
            <input type="date" name="from" x-model="from" class="rounded-xl bg-slate-50 border border-slate-300 px-3 py-2 text-sm text-slate-900 focus:outline-none focus:ring-2 focus:ring-lime-500">
            <span class="text-slate-400 text-xs">s/d</span>
            <input type="date" name="to" x-model="to" class="rounded-xl bg-slate-50 border border-slate-300 px-3 py-2 text-sm text-slate-900 focus:outline-none focus:ring-2 focus:ring-lime-500">
            <button type="submit" class="flex items-center gap-2 rounded-xl bg-slate-900 text-white text-sm font-bold px-4 py-2 hover:bg-slate-800 transition ml-auto shadow-sm">
                <svg class="w-4 h-4 text-lime-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                Unduh PDF
            </button>
        </form>
    </div>

    {{-- Laporan Pendapatan --}}
    <div class="bg-white border border-slate-200 rounded-2xl p-5 lg:p-6 shadow-sm">
        <div class="flex items-start gap-3 mb-4">
            <div class="w-10 h-10 rounded-xl bg-emerald-100 text-emerald-800 flex items-center justify-center shrink-0">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V6m0 10v2m9-8a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            </div>
            <div>
                <h3 class="font-display font-bold text-slate-900">Laporan Pendapatan</h3>
                <p class="text-xs text-slate-500 mt-0.5">Rekap pembayaran pendaftaran & perpanjangan, plus total.</p>
            </div>
        </div>
        <form method="GET" action="{{ route('admin.reports.revenue.pdf') }}" target="_blank" class="flex flex-wrap items-center gap-2">
            <input type="date" name="from" x-model="from" class="rounded-xl bg-slate-50 border border-slate-300 px-3 py-2 text-sm text-slate-900 focus:outline-none focus:ring-2 focus:ring-lime-500">
            <span class="text-slate-400 text-xs">s/d</span>
            <input type="date" name="to" x-model="to" class="rounded-xl bg-slate-50 border border-slate-300 px-3 py-2 text-sm text-slate-900 focus:outline-none focus:ring-2 focus:ring-lime-500">
            <button type="submit" class="flex items-center gap-2 rounded-xl bg-slate-900 text-white text-sm font-bold px-4 py-2 hover:bg-slate-800 transition ml-auto shadow-sm">
                <svg class="w-4 h-4 text-lime-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                Unduh PDF
            </button>
        </form>
    </div>

    {{-- Laporan Member Segera Berakhir --}}
    <div class="bg-white border border-slate-200 rounded-2xl p-5 lg:p-6 shadow-sm">
        <div class="flex items-start gap-3 mb-4">
            <div class="w-10 h-10 rounded-xl bg-rose-100 text-rose-800 flex items-center justify-center shrink-0">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            </div>
            <div>
                <h3 class="font-display font-bold text-slate-900">Member Segera Berakhir</h3>
                <p class="text-xs text-slate-500 mt-0.5">Daftar member aktif yang perlu di-follow up untuk perpanjangan.</p>
            </div>
        </div>
        <form method="GET" action="{{ route('admin.reports.expiring.pdf') }}" target="_blank" class="flex flex-wrap items-center gap-2">
            <select name="days" class="rounded-xl bg-slate-50 border border-slate-300 px-3 py-2 text-sm text-slate-900 focus:outline-none focus:ring-2 focus:ring-lime-500">
                <option value="3">3 hari ke depan</option>
                <option value="7" selected>7 hari ke depan</option>
                <option value="14">14 hari ke depan</option>
                <option value="30">30 hari ke depan</option>
            </select>
            <button type="submit" class="flex items-center gap-2 rounded-xl bg-slate-900 text-white text-sm font-bold px-4 py-2 hover:bg-slate-800 transition ml-auto shadow-sm">
                <svg class="w-4 h-4 text-lime-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                Unduh PDF
            </button>
        </form>
    </div>

    {{-- Laporan Log WhatsApp --}}
    <div class="bg-white border border-slate-200 rounded-2xl p-5 lg:p-6 shadow-sm md:col-span-2">
        <div class="flex items-start gap-3 mb-4">
            <div class="w-10 h-10 rounded-xl bg-amber-100 text-amber-800 flex items-center justify-center shrink-0">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-6l-4 4v-4z"/></svg>
            </div>
            <div>
                <h3 class="font-display font-bold text-slate-900">Laporan Log Notifikasi WhatsApp</h3>
                <p class="text-xs text-slate-500 mt-0.5">Rekap status pengiriman notifikasi (terkirim/gagal) untuk audit.</p>
            </div>
        </div>
        <form method="GET" action="{{ route('admin.reports.whatsapp.pdf') }}" target="_blank" class="flex flex-wrap items-center gap-2">
            <input type="date" name="from" x-model="from" class="rounded-xl bg-slate-50 border border-slate-300 px-3 py-2 text-sm text-slate-900 focus:outline-none focus:ring-2 focus:ring-lime-500">
            <span class="text-slate-400 text-xs">s/d</span>
            <input type="date" name="to" x-model="to" class="rounded-xl bg-slate-50 border border-slate-300 px-3 py-2 text-sm text-slate-900 focus:outline-none focus:ring-2 focus:ring-lime-500">
            <button type="submit" class="flex items-center gap-2 rounded-xl bg-slate-900 text-white text-sm font-bold px-4 py-2 hover:bg-slate-800 transition ml-auto shadow-sm">
                <svg class="w-4 h-4 text-lime-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                Unduh PDF
            </button>
        </form>
    </div>

</div>

<script>
    // Grafik tren pendapatan
    const revenueCtx = document.getElementById('revenueTrendChart');
    new Chart(revenueCtx, {
        type: 'bar',
        data: {
            labels: @json($revenueLast6Months->pluck('label')),
            datasets: [{
                data: @json($revenueLast6Months->pluck('total')),
                backgroundColor: '#84CC16',
                borderRadius: 8,
                maxBarThickness: 40,
            }]
        },
        options: {
            plugins: {
                legend: { display: false },
                tooltip: {
                    callbacks: {
                        label: (ctx) => 'Rp' + ctx.parsed.y.toLocaleString('id-ID')
                    }
                }
            },
            scales: {
                x: { grid: { display: false }, ticks: { color: '#64748B' } },
                y: {
                    beginAtZero: true,
                    ticks: {
                        color: '#64748B',
                        callback: (v) => 'Rp' + (v / 1000) + 'rb'
                    },
                    grid: { color: '#E2E8F0' }
                },
            }
        }
    });

    // Grafik distribusi status member
    const statusCtx = document.getElementById('memberStatusChart');
    new Chart(statusCtx, {
        type: 'doughnut',
        data: {
            labels: ['Aktif', 'Nonaktif', 'Expired'],
            datasets: [{
                data: [
                    {{ $memberStatusCounts['active'] }},
                    {{ $memberStatusCounts['inactive'] }},
                    {{ $memberStatusCounts['expired'] }}
                ],
                backgroundColor: ['#84CC16', '#CBD5E1', '#EF4444'],
                borderWidth: 0,
            }]
        },
        options: {
            cutout: '68%',
            plugins: {
                legend: {
                    position: 'bottom',
                    labels: { color: '#64748B', boxWidth: 10, padding: 14 }
                }
            }
        }
    });
</script>

@endsection
