@extends('layouts.admin')
@section('title', 'Dashboard')

@push('styles')
    @vite('resources/css/dashboard.css')
@endpush

@section('content')
{{-- =========================================================
    RFID CHECK-IN MONITOR
========================================================= --}}
<div
    id="rfid-checkin-monitor"
    class="bg-white border border-slate-200 rounded-2xl p-5 lg:p-6 mb-8 shadow-sm relative overflow-hidden"
>
    <div class="flex items-center justify-between mb-5">
        <div>
            <h2 class="font-display font-bold text-lg text-slate-900">
                Monitor Check-in RFID Real-Time
            </h2>
            <p class="text-xs text-slate-500 mt-0.5">
                Informasi member saat menempelkan kartu pada alat reader
            </p>
        </div>

        <div
            id="rfid-live-indicator"
            class="flex items-center gap-2 text-xs font-semibold px-3 py-1 rounded-full bg-slate-100 border border-slate-200 text-slate-600"
        >
            <span
                id="rfid-live-dot"
                class="w-2 h-2 rounded-full bg-slate-400"
            ></span>
            Menunggu kartu
        </div>
    </div>

    {{-- BELUM ADA CHECK-IN --}}
    <div
        id="rfid-empty-state"
        class="py-10 text-center bg-slate-50/70 border border-dashed border-slate-200 rounded-xl"
    >
        <div class="w-12 h-12 mx-auto rounded-xl bg-lime-100 text-lime-700 flex items-center justify-center mb-3">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M8 7V5a4 4 0 018 0v2m-9 4h10m-8 4h6m-9-8h12a2 2 0 012 2v7a2 2 0 01-2 2H6a2 2 0 01-2-2v-7a2 2 0 012-2z" />
            </svg>
        </div>
        <p class="text-slate-900 font-bold text-sm">
            Menunggu Tap Kartu RFID
        </p>
        <p class="text-xs text-slate-500 mt-1">
            Silakan tempelkan kartu RFID member pada reader untuk memproses check-in.
        </p>
    </div>

    {{-- KARTU BELUM TERDAFTAR / EXPIRED / NONAKTIF --}}
    <div
        id="rfid-warning-state"
        class="hidden py-8 text-center bg-rose-50 border border-rose-200 rounded-xl"
    >
        <div class="w-12 h-12 mx-auto rounded-xl bg-rose-100 text-rose-600 flex items-center justify-center mb-3">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 11-18 0 9 9 0 0118 0zm-9 3.75h.008v.008H12v-.008z" />
            </svg>
        </div>
        <p id="rfid-warning-title" class="text-rose-700 font-bold text-base">
            Kartu belum terdaftar
        </p>
        <p id="rfid-warning-detail" class="text-xs text-rose-600 mt-1">
            -
        </p>
    </div>

    {{-- HASIL CHECK-IN --}}
    <div
        id="rfid-checkin-data"
        class="hidden p-4 rounded-xl bg-slate-50 border border-slate-200"
    >
        <div class="flex flex-col sm:flex-row sm:items-center gap-5">
            {{-- FOTO --}}
            <div class="shrink-0">
                <div
                    id="rfid-member-photo-wrapper"
                    class="w-20 h-20 rounded-2xl overflow-hidden bg-slate-200 border border-slate-300 flex items-center justify-center text-slate-700 shadow-sm"
                >
                    <img
                        id="rfid-member-photo"
                        src=""
                        alt="Foto Member"
                        class="hidden w-full h-full object-cover"
                    >
                    <span
                        id="rfid-member-initial"
                        class="text-3xl font-display font-bold text-slate-700"
                    >
                        -
                    </span>
                </div>
            </div>

            {{-- INFO MEMBER --}}
            <div class="min-w-0 flex-1">
                <div class="flex flex-wrap items-center gap-2 mb-1">
                    <h3
                        id="rfid-member-name"
                        class="font-display text-xl font-bold text-slate-900"
                    >
                        -
                    </h3>
                    <span class="text-[11px] font-bold px-2 py-0.5 rounded-full bg-lime-100 text-lime-800">
                        RFID TAP
                    </span>
                </div>

                <p id="rfid-member-code" class="text-xs text-slate-500 font-mono">
                    -
                </p>

                <div class="flex flex-wrap gap-x-6 gap-y-2 mt-3 text-xs">
                    <div>
                        <p class="text-[10px] uppercase font-semibold tracking-wider text-slate-400">UID Kartu</p>
                        <p id="rfid-member-uid" class="font-mono font-bold text-slate-800 mt-0.5">-</p>
                    </div>
                    <div>
                        <p class="text-[10px] uppercase font-semibold tracking-wider text-slate-400">Check-In</p>
                        <p id="rfid-member-time" class="font-semibold text-slate-900 mt-0.5">-</p>
                    </div>
                    <div>
                        <p class="text-[10px] uppercase font-semibold tracking-wider text-slate-400">Check-Out</p>
                        <p id="rfid-member-checkout-time" class="font-semibold text-slate-900 mt-0.5">-</p>
                    </div>
                </div>
            </div>

            {{-- STATUS --}}
            <div class="sm:ml-auto shrink-0 text-left sm:text-right">
                <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-emerald-100 text-emerald-800 font-bold text-xs">
                    <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
                    <span id="rfid-status-text">Check-in berhasil</span>
                </div>
                <p id="rfid-member-date" class="text-xs text-slate-400 mt-1">
                    -
                </p>
            </div>
        </div>
    </div>
