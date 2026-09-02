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
    class="bg-base-card border border-base-line rounded-2xl p-5 lg:p-6 mb-8"
>
    <div class="flex items-center justify-between mb-5">

        <div>
            <h2 class="font-display font-semibold text-white">
                Check-in Terbaru
            </h2>

            <p class="text-xs text-slate-500 mt-1">
                Informasi member dari tap kartu RFID
            </p>
        </div>

        <div
            id="rfid-live-indicator"
            class="flex items-center gap-2 text-xs text-slate-500"
        >
            <span
                id="rfid-live-dot"
                class="w-2 h-2 rounded-full bg-slate-500"
            ></span>

            Menunggu kartu
        </div>

    </div>


    {{-- BELUM ADA CHECK-IN --}}
    <div
        id="rfid-empty-state"
        class="py-8 text-center"
    >
        <div class="w-14 h-14 mx-auto rounded-2xl bg-volt/10 text-volt flex items-center justify-center mb-3">

            <svg
                class="w-7 h-7"
                fill="none"
                stroke="currentColor"
                stroke-width="1.7"
                viewBox="0 0 24 24"
            >
                <path
                    stroke-linecap="round"
                    stroke-linejoin="round"
                    d="M8 7V5a4 4 0 018 0v2m-9 4h10m-8 4h6m-9-8h12a2 2 0 012 2v7a2 2 0 01-2 2H6a2 2 0 01-2-2v-7a2 2 0 012-2z"
                />
            </svg>

        </div>

        <p class="text-white font-medium">
            Menunggu kartu RFID
        </p>

        <p class="text-xs text-slate-500 mt-1">
            Silakan tap kartu member pada reader.
        </p>
    </div>


    {{-- HASIL CHECK-IN --}}
    <div
        id="rfid-checkin-data"
        class="hidden"
    >

        <div class="flex flex-col sm:flex-row sm:items-center gap-5">

            {{-- FOTO --}}
            <div class="shrink-0">

                <div
                    id="rfid-member-photo-wrapper"
                    class="w-20 h-20 lg:w-24 lg:h-24 rounded-2xl overflow-hidden bg-volt/10 text-volt flex items-center justify-center"
                >

                    <img
                        id="rfid-member-photo"
                        src=""
                        alt="Foto Member"
                        class="hidden w-full h-full object-cover"
                    >

                    <span
                        id="rfid-member-initial"
                        class="text-3xl lg:text-4xl font-bold"
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
                        class="font-display text-xl lg:text-2xl font-bold text-white"
                    >
                        -
                    </h3>

                    <span
                        class="text-xs px-2 py-1 rounded-full bg-volt/10 text-volt"
                    >
                        RFID
                    </span>

                </div>

                <p
                    id="rfid-member-code"
                    class="text-sm text-slate-400"
                >
                    -
                </p>

                <div class="flex flex-wrap gap-x-6 gap-y-2 mt-4">

                    <div>
                        <p class="text-[11px] uppercase tracking-wide text-slate-500">
                            UID Kartu
                        </p>

                        <p
                            id="rfid-member-uid"
                            class="font-mono text-sm text-slate-300 mt-0.5"
                        >
                            -
                        </p>
                    </div>

                    <div>
                        <p class="text-[11px] uppercase tracking-wide text-slate-500">
                            Waktu Check-in
                        </p>
                    
                        <p
                            id="rfid-member-time"
                            class="text-sm text-white font-semibold mt-0.5"
                        >
                            -
                        </p>
                    </div>
                    
                    <div>
                        <p class="text-[11px] uppercase tracking-wide text-slate-500">
                            Waktu Check-out
                        </p>
                    
                        <p
                            id="rfid-member-checkout-time"
                            class="text-sm text-white font-semibold mt-0.5"
                        >
                            -
                        </p>
                    </div>

                </div>

            </div>


            {{-- STATUS --}}
            <div class="sm:ml-auto shrink-0">

                <div class="flex items-center gap-2 text-volt">

                    <span class="w-2.5 h-2.5 rounded-full bg-volt"></span>

                    <span
                        id="rfid-status-text"
                        class="text-sm font-semibold"
                    >
                        Check-in berhasil
                    </span>

                </div>

                <p
                    id="rfid-member-date"
                    class="text-xs text-slate-500 mt-1 text-left sm:text-right"
                >
                    -
                </p>

            </div>

        </div>

    </div>
