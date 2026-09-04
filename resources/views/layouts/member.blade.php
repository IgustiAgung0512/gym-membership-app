<!DOCTYPE html>
<html lang="id" class="scroll-smooth">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Member') · GymPulse</title>

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
    </style>
</head>
<body class="font-sans text-slate-800 bg-[#F8FAFC] antialiased pb-24 lg:pb-12">

{{-- MEMBER HEADER BAR --}}
<header class="sticky top-0 z-20 bg-white/90 backdrop-blur border-b border-slate-200">
    <div class="max-w-4xl mx-auto flex items-center justify-between px-4 sm:px-6 h-16">
        
        <a href="{{ route('member.dashboard') }}" class="flex items-center gap-2.5">
            <div class="w-8 h-8 rounded-lg bg-slate-900 text-lime-400 flex items-center justify-center font-display font-extrabold text-base">
                G
            </div>
            <span class="font-display font-bold text-lg text-slate-900 tracking-tight">
                GymPulse<span class="text-lime-600">.</span>
            </span>
            <span class="hidden sm:inline text-[10px] font-bold uppercase tracking-wider px-2 py-0.5 rounded bg-lime-100 text-lime-800 ml-1">
                Portal Member
            </span>
        </a>

        <div class="flex items-center gap-2 sm:gap-3">
            <a href="{{ route('home') }}" class="text-xs px-3 py-1.5 rounded-lg border border-slate-200 bg-white text-slate-600 hover:text-slate-900 hover:border-slate-300 transition font-medium hidden sm:inline-flex">
                Beranda
            </a>

            <a href="{{ route('member.password.edit') }}" class="text-xs px-3 py-1.5 rounded-lg border border-slate-200 bg-white text-slate-700 hover:text-slate-900 hover:border-slate-300 transition font-medium">
                Ganti Password
            </a>

            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button class="text-xs px-3 py-1.5 rounded-lg bg-rose-50 text-rose-700 hover:bg-rose-100 border border-rose-200 transition font-medium">
                    Keluar
                </button>
            </form>
        </div>

    </div>
</header>

{{-- MAIN CONTENT --}}
<main class="max-w-4xl mx-auto px-4 sm:px-6 py-6 sm:py-8">
    @yield('content')
</main>

</body>
</html>
