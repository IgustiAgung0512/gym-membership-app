<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="scroll-smooth">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="theme-color" content="#0F172A">
    <title>GymPulse · Pusat Kebugaran Modern & Smart RFID Gym</title>
    <meta name="description" content="GymPulse adalah pusat kebugaran modern berfasilitas lengkap dengan akses instan teknologi Smart RFID Tap-In, personal trainer bersertifikat, dan komunitas fitness terbaik.">

    <!-- Favicon -->
    <link rel="icon" type="image/svg+xml" href="{{ asset('favicon.svg') }}?v=3">
    <link rel="icon" type="image/png" href="{{ asset('favicon.png') }}?v=3">
    <link rel="shortcut icon" href="{{ asset('favicon.ico') }}?v=3">

    <!-- DNS Prefetch & Preconnect -->
    <link rel="dns-prefetch" href="https://fonts.googleapis.com">
    <link rel="dns-prefetch" href="https://fonts.gstatic.com">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>

    <!-- Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Space+Grotesk:wght@500;600;700;800&display=swap" rel="stylesheet">

    <!-- CSRF Token -->
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <!-- Alpine.js -->
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>

    <!-- Preload LCP Hero Image -->
    <link rel="preload" as="image" href="{{ asset('images/gym/hero.jpg') }}" fetchpriority="high">

    <!-- Vite Assets (CSS + Tailwind via build) -->
    @vite(['resources/css/app.css', 'resources/css/landing.css'])

    <style>
        /* Skip to content for accessibility */
        .skip-to-content {
            position: absolute;
            left: -9999px;
            top: auto;
            width: 1px;
            height: 1px;
            overflow: hidden;
            z-index: -1;
        }
        .skip-to-content:focus {
            position: fixed;
            top: 16px;
            left: 16px;
            width: auto;
            height: auto;
            padding: 12px 20px;
            background: #0F172A;
            color: #FFFFFF;
            font-weight: 700;
            font-size: 0.875rem;
            border-radius: 8px;
            z-index: 9999;
            outline: 2px solid #84CC16;
            outline-offset: 2px;
        }
        .nav-light-blur {
            background: rgba(255, 255, 255, 0.92);
            backdrop-filter: blur(12px);
            -webkit-backdrop-filter: blur(12px);
            border-bottom: 1px solid #E2E8F0;
        }
    </style>
</head>

