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
        class="hidden p-5 sm:p-6 lg:p-7 rounded-2xl bg-gradient-to-br from-white via-slate-50/70 to-slate-100/50 border border-slate-200/80 transition-all duration-500 relative overflow-hidden shadow-sm hover:shadow-md"
    >
        {{-- Background Themed Topographic & Fitness Wave Pattern --}}
        <div class="absolute inset-0 pointer-events-none overflow-hidden select-none opacity-45">
            <svg class="absolute -right-12 -top-12 w-[520px] h-[340px] transition-all duration-700" viewBox="0 0 500 300" fill="none" xmlns="http://www.w3.org/2000/svg">
                <path class="rfid-topo-path" d="M30 0 C 140 70, 190 30, 310 110 C 410 170, 450 110, 500 190" stroke="#84cc16" stroke-width="2" stroke-linecap="round" stroke-dasharray="4 6" opacity="0.45"/>
                <path class="rfid-topo-path" d="M0 50 C 110 130, 210 70, 350 170 C 430 230, 470 150, 520 250" stroke="#84cc16" stroke-width="1.75" opacity="0.35"/>
                <path class="rfid-topo-path" d="M70 90 C 170 170, 250 110, 370 210 C 440 270, 480 190, 530 290" stroke="#84cc16" stroke-width="2.5" opacity="0.5"/>
                <path class="rfid-topo-path" d="M110 130 C 210 210, 290 150, 410 250 C 460 300, 500 230, 550 330" stroke="#84cc16" stroke-width="1.5" opacity="0.3"/>
                <path class="rfid-topo-path" d="M150 170 C 250 250, 330 190, 450 290 C 490 330, 520 270, 570 370" stroke="#84cc16" stroke-width="2" stroke-linecap="round" opacity="0.4"/>
                <circle class="rfid-topo-path" cx="410" cy="70" r="55" stroke="#84cc16" stroke-width="1" stroke-dasharray="3 5" opacity="0.25"/>
                <circle class="rfid-topo-path" cx="410" cy="70" r="105" stroke="#84cc16" stroke-width="1" opacity="0.15"/>
            </svg>
        </div>

        <div class="flex flex-col md:flex-row md:items-center gap-5 lg:gap-6 relative z-10">
            {{-- FOTO MEMBER --}}
            <div class="shrink-0 flex items-center justify-center self-center md:self-auto">
                <div
                    id="rfid-member-photo-wrapper"
                    class="w-20 h-20 sm:w-24 sm:h-24 md:w-28 md:h-28 rounded-2xl overflow-hidden bg-slate-100 ring-4 ring-offset-2 ring-slate-100 border border-slate-200 flex items-center justify-center text-slate-700 shadow-sm transition-all duration-300 group hover:scale-105"
                >
                    <img
                        id="rfid-member-photo"
                        src=""
                        alt="Foto Member"
                        class="hidden w-full h-full object-cover"
                    >
                    <span
                        id="rfid-member-initial"
                        class="text-3xl sm:text-4xl font-display font-extrabold text-slate-800"
                    >
                        -
                    </span>
                </div>
            </div>

            {{-- INFO MEMBER --}}
            <div class="min-w-0 flex-1 space-y-3">
                {{-- Nama & Badge --}}
                <div class="flex flex-wrap items-center gap-2 sm:gap-2.5">
                    <h3
                        id="rfid-member-name"
                        class="font-display text-xl sm:text-2xl lg:text-3xl font-extrabold tracking-tight text-slate-900 leading-tight"
                        style="color: #0f172a !important;"
                    >
                        -
                    </h3>
                    <div class="flex items-center gap-1.5">
                        <span class="text-[10px] sm:text-[11px] font-bold px-2.5 py-0.5 rounded-full bg-slate-100 text-slate-700 border border-slate-200 uppercase tracking-wider shadow-2xs">
                            RFID TAP
                        </span>
                        <span 
                            id="rfid-package-badge"
                            class="text-[11px] sm:text-xs font-bold px-3 py-0.5 rounded-full transition-all duration-300 inline-flex items-center shadow-2xs"
                        >
                            -
                        </span>
                    </div>
                </div>

                {{-- Kode Member & Masa Aktif --}}
                <div class="flex flex-wrap items-center gap-2 text-xs font-mono">
                    <span id="rfid-member-code" class="font-bold px-2 py-0.5 rounded-md bg-slate-100 border border-slate-200/80 text-slate-700">-</span>
                    <span id="rfid-member-validity" class="font-sans font-semibold px-2 py-0.5 rounded-md bg-emerald-50 border border-emerald-200/80 text-emerald-800">-</span>
                </div>

                {{-- Chip Data Kehadiran --}}
                <div class="grid grid-cols-2 sm:grid-cols-4 gap-2 sm:gap-2.5 pt-0.5 text-xs">
                    <div class="p-2.5 sm:p-3 rounded-xl bg-white/90 hover:bg-white border border-slate-200/90 shadow-2xs transition">
                        <p class="text-[10px] uppercase font-bold tracking-wider text-slate-400">
                            UID Kartu
                        </p>
                        <p id="rfid-member-uid" class="font-mono font-bold text-xs sm:text-sm text-slate-900 mt-1 truncate">-</p>
                    </div>
                    <div class="p-2.5 sm:p-3 rounded-xl bg-white/90 hover:bg-white border border-slate-200/90 shadow-2xs transition">
                        <p class="text-[10px] uppercase font-bold tracking-wider text-slate-400">
                            Check-In
                        </p>
                        <p id="rfid-member-time" class="font-mono font-bold text-xs sm:text-sm text-slate-900 mt-1">-</p>
                    </div>
                    <div class="p-2.5 sm:p-3 rounded-xl bg-white/90 hover:bg-white border border-slate-200/90 shadow-2xs transition">
                        <p class="text-[10px] uppercase font-bold tracking-wider text-slate-400">
                            Check-Out
                        </p>
                        <p id="rfid-member-checkout-time" class="font-mono font-bold text-xs sm:text-sm text-slate-900 mt-1">-</p>
                    </div>
                    <div id="rfid-session-box" class="p-2.5 sm:p-3 rounded-xl bg-amber-50/90 hover:bg-amber-50 border border-amber-300/80 shadow-2xs transition hidden">
                        <p class="text-[10px] uppercase font-bold tracking-wider text-amber-800 font-bold">
                            Durasi Sesi
                        </p>
                        <p id="rfid-session-duration" class="font-mono font-extrabold text-xs sm:text-sm text-amber-950 mt-1">-</p>
                    </div>
                </div>
            </div>

            {{-- STATUS PRESENSI --}}
            <div class="w-full md:w-auto md:shrink-0 flex md:flex-col items-center md:items-end justify-between md:justify-center gap-2 pt-3 md:pt-0 border-t md:border-t-0 border-slate-200/80">
                <div id="rfid-status-badge" class="inline-flex items-center gap-2 px-4 py-2 rounded-full font-bold text-xs sm:text-sm shadow-sm transition-all duration-300">
                    <span id="rfid-status-dot" class="w-2.5 h-2.5 rounded-full"></span>
                    <span id="rfid-status-text">Check-in berhasil</span>
                </div>
                <p id="rfid-member-date" class="text-xs text-slate-400 font-mono">
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
            ['id' => 'stat-active-members', 'label' => 'Member Aktif', 'value' => $totalActive, 'icon' => 'M17 20h5v-2a4 4 0 00-3-3.87M9 20H4v-2a4 4 0 013-3.87m6-8a4 4 0 11-8 0 4 4 0 018 0zm6 3a4 4 0 11-8 0 4 4 0 018 0z', 'bg' => 'bg-emerald-50', 'text' => 'text-emerald-700'],
            ['id' => 'stat-new-members', 'label' => 'Member Baru Bulan Ini', 'value' => $newThisMonth, 'icon' => 'M12 4v16m8-8H4', 'bg' => 'bg-blue-50', 'text' => 'text-blue-700'],
            ['id' => 'stat-today-checkins', 'label' => 'Check-in Hari Ini', 'value' => $todayCheckins, 'icon' => 'M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z', 'bg' => 'bg-lime-50', 'text' => 'text-lime-700'],
            ['id' => 'stat-expiring-soon', 'label' => 'Segera Berakhir (7hr)', 'value' => $expiringSoon, 'icon' => 'M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z', 'bg' => 'bg-rose-50', 'text' => 'text-rose-700'],
        ];
    @endphp
    @foreach ($stats as $s)
        <div class="bg-white border border-slate-200 rounded-2xl p-5 shadow-sm">
            <div class="w-10 h-10 rounded-xl {{ $s['bg'] }} {{ $s['text'] }} flex items-center justify-center mb-3">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="{{ $s['icon'] }}"/>
                </svg>
            </div>
            <p id="{{ $s['id'] }}" class="text-2xl lg:text-3xl font-display font-extrabold text-slate-900">{{ $s['value'] }}</p>
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
            <div class="flex items-center justify-between">
                <h2 class="font-display font-bold text-base text-slate-900">Performa Keuangan Bulan Ini</h2>
                <span class="text-[10px] font-bold px-2 py-0.5 rounded bg-lime-100 text-lime-800">Ringkasan</span>
            </div>
            <p class="text-xs text-slate-500 mt-0.5">Total Omzet Gabungan</p>
            
            <p class="font-display text-2xl lg:text-3xl font-extrabold text-slate-900 mt-2">
                Rp{{ number_format($totalCombinedRevenue, 0, ',', '.') }}
            </p>

            <div class="mt-4 pt-3 border-t border-slate-100 space-y-3 text-xs">
                {{-- Membership Revenue --}}
                <div class="p-2.5 rounded-xl bg-blue-50/70 border border-blue-100">
                    <div class="flex items-center justify-between mb-1">
                        <span class="font-bold text-blue-950 flex items-center gap-1.5">
                            <span class="w-2 h-2 rounded-full bg-blue-500"></span>
                            Pendapatan Membership
                        </span>
                        <span class="text-[10px] text-blue-700 font-semibold bg-blue-100/80 px-1.5 py-0.5 rounded">
                            {{ $membershipTransactionsCount }} Transaksi
                        </span>
                    </div>
                    <div class="flex justify-between items-baseline">
                        <span class="text-slate-500 text-[11px]">Total Omzet Jasa:</span>
                        <strong class="font-mono font-bold text-slate-900">Rp{{ number_format($revenueThisMonth, 0, ',', '.') }}</strong>
                    </div>
                </div>

                {{-- Store / POS Revenue & Profit --}}
                <div class="p-2.5 rounded-xl bg-emerald-50/70 border border-emerald-100">
                    <div class="flex items-center justify-between mb-1">
                        <span class="font-bold text-emerald-950 flex items-center gap-1.5">
                            <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
                            Penjualan Produk (Toko & POS)
                        </span>
                        <span class="text-[10px] text-emerald-700 font-semibold bg-emerald-100/80 px-1.5 py-0.5 rounded">
                            {{ $storeOrdersCount }} Transaksi
                        </span>
                    </div>
                    <div class="flex justify-between items-baseline mb-1">
                        <span class="text-slate-500 text-[11px]">Omzet Penjualan:</span>
                        <strong class="font-mono font-bold text-slate-900">Rp{{ number_format($storeRevenueThisMonth, 0, ',', '.') }}</strong>
                    </div>
                    <div class="flex justify-between items-baseline pt-1 border-t border-emerald-200/60">
                        <span class="text-emerald-800 font-semibold text-[11px]">Laba Bersih Toko ({{ $storeProfitMargin }}%):</span>
                        <strong class="font-mono font-bold text-emerald-700">+Rp{{ number_format($storeProfitThisMonth, 0, ',', '.') }}</strong>
                    </div>
                </div>
            </div>
        </div>

        <div class="pt-4 grid grid-cols-2 gap-2">
            <a href="{{ route('admin.pos.index') }}" class="flex items-center justify-center gap-1.5 rounded-xl bg-lime-500 text-slate-950 text-xs font-bold py-2.5 hover:bg-lime-400 transition shadow-sm">
                <span>Kasir POS</span>
            </a>
            <a href="{{ route('admin.orders.index') }}" class="flex items-center justify-center gap-1.5 rounded-xl bg-slate-900 text-white text-xs font-bold py-2.5 hover:bg-slate-800 transition shadow-sm">
                <span>Riwayat Toko</span>
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
        <div id="recent-checkins-container" class="space-y-3">
            @forelse ($recentCheckins as $a)
                <div class="flex items-center gap-3 text-sm p-2 rounded-xl hover:bg-slate-50 transition">
                    @if ($a->member?->photo)
                        <img src="{{ route('admin.members.photo', $a->member) }}" alt="{{ $a->member->user->name }}" class="w-9 h-9 rounded-full object-cover shrink-0" onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';">
                        <div class="w-9 h-9 rounded-full bg-lime-100 text-lime-800 hidden items-center justify-center font-bold text-sm shrink-0">
                            {{ strtoupper(substr($a->member->user->name ?? '?', 0, 1)) }}
                        </div>
                    @else
                        <div class="w-9 h-9 rounded-full bg-lime-100 text-lime-800 flex items-center justify-center font-bold text-sm shrink-0">
                            {{ strtoupper(substr($a->member->user->name ?? '?', 0, 1)) }}
                        </div>
                    @endif
                    <div class="min-w-0 flex-1">
                        <p class="text-slate-900 font-semibold truncate">{{ $a->member->user->name }}</p>
                        <p class="text-xs text-slate-400 font-mono">{{ $a->member->member_code }}</p>
                    </div>
                    <span class="text-xs font-semibold text-slate-600 bg-slate-100 px-2 py-1 rounded-md shrink-0">
                        {{ $a->check_in_time?->format('H:i') ?? '-' }}
                    </span>
                </div>
            @empty
                <p class="text-sm text-slate-400 py-4 text-center">Belum ada aktivitas check-in hari ini.</p>
            @endforelse
        </div>
    </div>

    <div class="bg-white border border-slate-200 rounded-2xl p-5 lg:p-6 shadow-sm">
        <h2 class="font-display font-bold text-base text-slate-900 mb-4">Member Baru</h2>
        <div class="space-y-3">
            @forelse ($recentMembers as $m)
                <div class="flex items-center justify-between text-sm p-2 rounded-xl hover:bg-slate-50 transition">
                    <div class="flex items-center gap-3 min-w-0">
                        @if ($m->photo)
                            <img src="{{ route('admin.members.photo', $m) }}" alt="{{ $m->user->name }}" class="w-9 h-9 rounded-full object-cover shrink-0" onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';">
                            <div class="w-9 h-9 rounded-full bg-slate-100 text-slate-700 hidden items-center justify-center font-bold text-sm shrink-0">
                                {{ strtoupper(substr($m->user->name, 0, 1)) }}
                            </div>
                        @else
                            <div class="w-9 h-9 rounded-full bg-slate-100 text-slate-700 flex items-center justify-center font-bold text-sm shrink-0">
                                {{ strtoupper(substr($m->user->name, 0, 1)) }}
                            </div>
                        @endif
                        <div class="min-w-0">
                            <p class="text-slate-900 font-semibold truncate">{{ $m->user->name }}</p>
                            <p class="text-xs text-slate-400 font-mono">{{ $m->member_code }}</p>
                        </div>
                    </div>
                    <span class="text-xs font-bold px-2.5 py-0.5 rounded-full {{ $m->status === 'active' ? 'bg-emerald-100 text-emerald-800' : 'bg-slate-100 text-slate-600' }} shrink-0">
                        {{ ucfirst($m->status) }}
                    </span>
                </div>
            @empty
                <p class="text-sm text-slate-400 py-4 text-center">Belum ada member terdaftar.</p>
            @endforelse
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', () => {
    const emptyState = document.getElementById('rfid-empty-state');
    const warningState = document.getElementById('rfid-warning-state');
    const checkinData = document.getElementById('rfid-checkin-data');

    const warningTitle = document.getElementById('rfid-warning-title');
    const warningDetail = document.getElementById('rfid-warning-detail');

    const memberName = document.getElementById('rfid-member-name');
    const memberCode = document.getElementById('rfid-member-code');
    const memberUid = document.getElementById('rfid-member-uid');
    const memberTime = document.getElementById('rfid-member-time');
    const memberCheckoutTime = document.getElementById('rfid-member-checkout-time');
    const memberDate = document.getElementById('rfid-member-date');
    const memberPhoto = document.getElementById('rfid-member-photo');
    const memberInitial = document.getElementById('rfid-member-initial');
    const memberPhotoWrapper = document.getElementById('rfid-member-photo-wrapper');
    const packageBadge = document.getElementById('rfid-package-badge');
    const memberValidity = document.getElementById('rfid-member-validity');
    const statusBadge = document.getElementById('rfid-status-badge');
    const statusDot = document.getElementById('rfid-status-dot');
    const statusText = document.getElementById('rfid-status-text');
    const sessionBox = document.getElementById('rfid-session-box');
    const sessionDurationEl = document.getElementById('rfid-session-duration');

    const liveIndicator = document.getElementById('rfid-live-indicator');
    const liveDot = document.getElementById('rfid-live-dot');

    const recentCheckinsContainer = document.getElementById('recent-checkins-container');
    const todayCheckinsStat = document.getElementById('stat-today-checkins');
    let lastCheckinsSignature = '';

    const themePresets = {
        vip: {
            cardClasses: 'p-5 sm:p-6 lg:p-7 rounded-2xl bg-gradient-to-br from-white via-slate-50/70 to-purple-50/20 border border-purple-200/90 transition-all duration-500 relative overflow-hidden shadow-sm hover:shadow-md',
            photoWrapperClasses: 'w-20 h-20 sm:w-24 sm:h-24 md:w-28 md:h-28 rounded-2xl overflow-hidden bg-slate-100 ring-4 ring-offset-2 ring-purple-300/60 border border-purple-300 flex items-center justify-center text-purple-900 shadow-sm transition-all duration-300 group hover:scale-105',
            badgeClasses: 'text-[11px] sm:text-xs font-bold px-3 py-0.5 rounded-full transition-all duration-300 inline-flex items-center bg-purple-100 text-purple-900 border border-purple-300/80 shadow-2xs uppercase tracking-wide',
            strokeColor: '#a855f7',
        },
        gold: {
            cardClasses: 'p-5 sm:p-6 lg:p-7 rounded-2xl bg-gradient-to-br from-white via-slate-50/70 to-amber-50/20 border border-amber-200/90 transition-all duration-500 relative overflow-hidden shadow-sm hover:shadow-md',
            photoWrapperClasses: 'w-20 h-20 sm:w-24 sm:h-24 md:w-28 md:h-28 rounded-2xl overflow-hidden bg-slate-100 ring-4 ring-offset-2 ring-amber-300/60 border border-amber-300 flex items-center justify-center text-amber-900 shadow-sm transition-all duration-300 group hover:scale-105',
            badgeClasses: 'text-[11px] sm:text-xs font-bold px-3 py-0.5 rounded-full transition-all duration-300 inline-flex items-center bg-amber-100 text-amber-900 border border-amber-300/80 shadow-2xs uppercase tracking-wide',
            strokeColor: '#f59e0b',
        },
        silver: {
            cardClasses: 'p-5 sm:p-6 lg:p-7 rounded-2xl bg-gradient-to-br from-white via-slate-50/70 to-cyan-50/20 border border-cyan-200/90 transition-all duration-500 relative overflow-hidden shadow-sm hover:shadow-md',
            photoWrapperClasses: 'w-20 h-20 sm:w-24 sm:h-24 md:w-28 md:h-28 rounded-2xl overflow-hidden bg-slate-100 ring-4 ring-offset-2 ring-cyan-300/60 border border-cyan-300 flex items-center justify-center text-cyan-900 shadow-sm transition-all duration-300 group hover:scale-105',
            badgeClasses: 'text-[11px] sm:text-xs font-bold px-3 py-0.5 rounded-full transition-all duration-300 inline-flex items-center bg-cyan-100 text-cyan-900 border border-cyan-300/80 shadow-2xs uppercase tracking-wide',
            strokeColor: '#06b6d4',
        },
        student: {
            cardClasses: 'p-5 sm:p-6 lg:p-7 rounded-2xl bg-gradient-to-br from-white via-slate-50/70 to-rose-50/20 border border-rose-200/90 transition-all duration-500 relative overflow-hidden shadow-sm hover:shadow-md',
            photoWrapperClasses: 'w-20 h-20 sm:w-24 sm:h-24 md:w-28 md:h-28 rounded-2xl overflow-hidden bg-slate-100 ring-4 ring-offset-2 ring-rose-300/60 border border-rose-300 flex items-center justify-center text-rose-900 shadow-sm transition-all duration-300 group hover:scale-105',
            badgeClasses: 'text-[11px] sm:text-xs font-bold px-3 py-0.5 rounded-full transition-all duration-300 inline-flex items-center bg-rose-100 text-rose-900 border border-rose-300/80 shadow-2xs uppercase tracking-wide',
            strokeColor: '#f43f5e',
        },
        basic: {
            cardClasses: 'p-5 sm:p-6 lg:p-7 rounded-2xl bg-gradient-to-br from-white via-slate-50/70 to-lime-50/20 border border-lime-200/90 transition-all duration-500 relative overflow-hidden shadow-sm hover:shadow-md',
            photoWrapperClasses: 'w-20 h-20 sm:w-24 sm:h-24 md:w-28 md:h-28 rounded-2xl overflow-hidden bg-slate-100 ring-4 ring-offset-2 ring-lime-300/60 border border-lime-300 flex items-center justify-center text-lime-900 shadow-sm transition-all duration-300 group hover:scale-105',
            badgeClasses: 'text-[11px] sm:text-xs font-bold px-3 py-0.5 rounded-full transition-all duration-300 inline-flex items-center bg-lime-100 text-lime-900 border border-lime-300/80 shadow-2xs uppercase tracking-wide',
            strokeColor: '#84cc16',
        }
    };

    function escapeHtml(str) {
        if (!str) return '';
        return String(str)
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#039;');
    }

    function updateRecentCheckins(items) {
        if (!recentCheckinsContainer || !Array.isArray(items)) return;

        const currentSignature = items.map(i => `${i.id}_${i.time}_${i.checkout_time || ''}`).join('|');
        if (currentSignature === lastCheckinsSignature) return;
        lastCheckinsSignature = currentSignature;

        if (items.length === 0) {
            recentCheckinsContainer.innerHTML = '<p class="text-sm text-slate-400 py-4 text-center">Belum ada aktivitas check-in hari ini.</p>';
            return;
        }

        recentCheckinsContainer.innerHTML = items.map(item => {
            const photoEl = item.photo
                ? `<img src="${escapeHtml(item.photo)}" alt="${escapeHtml(item.name)}" class="w-9 h-9 rounded-full object-cover shrink-0" onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';">
                   <div class="w-9 h-9 rounded-full bg-lime-100 text-lime-800 hidden items-center justify-center font-bold text-sm shrink-0">${escapeHtml(item.initial)}</div>`
                : `<div class="w-9 h-9 rounded-full bg-lime-100 text-lime-800 flex items-center justify-center font-bold text-sm shrink-0">${escapeHtml(item.initial)}</div>`;

            return `
                <div class="flex items-center gap-3 text-sm p-2 rounded-xl hover:bg-slate-50 transition">
                    ${photoEl}
                    <div class="min-w-0 flex-1">
                        <p class="text-slate-900 font-semibold truncate">${escapeHtml(item.name)}</p>
                        <p class="text-xs text-slate-400 font-mono">${escapeHtml(item.member_code)}</p>
                    </div>
                    <span class="text-xs font-semibold text-slate-600 bg-slate-100 px-2 py-1 rounded-md shrink-0">
                        ${escapeHtml(item.time)}
                    </span>
                </div>
            `;
        }).join('');
    }

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
        if (document.hidden) return;

        try {
            const response = await fetch('{{ route('admin.dashboard.latest-rfid-checkin') }}', {
                headers: { 'Accept': 'application/json' }
            });

            if (!response.ok) throw new Error('Gagal mengambil data check-in.');
            const data = await response.json();

            // Selalu perbarui riwayat check-in & hitungan hari ini secara live
            if (data.recent_checkins) {
                updateRecentCheckins(data.recent_checkins);
            }
            if (data.today_checkins !== undefined && todayCheckinsStat) {
                todayCheckinsStat.textContent = data.today_checkins;
            }

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

            // Paket & Tema Dinamis
            const currentTheme = themePresets[data.package_theme] || themePresets.basic;
            checkinData.className = currentTheme.cardClasses;
            memberPhotoWrapper.className = currentTheme.photoWrapperClasses;

            // Update warna SVG contour waves sesuai warna aksen tema
            const topoPaths = document.querySelectorAll('.rfid-topo-path');
            topoPaths.forEach(el => {
                el.setAttribute('stroke', currentTheme.strokeColor || '#84cc16');
            });

            if (packageBadge) {
                packageBadge.className = currentTheme.badgeClasses;
                packageBadge.textContent = data.package_name || 'Standard';
            }

            // Info Masa Aktif
            if (memberValidity) {
                if (data.days_remaining !== null && data.days_remaining !== undefined) {
                    if (data.days_remaining < 0) {
                        memberValidity.textContent = 'Masa aktif berakhir';
                        memberValidity.className = 'text-rose-600 font-semibold';
                    } else if (data.days_remaining === 0) {
                        memberValidity.textContent = 'Berakhir Hari Ini';
                        memberValidity.className = 'text-amber-600 font-semibold';
                    } else {
                        memberValidity.textContent = `Sisa ${data.days_remaining} Hari Aktif`;
                        memberValidity.className = 'text-slate-600 font-medium';
                    }
                } else {
                    memberValidity.textContent = 'Membership Aktif';
                    memberValidity.className = 'text-slate-600 font-medium';
                }
            }

            // Status Check-in vs Check-out Visual
            if (data.action === 'checkout') {
                statusBadge.className = 'inline-flex items-center gap-2 px-4 py-1.5 sm:py-2 rounded-full font-bold text-xs sm:text-sm bg-sky-500 text-white shadow-sm shadow-sky-500/20 transition-all duration-300';
                statusDot.className = 'w-2 h-2 rounded-full bg-white';
                statusText.textContent = 'Check-out berhasil';

                if (data.session_duration && sessionBox && sessionDurationEl) {
                    sessionBox.classList.remove('hidden');
                    sessionDurationEl.textContent = data.session_duration;
                } else if (sessionBox) {
                    sessionBox.classList.add('hidden');
                }
            } else {
                statusBadge.className = 'inline-flex items-center gap-2 px-4 py-1.5 sm:py-2 rounded-full font-bold text-xs sm:text-sm bg-emerald-500 text-white shadow-sm shadow-emerald-500/20 transition-all duration-300';
                statusDot.className = 'w-2 h-2 rounded-full bg-white animate-pulse';
                statusText.textContent = 'Check-in berhasil';

                if (sessionBox) {
                    sessionBox.classList.add('hidden');
                }
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

    // Polling setiap 500ms agar kartu yang di-tap langsung muncul di dashboard
    setInterval(checkLatestRfidCheckin, 500);
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