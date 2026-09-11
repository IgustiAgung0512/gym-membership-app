@extends('layouts.member')
@section('title', 'Dashboard Member')

@section('content')
@php
    $days = $member->daysRemaining();
    $expiringSoon = $member->isExpiringSoon();
    $currentPhotoUrl = $member->photo ? route('member.photo.show') . '?v=' . ($member->updated_at?->timestamp) : null;
    
    // Target latihan bulanan (12 sesi)
    $targetVisits = 12;
    $progressPercent = min(round(($visitsThisMonth / $targetVisits) * 100), 100);

    // Status keramaian berdasarkan jam
    $currentHour = (int) now()->format('H');
    if ($currentHour >= 6 && $currentHour < 11) {
        $crowdLabel = 'Sepi & Segar';
        $crowdDesc = 'Waktu terbaik untuk latihan bebas antrean alat.';
        $crowdBadge = 'bg-emerald-100 text-emerald-800 border-emerald-200';
        $crowdDot = 'bg-emerald-500';
    } elseif ($currentHour >= 11 && $currentHour < 16) {
        $crowdLabel = 'Lancar & Nyaman';
        $crowdDesc = 'Kapasitas gym optimal untuk fokus program latihan.';
        $crowdBadge = 'bg-emerald-100 text-emerald-800 border-emerald-200';
        $crowdDot = 'bg-emerald-500';
    } elseif ($currentHour >= 16 && $currentHour < 17) {
        $crowdLabel = 'Mulai Ramai';
        $crowdDesc = 'Member mulai berdatangan sepulang kerja/aktivitas.';
        $crowdBadge = 'bg-amber-100 text-amber-800 border-amber-200';
        $crowdDot = 'bg-amber-500';
    } elseif ($currentHour >= 17 && $currentHour < 20) {
        $crowdLabel = 'Jam Sibuk (Peak Hours)';
        $crowdDesc = 'Kapasitas padat, bersiap berbagi alat dengan member lain.';
        $crowdBadge = 'bg-rose-100 text-rose-800 border-rose-200';
        $crowdDot = 'bg-rose-500';
    } else {
        $crowdLabel = 'Lengang & Tenang';
        $crowdDesc = 'Suasana santai menjelang jam operasional tutup (22:00 WIB).';
        $crowdBadge = 'bg-emerald-100 text-emerald-800 border-emerald-200';
        $crowdDot = 'bg-emerald-500';
    }

    // Split latihan harian
    $dayOfWeek = now()->dayOfWeek;
    $dailySplits = [
        1 => ['title' => 'Senin: Chest & Triceps', 'focus' => 'Bench Press, Incline DB Press, Dips, Tricep Pushdown', 'nutrition' => 'Minum 2.5L air & 25-30g protein setelah latihan.'],
        2 => ['title' => 'Selasa: Back & Biceps', 'focus' => 'Lat Pulldown, Seated Cable Row, Face Pull, Bicep Curl', 'nutrition' => 'Konsumsi karbohidrat kompleks (oat/nasi merah) sebelum latihan.'],
        3 => ['title' => 'Rabu: Leg Day & Quads', 'focus' => 'Barbell Squat, Romanian Deadlift, Leg Press, Calf Raise', 'nutrition' => 'Pemanasan 10 menit & asupan kalium dari pisang.'],
        4 => ['title' => 'Kamis: Shoulder & Core', 'focus' => 'Overhead Press, Dumbbell Lateral Raise, Plank, Leg Raise', 'nutrition' => 'Jaga postur leher & hidrasi cukup saat istirahat set.'],
        5 => ['title' => 'Jumat: Full Body HIIT', 'focus' => 'Kettlebell Swing, Battle Rope, Sled Push, Burpee', 'nutrition' => 'Peregangan dinamis & istirahat tidur malam 7-8 jam.'],
        6 => ['title' => 'Sabtu: Functional & Abs', 'focus' => 'Foam Rolling, Mobility Dynamic, Light Cardio, Hanging Knee Raise', 'nutrition' => 'Konsumsi buah kaya antioksidan untuk pemulihan otot.'],
        0 => ['title' => 'Minggu: Active Recovery', 'focus' => 'Jalan santai 30 menit atau berenang ringan untuk relaksasi tubuh.', 'nutrition' => 'Fokus relaksasi otot & persiapan sesi minggu depan.']
    ];
    $todayWorkout = $dailySplits[$dayOfWeek] ?? $dailySplits[1];

    $whatsappRenewUrl = "https://wa.me/?text=" . urlencode("Halo Admin GymPulse, saya ingin memperpanjang membership atas nama " . $member->user->name . " (Kode: " . $member->member_code . ", Paket: " . ($member->package->name ?? '-') . "). Mohon info langkah selanjutnya. Terima kasih!");
@endphp