</div>

{{-- STATS GRID --}}
<div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-8">
    @php
        $stats = [
            ['label' => 'Member Aktif', 'value' => $totalActive, 'icon' => 'M17 20h5v-2a4 4 0 00-3-3.87M9 20H4v-2a4 4 0 013-3.87m6-8a4 4 0 11-8 0 4 4 0 018 0zm6 3a4 4 0 11-8 0 4 4 0 018 0z', 'bg' => 'bg-emerald-50', 'text' => 'text-emerald-700'],
            ['label' => 'Member Baru Bulan Ini', 'value' => $newThisMonth, 'icon' => 'M12 4v16m8-8H4', 'bg' => 'bg-blue-50', 'text' => 'text-blue-700'],
            ['label' => 'Check-in Hari Ini', 'value' => $todayCheckins, 'icon' => 'M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z', 'bg' => 'bg-lime-50', 'text' => 'text-lime-700'],
            ['label' => 'Segera Berakhir (7hr)', 'value' => $expiringSoon, 'icon' => 'M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z', 'bg' => 'bg-rose-50', 'text' => 'text-rose-700'],
        ];
    @endphp
    @foreach ($stats as $s)
        <div class="bg-white border border-slate-200 rounded-2xl p-5 shadow-sm">
            <div class="w-10 h-10 rounded-xl {{ $s['bg'] }} {{ $s['text'] }} flex items-center justify-center mb-3">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="{{ $s['icon'] }}"/>
                </svg>
            </div>
            <p class="text-2xl lg:text-3xl font-display font-extrabold text-slate-900">{{ $s['value'] }}</p>
            <p class="text-xs text-slate-500 font-medium mt-1">{{ $s['label'] }}</p>
        </div>
    @endforeach
</div>

{{-- CHART & REVENUE --}}
<div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-8">
    <div class="lg:col-span-2 bg-white border border-slate-200 rounded-2xl p-5 lg:p-6 shadow-sm">
        <div class="flex items-center justify-between mb-4">
            <div>
                <h2 class="font-display font-bold text-base text-slate-900">Kunjungan 7 Hari Terakhir</h2>
                <p class="text-xs text-slate-500">Frekuensi check-in member via RFID</p>
            </div>
            <span class="text-xs font-semibold px-2.5 py-1 rounded-md bg-slate-100 text-slate-600">RFID Tap</span>
        </div>
        <canvas id="attendanceChart" height="130"></canvas>
    </div>

    <div class="bg-white border border-slate-200 rounded-2xl p-5 lg:p-6 shadow-sm flex flex-col justify-between">
        <div>
            <h2 class="font-display font-bold text-base text-slate-900 mb-1">Pendapatan Bulan Ini</h2>
            <p class="text-xs text-slate-500">Total dari pembayaran paket & perpanjangan.</p>
            <p class="font-display text-3xl font-extrabold text-lime-700 mt-4">
                Rp{{ number_format($revenueThisMonth, 0, ',', '.') }}
            </p>
        </div>
        <div class="pt-6">
            <a href="{{ route('admin.members.create') }}" class="flex items-center justify-center gap-2 w-full rounded-xl bg-slate-900 text-white text-sm font-bold py-3 hover:bg-slate-800 transition shadow-sm">
                <svg class="w-4 h-4 text-lime-400" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/>
                </svg>
                <span>Daftarkan Member Baru</span>
            </a>
        </div>
    </div>
