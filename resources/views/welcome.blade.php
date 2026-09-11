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

<body class="landing-page bg-[#F8FAFC] text-slate-800 antialiased selection:bg-lime-200 selection:text-slate-900">

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
                                        <li class="flex items-center gap-2"><span class="text-lime-600 font-bold">✓</span> Gratis Kartu RFID Tap</li>
                                        <li class="flex items-center gap-2"><span class="text-lime-600 font-bold">✓</span> Akses 7 Hari Seminggu</li>
                                        <li class="flex items-center gap-2"><span class="text-lime-600 font-bold">✓</span> Loker & Hot Shower</li>
                                        <li class="flex items-center gap-2"><span class="text-lime-600 font-bold">✓</span> Notifikasi WhatsApp Check-In</li>
                                        <li class="flex items-center gap-2"><span class="text-lime-600 font-bold">✓</span> Akun Portal Member Online</li>
                                    </ul>
                                </div>

                                <div class="pt-6">
                                    <a href="https://wa.me/?text={{ urlencode('Halo GymPulse, saya ingin mendaftar paket: ' . $pkg->name) }}" target="_blank" class="{{ $isPopular ? 'btn-lime-action' : 'btn-secondary-action' }} w-full justify-center text-xs py-2.5">
                                        Daftar via WhatsApp
                                    </a>
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
                                    <li class="flex items-center gap-2"><span class="text-lime-600 font-bold">✓</span> Kartu RFID Tap</li>
                                    <li class="flex items-center gap-2"><span class="text-lime-600 font-bold">✓</span> Akses 7 Hari / Minggu</li>
                                    <li class="flex items-center gap-2"><span class="text-lime-600 font-bold">✓</span> Loker & Hot Shower</li>
                                </ul>
                            </div>
                            <div class="pt-6">
                                <a href="https://wa.me/?text=Halo%20GymPulse,%20saya%20tertarik%20paket%201%20Bulan" target="_blank" class="btn-secondary-action w-full justify-center text-xs py-2.5">Daftar Sekarang</a>
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
                            <div class="pt-6">
                                <a href="https://wa.me/?text=Halo%20GymPulse,%20saya%20tertarik%20paket%203%20Bulan" target="_blank" class="btn-lime-action w-full justify-center text-xs py-2.5">Daftar Sekarang</a>
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
                            <div class="pt-6">
                                <a href="https://wa.me/?text=Halo%20GymPulse,%20saya%20tertarik%20paket%206%20Bulan" target="_blank" class="btn-secondary-action w-full justify-center text-xs py-2.5">Daftar Sekarang</a>
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
                            <div class="pt-6">
                                <a href="https://wa.me/?text=Halo%20GymPulse,%20saya%20tertarik%20paket%2012%20Bulan" target="_blank" class="btn-secondary-action w-full justify-center text-xs py-2.5">Daftar Sekarang</a>
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
         JAVASCRIPT
         ========================================================================= -->
    <script>
        // Mobile Menu — with aria-expanded toggle
        const mobileMenuBtn = document.getElementById('mobileMenuBtn');
        const mobileMenu = document.getElementById('mobileMenu');
        const menuIconClosed = document.getElementById('menuIconClosed');
        const menuIconOpen = document.getElementById('menuIconOpen');

        mobileMenuBtn.addEventListener('click', () => {
            const isExpanded = mobileMenuBtn.getAttribute('aria-expanded') === 'true';
            mobileMenuBtn.setAttribute('aria-expanded', String(!isExpanded));
            mobileMenuBtn.setAttribute('aria-label', isExpanded ? 'Buka Menu Navigasi' : 'Tutup Menu Navigasi');
            mobileMenu.classList.toggle('hidden');
            menuIconClosed.classList.toggle('hidden');
            menuIconOpen.classList.toggle('hidden');
        });

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
            const heightInput = document.getElementById('bmiHeight').value;
            const weightInput = document.getElementById('bmiWeight').value;
            const resultBox = document.getElementById('bmiResultBox');
            const scoreText = document.getElementById('bmiScoreText');
            const categoryText = document.getElementById('bmiCategoryText');
            const adviceText = document.getElementById('bmiAdviceText');

            const heightM = parseFloat(heightInput) / 100;
            const weightKg = parseFloat(weightInput);

            if (!heightM || !weightKg || heightM <= 0 || weightKg <= 0) {
                alert('Silakan masukkan tinggi dan berat badan yang valid.');
                return;
            }

            const bmi = (weightKg / (heightM * heightM)).toFixed(1);
            scoreText.innerText = bmi;
            resultBox.classList.remove('hidden');

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
    </script>

</body>
</html>