<body class="landing-page bg-[#F8FAFC] text-slate-800 antialiased selection:bg-lime-200 selection:text-slate-900" x-data="onlineRegistrationApp()">

    <!-- Skip to main content (Accessibility) -->
    <a href="#main-content" class="skip-to-content">Lewati ke konten utama</a>

    <!-- =========================================================================
         TOP STATUS BAR
         ========================================================================= -->
    <div class="bg-slate-900 text-white text-xs py-2 px-4 border-b border-slate-800">
        <div class="max-w-7xl mx-auto flex flex-wrap items-center justify-between gap-2">
            <div class="flex items-center gap-3">
                <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full bg-lime-500/20 text-lime-400 font-semibold text-[11px]">
                    <span class="w-2 h-2 rounded-full bg-lime-400 animate-pulse"></span>
                    BUKA HARI INI
                </span>
                <span class="text-slate-300">Jam Operasional: <strong>06:00 - 22:00 WIB</strong> (Setiap Hari)</span>
            </div>
            <div class="flex items-center gap-4 text-[12px]">
                <span class="hidden md:inline text-slate-400">⚡ Akses Pintu Smart RFID Siap</span>
    <!-- Top Status Bar WhatsApp Link with aria-label -->
    <a href="https://wa.me/?text=Halo%20GymPulse,%20saya%20tertarik%20untuk%20mendaftar%20membership" target="_blank" rel="noopener noreferrer" aria-label="Chat WhatsApp GymPulse: +62 812-3456-7890 (buka tab baru)" class="text-lime-400 hover:text-lime-300 font-semibold flex items-center gap-1">
                        <span>Chat WhatsApp: +62 812-3456-7890</span>
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"></path></svg>
                    </a>
            </div>
        </div>
    </div>

    <!-- =========================================================================
         MAIN NAVIGATION BAR
         ========================================================================= -->
    <header class="sticky top-0 z-40 nav-light-blur">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex items-center justify-between h-20 gap-4">
                
                <!-- Brand Logo -->
                <a href="{{ route('home') }}" class="flex items-center gap-3 group shrink-0">
                    <div class="w-10 h-10 rounded-xl bg-slate-900 flex items-center justify-center font-display font-extrabold text-lime-400 text-xl shadow-sm group-hover:scale-105 transition-transform">
                        G
                    </div>
                    <div class="flex flex-col">
                        <span class="font-display font-extrabold text-2xl text-slate-900 tracking-tight leading-none">
                            GymPulse<span class="text-lime-600">.</span>
                        </span>
                        <span class="text-[10px] tracking-wider text-slate-500 uppercase font-semibold mt-0.5 whitespace-nowrap">
                            Smart RFID Fitness
                        </span>
                    </div>
                </a>

                <!-- Desktop Navigation Links (Clean & Spacious) -->
                <nav class="hidden lg:flex items-center gap-1 xl:gap-2 text-sm font-semibold text-slate-600" aria-label="Navigasi Utama">
                    <a href="#tentang" class="px-3 py-2 rounded-lg hover:text-slate-900 hover:bg-slate-100/80 transition-colors whitespace-nowrap">Tentang</a>
                    <a href="#fasilitas" class="px-3 py-2 rounded-lg hover:text-slate-900 hover:bg-slate-100/80 transition-colors whitespace-nowrap">Fasilitas</a>
                    <a href="#rfid-tech" class="px-3 py-2 rounded-lg hover:text-slate-900 hover:bg-slate-100/80 transition-colors inline-flex items-center gap-1.5 whitespace-nowrap">
                        <span class="w-2 h-2 rounded-full bg-lime-500" aria-hidden="true"></span>
                        <span>Smart RFID</span>
                    </a>
                    <a href="#program" class="px-3 py-2 rounded-lg hover:text-slate-900 hover:bg-slate-100/80 transition-colors whitespace-nowrap">Program</a>
                    <a href="#paket" class="px-3 py-2 rounded-lg hover:text-slate-900 hover:bg-slate-100/80 transition-colors whitespace-nowrap">Paket Member</a>
                    <a href="#kalkulator-bmi" class="px-3 py-2 rounded-lg hover:text-slate-900 hover:bg-slate-100/80 transition-colors whitespace-nowrap">Kalkulator BMI</a>
                    <a href="#faq" class="px-3 py-2 rounded-lg hover:text-slate-900 hover:bg-slate-100/80 transition-colors whitespace-nowrap">FAQ & Lokasi</a>
                </nav>

                <!-- Auth CTA Buttons (Optimized for Mobile & Desktop) -->
                <div class="flex items-center gap-1.5 sm:gap-2.5 shrink-0">
                    @auth
                        @php
                            $dashboardRoute = Auth::user()->isAdmin() 
                                ? route('admin.dashboard') 
                                : (Auth::user()->isCashier() 
                                    ? route('cashier.dashboard') 
                                    : route('member.dashboard'));
                            $dashboardLabel = Auth::user()->isAdmin() 
                                ? 'Dashboard Admin' 
                                : (Auth::user()->isCashier() 
                                    ? 'Dashboard Kasir' 
                                    : 'Dashboard Member');
                        @endphp
                        <a href="{{ $dashboardRoute }}" 
                           class="inline-flex items-center gap-1.5 px-3 sm:px-4 py-2 sm:py-2.5 rounded-xl bg-slate-900 hover:bg-slate-800 transition whitespace-nowrap shadow-sm"
                           style="color: #ffffff; font-size: 0.8125rem; font-weight: 700;">
                            <span class="w-2 h-2 rounded-full bg-lime-400 animate-pulse" aria-hidden="true"></span>
                            <span style="color: #ffffff;">{{ $dashboardLabel }}</span>
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true" style="color: #84cc16;"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5l7 7-7 7"/></svg>
                        </a>
                    @else
                        <a href="{{ route('login') }}" 
                           class="inline-flex items-center gap-1 px-2.5 sm:px-3.5 py-2 sm:py-2.5 rounded-xl border border-slate-200 bg-white text-slate-700 hover:text-slate-900 hover:border-slate-300 transition text-xs sm:text-sm font-semibold whitespace-nowrap shadow-2xs">
                            <svg class="w-3.5 h-3.5 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1"></path></svg>
                            <span>Masuk</span>
                        </a>

                        <a href="#paket" 
                           class="hidden sm:inline-flex items-center gap-1.5 px-4 py-2.5 rounded-xl bg-lime-500 text-slate-950 hover:bg-lime-400 transition text-xs sm:text-sm font-bold whitespace-nowrap shadow-sm">
                            <span>Daftar Member</span>
                        </a>
                    @endauth

                    <!-- Mobile Menu Button -->
                    <button type="button" id="mobileMenuBtn" 
                            aria-label="Buka Menu Navigasi" 
                            aria-expanded="false" 
                            aria-controls="mobileMenu" 
                            class="lg:hidden p-2 rounded-xl bg-white border border-slate-200 text-slate-700 hover:text-slate-900">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" id="menuIconClosed" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path></svg>
                        <svg class="w-6 h-6 hidden" fill="none" stroke="currentColor" viewBox="0 0 24 24" id="menuIconOpen" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                    </button>
                </div>

            </div>
        </div>

        <!-- Mobile Drawer -->
        <div class="lg:hidden hidden bg-white border-b border-slate-200 px-4 pt-3 pb-6 space-y-2.5" id="mobileMenu">
            <a href="#tentang" class="block py-2 text-slate-700 hover:text-lime-700 font-semibold border-b border-slate-100">Tentang GymPulse</a>
            <a href="#fasilitas" class="block py-2 text-slate-700 hover:text-lime-700 font-semibold border-b border-slate-100">Fasilitas Lengkap</a>
            <a href="#rfid-tech" class="block py-2 text-slate-700 hover:text-lime-700 font-semibold border-b border-slate-100">Teknologi Smart RFID</a>
            <a href="#program" class="block py-2 text-slate-700 hover:text-lime-700 font-semibold border-b border-slate-100">Program Latihan</a>
            <a href="#paket" class="block py-2 text-slate-700 hover:text-lime-700 font-semibold border-b border-slate-100">Paket Membership</a>
            <a href="#kalkulator-bmi" class="block py-2 text-slate-700 hover:text-lime-700 font-semibold border-b border-slate-100">Kalkulator BMI</a>
            <a href="#faq" class="block py-2 text-slate-700 hover:text-lime-700 font-semibold">FAQ & Lokasi Gym</a>
            <div class="pt-2">
                @auth
                    <a href="{{ Auth::user()->isAdmin() ? route('admin.dashboard') : (Auth::user()->isCashier() ? route('cashier.dashboard') : route('member.dashboard')) }}" 
                       class="flex items-center justify-center gap-2 w-full py-2.5 rounded-xl bg-slate-900 font-bold text-sm hover:bg-slate-800 transition"
                       style="color: #ffffff;">
                        {{ Auth::user()->isAdmin() ? 'Dashboard Admin' : (Auth::user()->isCashier() ? 'Dashboard Kasir' : 'Dashboard Member') }}
                    </a>
                @else
                    <div class="grid grid-cols-2 gap-2">
                        <a href="{{ route('login') }}" class="flex items-center justify-center py-2.5 rounded-xl border border-slate-300 bg-white text-slate-800 font-bold text-xs hover:bg-slate-50 transition">
                            Masuk Portal
                        </a>
                        <a href="#paket" onclick="document.getElementById('mobileMenu').classList.add('hidden')" class="flex items-center justify-center py-2.5 rounded-xl bg-lime-500 text-slate-950 font-bold text-xs hover:bg-lime-400 transition">
                            Daftar Member
                        </a>
                    </div>
                @endauth
            </div>
        </div>
    </header>

    <main id="main-content">

        <!-- =========================================================================
             HERO SECTION (LIGHT THEME)
             ========================================================================= -->
        <section class="relative pt-12 pb-20 lg:pt-16 lg:pb-28 bg-gradient-to-b from-white via-slate-50 to-[#F8FAFC] border-b border-slate-200" id="hero">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                
                <div class="grid grid-cols-1 lg:grid-cols-12 gap-12 lg:gap-8 items-center">
                    
                    <!-- Left Hero Content -->
                    <div class="lg:col-span-7 space-y-7 text-center lg:text-left">
                        
                        <!-- Eyebrow Badge -->
                        <div class="inline-flex items-center gap-2 px-3.5 py-1.5 rounded-full bg-slate-100 border border-slate-200 text-slate-800 text-xs sm:text-sm font-semibold">
                            <span class="w-2 h-2 rounded-full bg-lime-600"></span>
                            <span class="text-lime-700 font-bold">SMART FITNESS HUB</span>
                            <span class="text-slate-400">•</span>
                            <span>Akses Kartu RFID Cepat & Praktis</span>
                        </div>

                        <!-- Main Heading -->
                        <h1 class="font-display font-extrabold text-4xl sm:text-5xl lg:text-6xl text-slate-900 tracking-tight leading-[1.12]">
                            Pusat Kebugaran Modern untuk <span class="text-lime-700">Transformasi Tubuh</span> Maksimal.
                        </h1>

                        <!-- Subtitle -->
                        <p class="text-base sm:text-lg text-slate-600 max-w-2xl mx-auto lg:mx-0 leading-relaxed font-normal">
                            Nikmati pengalaman latihan berstandar internasional dengan 100+ peralatan modern, pelatih bersertifikat resmi, kebersihan terjamin, dan kemudahan check-in tap-in RFID tanpa antre.
                        </p>

                        <!-- Action Buttons -->
                        <div class="flex flex-col sm:flex-row items-center justify-center lg:justify-start gap-4 pt-2">
                            <a href="#paket" class="btn-lime-action w-full sm:w-auto text-base py-3.5 px-7">
                                <span>Lihat Paket Membership</span>
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 14l-7 7m0 0l-7-7m7 7V3"></path></svg>
                            </a>
                            <a href="{{ route('login') }}" class="btn-secondary-action w-full sm:w-auto text-base py-3.5 px-6">
                                <span>Masuk ke Portal Member</span>
                                <svg class="w-5 h-5 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"></path></svg>
                            </a>
                        </div>

                        <!-- Highlights Numbers -->
                        <div class="pt-6 border-t border-slate-200 grid grid-cols-2 sm:grid-cols-4 gap-4">
                            <div class="space-y-0.5">
                                <div class="font-display font-bold text-2xl sm:text-3xl text-slate-900">1,500+</div>
                                <div class="text-xs text-slate-500 font-medium">Member Aktif</div>
                            </div>
                            <div class="space-y-0.5">
                                <div class="font-display font-bold text-2xl sm:text-3xl text-slate-900">100+</div>
                                <div class="text-xs text-slate-500 font-medium">Alat Standar Global</div>
                            </div>
                            <div class="space-y-0.5">
                                <div class="font-display font-bold text-2xl sm:text-3xl text-slate-900">25+</div>
                                <div class="text-xs text-slate-500 font-medium">Trainer Bersertifikat</div>
                            </div>
                            <div class="space-y-0.5">
                                <div class="font-display font-bold text-2xl sm:text-3xl text-lime-700 font-bold">0.5 Detik</div>
                                <div class="text-xs text-slate-500 font-medium">Kecepatan RFID Tap</div>
                            </div>
                        </div>

                    </div>

                    <!-- Right Hero Visual -->
                    <div class="lg:col-span-5 relative">
                        <div class="relative rounded-3xl overflow-hidden border border-slate-200 shadow-xl bg-white">
                            <img src="{{ asset('images/gym/hero.jpg') }}" 
                                 alt="Fasilitas gym modern GymPulse dengan peralatan lengkap berstandar internasional" 
                                 class="w-full h-[450px] sm:h-[500px] object-cover"
                                 width="900" height="500"
                                 fetchpriority="high"
                                 decoding="async">
                            
                            <!-- Bottom floating card -->
                            <div class="absolute bottom-5 left-5 right-5">
                                <div class="bg-white/95 backdrop-blur-md p-4 sm:p-5 rounded-2xl border border-slate-200 shadow-lg space-y-3">
                                    <div class="flex items-center justify-between">
                                        <div class="flex items-center gap-2.5">
                                            <div class="w-8 h-8 rounded-lg bg-lime-100 text-lime-700 flex items-center justify-center font-bold">
                                                ✓
                                            </div>
                                            <div>
                                                <div class="text-xs font-bold text-slate-900">Check-In RFID Otomatis</div>
                                                <div class="text-[11px] text-slate-500">Pencatatan Absensi & Notifikasi WA</div>
                                            </div>
                                        </div>
                                        <span class="text-[11px] font-bold text-emerald-700 px-2 py-0.5 rounded bg-emerald-100">TERVERIFIKASI</span>
                                    </div>
                                    <div class="p-2.5 rounded-xl bg-slate-50 border border-slate-200/80 flex items-center justify-between text-xs">
                                        <span class="text-slate-600">Akses Gate: <strong>Gate 01 Terbuka</strong></span>
                                        <span class="font-mono text-slate-500 text-[11px]">06:45 WIB</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                </div>

            </div>
        </section>


        <!-- =========================================================================
             SECTION: SMART RFID SHOWCASE
             ========================================================================= -->
        <section class="py-20 bg-white border-b border-slate-200" id="rfid-tech">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                
                <div class="grid grid-cols-1 lg:grid-cols-12 gap-12 items-center">
                    
                    <!-- Left: Interactive RFID Card -->
                    <div class="lg:col-span-5 space-y-6">
                        <div class="rfid-box-clean">
                            <div class="flex items-center justify-between mb-4">
                                <span class="badge-green-clean">
                                    SISTEM TAP-IN
                                </span>
                                <span class="text-xs font-mono text-slate-400">SMART GATE READER</span>
                            </div>

                            <div class="flex justify-center my-6">
                                <div class="rfid-card-clean" id="demoCard">
                                    <div class="flex items-center justify-between">
                                        <span class="font-display font-bold text-lg tracking-wide text-white">GymPulse</span>
                                        <div class="w-7 h-7 rounded-lg bg-lime-400 text-slate-900 flex items-center justify-center font-bold text-xs">G</div>
                                    </div>
                                    <div class="space-y-1">
                                        <div class="text-[10px] text-slate-400 uppercase tracking-wider">Kartu Anggota Resmi</div>
                                        <div class="font-mono text-xs text-lime-300 font-bold">UID: 9A-4B-8C-F1</div>
                                    </div>
                                    <div class="flex items-center justify-between text-[11px] text-slate-300 border-t border-slate-700/60 pt-2">
                                        <span>MEMBERSHIP AKTIF</span>
                                        <span class="font-mono text-lime-400">PASSED</span>
                                    </div>
                                </div>
                            </div>

                            <div class="text-center space-y-3 pt-2">
                                <button type="button" onclick="simulateTap()" id="tapButton" class="btn-primary-action w-full text-sm py-3">
                                    ⚡ Simulasi Tempel Kartu (Tap Here)
                                </button>
                                <p class="text-xs text-slate-500" id="tapFeedback">
                                    Tekan tombol untuk melihat respons simulasi scan kartu.
                                </p>
                            </div>
                        </div>
                    </div>

                    <!-- Right: Benefits -->
                    <div class="lg:col-span-7 space-y-8">
                        <div class="space-y-3">
                            <span class="text-xs font-bold text-lime-700 uppercase tracking-wider">TEKNOLOGI PINTAR GYMPULSE</span>
                            <h2 class="font-display font-extrabold text-3xl sm:text-4xl text-slate-900 tracking-tight">
                                Masuk Gym Cepat & Nyaman Tanpa Antre dengan <span class="text-lime-700">Kartu RFID</span>
                            </h2>
                            <p class="text-slate-600 text-base leading-relaxed">
                                Setiap member terdaftar dibekali kartu RFID unik. Cukup sentuhkan kartu di gate pintu masuk, sistem akan memverifikasi status aktif, mencatat absensi, dan mengirimkan notifikasi secara otomatis.
                            </p>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                            
                            <div class="clean-panel p-5 space-y-2.5">
                                <div class="w-10 h-10 rounded-xl bg-lime-100 text-lime-700 flex items-center justify-center">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                </div>
                                <h3 class="font-display font-bold text-base text-slate-900">Akses Masuk 0.5 Detik</h3>
                                <p class="text-xs text-slate-600 leading-relaxed">Tidak perlu antre atau mengisi buku manual. Pintu turnstile terbuka seketika setelah kartu ditempelkan.</p>
                            </div>

                            <div class="clean-panel p-5 space-y-2.5">
                                <div class="w-10 h-10 rounded-xl bg-emerald-100 text-emerald-700 flex items-center justify-center">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"></path></svg>
                                </div>
                                <h3 class="font-display font-bold text-base text-slate-900">Notifikasi WhatsApp Otomatis</h3>
                                <p class="text-xs text-slate-600 leading-relaxed">Pemberitahuan kehadiran dan informasi sisa masa aktif membership dikirimkan langsung ke nomor WA Anda.</p>
                            </div>

                            <div class="clean-panel p-5 space-y-2.5">
                                <div class="w-10 h-10 rounded-xl bg-blue-100 text-blue-700 flex items-center justify-center">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path></svg>
                                </div>
                                <h3 class="font-display font-bold text-base text-slate-900">Pantau Absensi di Portal</h3>
                                <p class="text-xs text-slate-600 leading-relaxed">Cek log latihan dan riwayat kedatangan Anda secara rinci melalui dashboard akun member Anda.</p>
                            </div>

                            <div class="clean-panel p-5 space-y-2.5">
                                <div class="w-10 h-10 rounded-xl bg-amber-100 text-amber-700 flex items-center justify-center">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"></path></svg>
                                </div>
                                <h3 class="font-display font-bold text-base text-slate-900">Keamanan Terjamin</h3>
                                <p class="text-xs text-slate-600 leading-relaxed">UID kartu terenkripsi. Jika kartu hilang, admin dapat langsung memblokir kartu lama dan menerbitkan kartu baru.</p>
                            </div>

                        </div>
                    </div>

                </div>

            </div>
        </section>


        <!-- =========================================================================
             SECTION: TENTANG GYMPULSE
             ========================================================================= -->
        <section class="py-20 bg-[#F8FAFC]" id="tentang">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-14">
                
                <div class="text-center max-w-3xl mx-auto space-y-3">
                    <span class="badge-clean">TENTANG KAMI</span>
                    <h2 class="font-display font-extrabold text-3xl sm:text-4xl text-slate-900 tracking-tight">
                        Tempat Terbaik untuk Memulai & Menjaga <span class="text-lime-700">Kebugaran Anda</span>
                    </h2>
                    <p class="text-slate-600 text-base leading-relaxed">
                        GymPulse dirancang untuk semua kalangan—dari pemula yang baru pertama kali melangkah ke gym hingga penggiat kebugaran berpengalaman. Kami menjamin lingkungan yang bersih, nyaman, dan suportif.
                    </p>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    
                    <div class="clean-panel-interactive p-7 space-y-3">
                        <div class="w-12 h-12 rounded-xl bg-lime-100 text-lime-700 flex items-center justify-center font-bold text-lg">
                            🎯
                        </div>
                        <h3 class="font-display font-bold text-xl text-slate-900">Visi Kami</h3>
                        <p class="text-slate-600 text-sm leading-relaxed">
                            Mewujudkan masyarakat yang lebih sehat, bugar, dan berenergi tinggi melalui fasilitas olahraga berstandar tinggi yang mudah diakses.
                        </p>
                    </div>

                    <div class="clean-panel-interactive p-7 space-y-3">
                        <div class="w-12 h-12 rounded-xl bg-blue-100 text-blue-700 flex items-center justify-center font-bold text-lg">
                            ⚡
                        </div>
                        <h3 class="font-display font-bold text-xl text-slate-900">Misi & Kualitas</h3>
                        <p class="text-slate-600 text-sm leading-relaxed">
                            Menyediakan peralatan berkualitas internasional, pelatih bersertifikasi resmi, kebersihan ruangan yang ketat, serta pelayanan ramah.
                        </p>
                    </div>

                    <div class="clean-panel-interactive p-7 space-y-3">
                        <div class="w-12 h-12 rounded-xl bg-rose-100 text-rose-700 flex items-center justify-center font-bold text-lg">
                            🤝
                        </div>
                        <h3 class="font-display font-bold text-xl text-slate-900">Komunitas Positif</h3>
                        <p class="text-slate-600 text-sm leading-relaxed">
                            Membangun atmosfer olahraga yang ramah tanpa rasa canggung (*zero intimidation*), saling memotivasi untuk mencapai target hidup sehat.
                        </p>
                    </div>

                </div>

            </div>
        </section>


        <!-- =========================================================================
             SECTION: FASILITAS UNGGULAN
             ========================================================================= -->
        <section class="py-20 bg-white border-y border-slate-200" id="fasilitas">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-14">
                
                <div class="flex flex-col md:flex-row md:items-end justify-between gap-6">
                    <div class="space-y-3 max-w-2xl">
                        <span class="badge-clean">FASILITAS LENGKAP</span>
                        <h2 class="font-display font-extrabold text-3xl sm:text-4xl text-slate-900 tracking-tight">
                            Peralatan Lengkap untuk <span class="text-lime-700">Setiap Kebutuhan Latihan</span>
                        </h2>
                        <p class="text-slate-600 text-base">
                            Ruangan ber-AC sejuk, penerangan optimal, dan pembersihan berkala untuk kenyamanan maksimal Anda.
                        </p>
                    </div>
                    <div>
                        <a href="#paket" class="btn-secondary-action text-sm">
                            Lihat Pilihan Membership →
                        </a>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-7">
                    
                    <!-- 1. Free Weights -->
                    <div class="clean-panel-interactive overflow-hidden group">
                        <div class="h-52 overflow-hidden relative">
                            <img src="{{ asset('images/gym/facilities.jpg') }}" 
                                 alt="Area free weights dan power rack di GymPulse" 
                                 class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300"
                                 width="600" height="208"
                                 loading="lazy"
                                 decoding="async">
                            <span class="absolute top-3 left-3 px-3 py-1 rounded-md bg-slate-900/80 text-white text-[11px] font-bold">
                                STRENGTH ZONE
                            </span>
                        </div>
                        <div class="p-5 space-y-1.5">
                            <h3 class="font-display font-bold text-lg text-slate-900">Free Weights & Power Racks</h3>
                            <p class="text-slate-600 text-xs leading-relaxed">Dumbbell lengkap dari 2.5 kg hingga 50 kg, barbell olympic, squat cage, dan flat/incline bench.</p>
                        </div>
                    </div>

                    <!-- 2. Cardio -->
                    <div class="clean-panel-interactive overflow-hidden group">
                        <div class="h-52 overflow-hidden relative">
                            <img src="{{ asset('images/gym/cardio.jpg') }}" 
                                 alt="Mesin kardio treadmill dan elliptical di GymPulse Cardio Theater" 
                                 class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300"
                                 width="600" height="208"
                                 loading="lazy"
                                 decoding="async">
                            <span class="absolute top-3 left-3 px-3 py-1 rounded-md bg-slate-900/80 text-white text-[11px] font-bold">
                                CARDIO THEATER
                            </span>
                        </div>
                        <div class="p-5 space-y-1.5">
                            <h3 class="font-display font-bold text-lg text-slate-900">Cardio Machines with Display</h3>
                            <p class="text-slate-600 text-xs leading-relaxed">Treadmill modern, Stairmaster, Elliptical, dan Assault Bike dengan pemantau detak jantung.</p>
                        </div>
                    </div>

                    <!-- 3. Functional Turf -->
                    <div class="clean-panel-interactive overflow-hidden group">
                        <div class="h-52 overflow-hidden relative">
                            <img src="{{ asset('images/gym/functional.jpg') }}" 
                                 alt="Area functional training dan sled track di GymPulse" 
                                 class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300"
                                 width="600" height="208"
                                 loading="lazy"
                                 decoding="async">
                            <span class="absolute top-3 left-3 px-3 py-1 rounded-md bg-slate-900/80 text-white text-[11px] font-bold">
                                FUNCTIONAL ARENA
                            </span>
                        </div>
                        <div class="p-5 space-y-1.5">
                            <h3 class="font-display font-bold text-lg text-slate-900">CrossFit & Sled Sprint Track</h3>
                            <p class="text-slate-600 text-xs leading-relaxed">Area rumput sintetis untuk latihan fungsional, battle rope, sled push, kettlebell, dan plyobox.</p>
                        </div>
                    </div>

                    <!-- 4. Selectorized Machines -->
                    <div class="clean-panel-interactive p-6 space-y-3">
                        <div class="w-10 h-10 rounded-xl bg-lime-100 text-lime-700 flex items-center justify-center font-bold">
                            🦾
                        </div>
                        <h3 class="font-display font-bold text-lg text-slate-900">Machine Resistance Line</h3>
                        <p class="text-slate-600 text-xs leading-relaxed">Mesin isolasi otot dengan pengaturan beban pin-loaded yang aman dan ramah bagi pemula.</p>
                    </div>

                    <!-- 5. Lockers & Shower -->
                    <div class="clean-panel-interactive p-6 space-y-3">
                        <div class="w-10 h-10 rounded-xl bg-blue-100 text-blue-700 flex items-center justify-center font-bold">
                            🚿
                        </div>
                        <h3 class="font-display font-bold text-lg text-slate-900">Loker & Shower Air Panas</h3>
                        <p class="text-slate-600 text-xs leading-relaxed">Kamar mandi bersih terpisah pria & wanita, shower air hangat, hairdryer, serta loker barang aman.</p>
                    </div>

                    <!-- 6. Recovery Bar -->
                    <div class="clean-panel-interactive p-6 space-y-3">
                        <div class="w-10 h-10 rounded-xl bg-amber-100 text-amber-700 flex items-center justify-center font-bold">
                            🥤
                        </div>
                        <h3 class="font-display font-bold text-lg text-slate-900">Lounge & Protein Bar</h3>
                        <p class="text-slate-600 text-xs leading-relaxed">Tersedia whey protein shake segar, suplemen pre-workout, air mineral, dan area santai ber-WiFi.</p>
                    </div>

                </div>

            </div>
        </section>


        <!-- =========================================================================
             SECTION: PROGRAM LATIHAN
             ========================================================================= -->
        <section class="py-20 bg-[#F8FAFC]" id="program">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-14">
                
                <div class="text-center max-w-3xl mx-auto space-y-3">
                    <span class="badge-clean">PROGRAM KEBUGARAN</span>
                    <h2 class="font-display font-extrabold text-3xl sm:text-4xl text-slate-900 tracking-tight">
                        Panduan Latihan Sesuai <span class="text-lime-700">Target Tubuh Anda</span>
                    </h2>
                    <p class="text-slate-600 text-base">
                        Program latihan terstruktur untuk membantu Anda mencapai bentuk fisik ideal secara sehat dan terukur.
                    </p>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                    
                    <div class="clean-panel-interactive p-6 space-y-3">
                        <div class="text-2xl">🔥</div>
                        <h3 class="font-display font-bold text-lg text-slate-900">Fat Loss & Body Sculpting</h3>
                        <p class="text-slate-600 text-xs leading-relaxed">Fokus pembakaran lemak dengan kombinasi latihan beban dan kardio intensif untuk menjaga metabolisme.</p>
                    </div>

                    <div class="clean-panel-interactive p-6 space-y-3">
                        <div class="text-2xl">💪</div>
                        <h3 class="font-display font-bold text-lg text-slate-900">Hypertrophy & Muscle Building</h3>
                        <p class="text-slate-600 text-xs leading-relaxed">Program penambahan massa otot bertahap dengan konsep beban progresif (*progressive overload*).</p>
                    </div>

                    <div class="clean-panel-interactive p-6 space-y-3">
                        <div class="text-2xl">🎯</div>
                        <h3 class="font-display font-bold text-lg text-slate-900">1-on-1 Personal Training</h3>
                        <p class="text-slate-600 text-xs leading-relaxed">Bimbingan intensif privat bersama trainer berlisensi. Koreksi form gerakan dan panduan pola makan.</p>
                    </div>

                    <div class="clean-panel-interactive p-6 space-y-3">
                        <div class="text-2xl">⚡</div>
                        <h3 class="font-display font-bold text-lg text-slate-900">Functional HIIT & Stamina</h3>
                        <p class="text-slate-600 text-xs leading-relaxed">Latihan mobilitas dan kelincahan untuk meningkatkan kapasitas paru-paru dan kebugaran harian.</p>
                    </div>

                    <div class="clean-panel-interactive p-6 space-y-3">
                        <div class="text-2xl">🧘</div>
                        <h3 class="font-display font-bold text-lg text-slate-900">Core Stability & Posture</h3>
                        <p class="text-slate-600 text-xs leading-relaxed">Penguatan otot perut & punggung untuk memperbaiki postur tubuh dan mengurangi pegal saat bekerja.</p>
                    </div>

                    <div class="clean-panel-interactive p-6 space-y-3">
                        <div class="text-2xl">🏆</div>
                        <h3 class="font-display font-bold text-lg text-slate-900">Strength & Powerlifting</h3>
                        <p class="text-slate-600 text-xs leading-relaxed">Optimalisasi teknik angkatan utama (Squat, Bench Press, Deadlift) dengan pengawasan ketat.</p>
                    </div>

                </div>

            </div>
        </section>


        <!-- =========================================================================
             SECTION: PAKET MEMBERSHIP
             ========================================================================= -->
        <section class="py-20 bg-white border-y border-slate-200" id="paket">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-14">
                
                <div class="text-center max-w-3xl mx-auto space-y-3">
                    <span class="badge-clean">BIAYA KEANGGOTAAN</span>
                    <h2 class="font-display font-extrabold text-3xl sm:text-4xl text-slate-900 tracking-tight">
                        Paket Membership <span class="text-lime-700">Transparan & Terjangkau</span>
                    </h2>
                    <p class="text-slate-600 text-base">
                        Semua paket sudah termasuk kartu akses RFID gratis, akses seluruh alat gym, dan loker pribadi.
                    </p>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 items-stretch">
                    
                    @if(isset($packages) && $packages->count() > 0)
                        @foreach($packages as $index => $pkg)
                            @php
                                $isPopular = ($index === 1 || str_contains(strtolower($pkg->name), '3') || str_contains(strtolower($pkg->name), 'populer'));
                            @endphp
                            <div class="clean-panel-interactive p-6 flex flex-col justify-between {{ $isPopular ? 'popular ring-2 ring-lime-500' : '' }}">
                                @if($isPopular)
                                    <div class="mb-3">
                                        <span class="inline-block px-3 py-0.5 rounded-full bg-lime-100 text-lime-800 text-[11px] font-bold uppercase tracking-wider">
                                            PALING POPULER
                                        </span>
                                    </div>
                                @endif

                                <div class="space-y-4">
                                    <div class="space-y-1">
                                        <h3 class="font-display font-bold text-xl text-slate-900">{{ $pkg->name }}</h3>
                                        <p class="text-xs text-slate-500">{{ $pkg->description ?: ($pkg->duration_months . ' Bulan akses penuh ke semua fasilitas.') }}</p>
                                    </div>

                                    <div class="space-y-0.5">
                                        <div class="text-xs text-slate-400">Biaya Paket</div>
                                        <div class="flex items-baseline gap-1">
                                            <span class="text-sm font-bold text-slate-900">Rp</span>
                                            <span class="font-display font-extrabold text-3xl text-slate-900">
                                                {{ number_format($pkg->price, 0, ',', '.') }}
                                            </span>
                                        </div>
                                        <div class="text-[11px] text-slate-500 font-medium">Masa aktif: {{ $pkg->duration_months }} Bulan</div>
                                    </div>

                                    <ul class="space-y-2 text-xs text-slate-600 pt-3 border-t border-slate-100">
                                        <li class="flex items-center gap-2"><span class="text-lime-600 font-bold">✓</span> Gratis Kartu RFID Tap (Ambil di Gym)</li>
                                        <li class="flex items-center gap-2"><span class="text-lime-600 font-bold">✓</span> Akses 7 Hari Seminggu</li>
                                        <li class="flex items-center gap-2"><span class="text-lime-600 font-bold">✓</span> Loker & Hot Shower</li>
                                        <li class="flex items-center gap-2"><span class="text-lime-600 font-bold">✓</span> Notifikasi WhatsApp Check-In</li>
                                        <li class="flex items-center gap-2"><span class="text-lime-600 font-bold">✓</span> Akun Portal Member Online</li>
                                    </ul>
                                </div>

                                <div class="pt-6 space-y-2">
                                    <button type="button" 
                                            @click="openModal({{ $pkg->id }}, '{{ addslashes($pkg->name) }}', {{ $pkg->price }}, {{ $pkg->duration_months }}, '{{ addslashes($pkg->description ?? '') }}')"
                                            class="{{ $isPopular ? 'btn-lime-action' : 'btn-secondary-action' }} w-full justify-center text-xs py-2.5 cursor-pointer shadow-sm">
                                        <span>⚡ Daftar Online Sekarang</span>
                                    </button>
                                    <div class="text-center">
                                        <a href="https://wa.me/?text={{ urlencode('Halo GymPulse, saya ingin tanya seputar paket: ' . $pkg->name) }}" target="_blank" class="text-[11px] text-slate-400 hover:text-slate-600 transition">
                                            Atau tanya via WhatsApp &rarr;
                                        </a>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    @else
                        <!-- Fallback Packages -->
                        <div class="clean-panel-interactive p-6 flex flex-col justify-between">
                            <div class="space-y-4">
                                <div>
                                    <h3 class="font-display font-bold text-xl text-slate-900">1 Bulan</h3>
                                    <p class="text-xs text-slate-500">Starter Pass untuk pemula.</p>
                                </div>
                                <div>
                                    <span class="text-sm font-bold text-slate-900">Rp</span>
                                    <span class="font-display font-extrabold text-3xl text-slate-900">250.000</span>
                                </div>
                                <ul class="space-y-2 text-xs text-slate-600 pt-3 border-t border-slate-100">
                                    <li class="flex items-center gap-2"><span class="text-lime-600 font-bold">✓</span> Kartu RFID Tap (Ambil di Gym)</li>
                                    <li class="flex items-center gap-2"><span class="text-lime-600 font-bold">✓</span> Akses 7 Hari / Minggu</li>
                                    <li class="flex items-center gap-2"><span class="text-lime-600 font-bold">✓</span> Loker & Hot Shower</li>
                                </ul>
                            </div>
                            <div class="pt-6 space-y-2">
                                <button type="button" 
                                        @click="openModal(1, '1 Bulan Starter', 250000, 1, 'Starter Pass untuk pemula')"
                                        class="btn-secondary-action w-full justify-center text-xs py-2.5 cursor-pointer shadow-sm">
                                    <span>⚡ Daftar Online</span>
                                </button>
                                <div class="text-center">
                                    <a href="https://wa.me/?text=Halo%20GymPulse,%20saya%20tertarik%20paket%201%20Bulan" target="_blank" class="text-[11px] text-slate-400 hover:text-slate-600">Atau via WhatsApp &rarr;</a>
                                </div>
                            </div>
                        </div>

                        <div class="clean-panel-interactive p-6 flex flex-col justify-between ring-2 ring-lime-500">
                            <div class="mb-2">
                                <span class="px-3 py-0.5 rounded-full bg-lime-100 text-lime-800 text-[11px] font-bold uppercase">PALING POPULER</span>
                            </div>
                            <div class="space-y-4">
                                <div>
                                    <h3 class="font-display font-bold text-xl text-slate-900">3 Bulan</h3>
                                    <p class="text-xs text-slate-500">Paling disukai untuk hasil nyata.</p>
                                </div>
                                <div>
                                    <span class="text-sm font-bold text-slate-900">Rp</span>
                                    <span class="font-display font-extrabold text-3xl text-slate-900">650.000</span>
                                </div>
                                <ul class="space-y-2 text-xs text-slate-600 pt-3 border-t border-slate-100">
                                    <li class="flex items-center gap-2"><span class="text-lime-600 font-bold">✓</span> Semua Fitur 1 Bulan</li>
                                    <li class="flex items-center gap-2"><span class="text-lime-600 font-bold">✓</span> 1x Konsultasi Fitness</li>
                                    <li class="flex items-center gap-2"><span class="text-lime-600 font-bold">✓</span> Notifikasi WA Check-in</li>
                                </ul>
                            </div>
                            <div class="pt-6 space-y-2">
                                <button type="button" 
                                        @click="openModal(2, '3 Bulan Popular', 650000, 3, 'Paling disukai untuk hasil nyata')"
                                        class="btn-lime-action w-full justify-center text-xs py-2.5 cursor-pointer shadow-sm">
                                    <span>⚡ Daftar Online</span>
                                </button>
                                <div class="text-center">
                                    <a href="https://wa.me/?text=Halo%20GymPulse,%20saya%20tertarik%20paket%203%20Bulan" target="_blank" class="text-[11px] text-slate-400 hover:text-slate-600">Atau via WhatsApp &rarr;</a>
                                </div>
                            </div>
                        </div>

                        <div class="clean-panel-interactive p-6 flex flex-col justify-between">
                            <div class="space-y-4">
                                <div>
                                    <h3 class="font-display font-bold text-xl text-slate-900">6 Bulan</h3>
                                    <p class="text-xs text-slate-500">Komitmen pembentukan fisik.</p>
                                </div>
                                <div>
                                    <span class="text-sm font-bold text-slate-900">Rp</span>
                                    <span class="font-display font-extrabold text-3xl text-slate-900">1.200.000</span>
                                </div>
                                <ul class="space-y-2 text-xs text-slate-600 pt-3 border-t border-slate-100">
                                    <li class="flex items-center gap-2"><span class="text-lime-600 font-bold">✓</span> Semua Fitur 3 Bulan</li>
                                    <li class="flex items-center gap-2"><span class="text-lime-600 font-bold">✓</span> 2x Sesi Personal Trainer</li>
                                </ul>
                            </div>
                            <div class="pt-6 space-y-2">
                                <button type="button" 
                                        @click="openModal(3, '6 Bulan Commitment', 1200000, 6, 'Komitmen pembentukan fisik')"
                                        class="btn-secondary-action w-full justify-center text-xs py-2.5 cursor-pointer shadow-sm">
                                    <span>⚡ Daftar Online</span>
                                </button>
                                <div class="text-center">
                                    <a href="https://wa.me/?text=Halo%20GymPulse,%20saya%20tertarik%20paket%206%20Bulan" target="_blank" class="text-[11px] text-slate-400 hover:text-slate-600">Atau via WhatsApp &rarr;</a>
                                </div>
                            </div>
                        </div>

                        <div class="clean-panel-interactive p-6 flex flex-col justify-between">
                            <div class="space-y-4">
                                <div>
                                    <h3 class="font-display font-bold text-xl text-slate-900">12 Bulan</h3>
                                    <p class="text-xs text-slate-500">Nilai paling hemat per bulan.</p>
                                </div>
                                <div>
                                    <span class="text-sm font-bold text-slate-900">Rp</span>
                                    <span class="font-display font-extrabold text-3xl text-slate-900">2.100.000</span>
                                </div>
                                <ul class="space-y-2 text-xs text-slate-600 pt-3 border-t border-slate-100">
                                    <li class="flex items-center gap-2"><span class="text-lime-600 font-bold">✓</span> Kartu Black Member Pass</li>
                                    <li class="flex items-center gap-2"><span class="text-lime-600 font-bold">✓</span> 4x Sesi Personal Trainer</li>
                                    <li class="flex items-center gap-2"><span class="text-lime-600 font-bold">✓</span> Free Kaos GymPulse</li>
                                </ul>
                            </div>
                            <div class="pt-6 space-y-2">
                                <button type="button" 
                                        @click="openModal(4, '12 Bulan Ultimate', 2100000, 12, 'Nilai paling hemat per bulan')"
                                        class="btn-secondary-action w-full justify-center text-xs py-2.5 cursor-pointer shadow-sm">
                                    <span>⚡ Daftar Online</span>
                                </button>
                                <div class="text-center">
                                    <a href="https://wa.me/?text=Halo%20GymPulse,%20saya%20tertarik%20paket%2012%20Bulan" target="_blank" class="text-[11px] text-slate-400 hover:text-slate-600">Atau via WhatsApp &rarr;</a>
                                </div>
                            </div>
                        </div>
                    @endif

                </div>

            </div>
        </section>


        <!-- =========================================================================
             SECTION: KALKULATOR BMI
             ========================================================================= -->
        <section class="py-20 bg-[#F8FAFC]" id="kalkulator-bmi">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                
                <div class="clean-panel p-8 sm:p-12">
                    <div class="grid grid-cols-1 lg:grid-cols-12 gap-10 items-center">
                        
                        <div class="lg:col-span-6 space-y-4">
                            <span class="badge-clean">FITUR INTERAKTIF</span>
                            <h2 class="font-display font-extrabold text-3xl sm:text-4xl text-slate-900 tracking-tight">
                                Cek <span class="text-lime-700">Body Mass Index (BMI)</span> Anda
                            </h2>
                            <p class="text-slate-600 text-sm leading-relaxed">
                                Masukkan tinggi dan berat badan Anda untuk mengetahui perkiraan status berat badan dan rekomendasi program latihan di GymPulse.
                            </p>

                            <div class="grid grid-cols-2 gap-3 pt-2 text-xs">
                                <div class="p-3 rounded-xl bg-slate-50 border border-slate-200">
                                    <span class="text-slate-500 block">&lt; 18.5</span>
                                    <strong class="text-blue-700">Berat Kurang</strong>
                                </div>
                                <div class="p-3 rounded-xl bg-slate-50 border border-slate-200">
                                    <span class="text-slate-500 block">18.5 - 24.9</span>
                                    <strong class="text-lime-700">Normal / Ideal</strong>
                                </div>
                                <div class="p-3 rounded-xl bg-slate-50 border border-slate-200">
                                    <span class="text-slate-500 block">25.0 - 29.9</span>
                                    <strong class="text-amber-700">Kelebihan Berat</strong>
                                </div>
                                <div class="p-3 rounded-xl bg-slate-50 border border-slate-200">
                                    <span class="text-slate-500 block">&ge; 30.0</span>
                                    <strong class="text-rose-700">Obesitas</strong>
                                </div>
                            </div>
                        </div>

                        <!-- Form -->
                        <div class="lg:col-span-6 bg-slate-50 p-6 sm:p-8 rounded-2xl border border-slate-200 space-y-4" role="form" aria-label="Kalkulator BMI">
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                <div class="space-y-1">
                                    <label for="bmiHeight" class="text-xs font-semibold text-slate-700">Tinggi Badan (cm)</label>
                                    <input type="number" id="bmiHeight" name="bmi-height"
                                           placeholder="Contoh: 170" 
                                           min="50" max="300" step="0.1"
                                           aria-required="true"
                                           aria-describedby="bmiHeightHint"
                                           class="w-full bg-white border border-slate-300 rounded-xl px-4 py-2.5 text-slate-900 text-sm focus:outline-none focus:border-lime-600">
                                    <span id="bmiHeightHint" class="sr-only">Masukkan tinggi badan antara 50 hingga 300 sentimeter</span>
                                </div>
                                <div class="space-y-1">
                                    <label for="bmiWeight" class="text-xs font-semibold text-slate-700">Berat Badan (kg)</label>
                                    <input type="number" id="bmiWeight" name="bmi-weight"
                                           placeholder="Contoh: 65" 
                                           min="10" max="500" step="0.1"
                                           aria-required="true"
                                           aria-describedby="bmiWeightHint"
                                           class="w-full bg-white border border-slate-300 rounded-xl px-4 py-2.5 text-slate-900 text-sm focus:outline-none focus:border-lime-600">
                                    <span id="bmiWeightHint" class="sr-only">Masukkan berat badan antara 10 hingga 500 kilogram</span>
                                </div>
                            </div>

                            <button type="button" onclick="calculateBMI()" class="btn-primary-action w-full text-sm py-3">
                                📊 Hitung Skor BMI
                            </button>

                            <!-- Result -->
                            <div id="bmiResultBox" class="hidden p-4 rounded-xl bg-white border border-slate-200 space-y-2" aria-live="polite" aria-atomic="true">
                                <div class="flex items-center justify-between">
                                    <span class="text-xs text-slate-500">Skor BMI:</span>
                                    <span id="bmiScoreText" class="font-display font-extrabold text-2xl text-slate-900">22.4</span>
                                </div>
                                <div class="flex items-center justify-between text-xs">
                                    <span class="text-slate-500">Kategori:</span>
                                    <span id="bmiCategoryText" class="font-bold text-lime-700">Berat Badan Ideal</span>
                                </div>
                                <div class="text-[12px] text-slate-600 pt-2 border-t border-slate-100" id="bmiAdviceText">
                                    Luar biasa! Pertahankan kebugaran tubuh Anda dengan latihan teratur di GymPulse.
                                </div>
                            </div>
                        </div>

                    </div>
                </div>

            </div>
        </section>


        <!-- =========================================================================
             SECTION: TRAINER PROFESIONAL
             ========================================================================= -->
        <section class="py-20 bg-white border-y border-slate-200" id="pelatih">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-14">
                
                <div class="text-center max-w-3xl mx-auto space-y-3">
                    <span class="badge-clean">PELATIH PROFESIONAL</span>
                    <h2 class="font-display font-extrabold text-3xl sm:text-4xl text-slate-900 tracking-tight">
                        Didukung Tim Trainer <span class="text-lime-700">Bersertifikasi Resmi</span>
                    </h2>
                    <p class="text-slate-600 text-base">
                        Trainer kami siap membimbing form latihan yang benar untuk memaksimalkan hasil dan mencegah risiko cedera.
                    </p>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-7">
                    
                    <div class="clean-panel-interactive overflow-hidden group">
                        <div class="h-64 overflow-hidden relative">
                            <img src="{{ asset('images/gym/trainer.jpg') }}" 
                                 alt="Head Coach Kevin Pratama, S.Or — Spesialis Hypertrophy & Body Recomposition" 
                                 class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300"
                                 width="600" height="256"
                                 loading="lazy"
                                 decoding="async">
                            <span class="absolute top-3 right-3 px-3 py-1 rounded-md bg-slate-900 text-white text-[11px] font-bold">
                                HEAD COACH
                            </span>
                        </div>
                        <div class="p-5 space-y-1.5">
                            <div class="text-[11px] font-bold text-lime-700">NASM & APKI CERTIFIED</div>
                            <h3 class="font-display font-bold text-xl text-slate-900">Kevin Pratama, S.Or</h3>
                            <p class="text-slate-600 text-xs leading-relaxed">Spesialis Hypertrophy & Body Recomposition dengan pengalaman melatih lebih dari 8 tahun.</p>
                        </div>
                    </div>

                    <div class="clean-panel-interactive overflow-hidden group">
                        <div class="h-64 overflow-hidden relative">
                            <img src="{{ asset('images/gym/trainer-sarah.jpg') }}" 
                                 alt="Coach Sarah Amanda, CPT — Fat Loss Specialist & Functional Training Wanita" 
                                 class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300"
                                 width="600" height="256"
                                 loading="lazy"
                                 decoding="async">
                            <span class="absolute top-3 right-3 px-3 py-1 rounded-md bg-slate-900 text-white text-[11px] font-bold">
                                FAT LOSS SPECIALIST
                            </span>
                        </div>
                        <div class="p-5 space-y-1.5">
                            <div class="text-[11px] font-bold text-blue-700">ACE CERTIFIED COACH</div>
                            <h3 class="font-display font-bold text-xl text-slate-900">Sarah Amanda, CPT</h3>
                            <p class="text-slate-600 text-xs leading-relaxed">Ahli penurunan berat badan sehat, functional training wanita, dan pola nutrisi seimbang.</p>
                        </div>
                    </div>

                    <div class="clean-panel-interactive overflow-hidden group">
                        <div class="h-64 overflow-hidden relative">
                            <img src="{{ asset('images/gym/trainer-dimas.jpg') }}" 
                                 alt="Coach Dimas Wicaksono — Strength & Powerlifting Specialist" 
                                 class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300"
                                 width="600" height="256"
                                 loading="lazy"
                                 decoding="async">
                            <span class="absolute top-3 right-3 px-3 py-1 rounded-md bg-slate-900 text-white text-[11px] font-bold">
                                STRENGTH COACH
                            </span>
                        </div>
                        <div class="p-5 space-y-1.5">
                            <div class="text-[11px] font-bold text-amber-700">POWERLIFTING & MOBILITY</div>
                            <h3 class="font-display font-bold text-xl text-slate-900">Dimas Wicaksono</h3>
                            <p class="text-slate-600 text-xs leading-relaxed">Fokus pada form gerakan angkat beban utama, fleksibilitas sendi, dan pencegahan cedera.</p>
                        </div>
                    </div>

                </div>

            </div>
        </section>


        <!-- =========================================================================
             SECTION: JADWAL OPERASIONAL
             ========================================================================= -->
        <section class="py-20 bg-[#F8FAFC]" id="jadwal">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-12">
                
                <div class="text-center max-w-3xl mx-auto space-y-3">
                    <span class="badge-clean">WAKTU OPERASIONAL</span>
                    <h2 class="font-display font-extrabold text-3xl sm:text-4xl text-slate-900 tracking-tight">
                        Jam Buka & <span class="text-lime-700">Panduan Waktu Latihan</span>
                    </h2>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-7">
                    
                    <div class="clean-panel p-7 space-y-5">
                        <h3 class="font-display font-bold text-lg text-slate-900 flex items-center gap-2">
                            <span>⏰</span> Jam Buka Gym
                        </h3>
                        <div class="space-y-3.5 text-sm">
                            <div class="flex items-center justify-between pb-3 border-b border-slate-100">
                                <span class="text-slate-600 font-medium">Senin - Jumat (Weekday)</span>
                                <span class="font-mono font-bold text-slate-900">06:00 - 22:00 WIB</span>
                            </div>
                            <div class="flex items-center justify-between pb-3 border-b border-slate-100">
                                <span class="text-slate-600 font-medium">Sabtu - Minggu (Weekend)</span>
                                <span class="font-mono font-bold text-slate-900">07:00 - 21:00 WIB</span>
                            </div>
                            <div class="flex items-center justify-between pb-3 border-b border-slate-100">
                                <span class="text-slate-600 font-medium">Hari Libur Nasional</span>
                                <span class="font-mono font-bold text-slate-900">08:00 - 20:00 WIB</span>
                            </div>
                        </div>
                    </div>

                    <div class="clean-panel p-7 space-y-5">
                        <h3 class="font-display font-bold text-lg text-slate-900 flex items-center gap-2">
                            <span>📊</span> Perkiraan Kepadatan
                        </h3>
                        <div class="space-y-3 text-xs">
                            <div class="space-y-1">
                                <div class="flex justify-between">
                                    <span class="text-slate-600">Pagi (06:00 - 10:00)</span>
                                    <span class="text-emerald-600 font-bold">Suasana Tenang (30%)</span>
                                </div>
                                <div class="w-full h-2 rounded-full bg-slate-100 overflow-hidden">
                                    <div class="h-full bg-emerald-500 rounded-full" style="width: 30%"></div>
                                </div>
                            </div>
                            <div class="space-y-1">
                                <div class="flex justify-between">
                                    <span class="text-slate-600">Siang (10:00 - 16:00)</span>
                                    <span class="text-blue-600 font-bold">Sedang (45%)</span>
                                </div>
                                <div class="w-full h-2 rounded-full bg-slate-100 overflow-hidden">
                                    <div class="h-full bg-blue-500 rounded-full" style="width: 45%"></div>
                                </div>
                            </div>
                            <div class="space-y-1">
                                <div class="flex justify-between">
                                    <span class="text-slate-600">Sore - Malam (17:00 - 20:30)</span>
                                    <span class="text-amber-600 font-bold">Peak Hours (85%)</span>
                                </div>
                                <div class="w-full h-2 rounded-full bg-slate-100 overflow-hidden">
                                    <div class="h-full bg-amber-500 rounded-full" style="width: 85%"></div>
                                </div>
                            </div>
                        </div>
                    </div>

                </div>

            </div>
        </section>


        <!-- =========================================================================
             SECTION: FAQ ACCORDION
             ========================================================================= -->
        <section class="py-20 bg-white border-y border-slate-200" id="faq">
            <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 space-y-12">
                
                <div class="text-center space-y-3">
                    <span class="badge-clean">PERTANYAAN UMUM</span>
                    <h2 class="font-display font-extrabold text-3xl sm:text-4xl text-slate-900 tracking-tight">
                        Frequently Asked Questions (FAQ)
                    </h2>
                </div>

                <div class="space-y-3.5" role="list">
                    
                    <div class="faq-clean-item active" role="listitem">
                        <button class="faq-clean-header" 
                                onclick="toggleFaq(this)" 
                                aria-expanded="true" 
                                aria-controls="faq-answer-1"
                                type="button">
                            <span>Bagaimana cara mendaftar membership di GymPulse?</span>
                            <div class="faq-clean-icon" aria-hidden="true">↓</div>
                        </button>
                        <div class="faq-clean-content" id="faq-answer-1" role="region" aria-label="Jawaban: Cara mendaftar membership">
                            Pendaftaran sangat mudah! Anda dapat mendaftar langsung di resepsionis GymPulse atau menghubungi CS kami via WhatsApp. Setelah pendaftaran selesai, Anda akan menerima kartu RFID eksklusif dan akun portal member.
                        </div>
                    </div>

                    <div class="faq-clean-item" role="listitem">
                        <button class="faq-clean-header" 
                                onclick="toggleFaq(this)" 
                                aria-expanded="false" 
                                aria-controls="faq-answer-2"
                                type="button">
                            <span>Bagaimana cara kerja kartu RFID untuk masuk gym?</span>
                            <div class="faq-clean-icon" aria-hidden="true">↓</div>
                        </button>
                        <div class="faq-clean-content" id="faq-answer-2" role="region" aria-label="Jawaban: Cara kerja kartu RFID">
                            Cukup tempelkan kartu RFID Anda pada alat scanner di gate masuk. Pintu gate akan terbuka otomatis dalam 0.5 detik, absensi Anda tercatat di sistem, dan notifikasi konfirmasi dikirimkan ke WhatsApp Anda.
                        </div>
                    </div>

                    <div class="faq-clean-item" role="listitem">
                        <button class="faq-clean-header" 
                                onclick="toggleFaq(this)" 
                                aria-expanded="false" 
                                aria-controls="faq-answer-3"
                                type="button">
                            <span>Apakah pemula akan dibantu cara memakai alat gym?</span>
                            <div class="faq-clean-icon" aria-hidden="true">↓</div>
                        </button>
                        <div class="faq-clean-content" id="faq-answer-3" role="region" aria-label="Jawaban: Bantuan untuk pemula">
                            Tentu! Staf dan trainer kami selalu siap membantu member baru untuk memahami cara menggunakan setiap peralatan secara aman dan tepat.
                        </div>
                    </div>

                    <div class="faq-clean-item" role="listitem">
                        <button class="faq-clean-header" 
                                onclick="toggleFaq(this)" 
                                aria-expanded="false" 
                                aria-controls="faq-answer-4"
                                type="button">
                            <span>Bagaimana jika kartu RFID saya hilang?</span>
                            <div class="faq-clean-icon" aria-hidden="true">↓</div>
                        </button>
                        <div class="faq-clean-content" id="faq-answer-4" role="region" aria-label="Jawaban: Kartu RFID hilang">
                            Segera laporkan ke admin resepsionis. Kartu lama Anda akan diblokir di sistem agar tidak bisa digunakan orang lain, dan kami akan menerbitkan kartu baru untuk Anda.
                        </div>
                    </div>

                </div>

            </div>
        </section>


        <!-- =========================================================================
             SECTION: LOKASI & KONTAK
             ========================================================================= -->
        <section class="py-20 bg-[#F8FAFC]" id="kontak">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                
                <div class="clean-panel p-8 sm:p-12">
                    <div class="grid grid-cols-1 lg:grid-cols-12 gap-10 items-center">
                        
                        <div class="lg:col-span-7 space-y-6">
                            <span class="badge-clean">LOKASI & KONTAK</span>
                            <h2 class="font-display font-extrabold text-3xl sm:text-4xl text-slate-900 tracking-tight">
                                Kunjungi <span class="text-lime-700">GymPulse Sekarang</span>
                            </h2>
                            <p class="text-slate-600 text-sm leading-relaxed">
                                Silakan berkunjung langsung untuk melihat fasilitas kami atau hubungi tim customer service kami untuk informasi lebih lanjut.
                            </p>

                            <div class="space-y-3 text-xs sm:text-sm text-slate-600">
                                <div>
                                    <strong class="text-slate-900 block">📍 Alamat Gym:</strong>
                                    <span>Jl. Kebugaran Raya No. 88, Jakarta Selatan (Parkir Luas & Gratis)</span>
                                </div>
                                <div>
                                    <strong class="text-slate-900 block">📞 WhatsApp / Telepon:</strong>
                                    <span>+62 812-3456-7890 / (021) 555-4967</span>
                                </div>
                                <div>
                                    <strong class="text-slate-900 block">✉️ Email Layanan:</strong>
                                    <span>info@gympulse.id</span>
                                </div>
                            </div>

                            <div class="pt-2 flex flex-wrap gap-4">
                                <a href="https://wa.me/?text=Halo%20GymPulse,%20saya%20ingin%20tanya%20info%20membership" target="_blank" class="btn-lime-action text-sm">
                                    Hubungi Kami via WhatsApp
                                </a>
                                <a href="{{ route('login') }}" class="btn-secondary-action text-sm">
                                    Masuk Portal Member / Admin →
                                </a>
                            </div>
                        </div>

                        <div class="lg:col-span-5">
                            <div class="rounded-2xl border border-slate-200 bg-white p-6 space-y-4 shadow-sm text-center">
                                <div class="text-4xl">🏢</div>
                                <h3 class="font-display font-bold text-lg text-slate-900">GymPulse Fitness Club</h3>
                                <p class="text-xs text-slate-500">
                                    Akses mudah, pengawasan keamanan 24 jam dengan CCTV & Turnstile Gate RFID.
                                </p>
                                <div class="p-3 rounded-xl bg-lime-50 text-xs text-lime-800 font-semibold">
                                    ✓ Buka Setiap Hari: 06:00 - 22:00 WIB
                                </div>
                            </div>
                        </div>

                    </div>
                </div>

            </div>
        </section>

    </main>


    <!-- =========================================================================
         FOOTER (LIGHT THEME)
         ========================================================================= -->
    <footer class="bg-white border-t border-slate-200 py-12 text-xs text-slate-500">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-8">
            
            <div class="grid grid-cols-1 md:grid-cols-4 gap-8">
                
                <div class="space-y-3 md:col-span-1">
                    <div class="flex items-center gap-3">
                        <div class="w-8 h-8 rounded-lg bg-slate-900 flex items-center justify-center font-display font-extrabold text-lime-400 text-lg">
                            G
                        </div>
                        <span class="font-display font-extrabold text-xl text-slate-900">
                            GymPulse<span class="text-lime-600">.</span>
                        </span>
                    </div>
                    <p class="text-xs text-slate-500 leading-relaxed">
                        Sistem manajemen pusat kebugaran terintegrasi RFID, absensi otomatis, dan WhatsApp Gateway.
                    </p>
                </div>

                <div class="space-y-2.5">
                    <div class="font-display font-bold text-sm text-slate-900">Navigasi</div>
                    <nav aria-label="Navigasi Footer Halaman">
                        <ul class="space-y-1.5">
                            <li><a href="#tentang" class="hover:text-slate-900 transition-colors">Tentang Kami</a></li>
                            <li><a href="#fasilitas" class="hover:text-slate-900 transition-colors">Fasilitas</a></li>
                            <li><a href="#program" class="hover:text-slate-900 transition-colors">Program Latihan</a></li>
                            <li><a href="#paket" class="hover:text-slate-900 transition-colors">Paket Member</a></li>
                        </ul>
                    </nav>
                </div>

                <div class="space-y-2.5">
                    <div class="font-display font-bold text-sm text-slate-900">Fitur & Layanan</div>
                    <nav aria-label="Navigasi Footer Fitur">
                        <ul class="space-y-1.5">
                            <li><a href="#rfid-tech" class="hover:text-slate-900 transition-colors">Teknologi RFID Gate</a></li>
                            <li><a href="#kalkulator-bmi" class="hover:text-slate-900 transition-colors">Kalkulator BMI</a></li>
                            <li><a href="#pelatih" class="hover:text-slate-900 transition-colors">Trainer Profesional</a></li>
                            <li><a href="#jadwal" class="hover:text-slate-900 transition-colors">Jadwal Operasional</a></li>
                        </ul>
                    </nav>
                </div>

                <div class="space-y-3">
                    <div class="font-display font-bold text-sm text-slate-900">Portal Masuk</div>
                    <p class="text-xs text-slate-500">Akses dashboard untuk melihat masa aktif & riwayat absensi.</p>
                    <a href="{{ route('login') }}" class="btn-primary-action text-xs py-2.5 px-4 w-full justify-center">
                        Masuk ke Akun Anda →
                    </a>
                </div>

            </div>
            <div class="pt-6 border-t border-slate-100 flex flex-col sm:flex-row items-center justify-between gap-3 text-slate-400">
                <div>
                    &copy; {{ date('Y') }} <strong>GymPulse</strong>. Hak Cipta Dilindungi.
                </div>
                <div>
                    Smart RFID Management System
                </div>
            </div>

        </div>
    </footer>

    <!-- =========================================================================
         MODAL PENDAFTARAN ONLINE & PEMBAYARAN QRIS (ALPINE.JS)
         ========================================================================= -->
    <div x-show="isOpen" 
         x-cloak 
         class="fixed inset-0 z-50 overflow-y-auto" 
         style="display: none;"
         aria-labelledby="modal-title" 
         role="dialog" 
         aria-modal="true">
         
        <!-- Backdrop with Blur -->
        <div x-show="isOpen" 
             x-transition:enter="ease-out duration-300"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             x-transition:leave="ease-in duration-200"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0"
             class="fixed inset-0 bg-slate-950/80 backdrop-blur-sm transition-opacity" 
             @click="step !== 2 ? closeModal() : null"></div>

        <div class="flex min-h-full items-center justify-center p-3 sm:p-4 text-center">
            <div x-show="isOpen" 
                 x-transition:enter="ease-out duration-300"
                 x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                 x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
                 x-transition:leave="ease-in duration-200"
                 x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
                 x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                 class="relative transform overflow-hidden rounded-3xl bg-slate-900 border border-slate-800 text-left shadow-2xl transition-all w-full max-w-lg text-slate-100 my-6">
                 
                <!-- Modal Top Header & Close Button -->
                <div class="px-6 pt-6 pb-4 border-b border-slate-800 flex items-center justify-between">
                    <div class="flex items-center gap-2.5">
                        <div class="w-8 h-8 rounded-xl bg-lime-500/20 border border-lime-500/30 text-lime-400 flex items-center justify-center font-bold text-sm">
                            ⚡
                        </div>
                        <div>
                            <h3 class="font-display font-bold text-base text-white" id="modal-title">
                                <span x-show="step === 1">Daftar Membership Online</span>
                                <span x-show="step === 2">Pembayaran QRIS Instan</span>
                                <span x-show="step === 3">Pendaftaran Berhasil!</span>
                            </h3>
                            <p class="text-[11px] text-slate-400">GymPulse Smart RFID Fitness</p>
                        </div>
                    </div>
                    <button type="button" 
                            @click="closeModal()" 
                            class="p-1.5 rounded-xl bg-slate-800 hover:bg-slate-700 text-slate-400 hover:text-white transition">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>

                <!-- Step Indicator -->
                <div class="px-6 py-3 bg-slate-950/60 border-b border-slate-800 flex items-center justify-between text-xs">
                    <div class="flex items-center gap-2">
                        <span class="w-5 h-5 rounded-full flex items-center justify-center text-[10px] font-bold"
                              :class="step >= 1 ? 'bg-lime-500 text-slate-950' : 'bg-slate-800 text-slate-400'">1</span>
                        <span :class="step === 1 ? 'font-bold text-lime-400' : 'text-slate-400'">Isi Data</span>
                    </div>
                    <span class="text-slate-600">&rarr;</span>
                    <div class="flex items-center gap-2">
                        <span class="w-5 h-5 rounded-full flex items-center justify-center text-[10px] font-bold"
                              :class="step >= 2 ? 'bg-lime-500 text-slate-950' : 'bg-slate-800 text-slate-400'">2</span>
                        <span :class="step === 2 ? 'font-bold text-lime-400' : 'text-slate-400'">Bayar QRIS</span>
                    </div>
                    <span class="text-slate-600">&rarr;</span>
                    <div class="flex items-center gap-2">
                        <span class="w-5 h-5 rounded-full flex items-center justify-center text-[10px] font-bold"
                              :class="step === 3 ? 'bg-lime-500 text-slate-950' : 'bg-slate-800 text-slate-400'">3</span>
                        <span :class="step === 3 ? 'font-bold text-lime-400' : 'text-slate-400'">Ambil Kartu</span>
                    </div>
                </div>

                <!-- Modal Body -->
                <div class="p-6 space-y-5">
                    
                    <!-- =================== STEP 1: FORM PENDAFTARAN =================== -->
                    <div x-show="step === 1" class="space-y-4">
                        <!-- Selected Package Summary Banner -->
                        <div class="p-3.5 rounded-2xl bg-gradient-to-r from-lime-500/10 to-emerald-500/10 border border-lime-500/30 flex items-center justify-between">
                            <div>
                                <span class="text-[10px] uppercase font-bold tracking-wider text-lime-400">Paket Dipilih</span>
                                <h4 class="font-display font-bold text-sm text-white" x-text="packageName"></h4>
                                <span class="text-[11px] text-slate-400" x-text="packageDuration + ' Bulan Akses Penuh'"></span>
                            </div>
                            <div class="text-right">
                                <div class="font-display font-extrabold text-base text-lime-400" x-text="formatRupiah(packagePrice)"></div>
                                <span class="text-[10px] text-slate-400">Gratis Kartu RFID</span>
                            </div>
                        </div>

                        <!-- Error Message -->
                        <div x-show="errorMessage" x-cloak class="p-3 rounded-xl bg-rose-500/20 border border-rose-500/40 text-rose-300 text-xs flex items-start gap-2">
                            <span>⚠️</span>
                            <span x-text="errorMessage"></span>
                        </div>

                        <!-- Form Inputs -->
                        <form @submit.prevent="submitForm" class="space-y-3.5">
                            <div>
                                <label class="block text-xs font-semibold text-slate-300 mb-1">Nama Lengkap <span class="text-rose-400">*</span></label>
                                <input type="text" x-model="form.name" required placeholder="Contoh: Budi Pratama"
                                       class="w-full px-3.5 py-2.5 rounded-xl bg-slate-800 border border-slate-700 text-white placeholder-slate-500 text-xs focus:outline-none focus:ring-2 focus:ring-lime-500 focus:border-transparent">
                            </div>

                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                                <div>
                                    <label class="block text-xs font-semibold text-slate-300 mb-1">No. WhatsApp <span class="text-rose-400">*</span></label>
                                    <input type="tel" x-model="form.phone" required placeholder="081234567890"
                                           class="w-full px-3.5 py-2.5 rounded-xl bg-slate-800 border border-slate-700 text-white placeholder-slate-500 text-xs focus:outline-none focus:ring-2 focus:ring-lime-500 focus:border-transparent">
                                </div>
                                <div>
                                    <label class="block text-xs font-semibold text-slate-300 mb-1">Email Aktif <span class="text-rose-400">*</span></label>
                                    <input type="email" x-model="form.email" required placeholder="budi@example.com"
                                           class="w-full px-3.5 py-2.5 rounded-xl bg-slate-800 border border-slate-700 text-white placeholder-slate-500 text-xs focus:outline-none focus:ring-2 focus:ring-lime-500 focus:border-transparent">
                                </div>
                            </div>

                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                                <div>
                                    <label class="block text-xs font-semibold text-slate-300 mb-1">Password Portal <span class="text-rose-400">*</span></label>
                                    <input type="password" x-model="form.password" required minlength="6" placeholder="Min. 6 Karakter"
                                           class="w-full px-3.5 py-2.5 rounded-xl bg-slate-800 border border-slate-700 text-white placeholder-slate-500 text-xs focus:outline-none focus:ring-2 focus:ring-lime-500 focus:border-transparent">
                                    <p class="text-[10px] text-slate-400 mt-1">Untuk login portal member.</p>
                                </div>
                                <div>
                                    <label class="block text-xs font-semibold text-slate-300 mb-1">Jenis Kelamin <span class="text-rose-400">*</span></label>
                                    <select x-model="form.gender" required
                                            class="w-full px-3.5 py-2.5 rounded-xl bg-slate-800 border border-slate-700 text-white text-xs focus:outline-none focus:ring-2 focus:ring-lime-500 focus:border-transparent">
                                        <option value="male">Pria</option>
                                        <option value="female">Wanita</option>
                                    </select>
                                </div>
                            </div>

                            <div>
                                <label class="block text-xs font-semibold text-slate-300 mb-1">Alamat (Opsional)</label>
                                <textarea x-model="form.address" rows="2" placeholder="Alamat domisili Anda"
                                          class="w-full px-3.5 py-2 rounded-xl bg-slate-800 border border-slate-700 text-white placeholder-slate-500 text-xs focus:outline-none focus:ring-2 focus:ring-lime-500 focus:border-transparent"></textarea>
                            </div>

                            <div class="pt-2">
                                <button type="submit" 
                                        :disabled="loading"
                                        class="w-full py-3 px-4 rounded-xl bg-lime-500 hover:bg-lime-400 text-slate-950 font-bold text-xs sm:text-sm flex items-center justify-center gap-2 transition shadow-lg shadow-lime-500/20 cursor-pointer disabled:opacity-50">
                                    <span x-show="!loading">Lanjut ke Pembayaran QRIS &rarr;</span>
                                    <span x-show="loading" class="flex items-center gap-2">
                                        <svg class="animate-spin h-4 w-4 text-slate-950" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                                        <span>Membuat Invoice & QRIS...</span>
                                    </span>
                                </button>
                            </div>
                        </form>
                    </div>

                    <!-- =================== STEP 2: DYNAMIC QRIS PAYMENT =================== -->
                    <div x-show="step === 2" x-cloak class="space-y-4 text-center">
                        
                        <!-- Header info -->
                        <div class="flex items-center justify-between px-3.5 py-2.5 rounded-xl bg-slate-800/90 border border-slate-700 text-xs">
                            <div class="flex items-center gap-1.5">
                                <span class="px-2 py-0.5 rounded-md bg-slate-950 text-white font-black text-[11px] tracking-wider border border-slate-700">QRIS</span>
                                <span class="text-[11px] font-bold text-slate-400 uppercase tracking-wide">Dinamis Otomatis</span>
                            </div>
                            <div class="flex items-center gap-1 text-[11px] font-mono font-bold bg-amber-500/10 text-amber-400 px-2.5 py-1 rounded-lg border border-amber-500/30">
                                <svg class="w-3.5 h-3.5 text-amber-400 animate-spin" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                                <span x-text="formatTime(timeLeft)"></span>
                            </div>
                        </div>

                        <!-- QR Code Container -->
                        <div class="bg-gradient-to-b from-slate-100 to-slate-200 p-3 rounded-2xl border border-slate-300 shadow-inner inline-block mx-auto">
                            <div class="w-48 h-48 sm:w-56 sm:h-56 bg-white rounded-xl p-2 shadow-sm flex items-center justify-center mx-auto border border-slate-200">
                                <template x-if="qrisQrUrl">
                                    <img :src="qrisQrUrl" alt="QRIS Code GymPulse" class="w-full h-full object-contain">
                                </template>
                            </div>
                            <p class="font-mono text-[11px] text-slate-600 mt-1 font-semibold" x-text="invoice"></p>
                        </div>

                        <!-- Amount to pay -->
                        <div class="p-3 bg-slate-900 border border-slate-800 rounded-xl text-white">
                            <span class="text-[10px] text-slate-400 font-semibold uppercase tracking-wider block">Total Tagihan Membership</span>
                            <div class="font-display font-extrabold text-2xl text-lime-400 mt-0.5 font-mono" style="color: #a3e635 !important;" x-text="formatRupiah(packagePrice)"></div>
                            <div class="flex items-center justify-center gap-2 text-[11px] text-slate-300 mt-0.5">
                                <span x-text="'Paket: ' + packageName"></span>
                                <span>•</span>
                                <span class="text-lime-300 font-semibold" x-text="'Durasi: ' + packageDuration + ' Bulan'"></span>
                            </div>
                        </div>

                        <!-- Live Polling Radar Indicator -->
                        <div class="py-2 px-3 rounded-xl bg-emerald-950/40 border border-emerald-500/30 flex items-center justify-center gap-2 text-xs font-bold text-emerald-400">
                            <span class="w-2.5 h-2.5 rounded-full bg-emerald-400 animate-ping"></span>
                            <span>Menunggu Pembayaran (Sistem Otomatis Mendeteksi)...</span>
                        </div>

                        <!-- SIMULATOR TEST ACTION BOX -->
                        <div class="p-3 bg-gradient-to-br from-indigo-950/60 via-purple-950/40 to-slate-900 border border-indigo-500/30 rounded-2xl text-left space-y-2">
                            <div class="flex items-center justify-between">
                                <span class="text-[10px] font-bold text-indigo-300 uppercase tracking-wider flex items-center gap-1">
                                    <span>🧪</span> Mode Uji Coba (Simulator)
                                </span>
                                <span class="text-[9px] bg-indigo-500/20 text-indigo-300 border border-indigo-500/40 px-2 py-0.5 rounded-full font-bold">Midtrans Ready</span>
                            </div>
                            <p class="text-[11px] text-indigo-200/80 leading-snug">
                                Klik tombol di bawah untuk simulasi telah bayar via BCA Mobile / GoPay / OVO:
                            </p>
                            <button 
                                type="button" 
                                @click="simulatePayment()"
                                :disabled="simulating"
                                class="w-full py-2.5 px-3 bg-indigo-600 hover:bg-indigo-500 active:scale-95 text-white rounded-xl text-xs font-bold transition shadow-md flex items-center justify-center gap-1.5 cursor-pointer disabled:opacity-50"
                            >
                                <span x-show="!simulating">⚡ Simulasi: Bayar QRIS Sukses</span>
                                <span x-show="simulating">Memverifikasi Pembayaran...</span>
                            </button>
                        </div>

                        <!-- Cara Pembayaran Collapse/Card -->
                        <div class="text-[11px] text-slate-400 text-left bg-slate-950/60 p-3 rounded-xl border border-slate-800 space-y-1">
                            <p class="font-semibold text-slate-300">Petunjuk Pembayaran:</p>
                            <ol class="list-decimal list-inside space-y-0.5 text-slate-400">
                                <li>Buka aplikasi m-Banking (BCA, Mandiri, BRI, BNI) atau e-Wallet (GoPay, OVO, Dana).</li>
                                <li>Pilih menu <strong>Scan QRIS</strong> dan scan kode QR di atas.</li>
                                <li>Periksa nama merchant <strong>GYMPULSE</strong> dan konfirmasi bayar.</li>
                                <li>Sistem otomatis mengaktifkan keanggotaan Anda dalam hitungan detik.</li>
                            </ol>
                        </div>

                        <!-- Cancel Action -->
                        <div class="pt-1">
                            <button 
                                type="button" 
                                @click="cancelRegistration()" 
                                class="text-xs text-rose-400 hover:text-rose-300 font-bold hover:underline transition"
                            >
                                ✕ Batalkan Pendaftaran
                            </button>
                        </div>
                    </div>

                    <!-- =================== STEP 3: SUCCESS & IN-GYM PICKUP GUIDANCE =================== -->
                    <div x-show="step === 3" x-cloak class="space-y-4 text-center">
                        
                        <!-- Celebration Icon -->
                        <div class="w-16 h-16 rounded-full bg-lime-500/20 border-2 border-lime-400 text-lime-400 flex items-center justify-center text-3xl mx-auto shadow-lg shadow-lime-500/20 animate-bounce">
                            ✓
                        </div>

                        <div>
                            <h4 class="font-display font-bold text-xl text-white">Pendaftaran & Pembayaran Berhasil!</h4>
                            <p class="text-xs text-slate-400 mt-1">Selamat datang di keluarga besar GymPulse.</p>
                        </div>

                        <!-- Member Details Card -->
                        <div class="p-4 rounded-2xl bg-slate-950/70 border border-slate-800 text-left space-y-2.5 text-xs">
                            <div class="flex justify-between items-center pb-2 border-b border-slate-800">
                                <span class="text-slate-400">Nama Member</span>
                                <span class="font-bold text-white" x-text="successData.member_name"></span>
                            </div>
                            <div class="flex justify-between items-center pb-2 border-b border-slate-800">
                                <span class="text-slate-400">Nomor Member ID</span>
                                <span class="font-mono font-bold text-lime-400" x-text="successData.member_number"></span>
                            </div>
                            <div class="flex justify-between items-center pb-2 border-b border-slate-800">
                                <span class="text-slate-400">Paket Dipilih</span>
                                <span class="font-semibold text-slate-200" x-text="successData.package_name"></span>
                            </div>
                            <div class="flex justify-between items-center">
                                <span class="text-slate-400">Status Akun</span>
                                <span class="px-2 py-0.5 rounded-md bg-emerald-500/20 text-emerald-300 border border-emerald-500/30 text-[10px] font-bold">LUNAS & AKTIF</span>
                            </div>
                        </div>

                        <!-- Important Note: In-Gym RFID Card Pickup -->
                        <div class="p-4 rounded-2xl bg-amber-500/10 border border-amber-500/30 text-left space-y-2">
                            <div class="flex items-center gap-2 text-amber-400 font-bold text-xs sm:text-sm">
                                <span class="text-base">🏷️</span>
                                <span>Pengambilan Kartu RFID Akses Gate:</span>
                            </div>
                            <p class="text-xs text-amber-200/90 leading-relaxed">
                                Silakan datang langsung ke front-desk GymPulse dan sebutkan <strong>Nama</strong> atau <strong>ID Member (<span class="font-mono font-bold" x-text="successData.member_number"></span>)</strong>. Petugas kasir/admin akan langsung memprogram & menyerahkan kartu RFID fisik Anda untuk akses gate masuk & gym!
                            </p>
                        </div>

                        <!-- Next Actions -->
                        <div class="pt-2">
                            <a href="{{ route('login') }}" 
                               class="w-full py-3 px-4 rounded-xl bg-lime-500 hover:bg-lime-400 text-slate-950 font-bold text-xs sm:text-sm flex items-center justify-center gap-2 transition shadow-lg shadow-lime-500/20">
                                <span>Masuk ke Portal Member &rarr;</span>
                            </a>
                        </div>

                    </div>

                </div>

            </div>
        </div>
    </div>


    <!-- =========================================================================
         JAVASCRIPT
         ========================================================================= -->
    <script>
        // Alpine.js Online Registration Application Store / Component
        function onlineRegistrationApp() {
            return {
                isOpen: false,
                step: 1, // 1: form, 2: qris, 3: success
                packageId: null,
                packageName: '',
                packagePrice: 0,
                packageDuration: 1,
                packageDesc: '',
                
                form: {
                    name: '',
                    email: '',
                    phone: '',
                    password: '',
                    gender: 'male',
                    address: ''
                },

                invoice: '',
                qrisData: '',
                qrisQrUrl: '',
                timeLeft: 900,
                timerInterval: null,
                pollInterval: null,
                loading: false,
                simulating: false,
                errorMessage: '',

                successData: {
                    member_name: '',
                    member_number: '',
                    package_name: '',
                    phone: '',
                    email: ''
                },

                openModal(id, name, price, duration, desc) {
                    this.packageId = id;
                    this.packageName = name;
                    this.packagePrice = price;
                    this.packageDuration = duration;
                    this.packageDesc = desc || '';
                    this.step = 1;
                    this.errorMessage = '';
                    this.loading = false;
                    this.simulating = false;
                    this.isOpen = true;
                },

                closeModal() {
                    this.stopPolling();
                    this.stopTimer();
                    this.isOpen = false;
                },

                formatRupiah(num) {
                    return 'Rp ' + Number(num || 0).toLocaleString('id-ID');
                },

                formatTime(seconds) {
                    const m = Math.floor(seconds / 60);
                    const s = seconds % 60;
                    return `${m.toString().padStart(2, '0')}:${s.toString().padStart(2, '0')}`;
                },

                startTimer() {
                    this.stopTimer();
                    this.timeLeft = 900;
                    this.timerInterval = setInterval(() => {
                        if (this.timeLeft > 0) {
                            this.timeLeft--;
                        } else {
                            this.stopTimer();
                            this.stopPolling();
                            this.errorMessage = 'Waktu pembayaran telah habis. Silakan coba kembali.';
                            this.step = 1;
                        }
                    }, 1000);
                },

                stopTimer() {
                    if (this.timerInterval) {
                        clearInterval(this.timerInterval);
                        this.timerInterval = null;
                    }
                },

                startPolling() {
                    this.stopPolling();
                    this.pollInterval = setInterval(async () => {
                        if (!this.invoice) return;
                        try {
                            const res = await fetch(`/register/online/${this.invoice}/status`);
                            if (!res.ok) return;
                            const data = await res.json();
                            
                            if (data.status === 'paid') {
                                this.stopPolling();
                                this.stopTimer();
                                this.successData = {
                                    member_name: data.member_name || this.form.name,
                                    member_number: data.member_number || data.member_code || '-',
                                    package_name: data.package_name || this.packageName,
                                    phone: data.phone || this.form.phone,
                                    email: data.email || this.form.email
                                };
                                this.step = 3;
                            } else if (data.status === 'expired' || data.status === 'cancelled') {
                                this.stopPolling();
                                this.stopTimer();
                                this.errorMessage = 'Pendaftaran telah dibatalkan atau kedaluwarsa.';
                                this.step = 1;
                            }
                        } catch (e) {
                            console.error('Polling error:', e);
                        }
                    }, 2500);
                },

                stopPolling() {
                    if (this.pollInterval) {
                        clearInterval(this.pollInterval);
                        this.pollInterval = null;
                    }
                },

                async submitForm() {
                    this.loading = true;
                    this.errorMessage = '';

                    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';

                    try {
                        const response = await fetch('/register/online/initiate', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'Accept': 'application/json',
                                'X-CSRF-TOKEN': csrfToken
                            },
                            body: JSON.stringify({
                                membership_package_id: this.packageId,
                                name: this.form.name,
                                email: this.form.email,
                                phone: this.form.phone,
                                password: this.form.password,
                                gender: this.form.gender,
                                address: this.form.address
                            })
                        });

                        const res = await response.json();

                        if (!response.ok || !res.success) {
                            if (res.errors) {
                                const firstKey = Object.keys(res.errors)[0];
                                this.errorMessage = res.errors[firstKey][0];
                            } else {
                                this.errorMessage = res.message || 'Terjadi kesalahan saat memproses pendaftaran.';
                            }
                            this.loading = false;
                            return;
                        }

                        this.invoice = res.invoice_number || res.invoice || '';
                        this.qrisData = (res.qris && res.qris.qr_string) ? res.qris.qr_string : (res.qris_data || '');
                        this.qrisQrUrl = (res.qris && res.qris.qr_url) ? res.qris.qr_url : (res.qris_qr_url || '');
                        this.step = 2;
                        this.loading = false;
                        this.startTimer();
                        this.startPolling();

                    } catch (err) {
                        console.error('Submit error:', err);
                        this.errorMessage = 'Koneksi gagal. Silakan periksa koneksi internet Anda.';
                        this.loading = false;
                    }
                },

                async simulatePayment() {
                    if (!this.invoice || this.simulating) return;
                    this.simulating = true;

                    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';

                    try {
                        const response = await fetch(`/register/online/${this.invoice}/simulate`, {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'Accept': 'application/json',
                                'X-CSRF-TOKEN': csrfToken
                            }
                        });

                        const res = await response.json();
                        this.simulating = false;

                        if (response.ok && res.success) {
                            this.stopPolling();
                            this.stopTimer();
                            this.successData = {
                                member_name: res.member_name || this.form.name,
                                member_number: res.member_number || res.member_code || '-',
                                package_name: res.package_name || this.packageName,
                                phone: this.form.phone,
                                email: this.form.email
                            };
                            this.step = 3;
                        } else {
                            alert(res.message || 'Gagal simulasi pembayaran.');
                        }
                    } catch (err) {
                        this.simulating = false;
                        console.error('Simulate error:', err);
                    }
                },

                async cancelRegistration() {
                    if (!this.invoice) {
                        this.step = 1;
                        return;
                    }

                    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';

                    try {
                        await fetch(`/register/online/${this.invoice}/cancel`, {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'Accept': 'application/json',
                                'X-CSRF-TOKEN': csrfToken
                            }
                        });
                    } catch (e) {}

                    this.stopPolling();
                    this.stopTimer();
                    this.invoice = '';
                    this.step = 1;
                }
            };
        }

        // Mobile Menu — with aria-expanded toggle
        const mobileMenuBtn = document.getElementById('mobileMenuBtn');
        const mobileMenu = document.getElementById('mobileMenu');
        const menuIconClosed = document.getElementById('menuIconClosed');
        const menuIconOpen = document.getElementById('menuIconOpen');

        if (mobileMenuBtn && mobileMenu) {
            mobileMenuBtn.addEventListener('click', () => {
                const isExpanded = mobileMenuBtn.getAttribute('aria-expanded') === 'true';
                mobileMenuBtn.setAttribute('aria-expanded', String(!isExpanded));
                mobileMenuBtn.setAttribute('aria-label', isExpanded ? 'Buka Menu Navigasi' : 'Tutup Menu Navigasi');
                mobileMenu.classList.toggle('hidden');
                menuIconClosed?.classList.toggle('hidden');
                menuIconOpen?.classList.toggle('hidden');
            });
        }

        // FAQ Toggle — with aria-expanded toggle
        function toggleFaq(btn) {
            const item = btn.closest('.faq-clean-item');
            const isActive = item.classList.contains('active');
            
            // Close all
            document.querySelectorAll('.faq-clean-item').forEach(el => {
                el.classList.remove('active');
                const elBtn = el.querySelector('.faq-clean-header');
                if (elBtn) elBtn.setAttribute('aria-expanded', 'false');
            });

            // Open clicked if it was closed
            if (!isActive) {
                item.classList.add('active');
                btn.setAttribute('aria-expanded', 'true');
            }
        }

        // RFID Demo Simulation
        function simulateTap() {
            const demoCard = document.getElementById('demoCard');
            const tapFeedback = document.getElementById('tapFeedback');
            const tapButton = document.getElementById('tapButton');

            if (!tapButton || !demoCard || !tapFeedback) return;

            tapButton.disabled = true;
            tapButton.innerHTML = '⚡ Membaca Kartu...';
            demoCard.style.transform = 'scale(1.05) rotate(2deg)';

            setTimeout(() => {
                tapFeedback.innerHTML = '<span class="text-emerald-600 font-bold">✅ BEEP! Kartu Terdeteksi (UID: 9A-4B-8C-F1). Gate Terbuka & WA Terkirim!</span>';
                tapButton.innerHTML = '✓ Akses Terbuka (0.5s)';
                
                setTimeout(() => {
                    demoCard.style.transform = '';
                    tapButton.disabled = false;
                    tapButton.innerHTML = '⚡ Simulasi Tempel Kartu (Tap Here)';
                }, 2200);
            }, 500);
        }

        // BMI Calculator
        function calculateBMI() {
            const heightInput = document.getElementById('bmiHeight')?.value;
            const weightInput = document.getElementById('bmiWeight')?.value;
            const resultBox = document.getElementById('bmiResultBox');
            const scoreText = document.getElementById('bmiScoreText');
            const categoryText = document.getElementById('bmiCategoryText');
            const adviceText = document.getElementById('bmiAdviceText');

            if (!heightInput || !weightInput) return;

            const heightM = parseFloat(heightInput) / 100;
            const weightKg = parseFloat(weightInput);

            if (!heightM || !weightKg || heightM <= 0 || weightKg <= 0) {
                alert('Silakan masukkan tinggi dan berat badan yang valid.');
                return;
            }

            const bmi = (weightKg / (heightM * heightM)).toFixed(1);
            if (scoreText) scoreText.innerText = bmi;
            resultBox?.classList.remove('hidden');

            if (categoryText && adviceText) {
                if (bmi < 18.5) {
                    categoryText.innerText = 'Berat Badan Kurang (Underweight)';
                    categoryText.className = 'font-bold text-blue-700';
                    adviceText.innerText = 'Disarankan program Hypertrophy & asupan nutrisi terukur di GymPulse untuk menambah massa otot.';
                } else if (bmi >= 18.5 && bmi <= 24.9) {
                    categoryText.innerText = 'Berat Badan Ideal (Normal)';
                    categoryText.className = 'font-bold text-lime-700';
                    adviceText.innerText = 'Sangat baik! Pertahankan komposisi tubuh dengan latihan kekuatan & kardio teratur.';
                } else if (bmi >= 25.0 && bmi <= 29.9) {
                    categoryText.innerText = 'Kelebihan Berat Badan (Overweight)';
                    categoryText.className = 'font-bold text-amber-700';
                    adviceText.innerText = 'Disarankan kombinasi latihan beban dan Functional HIIT untuk optimalisasi pembakaran kalori.';
                } else {
                    categoryText.innerText = 'Kategori Obesitas';
                    categoryText.className = 'font-bold text-rose-700';
                    adviceText.innerText = 'Mulai perjalanan sehat dengan pendampingan Personal Trainer bersertifikat di GymPulse.';
                }
            }
        }
    </script>

</body>
</html>