</div>
<div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-8">
    @php
        $stats = [
            ['label' => 'Member Aktif', 'value' => $totalActive, 'icon' => 'M17 20h5v-2a4 4 0 00-3-3.87M9 20H4v-2a4 4 0 013-3.87m6-8a4 4 0 11-8 0 4 4 0 018 0zm6 3a4 4 0 11-8 0 4 4 0 018 0z', 'accent' => 'volt'],
            ['label' => 'Member Baru Bulan Ini', 'value' => $newThisMonth, 'icon' => 'M12 4v16m8-8H4', 'accent' => 'coral'],
            ['label' => 'Check-in Hari Ini', 'value' => $todayCheckins, 'icon' => 'M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z', 'accent' => 'volt'],
            ['label' => 'Segera Berakhir (7hr)', 'value' => $expiringSoon, 'icon' => 'M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z', 'accent' => 'coral'],
        ];
    @endphp
    @foreach ($stats as $s)
        <div class="bg-base-card border border-base-line rounded-2xl p-4 lg:p-5">
            <div class="w-9 h-9 rounded-lg bg-{{ $s['accent'] }}/10 text-{{ $s['accent'] }} flex items-center justify-center mb-3">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="{{ $s['icon'] }}"/></svg>
            </div>
            <p class="text-2xl lg:text-3xl font-display font-bold text-white">{{ $s['value'] }}</p>
            <p class="text-xs lg:text-sm text-slate-400 mt-1">{{ $s['label'] }}</p>
        </div>
    @endforeach
</div>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-8">
    <div class="lg:col-span-2 bg-base-card border border-base-line rounded-2xl p-5 lg:p-6">
        <div class="flex items-center justify-between mb-4">
            <h2 class="font-display font-semibold text-white">Kunjungan 7 Hari Terakhir</h2>
            <span class="text-xs text-slate-500">via RFID check-in</span>
        </div>
        <canvas id="attendanceChart" height="130"></canvas>
    </div>
    <div class="bg-base-card border border-base-line rounded-2xl p-5 lg:p-6">
        <h2 class="font-display font-semibold text-white mb-1">Pendapatan Bulan Ini</h2>
        <p class="font-display text-3xl font-bold text-volt mt-3">Rp{{ number_format($revenueThisMonth, 0, ',', '.') }}</p>
        <p class="text-xs text-slate-500 mt-2">Total dari pembayaran paket & perpanjangan.</p>
        <a href="{{ route('admin.members.create') }}" class="mt-5 flex items-center justify-center gap-2 w-full rounded-xl bg-volt text-base text-sm font-semibold py-2.5 hover:brightness-95 transition">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/></svg>
            Daftarkan Member Baru
        </a>
    </div>
</div>