<div x-data="{ 
    invoiceModal: false, 
    renewModal: false,
    renewStep: 'select', // 'select', 'qris', 'success'
    selectedPackageId: '{{ $packages->first()->id ?? '' }}',
    selectedPackagePrice: {{ $packages->first()->price ?? 150000 }},
    selectedPackageName: '{{ $packages->first()->name ?? '' }}',
    selectedPackageDuration: {{ $packages->first()->duration_months ?? 1 }},
    onlinePaymentMethod: 'qris',
    packagesList: {{ Js::from($packages) }},
    copiedBank: null,

    // Renewal QRIS & Checkout state
    loadingRenewal: false,
    renewErrorMessage: '',
    renewPayment: null,
    renewQrisData: null,
    renewNewExpirePreview: '',
    renewTimer: 300,
    renewTimerInterval: null,
    renewPollingInterval: null,
    renewSimulating: false,
    renewSuccessData: null,

    setPackage(id, price, name, duration = 1) {
        this.selectedPackageId = id;
        this.selectedPackagePrice = price;
        this.selectedPackageName = name;
        this.selectedPackageDuration = duration;
    },

    openRenewModal() {
        this.renewStep = 'select';
        this.renewErrorMessage = '';
        this.renewModal = true;
    },

    get renewFormattedTimer() {
        const minutes = Math.floor(this.renewTimer / 60);
        const seconds = this.renewTimer % 60;
        return `${String(minutes).padStart(2, '0')}:${String(seconds).padStart(2, '0')}`;
    },

    copyBank(accNumber, key) {
        if (navigator.clipboard) {
            navigator.clipboard.writeText(accNumber);
        }
        this.copiedBank = key;
        setTimeout(() => { this.copiedBank = null; }, 2000);
    },

    async submitRenewalCheckout() {
        if (!this.selectedPackageId) return;

        this.loadingRenewal = true;
        this.renewErrorMessage = '';

        try {
            const response = await fetch('{{ route('member.renew.initiate') }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json',
                },
                body: JSON.stringify({
                    membership_package_id: this.selectedPackageId,
                    payment_method: this.onlinePaymentMethod,
                })
            });

            const data = await response.json();

            if (!response.ok || !data.success) {
                this.renewErrorMessage = data.message || 'Gagal membuat tagihan perpanjangan.';
                this.loadingRenewal = false;
                return;
            }

            this.renewPayment = data.payment;
            this.renewQrisData = data.qris;
            this.renewNewExpirePreview = data.new_expire_preview;

            if (this.onlinePaymentMethod === 'qris') {
                this.startQrisFlow(data);
            } else {
                this.handleRenewalSuccess(data);
            }
        } catch (err) {
            this.renewErrorMessage = 'Gagal menghubungi server. Periksa koneksi internet Anda.';
        } finally {
            this.loadingRenewal = false;
        }
    },

    startQrisFlow(data) {
        this.renewStep = 'qris';
        this.renewTimer = 300;
        this.renewSimulating = false;

        // Timer countdown
        clearInterval(this.renewTimerInterval);
        this.renewTimerInterval = setInterval(() => {
            if (this.renewTimer > 0) {
                this.renewTimer--;
            } else {
                this.cancelRenewalOrder(true);
            }
        }, 1000);

        // Status polling
        clearInterval(this.renewPollingInterval);
        this.renewPollingInterval = setInterval(() => {
            this.checkRenewalStatus(data.status_url);
        }, 2500);
    },

    async checkRenewalStatus(statusUrl) {
        if (this.renewStep !== 'qris' || !this.renewModal) return;

        try {
            const res = await fetch(statusUrl, {
                headers: { 'Accept': 'application/json' }
            });
            const data = await res.json();

            if (data.success && data.is_paid) {
                this.handleRenewalSuccess(data);
            }
        } catch (e) {}
    },

    async simulateRenewalPayment() {
        if (!this.renewPayment || this.renewSimulating) return;

        this.renewSimulating = true;

        try {
            const res = await fetch('{{ url('/member/renew') }}/' + this.renewPayment.id + '/simulate', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json',
                }
            });

            const data = await res.json();
            if (data.success) {
                this.handleRenewalSuccess(data);
            } else {
                alert(data.message || 'Gagal memproses simulasi');
            }
        } catch (err) {
            alert('Gagal menghubungi server');
        } finally {
            this.renewSimulating = false;
        }
    },

    async cancelRenewalOrder(isExpired = false) {
        if (!this.renewPayment) {
            this.closeRenewalModal();
            return;
        }

        if (!isExpired && !confirm('Apakah Anda yakin ingin membatalkan transaksi perpanjangan ini?')) {
            return;
        }

        try {
            await fetch('{{ url('/member/renew') }}/' + this.renewPayment.id + '/cancel', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json',
                }
            });
        } catch (e) {}

        this.closeRenewalModal();
        if (isExpired) {
            alert('Waktu pembayaran QRIS telah habis. Transaksi dibatalkan.');
        }
    },

    handleRenewalSuccess(data) {
        clearInterval(this.renewTimerInterval);
        clearInterval(this.renewPollingInterval);
        this.renewSuccessData = data;
        this.renewStep = 'success';
    },

    closeRenewalModal() {
        clearInterval(this.renewTimerInterval);
        clearInterval(this.renewPollingInterval);
        this.renewModal = false;
        this.renewStep = 'select';
        this.renewPayment = null;
        this.renewQrisData = null;
    },

    finishAndReload() {
        this.closeRenewalModal();
        window.location.reload();
    },

    classTab: 'today',
    nutritionTab: 'tips',
    nutritionCategory: 'all',
    weight: 65,
    dietGoal: 'fatloss',
    calcCalories() {
        let base = Number(this.weight) * 24 * 1.35;
        if (this.dietGoal === 'fatloss') return Math.round(base - 400);
        if (this.dietGoal === 'muscle') return Math.round(base + 350);
        return Math.round(base);
    },
    calcProtein() {
        let mult = this.dietGoal === 'muscle' ? 1.8 : (this.dietGoal === 'fatloss' ? 1.6 : 1.4);
        return Math.round(Number(this.weight) * mult);
    },
    calcWater() {
        return (Number(this.weight) * 0.04).toFixed(1);
    },
    calcEggs() {
        return Math.round(this.calcProtein() / 6);
    },
    calcChicken() {
        return Math.round((this.calcProtein() / 31) * 100);
    }
}" class="space-y-6">

    <div class="print-hide-on-modal space-y-6">
        {{-- FLASH MESSAGES --}}
    @if (session('success'))
        <div class="rounded-2xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800 flex items-center gap-2 shadow-sm">
            <svg class="w-4 h-4 shrink-0 text-emerald-600" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
            </svg>
            <span class="font-medium">{{ session('success') }}</span>
        </div>
    @endif

    @if ($errors->any())
        <div class="rounded-2xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-800 flex items-center gap-2 shadow-sm">
            <svg class="w-4 h-4 shrink-0 text-rose-600" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
            </svg>
            <span class="font-medium">{{ $errors->first() }}</span>
        </div>
    @endif

    {{-- EXPIRING SOON / EXPIRED ALERT BANNER --}}
    @if ($expiringSoon || $member->status !== 'active')
        <div class="rounded-3xl p-5 sm:p-6 {{ $member->status === 'active' ? 'bg-gradient-to-r from-amber-500/15 via-amber-50 to-orange-50 border-2 border-amber-300' : 'bg-gradient-to-r from-rose-500/15 via-rose-50 to-red-50 border-2 border-rose-300' }} shadow-md flex flex-col md:flex-row items-start md:items-center justify-between gap-4">
            <div class="flex items-start sm:items-center gap-3.5">
                <div class="w-12 h-12 rounded-2xl {{ $member->status === 'active' ? 'bg-amber-400 text-slate-950' : 'bg-rose-500 text-white' }} flex items-center justify-center font-black text-xl shrink-0 shadow-sm">
                    {{ $member->status === 'active' ? '⏳' : '⚠️' }}
                </div>
                <div>
                    <div class="flex items-center gap-2 mb-0.5">
                        <span class="text-[10px] font-extrabold uppercase tracking-wider px-2 py-0.5 rounded {{ $member->status === 'active' ? 'bg-amber-200/80 text-amber-900' : 'bg-rose-200/80 text-rose-900' }}">
                            {{ $member->status === 'active' ? 'Pemberitahuan Masa Aktif' : 'Peringatan Keanggotaan' }}
                        </span>
                    </div>
                    <h3 class="font-display font-extrabold text-base sm:text-lg text-slate-900">
                        @if ($member->status === 'active')
                            Masa Aktif Membership Segera Berakhir! (Sisa {{ $days }} Hari)
                        @else
                            Masa Aktif Membership Telah Berakhir!
                        @endif
                    </h3>
                    <p class="text-xs text-slate-600 mt-1 leading-relaxed">
                        Masa berlaku berakhir pada <strong class="text-slate-900">{{ optional($member->expire_date)->translatedFormat('d F Y') }}</strong>. Perpanjang sekarang secara online agar kartu RFID gate Anda tetap aktif.
                    </p>
                </div>
            </div>

            <button 
                type="button" 
                @click="openRenewModal()"
                class="inline-flex items-center gap-2 px-5 py-3 rounded-2xl bg-slate-900 hover:bg-slate-800 text-white text-xs font-display font-bold shadow-lg hover:shadow-xl transition whitespace-nowrap self-stretch md:self-auto justify-center"
            >
                <span class="text-lime-400 font-bold">⚡</span>
                <span>Perpanjang Online Sekarang</span>
                <svg class="w-4 h-4 text-lime-400" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
            </button>
        </div>
    @endif

    {{-- =========================================================
        1. DIGITAL MEMBER PASS CARD (EXECUTIVE RFID PASS)
    ========================================================= --}}
    <div class="rounded-3xl p-6 sm:p-7 bg-gradient-to-br from-slate-900 via-slate-850 to-slate-950 text-white border border-slate-800 shadow-xl relative overflow-hidden">
        
        {{-- Subtle Glow Ambient --}}
        <div class="absolute -right-12 -bottom-12 w-64 h-64 rounded-full bg-lime-500/10 blur-3xl pointer-events-none"></div>

        <div class="flex flex-col sm:flex-row sm:items-start justify-between gap-5 relative z-10">
            
            <div class="flex items-center gap-4">
                {{-- PHOTO AVATAR & UPLOAD TRIGGER --}}
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
                        <label for="photo-input" class="block w-20 h-20 rounded-2xl overflow-hidden border-2 border-slate-700 bg-slate-800 cursor-pointer group relative shadow-md" title="Klik untuk ganti foto profil">
                            <template x-if="preview">
                                <img :src="preview" class="w-full h-full object-cover">
                            </template>
                            <template x-if="!preview">
                                @if ($currentPhotoUrl)
                                    <img src="{{ $currentPhotoUrl }}" alt="Foto {{ $member->user->name }}" class="w-full h-full object-cover">
                                @else
                                    <div class="w-full h-full flex items-center justify-center bg-slate-800 text-lime-400 font-display font-bold text-2xl">
                                        {{ strtoupper(substr($member->user->name, 0, 1)) }}
                                    </div>
                                @endif
                            </template>
                            <div class="absolute inset-0 bg-slate-950/60 opacity-0 group-hover:opacity-100 flex flex-col items-center justify-center transition-opacity text-[10px] text-white">
                                <svg class="w-5 h-5 mb-0.5 text-lime-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"/>
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z"/>
                                </svg>
                                <span>Ubah</span>
                            </div>
                        </label>
                        <input id="photo-input" type="file" name="photo" accept="image/png,image/jpeg,image/webp" class="hidden" @change="setFile($event.target)">

                        <div x-show="hasFile" x-cloak class="absolute top-full left-0 mt-2 flex items-center gap-2 z-20 whitespace-nowrap bg-slate-900 p-1.5 rounded-xl border border-slate-700 shadow-xl">
                            <button type="submit" class="text-xs px-3 py-1.5 rounded-lg bg-lime-500 text-slate-950 font-bold hover:bg-lime-400 transition">
                                Simpan
                            </button>
                            <button type="button" @click="hasFile = false; preview = null; $refs.photoForm.querySelector('#photo-input').value = ''"
                                    class="text-xs px-3 py-1.5 rounded-lg bg-slate-800 text-slate-300 hover:text-white transition">
                                Batal
                            </button>
                        </div>
                    </form>
                </div>

                <div>
                    <span class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">MEMBER PASS</span>
                    <h2 class="font-display font-extrabold text-2xl sm:text-3xl text-white mt-0.5">{{ $member->user->name }}</h2>
                    <p class="text-xs text-lime-400 font-mono mt-1 font-bold">{{ $member->member_code }}</p>
                </div>
            </div>

            <div class="flex items-center gap-2 self-start sm:self-auto">
                <span class="text-xs font-bold px-3 py-1.5 rounded-full inline-flex items-center gap-1.5 {{ $member->status == 'active' ? 'bg-lime-500/20 text-lime-400 border border-lime-500/30' : 'bg-rose-500/20 text-rose-400 border border-rose-500/30' }}">
                    <span class="w-2 h-2 rounded-full {{ $member->status == 'active' ? 'bg-lime-400 animate-pulse' : 'bg-rose-400' }}"></span>
                    {{ strtoupper($member->status) }}
                </span>
            </div>

        </div>

        @php
            $latestAttendance = $attendances->first();
        @endphp
        {{-- Meta Info --}}
        <div class="mt-7 pt-5 border-t border-slate-800/80 grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-5 gap-4 text-xs">
            <div>
                <p class="text-slate-400 text-[11px] uppercase font-semibold tracking-wider">Paket Keanggotaan</p>
                <p class="text-white font-bold text-sm sm:text-base mt-0.5">{{ $member->package->name ?? '-' }}</p>
            </div>
            <div>
                <p class="text-slate-400 text-[11px] uppercase font-semibold tracking-wider">UID Kartu RFID</p>
                <p class="text-lime-400 font-mono font-bold text-sm sm:text-base mt-0.5">{{ $member->rfidCard->uid ?? 'Belum terhubung' }}</p>
            </div>
            <div>
                <p class="text-slate-400 text-[11px] uppercase font-semibold tracking-wider">Check-In</p>
                <p id="member-card-checkin" class="text-white font-bold text-sm sm:text-base mt-0.5">
                    @if ($latestAttendance)
                        {{ $latestAttendance->check_in_at->format('H:i') }} WIB
                        @if (!$latestAttendance->check_in_at->isToday())
                            <span class="text-[10px] text-slate-400 font-normal">({{ $latestAttendance->check_in_at->format('d/m') }})</span>
                        @endif
                    @else
                        <span class="text-slate-500 font-normal">-</span>
                    @endif
                </p>
            </div>
            <div>
                <p class="text-slate-400 text-[11px] uppercase font-semibold tracking-wider">Check-Out</p>
                <p id="member-card-checkout" class="text-white font-bold text-sm sm:text-base mt-0.5">
                    @if ($latestAttendance && $latestAttendance->check_out_at)
                        {{ $latestAttendance->check_out_at->format('H:i') }} WIB
                        @if (!$latestAttendance->check_out_at->isToday())
                            <span class="text-[10px] text-slate-400 font-normal">({{ $latestAttendance->check_out_at->format('d/m') }})</span>
                        @endif
                    @elseif ($latestAttendance && !$latestAttendance->check_out_at && $latestAttendance->check_in_at->isToday())
                        <span class="text-lime-400 font-bold text-xs inline-flex items-center gap-1">
                            <span class="w-1.5 h-1.5 rounded-full bg-lime-400 animate-pulse"></span>
                            Sedang Latihan
                        </span>
                    @else
                        <span class="text-slate-500 font-normal">-</span>
                    @endif
                </p>
            </div>
            <div class="col-span-2 sm:col-span-1 lg:text-right">
                <p class="text-slate-400 text-[11px] uppercase font-semibold tracking-wider">Masa Berlaku</p>
                <p class="text-white font-bold text-sm sm:text-base mt-0.5">{{ optional($member->expire_date)->translatedFormat('d M Y') }}</p>
            </div>
        </div>

        {{-- Action Bar --}}
        <div class="mt-6 pt-4 border-t border-slate-800/60 flex flex-wrap items-center justify-between gap-3">
            <div class="flex items-center gap-2 text-xs text-slate-400">
                <span class="w-2 h-2 rounded-full bg-lime-400"></span>
                <span>Tempelkan kartu fisik RFID Anda pada alat scanner gate saat tiba.</span>
            </div>

            <div class="flex items-center gap-2.5 ml-auto flex-wrap">
                <button @click="invoiceModal = true" class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-xl bg-slate-800 hover:bg-slate-750 text-slate-200 text-xs font-bold border border-slate-700 transition">
                    <svg class="w-4 h-4 text-blue-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                    <span>Bukti Keanggotaan</span>
                </button>

                <button @click="openRenewModal()" class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-xl bg-lime-500 hover:bg-lime-400 text-slate-950 text-xs font-bold transition shadow-sm">
                    <span class="text-sm">⚡</span>
                    <span>Perpanjang Online</span>
                </button>

                <a href="{{ $whatsappRenewUrl }}" target="_blank" class="inline-flex items-center gap-1.5 px-3 py-2 rounded-xl bg-slate-800 hover:bg-slate-700 text-slate-300 text-xs font-semibold border border-slate-700 transition">
                    <span>Chat WA</span>
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
                </a>
            </div>
        </div>
    </div>

    {{-- =========================================================
        2. DUA KOLOM DASHBOARD UTAMA (LEFT: STATS & TIMELINE, RIGHT: CROWD & CLASSES)
    ========================================================= --}}
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

        {{-- KOLOM KIRI (UTAMA - 2 COL SPAN) --}}
        <div class="lg:col-span-2 space-y-6">

            {{-- STATS & GOAL GRID --}}
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                
                {{-- Sisa Masa Aktif --}}
                <div class="bg-white border border-slate-200 rounded-2xl p-5 shadow-sm">
                    <div class="flex items-center justify-between">
                        <p class="text-xs font-semibold uppercase tracking-wide text-slate-400">Sisa Masa Aktif</p>
                        <span class="text-xs px-2.5 py-0.5 rounded-full {{ $expiringSoon ? 'bg-rose-100 text-rose-700' : 'bg-lime-100 text-lime-800' }} font-bold">
                            {{ $expiringSoon ? 'Segera Habis' : 'Aktif' }}
                        </span>
                    </div>
                    <p class="font-display text-3xl font-extrabold {{ $expiringSoon ? 'text-rose-600' : 'text-slate-900' }} mt-2">
                        {{ $days !== null ? max($days, 0) : '-' }}
                        <span class="text-base text-slate-500 font-sans font-normal">hari</span>
                    </p>
                    <p class="text-xs text-slate-500 mt-1.5">
                        Berlaku hingga <strong>{{ optional($member->expire_date)->translatedFormat('d F Y') }}</strong>
                    </p>
                </div>

                {{-- Target Kunjungan Bulanan --}}
                <div class="bg-white border border-slate-200 rounded-2xl p-5 shadow-sm flex flex-col justify-between">
                    <div>
                        <div class="flex items-center justify-between">
                            <p class="text-xs font-semibold uppercase tracking-wide text-slate-400">Target Latihan Bulan Ini</p>
                            <span id="stat-visits-month-badge" class="text-xs font-bold text-lime-800 bg-lime-100 px-2.5 py-0.5 rounded-full">
                                {{ $visitsThisMonth }}/{{ $targetVisits }} Sesi
                            </span>
                        </div>
                        <p class="font-display text-2xl font-extrabold text-slate-900 mt-2">
                            <span id="stat-progress-percent">{{ $progressPercent }}</span>%
                            <span class="text-xs text-slate-500 font-sans font-normal">Tercapai</span>
                        </p>
                    </div>
                    <div class="mt-3">
                        <div class="w-full bg-slate-100 rounded-full h-2 overflow-hidden">
                            <div id="stat-progress-bar" class="bg-lime-500 h-2 rounded-full transition-all duration-500" style="width: {{ $progressPercent }}%"></div>
                        </div>
                        <p class="text-[11px] text-slate-400 mt-1.5">
                            Total kehadiran keseluruhan: <strong id="stat-total-visits">{{ $totalVisits }} kali</strong>
                        </p>
                    </div>
                </div>

            </div>

            {{-- RIWAYAT PRESENSI & SESI LATIHAN (CHECK-IN & CHECK-OUT) --}}
            <div class="bg-white border border-slate-200 rounded-2xl p-5 sm:p-6 shadow-sm">
                <div class="flex items-center justify-between mb-4">
                    <div>
                        <h3 class="font-display font-bold text-base text-slate-900">Riwayat Presensi & Sesi Latihan</h3>
                        <p class="text-xs text-slate-500">Aktivitas check-in, check-out & durasi latihan via kartu RFID</p>
                    </div>
                    <span class="text-xs font-semibold px-2.5 py-1 rounded-md bg-slate-100 text-slate-600">
                        Log Absensi
                    </span>
                </div>

                <div id="member-attendance-history-list" class="space-y-3">
                    @forelse ($attendances as $a)
                        @php
                            $isTraining = (!$a->check_out_at && $a->check_in_at->isToday());
                        @endphp
                        <div class="p-3.5 rounded-xl bg-slate-50 border border-slate-100 hover:bg-slate-100/70 transition space-y-2">
                            <div class="flex items-center justify-between">
                                <div class="flex items-center gap-3">
                                    <div class="w-9 h-9 rounded-xl {{ $a->check_out_at ? 'bg-lime-100 text-lime-800' : 'bg-lime-400 text-slate-950 animate-pulse' }} flex items-center justify-center shrink-0">
                                        @if ($a->check_out_at)
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                            </svg>
                                        @else
                                            <span class="text-xs font-black">🔥</span>
                                        @endif
                                    </div>
                                    <div>
                                        <p class="text-sm font-semibold text-slate-900">{{ $a->check_in_at->translatedFormat('l, d F Y') }}</p>
                                        <p class="text-xs text-slate-500">
                                            Akses: <span class="font-medium text-slate-700">{{ $a->method === 'rfid' ? 'Kartu RFID' : 'Manual' }}</span>
                                        </p>
                                    </div>
                                </div>

                                <div>
                                    @if ($a->check_out_at)
                                        <span class="text-[11px] font-bold text-slate-700 bg-white border border-slate-200 px-2.5 py-1 rounded-lg shadow-2xs inline-flex items-center gap-1">
                                            <span>✓ Selesai</span>
                                        </span>
                                    @else
                                        <span class="text-[11px] font-black text-slate-950 bg-lime-400 px-2.5 py-1 rounded-lg shadow-sm animate-pulse inline-flex items-center gap-1">
                                            <span>🔥 Sedang Latihan</span>
                                        </span>
                                    @endif
                                </div>
                            </div>

                            {{-- Rincian Sesi Check-in, Check-out & Durasi --}}
                            <div class="pt-2 border-t border-slate-200/60 grid grid-cols-3 gap-2 text-xs">
                                <div class="bg-white p-2 rounded-lg border border-slate-200/70">
                                    <span class="text-[10px] uppercase font-semibold text-slate-400 block">Jam Masuk</span>
                                    <span class="font-mono font-bold text-slate-800">{{ $a->check_in_at->format('H:i') }} WIB</span>
                                </div>

                                <div class="bg-white p-2 rounded-lg border border-slate-200/70">
                                    <span class="text-[10px] uppercase font-semibold text-slate-400 block">Jam Keluar</span>
                                    @if ($a->check_out_at)
                                        <span class="font-mono font-bold text-slate-800">{{ $a->check_out_at->format('H:i') }} WIB</span>
                                    @else
                                        <span class="font-bold text-lime-700 italic">Belum Tap Out</span>
                                    @endif
                                </div>

                                <div class="bg-white p-2 rounded-lg border border-slate-200/70">
                                    <span class="text-[10px] uppercase font-semibold text-slate-400 block">Durasi Sesi</span>
                                    <span class="font-mono font-extrabold text-lime-700 {{ $isTraining ? 'live-duration-timer' : '' }}" {!! $isTraining ? 'data-start-time="' . $a->check_in_at->timestamp . '"' : '' !!}>{{ $a->duration_formatted }}</span>
                                </div>
                            </div>
                        </div>
                    @empty
                        <div class="text-center py-8 text-slate-400 text-xs">
                            <p class="text-2xl mb-1">🏷️</p>
                            <p class="font-bold text-slate-700">Belum ada riwayat presensi tercatat.</p>
                            <p class="mt-1 text-slate-500">Cukup tempelkan kartu RFID Anda di gate scanner saat tiba dan selesai latihan di gym!</p>
                        </div>
                    @endforelse
                </div>
            </div>

        </div>

        {{-- KOLOM KANAN (SIDEBAR INFO - 1 COL SPAN) --}}
        <div class="space-y-6">

            {{-- LIVE CROWD METER --}}
            <div class="bg-white border border-slate-200 rounded-2xl p-5 shadow-sm">
                <div class="flex items-center justify-between mb-2">
                    <span class="text-xs font-semibold uppercase tracking-wider text-slate-400">Keramaian Gym</span>
                    <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-xs font-bold border {{ $crowdBadge }}">
                        <span class="w-2 h-2 rounded-full {{ $crowdDot }} animate-pulse"></span>
                        {{ $crowdLabel }}
                    </span>
                </div>
                <p class="font-display font-bold text-base text-slate-900 mt-1">
                    {{ now()->format('H:i') }} WIB · Jam Ini
                </p>
                <p class="text-xs text-slate-600 mt-1 leading-relaxed">
                    {{ $crowdDesc }}
                </p>
            </div>

            {{-- WORKOUT OF THE DAY --}}
            <div class="bg-white border border-slate-200 rounded-2xl p-5 shadow-sm">
                <div class="flex items-center justify-between mb-2">
                    <span class="text-xs font-semibold uppercase tracking-wider text-slate-400">Program Hari Ini</span>
                    <span class="text-[10px] font-bold text-lime-800 bg-lime-100 px-2 py-0.5 rounded-full">
                        {{ now()->translatedFormat('l') }}
                    </span>
                </div>
                <h4 class="font-display font-bold text-sm text-slate-900 mt-1">
                    {{ $todayWorkout['title'] }}
                </h4>
                <p class="text-xs text-slate-600 mt-1">
                    <strong class="text-slate-800">Gerakan:</strong> {{ $todayWorkout['focus'] }}
                </p>
                <div class="mt-3 p-2.5 rounded-xl bg-slate-50 border border-slate-100 text-xs text-slate-600 flex items-start gap-1.5">
                    <span class="text-lime-600 font-bold">💡</span>
                    <span>{{ $todayWorkout['nutrition'] }}</span>
                </div>
            </div>

            {{-- JADWAL KELAS GYM --}}
            <div class="bg-white border border-slate-200 rounded-2xl p-5 shadow-sm">
                <div class="flex items-center justify-between mb-3">
                    <h4 class="font-display font-bold text-sm text-slate-900">Jadwal Kelas Hari Ini</h4>
                    <span class="text-[11px] text-slate-400 font-medium">Gratis</span>
                </div>

                <div class="space-y-2.5">
                    <div class="p-2.5 rounded-xl bg-slate-50 border border-slate-100 flex items-center justify-between text-xs">
                        <div>
                            <p class="font-bold text-slate-900">🧘 Yoga Flow</p>
                            <p class="text-[11px] text-slate-400">Coach Maya · 08:00 WIB</p>
                        </div>
                        <span class="text-[10px] font-bold px-2 py-0.5 rounded bg-lime-100 text-lime-800">Studio 2</span>
                    </div>

                    <div class="p-2.5 rounded-xl bg-slate-50 border border-slate-100 flex items-center justify-between text-xs">
                        <div>
                            <p class="font-bold text-slate-900">⚡ HIIT Circuit</p>
                            <p class="text-[11px] text-slate-400">Coach Rio · 16:30 WIB</p>
                        </div>
                        <span class="text-[10px] font-bold px-2 py-0.5 rounded bg-orange-100 text-orange-800">Turf</span>
                    </div>

                    <div class="p-2.5 rounded-xl bg-slate-50 border border-slate-100 flex items-center justify-between text-xs">
                        <div>
                            <p class="font-bold text-slate-900">🏋️ BodyPump</p>
                            <p class="text-[11px] text-slate-400">Coach David · 18:30 WIB</p>
                        </div>
                        <span class="text-[10px] font-bold px-2 py-0.5 rounded bg-blue-100 text-blue-800">Studio 1</span>
                    </div>
                </div>
            </div>

        </div>

    </div>

    {{-- =========================================================
        3. GYMPULSE NUTRITION & HEALTH HUB (PANDUAN MAKANAN SEHAT & DIET)
    ========================================================= --}}
    <div class="bg-white border border-slate-200 rounded-3xl p-5 sm:p-7 shadow-sm">
        
        {{-- Section Header --}}
        <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-4 pb-6 border-b border-slate-100">
            <div>
                <div class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-lime-100 text-lime-800 uppercase tracking-wider mb-1.5">
                    <span>🥗</span>
                    <span>Nutrition & Diet Hub</span>
                </div>
                <h3 class="font-display font-extrabold text-xl sm:text-2xl text-slate-900 tracking-tight">
                    Panduan Makanan Diet & Pola Hidup Sehat
                </h3>
                <p class="text-xs sm:text-sm text-slate-500 mt-1">
                    Optimalkan hasil latihan gym dengan nutrisi seimbang, defisit/surplus terukur, dan pola makan tepat.
                </p>
            </div>

            {{-- Tab Navigation Buttons --}}
            <div class="flex flex-wrap sm:flex-nowrap items-center gap-1.5 bg-slate-100 p-1.5 rounded-2xl shrink-0 text-xs font-bold">
                <button 
                    @click="nutritionTab = 'tips'" 
                    :class="nutritionTab === 'tips' ? 'bg-white text-slate-900 shadow-sm' : 'text-slate-600 hover:text-slate-900'"
                    class="flex-1 sm:flex-initial px-3.5 py-2 rounded-xl transition whitespace-nowrap text-center"
                >
                    💡 Tips Makanan
                </button>
                <button 
                    @click="nutritionTab = 'calculator'" 
                    :class="nutritionTab === 'calculator' ? 'bg-white text-slate-900 shadow-sm' : 'text-slate-600 hover:text-slate-900'"
                    class="flex-1 sm:flex-initial px-3.5 py-2 rounded-xl transition whitespace-nowrap text-center"
                >
                    🧮 Kalkulator Gizi
                </button>
                <button 
                    @click="nutritionTab = 'mealplan'" 
                    :class="nutritionTab === 'mealplan' ? 'bg-white text-slate-900 shadow-sm' : 'text-slate-600 hover:text-slate-900'"
                    class="flex-1 sm:flex-initial px-3.5 py-2 rounded-xl transition whitespace-nowrap text-center"
                >
                    🍱 Menu 1 Hari
                </button>
                <button 
                    @click="nutritionTab = 'myths'" 
                    :class="nutritionTab === 'myths' ? 'bg-white text-slate-900 shadow-sm' : 'text-slate-600 hover:text-slate-900'"
                    class="flex-1 sm:flex-initial px-3.5 py-2 rounded-xl transition whitespace-nowrap text-center"
                >
                    ❓ Mitos vs Fakta
                </button>
            </div>
        </div>

        {{-- =========================================================
            TAB 1: TIPS & PILIHAN MAKANAN SEHAT
        ========================================================= --}}
        <div x-show="nutritionTab === 'tips'" class="pt-6 space-y-6">
            
            {{-- Category Filter Chips --}}
            <div class="flex items-center gap-2 overflow-x-auto pb-1 text-xs">
                <button 
                    @click="nutritionCategory = 'all'"
                    :class="nutritionCategory === 'all' ? 'bg-slate-900 text-white font-bold' : 'bg-slate-100 text-slate-600 hover:bg-slate-200'"
                    class="px-3 py-1.5 rounded-xl transition shrink-0"
                >
                    Semua Kategori
                </button>
                <button 
                    @click="nutritionCategory = 'fatloss'"
                    :class="nutritionCategory === 'fatloss' ? 'bg-slate-900 text-white font-bold' : 'bg-slate-100 text-slate-600 hover:bg-slate-200'"
                    class="px-3 py-1.5 rounded-xl transition shrink-0"
                >
                    🥗 Fat Loss & Defisit
                </button>
                <button 
                    @click="nutritionCategory = 'muscle'"
                    :class="nutritionCategory === 'muscle' ? 'bg-slate-900 text-white font-bold' : 'bg-slate-100 text-slate-600 hover:bg-slate-200'"
                    class="px-3 py-1.5 rounded-xl transition shrink-0"
                >
                    💪 Tinggi Protein & Otot
                </button>
                <button 
                    @click="nutritionCategory = 'workout'"
                    :class="nutritionCategory === 'workout' ? 'bg-slate-900 text-white font-bold' : 'bg-slate-100 text-slate-600 hover:bg-slate-200'"
                    class="px-3 py-1.5 rounded-xl transition shrink-0"
                >
                    ⚡ Pre & Post Workout
                </button>
                <button 
                    @click="nutritionCategory = 'snacks'"
                    :class="nutritionCategory === 'snacks' ? 'bg-slate-900 text-white font-bold' : 'bg-slate-100 text-slate-600 hover:bg-slate-200'"
                    class="px-3 py-1.5 rounded-xl transition shrink-0"
                >
                    🥑 Camilan & Hidrasi
                </button>
            </div>

            {{-- Grid of Nutrition Cards --}}
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                
                {{-- Card 1: Protein Juara --}}
                <div x-show="nutritionCategory === 'all' || nutritionCategory === 'muscle'" class="p-5 rounded-2xl bg-slate-50 border border-slate-100 flex flex-col justify-between hover:border-slate-300 transition shadow-sm">
                    <div>
                        <div class="flex items-center justify-between mb-3">
                            <span class="text-2xl">🍗</span>
                            <span class="text-[10px] font-bold uppercase tracking-wider px-2 py-0.5 rounded bg-emerald-100 text-emerald-800">Tinggi Protein</span>
                        </div>
                        <h4 class="font-display font-bold text-base text-slate-900">Sumber Protein Harian</h4>
                        <p class="text-xs text-slate-600 mt-1.5 leading-relaxed">
                            Protein adalah pondasi utama pembentukan otot dan rasa kenyang tahan lama.
                        </p>
                        <div class="mt-3 space-y-1.5 text-xs text-slate-700 bg-white p-3 rounded-xl border border-slate-200/80">
                            <div class="flex justify-between">
                                <span>• Dada Ayam Fillet (100g)</span>
                                <strong class="text-slate-900">~31g Protein</strong>
                            </div>
                            <div class="flex justify-between">
                                <span>• Telur Utuh (1 butir)</span>
                                <strong class="text-slate-900">~6g Protein</strong>
                            </div>
                            <div class="flex justify-between">
                                <span>• Tempe Lokal (100g)</span>
                                <strong class="text-slate-900">~19g Protein</strong>
                            </div>
                            <div class="flex justify-between">
                                <span>• Ikan Kembung / Tongkol (100g)</span>
                                <strong class="text-slate-900">~22g Protein</strong>
                            </div>
                        </div>
                    </div>
                    <p class="text-[11px] text-slate-500 mt-3 italic">
                        💡 <strong>Tips:</strong> Utamakan teknik rebus, panggang, atau kukus daripada deep-frying.
                    </p>
                </div>

                {{-- Card 2: Defisit Kalori & Volume Eating --}}
                <div x-show="nutritionCategory === 'all' || nutritionCategory === 'fatloss'" class="p-5 rounded-2xl bg-slate-50 border border-slate-100 flex flex-col justify-between hover:border-slate-300 transition shadow-sm">
                    <div>
                        <div class="flex items-center justify-between mb-3">
                            <span class="text-2xl">🥗</span>
                            <span class="text-[10px] font-bold uppercase tracking-wider px-2 py-0.5 rounded bg-amber-100 text-amber-800">Fat Loss</span>
                        </div>
                        <h4 class="font-display font-bold text-base text-slate-900">Trik Defisit Tanpa Kelaparan</h4>
                        <p class="text-xs text-slate-600 mt-1.5 leading-relaxed">
                            Kunci sukses diet bukan makan sesedikit mungkin, melainkan menerapkan metode <em>Volume Eating</em>.
                        </p>
                        <div class="mt-3 space-y-2 text-xs text-slate-700 bg-white p-3 rounded-xl border border-slate-200/80">
                            <p>🥦 <strong>Perbanyak Sayuran Serat:</strong> Brokoli, bayam, selada, dan kubis memberi volume besar di lambung dengan kalori sangat rendah.</p>
                            <p>🥣 <strong>Sup Bening & Kuah:</strong> Memulai makan dengan kuah bening menekan nafsu makan berlebih.</p>
                            <p>🚫 <strong>Hindari Kalori Cair:</strong> Kurangi boba, soda manis, dan kopi susu kental manis (penyumbang kalori tersembunyi).</p>
                        </div>
                    </div>
                    <p class="text-[11px] text-slate-500 mt-3 italic">
                        💡 <strong>Defisit Ideal:</strong> 300 - 500 kkal di bawah kebutuhan harian agar lemak turun bertahap tanpa kehilangan massa otot.
                    </p>
                </div>

                {{-- Card 3: Pre & Post Workout Fuel --}}
                <div x-show="nutritionCategory === 'all' || nutritionCategory === 'workout'" class="p-5 rounded-2xl bg-slate-50 border border-slate-100 flex flex-col justify-between hover:border-slate-300 transition shadow-sm">
                    <div>
                        <div class="flex items-center justify-between mb-3">
                            <span class="text-2xl">⚡</span>
                            <span class="text-[10px] font-bold uppercase tracking-wider px-2 py-0.5 rounded bg-blue-100 text-blue-800">Timing Latihan</span>
                        </div>
                        <h4 class="font-display font-bold text-base text-slate-900">Nutrisi Sebelum & Sesudah Gym</h4>
                        <p class="text-xs text-slate-600 mt-1.5 leading-relaxed">
                            Beri bahan bakar pada otot saat berjuang dan nutrisi recovery saat selesai.
                        </p>
                        <div class="mt-3 space-y-2 text-xs text-slate-700 bg-white p-3 rounded-xl border border-slate-200/80">
                            <div>
                                <span class="font-bold text-slate-900">⏰ 1-2 Jam Sebelum Latihan (Pre-Workout):</span>
                                <p class="text-slate-600 mt-0.5">Pisang + 1 sdm selai kacang / Oatmeal + Kopi hitam (tanpa gula) untuk tenaga & fokus.</p>
                            </div>
                            <div class="pt-1.5 border-t border-slate-100">
                                <span class="font-bold text-slate-900">⏰ 30-60 Menit Sesudah Latihan (Post-Workout):</span>
                                <p class="text-slate-600 mt-0.5">Dada ayam / 2-3 butir telur + Nasi hangat / Pisang untuk regenerasi serat otot yang lelah.</p>
                            </div>
                        </div>
                    </div>
                    <p class="text-[11px] text-slate-500 mt-3 italic">
                        💡 <strong>Catatan:</strong> Jangan berolahraga dengan perut terlalu penuh agar tidak mual.
                    </p>
                </div>

                {{-- Card 4: Karbohidrat Pintar (Kompleks vs Sederhana) --}}
                <div x-show="nutritionCategory === 'all' || nutritionCategory === 'fatloss' || nutritionCategory === 'muscle'" class="p-5 rounded-2xl bg-slate-50 border border-slate-100 flex flex-col justify-between hover:border-slate-300 transition shadow-sm">
                    <div>
                        <div class="flex items-center justify-between mb-3">
                            <span class="text-2xl">🍠</span>
                            <span class="text-[10px] font-bold uppercase tracking-wider px-2 py-0.5 rounded bg-purple-100 text-purple-800">Energi Bersih</span>
                        </div>
                        <h4 class="font-display font-bold text-base text-slate-900">Pilihan Karbohidrat Sehat</h4>
                        <p class="text-xs text-slate-600 mt-1.5 leading-relaxed">
                            Karbohidrat adalah bensin utama tubuh untuk mengangkat beban berat.
                        </p>
                        <div class="mt-3 space-y-1.5 text-xs text-slate-700 bg-white p-3 rounded-xl border border-slate-200/80">
                            <div class="flex justify-between">
                                <span>• Beras Merah / Beras Cokelat</span>
                                <span class="text-emerald-700 font-bold">Kaya Serat</span>
                            </div>
                            <div class="flex justify-between">
                                <span>• Ubi Jalar / Singkong Rebus</span>
                                <span class="text-emerald-700 font-bold">Low GI</span>
                            </div>
                            <div class="flex justify-between">
                                <span>• Oatmeal / Roti Gandum Utuh</span>
                                <span class="text-emerald-700 font-bold">Kenyang Lama</span>
                            </div>
                            <div class="flex justify-between">
                                <span>• Kentang Rebus (dengan kulit)</span>
                                <span class="text-emerald-700 font-bold">Tinggi Kalium</span>
                            </div>
                        </div>
                    </div>
                    <p class="text-[11px] text-slate-500 mt-3 italic">
                        💡 Nasi putih tetap aman dikonsumsi asalkan porsinya terkontrol sesuai target kalori.
                    </p>
                </div>

                {{-- Card 5: Camilan Sehat & Bebas Rasa Bersalah --}}
                <div x-show="nutritionCategory === 'all' || nutritionCategory === 'snacks'" class="p-5 rounded-2xl bg-slate-50 border border-slate-100 flex flex-col justify-between hover:border-slate-300 transition shadow-sm">
                    <div>
                        <div class="flex items-center justify-between mb-3">
                            <span class="text-2xl">🥑</span>
                            <span class="text-[10px] font-bold uppercase tracking-wider px-2 py-0.5 rounded bg-lime-100 text-lime-800">Healthy Snacks</span>
                        </div>
                        <h4 class="font-display font-bold text-base text-slate-900">Camilan Saat Lapar Menyerang</h4>
                        <p class="text-xs text-slate-600 mt-1.5 leading-relaxed">
                            Ganti gorengan dan keripik olahan dengan camilan bernutrisi tinggi.
                        </p>
                        <div class="mt-3 space-y-1.5 text-xs text-slate-700 bg-white p-3 rounded-xl border border-slate-200/80">
                            <p>🥜 <strong>Edamame Rebus:</strong> Camilan kaya protein nabati dan serat.</p>
                            <p>🥚 <strong>Telur Rebus:</strong> Praktis, murah, dan padat asam amino esensial.</p>
                            <p>🥣 <strong>Greek Yogurt + Buah:</strong> Probiotik baik untuk pencernaan dan tinggi kasein.</p>
                            <p>🌰 <strong>Kacang Almond / Kenari:</strong> Lemak sehat omega-3 (cukup 10-15 butir).</p>
                        </div>
                    </div>
                    <p class="text-[11px] text-slate-500 mt-3 italic">
                        💡 <strong>Tips:</strong> Siapkan rebusan telur atau edamame di kulkas sebagai stok praktis.
                    </p>
                </div>

                {{-- Card 6: Hidrasi & Elektrolit --}}
                <div x-show="nutritionCategory === 'all' || nutritionCategory === 'snacks'" class="p-5 rounded-2xl bg-slate-50 border border-slate-100 flex flex-col justify-between hover:border-slate-300 transition shadow-sm">
                    <div>
                        <div class="flex items-center justify-between mb-3">
                            <span class="text-2xl">💧</span>
                            <span class="text-[10px] font-bold uppercase tracking-wider px-2 py-0.5 rounded bg-cyan-100 text-cyan-800">Hidrasi</span>
                        </div>
                        <h4 class="font-display font-bold text-base text-slate-900">Kunci Hidrasi & Elektrolit</h4>
                        <p class="text-xs text-slate-600 mt-1.5 leading-relaxed">
                            Dehidrasi sebesar 2% saja sudah menurunkan kekuatan angkatan gym hingga 15%!
                        </p>
                        <div class="mt-3 space-y-1.5 text-xs text-slate-700 bg-white p-3 rounded-xl border border-slate-200/80">
                            <p>• <strong>Sebelum Latihan:</strong> Minum 300-500 ml air 30 menit sebelum sesi gym.</p>
                            <p>• <strong>Saat Latihan:</strong> Teguk 100-150 ml air setiap jeda istirahat set.</p>
                            <p>• <strong>Tambahan Alami:</strong> Sedikit garam Himalaya atau air kelapa murni untuk mengganti elektrolit yang keluar lewat keringat.</p>
                        </div>
                    </div>
                    <p class="text-[11px] text-slate-500 mt-3 italic">
                        💡 Indikator hidrasi baik: Warna urin bening atau kuning sangat muda.
                    </p>
                </div>

            </div>

        </div>

        {{-- =========================================================
            TAB 2: KALKULATOR KEBUTUHAN GIZI INTERAKTIF
        ========================================================= --}}
        <div x-show="nutritionTab === 'calculator'" class="pt-6 space-y-6">
            <div class="p-5 sm:p-6 rounded-2xl bg-slate-900 text-white border border-slate-800 shadow-xl">
                <div class="max-w-2xl">
                    <span class="text-[10px] font-bold uppercase tracking-widest text-lime-400">PERSONAL NUTRITION CALCULATOR</span>
                    <h4 class="font-display font-extrabold text-xl sm:text-2xl text-white mt-1">
                        Hitung Kebutuhan Kalori & Protein Pribadi Anda
                    </h4>
                    <p class="text-xs text-slate-400 mt-1">
                        Sesuaikan berat badan dan tujuan latihan untuk mendapatkan rekomendasi nutrisi harian secara instan.
                    </p>
                </div>

                {{-- Interactive Controls --}}
                <div class="mt-6 grid grid-cols-1 md:grid-cols-2 gap-6 pt-5 border-t border-slate-800">
                    
                    {{-- Weight Input Slider --}}
                    <div>
                        <div class="flex justify-between items-center mb-2">
                            <label class="text-xs font-bold text-slate-300">Berat Badan Anda</label>
                            <span class="font-display font-extrabold text-lime-400 text-lg">
                                <span x-text="weight"></span> kg
                            </span>
                        </div>
                        <input 
                            type="range" 
                            min="40" 
                            max="130" 
                            step="1" 
                            x-model="weight" 
                            class="w-full h-2 bg-slate-700 rounded-lg appearance-none cursor-pointer accent-lime-400"
                        >
                        <div class="flex justify-between text-[10px] text-slate-500 mt-1 font-mono">
                            <span>40 kg</span>
                            <span>85 kg</span>
                            <span>130 kg</span>
                        </div>
                    </div>

                    {{-- Goal Selection --}}
                    <div>
                        <label class="block text-xs font-bold text-slate-300 mb-2">Tujuan Kebugaran Saat Ini</label>
                        <div class="grid grid-cols-3 gap-2">
                            <button 
                                type="button"
                                @click="dietGoal = 'fatloss'"
                                :class="dietGoal === 'fatloss' ? 'bg-lime-500 text-slate-950 font-bold border-lime-400' : 'bg-slate-800 text-slate-300 border-slate-700 hover:bg-slate-750'"
                                class="p-2 rounded-xl text-xs border transition text-center"
                            >
                                🔻 Turun Lemak
                            </button>
                            <button 
                                type="button"
                                @click="dietGoal = 'maintain'"
                                :class="dietGoal === 'maintain' ? 'bg-lime-500 text-slate-950 font-bold border-lime-400' : 'bg-slate-800 text-slate-300 border-slate-700 hover:bg-slate-750'"
                                class="p-2 rounded-xl text-xs border transition text-center"
                            >
                                ⚖️ Jaga Stamina
                            </button>
                            <button 
                                type="button"
                                @click="dietGoal = 'muscle'"
                                :class="dietGoal === 'muscle' ? 'bg-lime-500 text-slate-950 font-bold border-lime-400' : 'bg-slate-800 text-slate-300 border-slate-700 hover:bg-slate-750'"
                                class="p-2 rounded-xl text-xs border transition text-center"
                            >
                                🔺 Tambah Otot
                            </button>
                        </div>
                    </div>

                </div>

                {{-- Live Results Matrix --}}
                <div class="mt-6 grid grid-cols-1 sm:grid-cols-3 gap-4">
                    
                    {{-- Kalori Harian --}}
                    <div class="bg-slate-800/90 border border-slate-700 rounded-2xl p-4 text-center">
                        <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">Estimasi Kalori Harian</span>
                        <p class="font-display text-3xl font-extrabold text-white mt-1">
                            <span x-text="calcCalories()"></span>
                            <span class="text-xs font-sans text-slate-400 font-normal">kkal</span>
                        </p>
                        <p class="text-[11px] text-lime-400 mt-1">
                            <template x-if="dietGoal === 'fatloss'"><span>Defisit ~400 kkal untuk bakar lemak</span></template>
                            <template x-if="dietGoal === 'maintain'"><span>Keseimbangan energi tubuh stabil</span></template>
                            <template x-if="dietGoal === 'muscle'"><span>Surplus ~350 kkal untuk hipertrofi otot</span></template>
                        </p>
                    </div>

                    {{-- Target Protein --}}
                    <div class="bg-slate-800/90 border border-slate-700 rounded-2xl p-4 text-center">
                        <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">Target Protein Harian</span>
                        <p class="font-display text-3xl font-extrabold text-lime-400 mt-1">
                            <span x-text="calcProtein()"></span>
                            <span class="text-xs font-sans text-slate-400 font-normal">gram</span>
                        </p>
                        <p class="text-[11px] text-slate-300 mt-1">
                            Setara <strong><span x-text="calcChicken()"></span>g dada ayam</strong> atau <strong><span x-text="calcEggs()"></span> butir telur</strong>
                        </p>
                    </div>

                    {{-- Target Air --}}
                    <div class="bg-slate-800/90 border border-slate-700 rounded-2xl p-4 text-center">
                        <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">Kebutuhan Air Putih</span>
                        <p class="font-display text-3xl font-extrabold text-cyan-400 mt-1">
                            <span x-text="calcWater()"></span>
                            <span class="text-xs font-sans text-slate-400 font-normal">Liter</span>
                        </p>
                        <p class="text-[11px] text-slate-300 mt-1">
                            Minimal 8 - 12 gelas air per hari
                        </p>
                    </div>

                </div>

            </div>
        </div>

        {{-- =========================================================
            TAB 3: CONTOH MENU HARIAN (MEAL PLAN LOKAL)
        ========================================================= --}}
        <div x-show="nutritionTab === 'mealplan'" class="pt-6 space-y-4">
            <div class="p-4 rounded-2xl bg-lime-50 border border-lime-200 text-xs text-lime-900 flex items-start gap-2.5">
                <span class="text-lg">🍱</span>
                <div>
                    <strong class="font-bold">Contoh Pola Makan Harian Bergizi, Sehat, & Terjangkau (Indonesia):</strong>
                    <p class="mt-0.5 text-lime-800">Menu ini dirancang seimbang dengan total ~1.700 - 1.900 kkal dan ~110-130g protein. Anda dapat menyesuaikan porsi sesuai kebutuhan.</p>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                
                {{-- Sarapan --}}
                <div class="p-5 rounded-2xl bg-slate-50 border border-slate-100 flex gap-4">
                    <div class="w-10 h-10 rounded-xl bg-amber-100 text-amber-800 flex items-center justify-center shrink-0 font-bold text-lg">
                        🌅
                    </div>
                    <div class="flex-1">
                        <div class="flex items-center justify-between">
                            <h4 class="font-bold text-slate-900 text-sm">07:00 WIB · Sarapan Pagi</h4>
                            <span class="text-[10px] font-bold px-2 py-0.5 rounded bg-white border border-slate-200 text-slate-700">~380 kkal · 22g Protein</span>
                        </div>
                        <ul class="text-xs text-slate-600 mt-2 space-y-1">
                            <li>• 4-5 sdm Oatmeal diseduh air hangat / 2 lembar Roti Gandum</li>
                            <li>• 2 Butir Telur Rebus (1 utuh + 1 putih telur)</li>
                            <li>• 1 Buah Pisang atau Pepaya iris</li>
                            <li>• 1 Cangkir Kopi Hitam / Teh Hijau (tanpa gula)</li>
                        </ul>
                    </div>
                </div>

                {{-- Makan Siang --}}
                <div class="p-5 rounded-2xl bg-slate-50 border border-slate-100 flex gap-4">
                    <div class="w-10 h-10 rounded-xl bg-orange-100 text-orange-800 flex items-center justify-center shrink-0 font-bold text-lg">
                        ☀️
                    </div>
                    <div class="flex-1">
                        <div class="flex items-center justify-between">
                            <h4 class="font-bold text-slate-900 text-sm">12:30 WIB · Makan Siang</h4>
                            <span class="text-[10px] font-bold px-2 py-0.5 rounded bg-white border border-slate-200 text-slate-700">~480 kkal · 42g Protein</span>
                        </div>
                        <ul class="text-xs text-slate-600 mt-2 space-y-1">
                            <li>• 1 Centong Nasi Merah atau Nasi Putih (~100-120g)</li>
                            <li>• 150g Dada Ayam Panggang Teplon / Pepes Ikan Nila</li>
                            <li>• 1 Mangkuk Sayur Bening Bayam Jagung / Tumis Buncis</li>
                            <li>• 1 Gelas Air Putih (500ml)</li>
                        </ul>
                    </div>
                </div>

                {{-- Snack Pre-Workout --}}
                <div class="p-5 rounded-2xl bg-slate-50 border border-slate-100 flex gap-4">
                    <div class="w-10 h-10 rounded-xl bg-blue-100 text-blue-800 flex items-center justify-center shrink-0 font-bold text-lg">
                        🌤️
                    </div>
                    <div class="flex-1">
                        <div class="flex items-center justify-between">
                            <h4 class="font-bold text-slate-900 text-sm">16:30 WIB · Snack Pre-Workout</h4>
                            <span class="text-[10px] font-bold px-2 py-0.5 rounded bg-white border border-slate-200 text-slate-700">~150 kkal · 6g Protein</span>
                        </div>
                        <ul class="text-xs text-slate-600 mt-2 space-y-1">
                            <li>• 1 Buah Pisang Cavendish + 1 sdt Selai Kacang</li>
                            <li>• Secangkir Kopi Hitam (opsional sebagai pre-workout alami)</li>
                            <li>• 1 Gelas Air Putih 300ml</li>
                        </ul>
                    </div>
                </div>

                {{-- Makan Malam --}}
                <div class="p-5 rounded-2xl bg-slate-50 border border-slate-100 flex gap-4">
                    <div class="w-10 h-10 rounded-xl bg-purple-100 text-purple-800 flex items-center justify-center shrink-0 font-bold text-lg">
                        🌙
                    </div>
                    <div class="flex-1">
                        <div class="flex items-center justify-between">
                            <h4 class="font-bold text-slate-900 text-sm">19:30 WIB · Makan Malam Recovery</h4>
                            <span class="text-[10px] font-bold px-2 py-0.5 rounded bg-white border border-slate-200 text-slate-700">~420 kkal · 34g Protein</span>
                        </div>
                        <ul class="text-xs text-slate-600 mt-2 space-y-1">
                            <li>• 1 Potong Ubi Jalar Kukus / 1 Centong Nasi</li>
                            <li>• 2 Potong Tempe & Tahu Bacem / Panggang</li>
                            <li>• 100g Daging Cincang Tanpa Lemak / Telur Dadar Jamur</li>
                            <li>• Lalapan Selada, Timun, dan Tomat Segar</li>
                        </ul>
                    </div>
                </div>

            </div>
        </div>

        {{-- =========================================================
            TAB 4: MITOS VS FAKTA DIET FITNESS
        ========================================================= --}}
        <div x-show="nutritionTab === 'myths'" class="pt-6 grid grid-cols-1 md:grid-cols-2 gap-4">
            
            <div class="p-5 rounded-2xl bg-slate-50 border border-slate-100 hover:border-slate-200 transition">
                <div class="flex items-center gap-2 text-xs font-bold text-rose-600">
                    <span>❌ Mitos:</span>
                    <span>Makan malam di atas jam 7 otomatis jadi lemak.</span>
                </div>
                <div class="mt-2 text-xs text-emerald-800 bg-emerald-50 p-3 rounded-xl border border-emerald-100">
                    <strong>✅ Fakta:</strong> Tubuh membakar kalori sepanjang 24 jam. Yang menentukan berat badan adalah <em>total asupan kalori harian</em>, bukan jam saat makanan masuk. Pastikan ada jeda 2 jam sebelum tidur agar lambung nyaman.
                </div>
            </div>

            <div class="p-5 rounded-2xl bg-slate-50 border border-slate-100 hover:border-slate-200 transition">
                <div class="flex items-center gap-2 text-xs font-bold text-rose-600">
                    <span>❌ Mitos:</span>
                    <span>Kuning telur berbahaya dan wajib dibuang.</span>
                </div>
                <div class="mt-2 text-xs text-emerald-800 bg-emerald-50 p-3 rounded-xl border border-emerald-100">
                    <strong>✅ Fakta:</strong> Kuning telur mengandung nutrisi penting seperti Kolin, Vitamin D, dan asam lemak baik untuk pembentukan hormon. Mengonsumsi 2-3 butir telur utuh setiap hari sangat aman bagi orang yang aktif berolahraga.
                </div>
            </div>

            <div class="p-5 rounded-2xl bg-slate-50 border border-slate-100 hover:border-slate-200 transition">
                <div class="flex items-center gap-2 text-xs font-bold text-rose-600">
                    <span>❌ Mitos:</span>
                    <span>Wajib minum suplemen mahal agar badan berotot.</span>
                </div>
                <div class="mt-2 text-xs text-emerald-800 bg-emerald-50 p-3 rounded-xl border border-emerald-100">
                    <strong>✅ Fakta:</strong> Suplemen hanya pembantu 5-10%. Pondasi 90% hasil fisik berasal dari makanan utuh (*whole food*) seperti dada ayam, tempe, telur, sayur, serta istirahat tidur yang cukup.
                </div>
            </div>

            <div class="p-5 rounded-2xl bg-slate-50 border border-slate-100 hover:border-slate-200 transition">
                <div class="flex items-center gap-2 text-xs font-bold text-rose-600">
                    <span>❌ Mitos:</span>
                    <span>Minum air es membuat perut buncit dan membekukan lemak.</span>
                </div>
                <div class="mt-2 text-xs text-emerald-800 bg-emerald-50 p-3 rounded-xl border border-emerald-100">
                    <strong>✅ Fakta:</strong> Air putih dingin memiliki 0 kalori dan suhunya akan segera dinetralkan oleh suhu tubuh (37°C) di dalam lambung. Perut buncit terjadi karena surplus kalori dan makanan manis, bukan suhu air.
                </div>
            </div>

        </div>

    </div>

    </div>

    {{-- =========================================================
        MODAL: BUKTI / INVOICE MEMBERSHIP
    ========================================================= --}}
    <div x-show="invoiceModal" x-cloak class="invoice-modal-overlay fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/70 backdrop-blur-sm" @keydown.escape.window="invoiceModal = false">
        <div @click.outside="invoiceModal = false" class="invoice-card bg-white border border-slate-200 rounded-3xl w-full max-w-md p-6 sm:p-7 shadow-2xl relative">
            <button @click="invoiceModal = false" class="no-print print:hidden absolute top-4 right-4 text-slate-400 hover:text-slate-700 p-1" title="Tutup">
                ✕
            </button>

            {{-- Receipt Header --}}
            <div class="flex items-center gap-3 pb-4 border-b border-slate-200">
                <div class="w-10 h-10 rounded-xl bg-slate-900 text-lime-400 flex items-center justify-center font-display font-extrabold text-xl">
                    G
                </div>
                <div>
                    <h3 class="font-display font-bold text-slate-900 text-base">E-Receipt / Bukti Keanggotaan</h3>
                    <p class="text-xs text-slate-500">GymPulse Smart RFID Fitness</p>
                </div>
            </div>

            {{-- Receipt Details --}}
            <div class="py-4 space-y-3 text-xs">
                <div class="flex justify-between">
                    <span class="text-slate-500">Nomor Invoice</span>
                    <span class="font-mono font-bold text-slate-900">INV-{{ date('Ym') }}-{{ $member->id }}</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-slate-500">Nama Member</span>
                    <span class="font-bold text-slate-900">{{ $member->user->name }}</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-slate-500">Kode Member</span>
                    <span class="font-mono font-bold text-slate-900">{{ $member->member_code }}</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-slate-500">Paket</span>
                    <span class="font-bold text-slate-900">{{ $member->package->name ?? '-' }} ({{ $member->package->duration_months ?? 1 }} Bulan)</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-slate-500">Tanggal Mulai</span>
                    <span class="text-slate-900">{{ optional($member->join_date)->format('d F Y') }}</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-slate-500">Masa Berlaku Hingga</span>
                    <span class="font-bold text-slate-900">{{ optional($member->expire_date)->format('d F Y') }}</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-slate-500">Status Pembayaran</span>
                    <span class="font-bold text-emerald-700 bg-emerald-100 px-2 py-0.5 rounded">LUNAS / AKTIF</span>
                </div>
            </div>

            {{-- Price Total --}}
            <div class="pt-4 border-t border-slate-200 flex items-center justify-between">
                <div>
                    <p class="text-xs text-slate-500">Total Pembayaran Paket</p>
                    <p class="font-display text-2xl font-extrabold text-slate-900 mt-0.5">
                        Rp{{ number_format($member->package->price ?? 0, 0, ',', '.') }}
                    </p>
                </div>
                <button onclick="window.print()" class="no-print print:hidden px-4 py-2.5 rounded-xl bg-slate-900 text-white text-xs font-bold hover:bg-slate-800 transition">
                    🖨️ Cetak / Simpan PDF
                </button>
            </div>
        </div>
    </div>

    {{-- =========================================================
        MODAL: PERPANJANGAN MEMBERSHIP ONLINE (QRIS DINAMIS / TRANSFER)
    ========================================================= --}}
    <div 
        x-show="renewModal" 
        x-cloak 
        class="fixed inset-0 z-50 flex items-center justify-center p-3 sm:p-4 bg-slate-950/75 backdrop-blur-md overflow-y-auto"
        @keydown.escape.window="if(renewStep !== 'qris') closeRenewalModal()"
    >
        <div 
            @click.outside="if(renewStep !== 'qris') closeRenewalModal()" 
            class="bg-white border border-slate-100 rounded-2xl sm:rounded-3xl w-full max-w-lg p-5 sm:p-7 shadow-2xl relative my-6 max-h-[92vh] overflow-y-auto"
        >
            {{-- Close Button (only on non-qris step) --}}
            <button 
                x-show="renewStep !== 'qris'" 
                @click="closeRenewalModal()" 
                class="absolute top-4 right-4 text-slate-400 hover:text-slate-700 p-1 text-lg font-bold" 
                title="Tutup"
            >
                ✕
            </button>

            {{-- =========================================================
                STEP 1: SELEKSI PAKET & METODE PEMBAYARAN
            ========================================================= --}}
            <div x-show="renewStep === 'select'" class="space-y-4 sm:space-y-5">
                {{-- Header Modal --}}
                <div class="pb-3 sm:pb-4 border-b border-slate-100">
                    <span class="text-[10px] font-extrabold uppercase tracking-widest text-lime-700 bg-lime-100 px-2.5 py-0.5 rounded-md inline-flex items-center gap-1">
                        <span>⚡</span>
                        <span>PERPANJANGAN ONLINE OTOMATIS</span>
                    </span>
                    <h3 class="font-display font-extrabold text-xl text-slate-900 mt-1.5">Perpanjang Membership Gym</h3>
                    <p class="text-xs text-slate-500 mt-0.5 leading-relaxed">
                        Pilih paket dan selesaikan pembayaran online. Masa aktif bertambah & akses RFID gate langsung aktif seketika!
                    </p>
                </div>

                {{-- Error Message Box --}}
                <template x-if="renewErrorMessage">
                    <div class="p-3 rounded-xl bg-rose-50 border border-rose-200 text-rose-700 text-xs font-semibold" x-text="renewErrorMessage"></div>
                </template>

                {{-- 1. PILIH PAKET MEMBERSHIP --}}
                <div>
                    <label class="block text-xs font-extrabold text-slate-800 uppercase tracking-wider mb-2.5">
                        1. Pilih Paket Membership
                    </label>
                    
                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-2.5">
                        @foreach ($packages as $pkg)
                            <div 
                                @click="setPackage({{ $pkg->id }}, {{ $pkg->price }}, '{{ $pkg->name }}', {{ $pkg->duration_months }})"
                                :class="selectedPackageId == {{ $pkg->id }} ? 'border-lime-500 bg-lime-50/50 shadow-sm ring-2 ring-lime-500/20' : 'border-slate-200 bg-slate-50/50 hover:bg-slate-50'"
                                class="border-2 rounded-2xl p-3 cursor-pointer transition flex flex-col justify-between select-none relative"
                            >
                                <div>
                                    <div class="flex items-center justify-between mb-1">
                                        <span class="text-[10px] font-bold uppercase tracking-wider text-slate-500">{{ $pkg->duration_months }} Bulan</span>
                                        <span x-show="selectedPackageId == {{ $pkg->id }}" class="w-4 h-4 rounded-full bg-lime-500 text-slate-950 flex items-center justify-center text-[10px] font-black">✓</span>
                                    </div>
                                    <h4 class="font-display font-bold text-xs text-slate-900">{{ $pkg->name }}</h4>
                                </div>
                                <div class="mt-2 pt-2 border-t border-slate-200/60">
                                    <p class="font-mono font-extrabold text-xs text-lime-700">Rp{{ number_format($pkg->price, 0, ',', '.') }}</p>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>

                {{-- 2. PILIH METODE PEMBAYARAN ONLINE --}}
                <div>
                    <label class="block text-xs font-extrabold text-slate-800 uppercase tracking-wider mb-2.5">
                        2. Metode Pembayaran Online
                    </label>

                    <div class="grid grid-cols-2 gap-2.5">
                        <button 
                            type="button" 
                            @click="onlinePaymentMethod = 'qris'"
                            :class="onlinePaymentMethod === 'qris' ? 'border-lime-500 bg-lime-50/50 ring-2 ring-lime-500/20 text-slate-950 font-bold' : 'border-slate-200 bg-white text-slate-600 hover:bg-slate-50'"
                            class="border-2 rounded-2xl p-3 text-xs flex items-center gap-2.5 transition text-left"
                        >
                            <span class="text-2xl shrink-0">📱</span>
                            <div>
                                <p class="font-bold">QRIS Dinamis</p>
                                <p class="text-[10px] text-slate-400 font-normal">GoPay, OVO, Dana, BCA, dll</p>
                            </div>
                        </button>

                        <button 
                            type="button" 
                            @click="onlinePaymentMethod = 'transfer'"
                            :class="onlinePaymentMethod === 'transfer' ? 'border-lime-500 bg-lime-50/50 ring-2 ring-lime-500/20 text-slate-950 font-bold' : 'border-slate-200 bg-white text-slate-600 hover:bg-slate-50'"
                            class="border-2 rounded-2xl p-3 text-xs flex items-center gap-2.5 transition text-left"
                        >
                            <span class="text-2xl shrink-0">🏦</span>
                            <div>
                                <p class="font-bold">Transfer Bank</p>
                                <p class="text-[10px] text-slate-400 font-normal">BCA & Mandiri GymPulse</p>
                            </div>
                        </button>
                    </div>
                </div>

                {{-- 3. DETAIL METODE: QRIS / TRANSFER --}}
                <div x-show="onlinePaymentMethod === 'qris'" class="p-3.5 sm:p-4 rounded-2xl bg-slate-950 text-white space-y-2 text-left">
                    <div class="flex items-center justify-between text-[11px] text-slate-400">
                        <span>Paket Terpilih</span>
                        <span class="font-bold text-white" x-text="selectedPackageName + ' (' + selectedPackageDuration + ' Bulan)'"></span>
                    </div>
                    <div class="flex items-center justify-between text-[11px] text-slate-400">
                        <span>Metode Pembayaran</span>
                        <span class="font-bold text-lime-400">📱 QRIS Dinamis Otomatis</span>
                    </div>
                    <div class="pt-2 border-t border-slate-800 flex items-center justify-between">
                        <span class="text-xs text-slate-300 font-semibold uppercase">Total Tagihan</span>
                        <span class="font-display font-black text-lg sm:text-xl text-lime-400 font-mono">
                            Rp<span x-text="Number(selectedPackagePrice).toLocaleString('id-ID')"></span>
                        </span>
                    </div>
                </div>

                <div x-show="onlinePaymentMethod === 'transfer'" class="p-3.5 sm:p-4 rounded-2xl bg-slate-50 border border-slate-200 space-y-3 text-xs">
                    <p class="font-bold text-slate-800">Silakan transfer persis ke rekening resmi gym:</p>

                    <div class="space-y-2">
                        <div class="p-2.5 rounded-xl bg-white border border-slate-200 flex items-center justify-between">
                            <div>
                                <span class="font-bold text-blue-900 block">Bank BCA</span>
                                <span class="font-mono font-extrabold text-sm text-slate-900">8271-9928-1120</span>
                                <span class="text-[10px] text-slate-400 block">a.n. GymPulse Indonesia</span>
                            </div>
                            <button 
                                type="button" 
                                @click="copyBank('827199281120', 'bca')" 
                                class="px-2.5 py-1 rounded-lg bg-blue-50 hover:bg-blue-100 text-blue-700 font-bold text-[11px] transition"
                            >
                                <span x-show="copiedBank !== 'bca'">📋 Salin No</span>
                                <span x-show="copiedBank === 'bca'">✓ Tersalin</span>
                            </button>
                        </div>

                        <div class="p-2.5 rounded-xl bg-white border border-slate-200 flex items-center justify-between">
                            <div>
                                <span class="font-bold text-indigo-900 block">Bank Mandiri</span>
                                <span class="font-mono font-extrabold text-sm text-slate-900">1370-0099-2811-2</span>
                                <span class="text-[10px] text-slate-400 block">a.n. GymPulse Indonesia</span>
                            </div>
                            <button 
                                type="button" 
                                @click="copyBank('1370009928112', 'mandiri')" 
                                class="px-2.5 py-1 rounded-lg bg-indigo-50 hover:bg-indigo-100 text-indigo-700 font-bold text-[11px] transition"
                            >
                                <span x-show="copiedBank !== 'mandiri'">📋 Salin No</span>
                                <span x-show="copiedBank === 'mandiri'">✓ Tersalin</span>
                            </button>
                        </div>
                    </div>

                    <div class="flex justify-between items-center pt-2 border-t border-slate-200">
                        <span class="text-slate-500 font-medium">Nominal Transfer:</span>
                        <strong class="font-mono text-sm text-slate-900">Rp<span x-text="Number(selectedPackagePrice).toLocaleString('id-ID')"></span></strong>
                    </div>
                </div>

                {{-- ACTION BUTTONS --}}
                <div class="pt-3 border-t border-slate-100 flex gap-2.5">
                    <button 
                        type="button" 
                        @click="closeRenewalModal()" 
                        class="px-4 py-2.5 rounded-xl border border-slate-200 text-slate-600 text-xs font-bold hover:bg-slate-50 transition"
                    >
                        Batal
                    </button>

                    {{-- QRIS Trigger Button --}}
                    <template x-if="onlinePaymentMethod === 'qris'">
                        <button 
                            type="button" 
                            @click="submitRenewalCheckout()" 
                            :disabled="loadingRenewal"
                            class="flex-1 py-2.5 rounded-xl bg-lime-500 hover:bg-lime-400 active:scale-95 text-slate-950 font-display font-extrabold text-xs transition shadow-md flex items-center justify-center gap-2"
                        >
                            <span x-show="!loadingRenewal">⚡ Bayar via QRIS (Rp<span x-text="Number(selectedPackagePrice).toLocaleString('id-ID')"></span>)</span>
                            <span x-show="loadingRenewal">Membuat QRIS...</span>
                        </button>
                    </template>

                    {{-- Transfer Direct Form Trigger --}}
                    <template x-if="onlinePaymentMethod === 'transfer'">
                        <form method="POST" action="{{ route('member.renew') }}" class="flex-1">
                            @csrf
                            <input type="hidden" name="membership_package_id" :value="selectedPackageId">
                            <input type="hidden" name="payment_method" value="transfer">
                            <button 
                                type="submit" 
                                class="w-full py-2.5 rounded-xl bg-lime-500 hover:bg-lime-400 active:scale-95 text-slate-950 font-display font-extrabold text-xs transition shadow-md flex items-center justify-center gap-2"
                            >
                                <span>Konfirmasi Pembayaran Transfer</span>
                            </button>
                        </form>
                    </template>
                </div>
            </div>

            {{-- =========================================================
                STEP 2: MODAL QRIS DINAMIS PERPANJANGAN (IDENTIK PRODUK)
            ========================================================= --}}
            <div x-show="renewStep === 'qris'" class="text-center space-y-3">
                {{-- Top Header --}}
                <div class="flex items-center justify-between pb-2 border-b border-slate-100">
                    <div class="flex items-center gap-1.5">
                        <span class="px-2 py-0.5 rounded-md bg-slate-900 text-white font-black text-[11px] tracking-wider">QRIS</span>
                        <span class="text-[11px] font-bold text-slate-500 uppercase tracking-wide">Dinamis Otomatis</span>
                    </div>
                    <div class="flex items-center gap-1 text-[11px] font-mono font-bold bg-amber-50 text-amber-800 px-2 py-0.5 rounded-lg border border-amber-200">
                        <svg class="w-3 h-3 text-amber-600 animate-spin" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                        <span x-text="renewFormattedTimer"></span>
                    </div>
                </div>

                {{-- QR Code Container --}}
                <div class="bg-gradient-to-b from-slate-50 to-slate-100/90 p-2.5 sm:p-3 rounded-2xl border border-slate-200 shadow-inner inline-block mx-auto">
                    <div class="w-40 h-40 sm:w-44 sm:h-44 bg-white rounded-xl p-2 shadow-sm flex items-center justify-center mx-auto border border-slate-200">
                        <template x-if="renewQrisData && renewQrisData.qr_url">
                            <img :src="renewQrisData.qr_url" alt="QRIS Code" class="w-full h-full object-contain">
                        </template>
                    </div>
                    <p class="font-mono text-[11px] text-slate-500 mt-1 font-semibold" x-text="renewPayment ? renewPayment.invoice_number : ''"></p>
                </div>

                {{-- Total Tagihan Display --}}
                <div class="p-2.5 bg-slate-900 text-white rounded-xl">
                    <p class="text-[10px] text-slate-400 font-semibold uppercase tracking-wider">Total Tagihan Membership</p>
                    <p class="font-display font-black text-xl text-lime-400 mt-0.5 font-mono">
                        Rp<span x-text="renewPayment ? Number(renewPayment.amount).toLocaleString('id-ID') : Number(selectedPackagePrice).toLocaleString('id-ID')"></span>
                    </p>
                    <div class="flex items-center justify-center gap-2 text-[11px] text-slate-300 mt-0.5">
                        <span x-text="'Paket: ' + selectedPackageName"></span>
                        <span>•</span>
                        <span class="text-lime-300 font-semibold" x-text="'Perpanjangan: ' + selectedPackageDuration + ' Bulan'"></span>
                    </div>
                </div>

                {{-- Live Status Indicator --}}
                <div class="py-1.5 px-2.5 rounded-lg bg-emerald-50 border border-emerald-200 flex items-center justify-center gap-1.5 text-[11px] font-bold text-emerald-800">
                    <span class="w-2 h-2 rounded-full bg-emerald-500 animate-ping"></span>
                    <span>Menunggu Pembayaran Anda...</span>
                </div>

                {{-- SIMULATOR TEST ACTION BOX --}}
                <div class="p-2.5 bg-gradient-to-br from-indigo-50 via-purple-50 to-blue-50 border border-indigo-200 rounded-xl text-left space-y-1.5">
                    <div class="flex items-center justify-between">
                        <span class="text-[10px] font-bold text-indigo-950 uppercase tracking-wider flex items-center gap-1">
                            <span>🧪</span> Mode Uji Coba (Simulator)
                        </span>
                        <span class="text-[9px] bg-indigo-200 text-indigo-900 px-1.5 py-0.5 rounded-full font-bold">Midtrans Ready</span>
                    </div>
                    <p class="text-[10px] text-indigo-800 leading-snug">
                        Klik tombol di bawah untuk simulasi telah bayar via BCA Mobile / GoPay / OVO:
                    </p>
                    <button 
                        type="button" 
                        @click="simulateRenewalPayment()"
                        :disabled="renewSimulating"
                        class="w-full py-2 px-3 bg-indigo-600 hover:bg-indigo-700 active:scale-95 text-white rounded-lg text-xs font-bold transition shadow-sm flex items-center justify-center gap-1.5"
                    >
                        <span x-show="!renewSimulating">⚡ Simulasi: Bayar QRIS Sukses</span>
                        <span x-show="renewSimulating">Memverifikasi Pembayaran...</span>
                    </button>
                </div>

                {{-- Cancel Action --}}
                <div class="pt-1">
                    <button 
                        type="button" 
                        @click="cancelRenewalOrder()"
                        class="text-xs text-rose-600 hover:text-rose-800 font-bold hover:underline"
                    >
                        ✕ Batalkan Transaksi
                    </button>
                </div>
            </div>

            {{-- =========================================================
                STEP 3: MODAL SUKSES PERPANJANGAN & AUTO EXTENSION
            ========================================================= --}}
            <div x-show="renewStep === 'success'" class="text-center space-y-3">
                <div class="w-12 h-12 sm:w-14 sm:h-14 mx-auto rounded-2xl bg-emerald-100 text-emerald-600 flex items-center justify-center text-xl sm:text-2xl mb-1 shadow-inner">
                    ✓
                </div>

                <span class="px-2.5 py-0.5 rounded-full bg-emerald-100 text-emerald-800 font-black text-[10px] uppercase tracking-wider">
                    Lunas via QRIS
                </span>

                <h3 class="font-display font-extrabold text-lg sm:text-xl text-slate-900 mt-1">Perpanjangan Berhasil! 🎉</h3>
                <p class="font-mono text-xs text-slate-500 font-bold" x-text="renewPayment ? renewPayment.invoice_number : ''"></p>

                {{-- EXTENSION SUMMARY CARD --}}
                <div class="p-3.5 sm:p-4 rounded-2xl bg-lime-50 border border-lime-200 text-left space-y-2">
                    <div class="flex items-center justify-between text-xs pb-2 border-b border-lime-200/60">
                        <span class="text-slate-600 font-medium">Paket Keanggotaan</span>
                        <span class="font-bold text-slate-900" x-text="selectedPackageName + ' (' + selectedPackageDuration + ' Bulan)'"></span>
                    </div>

                    <div class="flex items-center justify-between text-xs pb-2 border-b border-lime-200/60">
                        <span class="text-slate-600 font-medium">Masa Aktif Baru</span>
                        <span class="font-bold text-emerald-700 bg-white px-2 py-0.5 rounded border border-emerald-200 font-mono" x-text="'s/d ' + (renewSuccessData && renewSuccessData.new_expire_date ? renewSuccessData.new_expire_date : renewNewExpirePreview)"></span>
                    </div>

                    <div class="flex items-center justify-between text-xs pb-2 border-b border-lime-200/60">
                        <span class="text-slate-600 font-medium">Status RFID Gate</span>
                        <span class="inline-flex items-center gap-1 font-bold text-emerald-700">
                            <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
                            <span>Aktif & Siap Digunakan</span>
                        </span>
                    </div>

                    <div class="flex items-center justify-between text-xs pt-1">
                        <span class="text-slate-700 font-bold">Total Pembayaran:</span>
                        <span class="font-display font-black text-sm text-emerald-800 font-mono">
                            Rp<span x-text="renewPayment ? Number(renewPayment.amount).toLocaleString('id-ID') : Number(selectedPackagePrice).toLocaleString('id-ID')"></span>
                        </span>
                    </div>
                </div>

                {{-- Action Buttons --}}
                <div class="pt-2 space-y-2">
                    <button 
                        type="button" 
                        @click="invoiceModal = true; closeRenewalModal()" 
                        class="w-full py-2.5 px-4 bg-slate-900 hover:bg-slate-800 text-white rounded-xl text-xs font-bold transition flex items-center justify-center gap-1.5 shadow-sm"
                    >
                        <span>📄</span>
                        <span>Buka Bukti Keanggotaan / E-Receipt</span>
                    </button>
                    <button 
                        type="button" 
                        @click="finishAndReload()" 
                        class="w-full py-2 px-4 rounded-xl border border-slate-200 text-slate-700 text-xs font-semibold hover:bg-slate-100 transition"
                    >
                        Selesai & Muat Ulang Dashboard
                    </button>
                </div>
            </div>

        </div>
    </div>

