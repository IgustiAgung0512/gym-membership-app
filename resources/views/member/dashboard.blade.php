@extends('layouts.member')
@section('title', 'Dashboard Saya')

@section('content')
@php
    $days = $member->daysRemaining();
    $expiringSoon = $member->isExpiringSoon();
    $currentPhotoUrl = $member->photo ? route('member.photo.show') . '?v=' . ($member->updated_at?->timestamp) : null;
@endphp

@if (session('success'))
    <div class="mb-4 rounded-xl border border-volt/30 bg-volt/10 px-4 py-3 text-sm text-volt">
        {{ session('success') }}
    </div>
@endif

@if ($errors->any())
    <div class="mb-4 rounded-xl border border-coral/30 bg-coral/10 px-4 py-3 text-sm text-coral">
        {{ $errors->first() }}
    </div>
@endif

<!-- Digital membership card -->
<div class="rounded-3xl p-6 bg-gradient-to-br from-[#1C2029] to-[#12141A] border border-base-line relative overflow-hidden mb-6">
    <div class="absolute -right-10 -top-10 w-40 h-40 rounded-full bg-volt/10 blur-2xl"></div>
    <div class="flex items-start justify-between relative">
        <div class="flex items-center gap-4">
            <div
                x-data="{
                    preview: null,
                    hasFile: false,
                    fallback: '{{ $currentPhotoUrl }}',
                    setFile(input) {
                        const file = input.files && input.files[0];
                        if (!file) { this.hasFile = false; this.preview = null; return; }
                        this.hasFile = true;
                        this.preview = URL.createObjectURL(file);
                    }
                }"
                class="relative shrink-0"
            >
                <form method="POST" action="{{ route('member.photo.update') }}" enctype="multipart/form-data" x-ref="photoForm" class="contents">
                    @csrf
                    <label for="photo-input" class="block w-16 h-16 rounded-2xl overflow-hidden border border-base-line bg-base cursor-pointer group relative">
                        <template x-if="preview">
                            <img :src="preview" class="w-full h-full object-cover">
                        </template>
                        <template x-if="!preview">
                            @if ($currentPhotoUrl)
                                <img src="{{ $currentPhotoUrl }}" alt="Foto {{ $member->user->name }}" class="w-full h-full object-cover">
                            @else
                                <div class="w-full h-full flex items-center justify-center bg-base-card text-slate-500 font-display font-bold text-lg">
                                    {{ strtoupper(substr($member->user->name, 0, 1)) }}
                                </div>
                            @endif
                        </template>
                        <div class="absolute inset-0 bg-black/50 opacity-0 group-hover:opacity-100 flex items-center justify-center transition-opacity">
                            <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"/>
                                <path stroke-linecap="round" stroke-linejoin="round" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z"/>
                            </svg>
                        </div>
                    </label>
                    <input id="photo-input" type="file" name="photo" accept="image/png,image/jpeg,image/webp" class="hidden" @change="setFile($event.target)">

                    <div x-show="hasFile" x-cloak class="absolute top-full left-0 mt-2 flex items-center gap-2 z-10 whitespace-nowrap">
                        <button type="submit" class="text-xs px-3 py-1.5 rounded-full bg-volt text-base font-semibold hover:brightness-95 transition">
                            Simpan Foto
                        </button>
                        <button type="button" @click="hasFile = false; preview = null; $refs.photoForm.querySelector('#photo-input').value = ''"
                                class="text-xs px-3 py-1.5 rounded-full border border-base-line text-slate-400 hover:text-coral hover:border-coral/40 transition-colors">
                            Batal
                        </button>
                    </div>
                </form>
            </div>
            <div>
                <p class="text-xs text-slate-400">Member GymPulse</p>
                <h2 class="font-display font-bold text-2xl text-white mt-1">{{ $member->user->name }}</h2>
                <p class="text-sm text-slate-400 mt-0.5 font-mono">{{ $member->member_code }}</p>
            </div>
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
