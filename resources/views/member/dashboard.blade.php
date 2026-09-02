@extends('layouts.member')
@section('title', 'Dashboard Saya')

@section('content')
@php
    $days = $member->daysRemaining();
    $expiringSoon = $member->isExpiringSoon();
@endphp

<!-- Digital membership card -->
<div class="rounded-3xl p-6 bg-gradient-to-br from-[#1C2029] to-[#12141A] border border-base-line relative overflow-hidden mb-6">
    <div class="absolute -right-10 -top-10 w-40 h-40 rounded-full bg-volt/10 blur-2xl"></div>
    <div class="flex items-start justify-between relative">
        <div>
            <p class="text-xs text-slate-400">Member GymPulse</p>
            <h2 class="font-display font-bold text-2xl text-white mt-1">{{ $member->user->name }}</h2>
            <p class="text-sm text-slate-400 mt-0.5 font-mono">{{ $member->member_code }}</p>
        </div>
        <span class="text-xs px-3 py-1.5 rounded-full shrink-0 {{ $member->status=='active' ? 'bg-volt/10 text-volt' : 'bg-coral/10 text-coral' }}">
            {{ ucfirst($member->status) }}
        </span>
    </div>

    <div class="mt-6 flex items-end justify-between relative">
        <div>
            <p class="text-xs text-slate-500">Paket</p>
            <p class="text-white font-medium">{{ $member->package->name ?? '-' }}</p>
        </div>
        <div class="text-right">
            <p class="text-xs text-slate-500">RFID UID</p>
            <p class="text-white font-mono">{{ $member->rfidCard->uid ?? 'Belum ditautkan' }}</p>
        </div>
    </div>
</div>

<!-- Countdown -->
<div class="grid grid-cols-2 gap-4 mb-6">
    <div class="bg-base-card border border-base-line rounded-2xl p-5">
        <p class="text-xs text-slate-500 mb-1">Sisa Masa Aktif</p>
        <p class="font-display text-3xl font-bold {{ $expiringSoon ? 'text-coral' : 'text-volt' }}">{{ $days !== null ? max($days,0) : '-' }}<span class="text-base text-slate-500 font-sans"> hari</span></p>
        @if ($expiringSoon)
            <p class="text-xs text-coral mt-1">Segera perpanjang di kasir!</p>
        @else
            <p class="text-xs text-slate-500 mt-1">Berakhir {{ optional($member->expire_date)->translatedFormat('d M Y') }}</p>
        @endif
    </div>
    <div class="bg-base-card border border-base-line rounded-2xl p-5">
        <p class="text-xs text-slate-500 mb-1">Kunjungan Bulan Ini</p>
        <p class="font-display text-3xl font-bold text-white">{{ $visitsThisMonth }}</p>
        <p class="text-xs text-slate-500 mt-1">Total keseluruhan: {{ $totalVisits }}x</p>
    </div>
</div>

<!-- History -->
<div class="bg-base-card border border-base-line rounded-2xl p-5">
    <h3 class="font-display font-semibold text-white mb-4">Riwayat Check-in</h3>
    <div class="space-y-3">
        @forelse ($attendances as $a)
            <div class="flex items-center gap-3 text-sm">
                <div class="w-9 h-9 rounded-full bg-volt/10 text-volt flex items-center justify-center shrink-0">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                </div>
                <div class="flex-1">
                    <p class="text-white">{{ $a->check_in_at->translatedFormat('l, d M Y') }}</p>
                    <p class="text-xs text-slate-500">via {{ $a->method === 'rfid' ? 'Kartu RFID' : 'Manual' }}</p>
                </div>
                <span class="text-sm text-slate-400 shrink-0">{{ $a->check_in_at->format('H:i') }}</span>
            </div>
        @empty
            <p class="text-sm text-slate-500 text-center py-6">Belum ada riwayat check-in. Tempelkan kartu RFID kamu di pintu masuk gym!</p>
        @endforelse
    </div>
</div>
@endsection