</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const checkinEl = document.getElementById('member-card-checkin');
    const checkoutEl = document.getElementById('member-card-checkout');
    const historyListContainer = document.getElementById('member-attendance-history-list');
    const badgeVisitsMonth = document.getElementById('stat-visits-month-badge');
    const textProgressPercent = document.getElementById('stat-progress-percent');
    const barProgress = document.getElementById('stat-progress-bar');
    const textTotalVisits = document.getElementById('stat-total-visits');

    let lastAttendanceSignature = '';

    function escapeHtml(str) {
        if (!str) return '';
        return String(str)
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#039;');
    }

    function formatDuration(totalSeconds) {
        if (totalSeconds < 0) totalSeconds = 0;
        if (totalSeconds < 60) return totalSeconds + ' Detik';

        const hours = Math.floor(totalSeconds / 3600);
        const minutes = Math.floor((totalSeconds % 3600) / 60);
        const seconds = totalSeconds % 60;

        const parts = [];
        if (hours > 0) parts.push(hours + ' Jam');
        if (minutes > 0) parts.push(minutes + ' Menit');
        if (seconds > 0) parts.push(seconds + ' Detik');
        return parts.join(' ');
    }

    function tickLiveDurations() {
        const liveTimerEls = document.querySelectorAll('.live-duration-timer');
        if (!liveTimerEls.length) return;
        const nowTimestamp = Math.floor(Date.now() / 1000);

        liveTimerEls.forEach(el => {
            const start = parseInt(el.getAttribute('data-start-time'), 10);
            if (start && !isNaN(start)) {
                const diff = nowTimestamp - start;
                el.textContent = formatDuration(diff);
            }
        });
    }

    setInterval(tickLiveDurations, 1000);

    function renderAttendanceHistory(items) {
        if (!historyListContainer || !Array.isArray(items)) return;

        if (items.length === 0) {
            historyListContainer.innerHTML = `
                <div class="text-center py-8 text-slate-400 text-xs">
                    <p class="text-2xl mb-1">🏷️</p>
                    <p class="font-bold text-slate-700">Belum ada riwayat presensi tercatat.</p>
                    <p class="mt-1 text-slate-500">Cukup tempelkan kartu RFID Anda di gate scanner saat tiba dan selesai latihan di gym!</p>
                </div>
            `;
            return;
        }

        historyListContainer.innerHTML = items.map(a => {
            const isTraining = a.is_training;
            const iconHtml = isTraining
                ? `<div class="w-9 h-9 rounded-xl bg-lime-400 text-slate-950 animate-pulse flex items-center justify-center shrink-0"><span class="text-xs font-black">🔥</span></div>`
                : `<div class="w-9 h-9 rounded-xl bg-lime-100 text-lime-800 flex items-center justify-center shrink-0">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                   </div>`;

            const badgeHtml = isTraining
                ? `<span class="text-[11px] font-black text-slate-950 bg-lime-400 px-2.5 py-1 rounded-lg shadow-sm animate-pulse inline-flex items-center gap-1"><span>🔥 Sedang Latihan</span></span>`
                : `<span class="text-[11px] font-bold text-slate-700 bg-white border border-slate-200 px-2.5 py-1 rounded-lg shadow-2xs inline-flex items-center gap-1"><span>✓ Selesai</span></span>`;

            const checkoutValHtml = a.check_out_time
                ? `<span class="font-mono font-bold text-slate-800">${escapeHtml(a.check_out_time)}</span>`
                : `<span class="font-bold text-lime-700 italic">Belum Tap Out</span>`;

            const durationClass = isTraining ? 'live-duration-timer' : '';
            const durationDataAttr = isTraining ? `data-start-time="${a.check_in_timestamp}"` : '';

            return `
                <div class="p-3.5 rounded-xl bg-slate-50 border border-slate-100 hover:bg-slate-100/70 transition space-y-2">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-3">
                            ${iconHtml}
                            <div>
                                <p class="text-sm font-semibold text-slate-900">${escapeHtml(a.date_formatted)}</p>
                                <p class="text-xs text-slate-500">
                                    Akses: <span class="font-medium text-slate-700">${escapeHtml(a.method_label)}</span>
                                </p>
                            </div>
                        </div>
                        <div>${badgeHtml}</div>
                    </div>

                    <div class="pt-2 border-t border-slate-200/60 grid grid-cols-3 gap-2 text-xs">
                        <div class="bg-white p-2 rounded-lg border border-slate-200/70">
                            <span class="text-[10px] uppercase font-semibold text-slate-400 block">Jam Masuk</span>
                            <span class="font-mono font-bold text-slate-800">${escapeHtml(a.check_in_time)}</span>
                        </div>

                        <div class="bg-white p-2 rounded-lg border border-slate-200/70">
                            <span class="text-[10px] uppercase font-semibold text-slate-400 block">Jam Keluar</span>
                            ${checkoutValHtml}
                        </div>

                        <div class="bg-white p-2 rounded-lg border border-slate-200/70">
                            <span class="text-[10px] uppercase font-semibold text-slate-400 block">Durasi Sesi</span>
                            <span class="font-mono font-extrabold text-lime-700 ${durationClass}" ${durationDataAttr}>${escapeHtml(a.duration_formatted)}</span>
                        </div>
                    </div>
                </div>
            `;
        }).join('');
    }

    async function pollMemberAttendance() {
        if (document.hidden) return;

        try {
            const response = await fetch('{{ route('member.latest-attendance') }}', {
                headers: { 'Accept': 'application/json' }
            });

            if (!response.ok) return;
            const data = await response.json();
            if (!data.success) return;

            // Signature untuk mendeteksi perubahan data presensi
            const signature = (data.attendances || []).map(a => `${a.id}_${a.check_in_time}_${a.check_out_time || ''}_${a.is_training ? '1' : '0'}`).join('|');

            if (signature !== lastAttendanceSignature) {
                lastAttendanceSignature = signature;

                // 1. Update Card Member Pass
                if (checkinEl) {
                    let text = data.check_in_time || '-';
                    if (!data.is_today && data.check_in_date && data.check_in_time !== '-') {
                        text += ` <span class="text-[10px] text-slate-400 font-normal">(${data.check_in_date})</span>`;
                    }
                    checkinEl.innerHTML = text;
                }

                if (checkoutEl) {
                    if (data.check_out_time) {
                        let text = data.check_out_time;
                        if (!data.is_today && data.check_in_date) {
                            text += ` <span class="text-[10px] text-slate-400 font-normal">(${data.check_in_date})</span>`;
                        }
                        checkoutEl.innerHTML = text;
                    } else if (data.is_training) {
                        checkoutEl.innerHTML = `<span class="text-lime-400 font-bold text-xs inline-flex items-center gap-1"><span class="w-1.5 h-1.5 rounded-full bg-lime-400 animate-pulse"></span>Sedang Latihan</span>`;
                    } else {
                        checkoutEl.innerHTML = `<span class="text-slate-500 font-normal">-</span>`;
                    }
                }

                // 2. Update Stats Target Latihan
                if (badgeVisitsMonth && data.visits_this_month !== undefined) {
                    badgeVisitsMonth.textContent = `${data.visits_this_month}/12 Sesi`;
                }
                if (textProgressPercent && data.progress_percent !== undefined) {
                    textProgressPercent.textContent = data.progress_percent;
                }
                if (barProgress && data.progress_percent !== undefined) {
                    barProgress.style.width = `${data.progress_percent}%`;
                }
                if (textTotalVisits && data.total_visits !== undefined) {
                    textTotalVisits.textContent = `${data.total_visits} kali`;
                }

                // 3. Render Riwayat Presensi & Sesi Latihan
                if (Array.isArray(data.attendances)) {
                    renderAttendanceHistory(data.attendances);
                }
            }
        } catch (e) {}
    }

    setInterval(pollMemberAttendance, 2000);
});
</script>
@endsection