<div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
    <div class="bg-base-card border border-base-line rounded-2xl p-5 lg:p-6">
        <h2 class="font-display font-semibold text-white mb-4">Check-in Terbaru</h2>
        <div class="space-y-3">
            @forelse ($recentCheckins as $a)
                <div class="flex items-center gap-3 text-sm">
                    <div class="w-8 h-8 rounded-full bg-volt/10 text-volt flex items-center justify-center font-semibold shrink-0">{{ substr($a->member->user->name,0,1) }}</div>
                    <div class="min-w-0 flex-1">
                        <p class="text-white truncate">{{ $a->member->user->name }}</p>
                        <p class="text-xs text-slate-500">{{ $a->member->member_code }}</p>
                    </div>
                    <span class="text-xs text-slate-400 shrink-0">{{ $a->check_in_at->format('H:i') }}</span>
                </div>
            @empty
                <p class="text-sm text-slate-500">Belum ada aktivitas check-in.</p>
            @endforelse
        </div>
    </div>
    <div class="bg-base-card border border-base-line rounded-2xl p-5 lg:p-6">
        <h2 class="font-display font-semibold text-white mb-4">Member Terbaru</h2>
        <div class="space-y-3">
            @forelse ($recentMembers as $m)
                <div class="flex items-center gap-3 text-sm">
                    <div class="w-8 h-8 rounded-full bg-coral/10 text-coral flex items-center justify-center font-semibold shrink-0">{{ substr($m->user->name,0,1) }}</div>
                    <div class="min-w-0 flex-1">
                        <p class="text-white truncate">{{ $m->user->name }}</p>
                        <p class="text-xs text-slate-500">{{ $m->package->name ?? '-' }}</p>
                    </div>
                    <span class="text-xs text-slate-400 shrink-0">{{ $m->join_date->format('d M') }}</span>
                </div>
            @empty
                <p class="text-sm text-slate-500">Belum ada member.</p>
            @endforelse
        </div>
    </div>
</div>
<script>
document.addEventListener('DOMContentLoaded', function () {

    const emptyState = document.getElementById('rfid-empty-state');
    const checkinData = document.getElementById('rfid-checkin-data');

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


    async function checkLatestRfidCheckin() {

        try {

            const response = await fetch(
                '{{ route('admin.dashboard.latest-rfid-checkin') }}',
                {
                    headers: {
                        'Accept': 'application/json'
                    }
                }
            );

            if (!response.ok) {
                throw new Error('Gagal mengambil data check-in.');
            }

            const data = await response.json();


            if (!data.exists) {
                return;
            }


            // Kalau masih check-in yang sama,
            // tidak perlu render ulang.
            
            if (lastAttendanceId === data.id ) {
                return;
            }


            lastAttendanceId = data.id;
            lastCheckoutTime = data.checkout_time;


            // ==========================================
            // UPDATE DATA MEMBER
            // ==========================================

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

                statusText.textContent =
                    'Check-in sudah dilakukan hari ini';
                }

            // ==========================================
            // FOTO MEMBER
            // ==========================================

            const initial = data.name
                ? data.name.charAt(0).toUpperCase()
                : '-';

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


            // ==========================================
            // TAMPILKAN DATA
            // ==========================================

            emptyState.classList.add('hidden');
            checkinData.classList.remove('hidden');


            // ==========================================
            // INDIKATOR LIVE
            // ==========================================

            liveIndicator.classList.remove('text-slate-500');
            liveIndicator.classList.add('text-volt');

            liveDot.classList.remove('bg-slate-500');
            liveDot.classList.add('bg-volt');

            liveIndicator.lastChild.textContent = ' RFID terdeteksi';


            // ==========================================
            // EFEK SAAT KARTU BARU DITAP
            // ==========================================

            checkinData.classList.remove('rfid-checkin-pulse');

            void checkinData.offsetWidth;

            checkinData.classList.add('rfid-checkin-pulse');


        } catch (error) {

            console.error('RFID Dashboard Error:', error);

        }

    }


    // Cek setiap 1 detik
    setInterval(checkLatestRfidCheckin, 1000);

    // Cek langsung saat dashboard dibuka
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
                backgroundColor: '#C6FF3D',
                borderRadius: 8,
                maxBarThickness: 36,
            }]
        },
        options: {
            plugins: { legend: { display: false } },
            scales: {
                x: { grid: { display: false }, ticks: { color: '#94A3B8' } },
                y: { beginAtZero: true, ticks: { color: '#94A3B8', precision: 0 }, grid: { color: '#262A34' } },
            }
        }
    });
</script>
@endsection