</div>

{{-- RECENT CHECK-INS & RECENT MEMBERS --}}
<div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
    <div class="bg-white border border-slate-200 rounded-2xl p-5 lg:p-6 shadow-sm">
        <h2 class="font-display font-bold text-base text-slate-900 mb-4 flex items-center justify-between">
            <span>Check-in Terbaru</span>
            <span class="text-xs font-normal text-slate-400">Hari ini</span>
        </h2>
        <div class="space-y-3">
            @forelse ($recentCheckins as $a)
                <div class="flex items-center gap-3 text-sm p-2 rounded-xl hover:bg-slate-50 transition">
                    <div class="w-9 h-9 rounded-full bg-lime-100 text-lime-800 flex items-center justify-center font-bold text-sm shrink-0">
                        {{ substr($a->member->user->name, 0, 1) }}
                    </div>
                    <div class="min-w-0 flex-1">
                        <p class="text-slate-900 font-semibold truncate">{{ $a->member->user->name }}</p>
                        <p class="text-xs text-slate-400 font-mono">{{ $a->member->member_code }}</p>
                    </div>
                    <span class="text-xs font-semibold text-slate-600 bg-slate-100 px-2 py-1 rounded-md shrink-0">
                        {{ $a->check_in_at->format('H:i') }} WIB
                    </span>
                </div>
            @empty
                <p class="text-sm text-slate-400 py-4 text-center">Belum ada aktivitas check-in hari ini.</p>
            @endforelse
        </div>
    </div>

    <div class="bg-white border border-slate-200 rounded-2xl p-5 lg:p-6 shadow-sm">
        <h2 class="font-display font-bold text-base text-slate-900 mb-4 flex items-center justify-between">
            <span>Member Terbaru</span>
            <span class="text-xs font-normal text-slate-400">Baru Bergabung</span>
        </h2>
        <div class="space-y-3">
            @forelse ($recentMembers as $m)
                <div class="flex items-center gap-3 text-sm p-2 rounded-xl hover:bg-slate-50 transition">
                    <div class="w-9 h-9 rounded-full bg-blue-100 text-blue-800 flex items-center justify-center font-bold text-sm shrink-0">
                        {{ substr($m->user->name, 0, 1) }}
                    </div>
                    <div class="min-w-0 flex-1">
                        <p class="text-slate-900 font-semibold truncate">{{ $m->user->name }}</p>
                        <p class="text-xs text-slate-500">{{ $m->package->name ?? '-' }}</p>
                    </div>
                    <span class="text-xs text-slate-400 shrink-0">{{ $m->join_date->format('d M Y') }}</span>
                </div>
            @empty
                <p class="text-sm text-slate-400 py-4 text-center">Belum ada member terdaftar.</p>
            @endforelse
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const emptyState = document.getElementById('rfid-empty-state');
    const warningState = document.getElementById('rfid-warning-state');
    const checkinData = document.getElementById('rfid-checkin-data');

    const warningTitle = document.getElementById('rfid-warning-title');
    const warningDetail = document.getElementById('rfid-warning-detail');

    const memberPhoto = document.getElementById('rfid-member-photo');
    const memberInitial = document.getElementById('rfid-member-initial');

    const memberName = document.getElementById('rfid-member-name');
    const memberCode = document.getElementById('rfid-member-code');
    const memberUid = document.getElementById('rfid-member-uid');
    const memberTime = document.getElementById('rfid-member-time');
    const memberCheckoutTime = document.getElementById('rfid-member-checkout-time');
    const memberDate = document.getElementById('rfid-member-date');

    const statusText = document.getElementById('rfid-status-text');

    const liveIndicator = document.getElementById('rfid-live-indicator');
    const liveDot = document.getElementById('rfid-live-dot');

    let lastAttendanceId = null;
    let lastCheckoutTime = null;
    let lastWarningKey = null;

    const warningMessages = {
        unregistered: {
            title: 'Kartu belum terdaftar',
            detail: (data) => 'UID ' + (data.uid ?? '-') + ' belum terhubung dengan member manapun.',
        },
        expired: {
            title: 'Member sudah expired',
            detail: (data) => (data.name ? data.name + ' (' + data.member_code + ')' : 'Member ini') + ' masa aktifnya sudah habis.',
        },
        inactive: {
            title: 'Member tidak aktif',
            detail: (data) => (data.name ? data.name + ' (' + data.member_code + ')' : 'Member ini') + ' statusnya sedang nonaktif.',
        },
        blocked: {
            title: 'Kartu diblokir',
            detail: (data) => (data.name ? data.name + ' (' + data.member_code + ')' : 'Kartu ini') + ' telah diblokir.',
        },
    };

    function setLiveIndicator(colorClass, dotClass, text) {
        liveIndicator.className = 'flex items-center gap-2 text-xs font-semibold px-3 py-1 rounded-full ' + colorClass;
        liveDot.className = 'w-2 h-2 rounded-full ' + dotClass;
        liveIndicator.lastChild.textContent = ' ' + text;
    }

    async function checkLatestRfidCheckin() {
        try {
            const response = await fetch('{{ route('admin.dashboard.latest-rfid-checkin') }}', {
                headers: { 'Accept': 'application/json' }
            });

            if (!response.ok) throw new Error('Gagal mengambil data check-in.');
            const data = await response.json();

            if (!data.exists) {
                if (data.reason && warningMessages[data.reason]) {
                    const warningKey = data.reason + ':' + (data.uid ?? '') + ':' + (data.scan_at ?? '');
                    if (lastWarningKey === warningKey) return;
                    lastWarningKey = warningKey;

                    const msg = warningMessages[data.reason];
                    warningTitle.textContent = msg.title;
                    warningDetail.textContent = msg.detail(data);

                    emptyState.classList.add('hidden');
                    checkinData.classList.add('hidden');
                    warningState.classList.remove('hidden');

                    setLiveIndicator('bg-rose-100 text-rose-800 border border-rose-200', 'bg-rose-500', 'Perlu perhatian');
                }
                return;
            }

            lastWarningKey = null;
            const stateKey = data.id + ':' + (data.checkout_time ?? '');
            if (lastAttendanceId === stateKey) return;

            lastAttendanceId = stateKey;
            lastCheckoutTime = data.checkout_time;

            memberName.textContent = data.name;
            memberCode.textContent = data.member_code;
            memberUid.textContent = data.uid;
            memberTime.textContent = data.time;
            memberCheckoutTime.textContent = data.checkout_time ?? '-';
            memberDate.textContent = data.date;

            if (data.action === 'checkin') {
                statusText.textContent = 'Check-in berhasil';
            } else if (data.action === 'checkout') {
                statusText.textContent = 'Check-out berhasil';
            } else if (data.action === 'completed') {
                statusText.textContent = 'Sudah check-in hari ini';
            }

            const initial = data.name ? data.name.charAt(0).toUpperCase() : '-';
            memberInitial.textContent = initial;

            if (data.photo) {
                memberPhoto.src = data.photo;
                memberPhoto.classList.remove('hidden');
                memberInitial.classList.add('hidden');
            } else {
                memberPhoto.src = '';
                memberPhoto.classList.add('hidden');
                memberInitial.classList.remove('hidden');
            }

            emptyState.classList.add('hidden');
            warningState.classList.add('hidden');
            checkinData.classList.remove('hidden');

            setLiveIndicator('bg-emerald-100 text-emerald-800 border border-emerald-200', 'bg-emerald-500', 'RFID Terdeteksi');

        } catch (error) {
            console.error('RFID Dashboard Error:', error);
        }
    }

    setInterval(checkLatestRfidCheckin, 1000);
    checkLatestRfidCheckin();
});
</script>

<script>
    const ctx = document.getElementById('attendanceChart');
    new Chart(ctx, {
        type: 'bar',
        data: {
            labels: @json($attendanceLast7Days->pluck('label')),
            datasets: [{
                data: @json($attendanceLast7Days->pluck('count')),
                backgroundColor: '#84CC16',
                borderRadius: 8,
                maxBarThickness: 36,
            }]
        },
        options: {
            plugins: { legend: { display: false } },
            scales: {
                x: { grid: { display: false }, ticks: { color: '#64748B' } },
                y: { beginAtZero: true, ticks: { color: '#64748B', precision: 0 }, grid: { color: '#E2E8F0' } },
            }
        }
    });
</script>
@endsection