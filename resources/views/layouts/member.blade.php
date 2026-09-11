<!DOCTYPE html>
<html lang="id" class="scroll-smooth">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Member') · GymPulse</title>
    <link rel="icon" type="image/svg+xml" href="{{ asset('favicon.svg') }}?v=3">
    <link rel="icon" type="image/png" href="{{ asset('favicon.png') }}?v=3">
    <link rel="shortcut icon" href="{{ asset('favicon.ico') }}?v=3">

    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: {
                        display: ['"Space Grotesk"', 'sans-serif'],
                        sans: ['"Inter"', 'sans-serif'],
                    },
                    colors: {
                        base: {
                            DEFAULT: '#F8FAFC',
                            card: '#FFFFFF',
                            line: '#E2E8F0'
                        },
                        volt: '#84CC16',
                        coral: '#EF4444'
                    }
                }
            }
        }
    </script>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Space+Grotesk:wght@500;600;700&family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js" defer></script>

    <style>
        body { background: #F8FAFC; color: #0F172A; }
        [x-cloak] { display: none !important; }

        @media print {
            @page {
                size: auto;
                margin: 0;
            }

            /* Sembunyikan SEMUA header navigasi, navbar, tombol, footer, dan elemen no-print */
            header,
            header *,
            nav,
            nav *,
            footer,
            footer *,
            .no-print,
            .no-print *,
            .print\:hidden,
            [class*="print:hidden"],
            [class*="no-print"] {
                display: none !important;
                visibility: hidden !important;
                height: 0 !important;
                min-height: 0 !important;
                max-height: 0 !important;
                padding: 0 !important;
                margin: 0 !important;
                border: none !important;
                opacity: 0 !important;
            }

            body {
                background: #ffffff !important;
                color: #0f172a !important;
                padding: 0 !important;
                margin: 0 !important;
            }

            main {
                max-width: 100% !important;
                padding: 20px 0 !important;
                margin: 0 auto !important;
            }

            /* Saat modal bukti keanggotaan dicetak */
            .print-hide-on-modal,
            .print-hide-on-modal * {
                display: none !important;
                height: 0 !important;
                margin: 0 !important;
                padding: 0 !important;
            }

            .invoice-modal-overlay {
                position: static !important;
                display: flex !important;
                align-items: center !important;
                justify-content: center !important;
                background: transparent !important;
                backdrop-filter: none !important;
                padding: 0 !important;
                margin: 0 auto !important;
            }

            .invoice-card {
                box-shadow: none !important;
                border: 1px solid #cbd5e1 !important;
                border-radius: 16px !important;
                margin: 20px auto !important;
                max-width: 480px !important;
                width: 100% !important;
                padding: 24px !important;
                page-break-inside: avoid !important;
                break-inside: avoid !important;
            }

            /* Pastikan warna teks & badge tetap jelas */
            * {
                -webkit-print-color-adjust: exact !important;
                print-color-adjust: exact !important;
            }
        }
    </style>
</head>
<body class="font-sans text-slate-800 bg-[#F8FAFC] antialiased pb-24 lg:pb-12">

{{-- MEMBER HEADER BAR --}}
<header class="print:hidden no-print sticky top-0 z-20 bg-white/95 backdrop-blur border-b border-slate-200">
    <div class="max-w-6xl mx-auto flex items-center justify-between px-3.5 sm:px-6 h-14 sm:h-16">
        
        <a href="{{ route('member.dashboard') }}" class="flex items-center gap-3 group shrink-0">
            <div class="w-9 h-9 sm:w-10 sm:h-10 rounded-xl bg-slate-900 flex items-center justify-center font-display font-extrabold text-lime-400 text-lg sm:text-xl shadow-sm group-hover:scale-105 transition-transform">
                G
            </div>
            <div class="flex flex-col">
                <div class="flex items-center gap-1.5">
                    <span class="font-display font-extrabold text-lg sm:text-xl text-slate-900 tracking-tight leading-none">
                        GymPulse<span class="text-lime-600">.</span>
                    </span>
                    <span class="hidden md:inline text-[9px] font-bold uppercase tracking-wider px-1.5 py-0.5 rounded bg-lime-100 text-lime-800 border border-lime-200/60">
                        Portal Member
                    </span>
                </div>
                <span class="text-[9px] sm:text-[10px] tracking-wider text-slate-500 uppercase font-semibold mt-0.5 whitespace-nowrap">
                    Smart RFID Fitness
                </span>
            </div>
        </a>

        <div class="flex items-center gap-1.5 sm:gap-2">
            <a href="{{ route('member.dashboard') }}" class="text-xs px-2.5 sm:px-3 py-1.5 rounded-lg border {{ request()->routeIs('member.dashboard') ? 'bg-slate-900 text-white font-bold border-slate-900 shadow-sm' : 'border-slate-200 bg-white text-slate-700 hover:text-slate-900' }} transition font-medium">
                Dashboard
            </a>

            <a href="{{ route('member.store.index') }}" class="text-xs px-2.5 sm:px-3 py-1.5 rounded-lg border {{ request()->routeIs('member.store.*') ? 'bg-slate-900 text-white font-bold border-slate-900 shadow-sm' : 'border-slate-200 bg-white text-slate-700 hover:text-slate-900' }} transition font-medium inline-flex items-center gap-1">
                <span>🛒</span>
                <span>Toko</span>
            </a>

            <a href="{{ route('member.password.edit') }}" class="text-xs px-2.5 sm:px-3 py-1.5 rounded-lg border border-slate-200 bg-white text-slate-700 hover:text-slate-900 hover:border-slate-300 transition font-medium hidden sm:inline-flex">
                Ganti Password
            </a>

            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button class="text-xs px-2.5 sm:px-3 py-1.5 rounded-lg bg-rose-50 text-rose-700 hover:bg-rose-100 border border-rose-200 transition font-medium">
                    Keluar
                </button>
            </form>
        </div>

    </div>
</header>

{{-- MAIN CONTENT --}}
<main class="max-w-6xl mx-auto px-3.5 sm:px-6 py-4 sm:py-6">
    @yield('content')
</main>

</body>
</html>
