<!DOCTYPE html>
<html lang="id" x-data="{ sidebarOpen: false }" class="scroll-smooth">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Admin') · GymPulse</title>

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
                        coral: '#EF4444',
                    }
                }
            }
        }
    </script>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Space+Grotesk:wght@500;600;700&family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">

    @vite('resources/css/admin.css')
    @stack('styles')

    <script src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js" defer></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4"></script>
</head>

<body class="font-sans text-slate-800 bg-[#F8FAFC] antialiased">

<div class="min-h-screen flex">

    {{-- SIDEBAR --}}
    <aside
        :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full lg:translate-x-0'"
        class="fixed z-40 inset-y-0 left-0 w-64 bg-[#0F172A] border-r border-slate-800 transform transition-transform duration-200 ease-in-out lg:sticky lg:top-0 lg:h-screen lg:translate-x-0 lg:shrink-0 flex flex-col shadow-lg"
    >
        {{-- LOGO --}}
        <div class="h-16 flex items-center justify-between px-5 border-b border-slate-800 bg-slate-950/40">
            <a href="{{ route('admin.dashboard') }}" class="flex items-center gap-2.5">
                <div class="w-8 h-8 rounded-lg bg-lime-400 text-slate-950 flex items-center justify-center font-display font-extrabold text-base">
                    G
                </div>
                <span class="font-display font-bold text-lg text-white tracking-tight">
                    GymPulse<span class="text-lime-400">.</span>
                </span>
            </a>
            <span class="text-[10px] font-bold uppercase tracking-wider px-2 py-0.5 rounded bg-slate-800 text-slate-400">
                Admin
            </span>
        </div>

        {{-- NAVIGATION --}}
        <nav class="flex-1 overflow-y-auto py-4 px-3 space-y-1 text-sm">
            @php
                $nav = [
                    [
                        'route' => 'admin.dashboard',
                        'label' => 'Dashboard',
                        'icon' => 'M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a4 4 0 001 1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6'
                    ],
                    [
                        'route' => 'admin.members.index',
                        'label' => 'Member',
                        'icon' => 'M17 20h5v-2a4 4 0 00-3-3.87M9 20H4v-2a4 4 0 013-3.87m6-8a4 4 0 11-8 0 4 4 0 018 0zm6 3a4 4 0 11-8 0 4 4 0 018 0z'
                    ],
                    [
                        'route' => 'admin.packages.index',
                        'label' => 'Paket Membership',
                        'icon' => 'M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4'
                    ],
                    [
                        'route' => 'admin.rfid.index',
                        'label' => 'Kartu RFID',
                        'icon' => 'M3 10h18M7 15h1m4 0h1m-7 4h16a1 1 0 001-1V6a1 1 0 00-1-1H4a1 1 0 00-1 1v12a1 1 0 001 1z'
                    ],
                    [
                        'route' => 'admin.attendance.index',
                        'label' => 'Absensi / Check-in',
                        'icon' => 'M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z'
                    ],
                    [
                        'route' => 'admin.whatsapp.index',
                        'label' => 'Log WhatsApp',
                        'icon' => 'M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-6l-4 4v-4z'
                    ],
                    [
                        'route' => 'admin.reports.index',
                        'label' => 'Laporan',
                        'icon' => 'M9 17v-2a2 2 0 012-2h2a2 2 0 012 2v2m-9 4h14a1 1 0 001-1V4a1 1 0 00-1-1H6a1 1 0 00-1 1v16a1 1 0 001 1z'
                    ],
                ];
            @endphp

            @foreach ($nav as $item)
                @php
                    $isActive = request()->routeIs($item['route'].'*');
                @endphp
                <a
                    href="{{ route($item['route']) }}"
                    class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl transition-all font-medium {{ $isActive ? 'bg-lime-500 text-slate-950 font-bold shadow-sm' : 'text-slate-300 hover:bg-slate-800/80 hover:text-white' }}"
                >
                    <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="{{ $item['icon'] }}"/>
                    </svg>
                    <span>{{ $item['label'] }}</span>
                </a>
            @endforeach
        </nav>

        {{-- USER INFO & LOGOUT --}}
        <div class="p-3 border-t border-slate-800 bg-slate-950/40">
            <div class="flex items-center gap-3 px-2 py-2">
                <div class="w-9 h-9 rounded-full bg-lime-400 text-slate-950 flex items-center justify-center font-bold text-sm">
                    {{ substr(auth()->user()->name, 0, 1) }}
                </div>
                <div class="flex-1 min-w-0">
                    <p class="text-sm font-semibold text-white truncate">
                        {{ auth()->user()->name }}
                    </p>
                    <p class="text-xs text-slate-400">
                        Administrator
                    </p>
                </div>
            </div>

            <div class="mt-2 pt-2 border-t border-slate-800 flex gap-1">
                <a href="{{ route('home') }}" class="flex-1 flex items-center justify-center gap-1.5 px-2 py-1.5 rounded-lg text-xs font-medium text-slate-400 hover:text-white hover:bg-slate-800 transition">
                    <span>Lihat Web</span>
                </a>
                <form method="POST" action="{{ route('logout') }}" class="flex-1">
                    @csrf
                    <button class="w-full flex items-center justify-center gap-1.5 px-2 py-1.5 rounded-lg text-xs font-medium text-rose-400 hover:bg-rose-500/10 transition">
                        Keluar
                    </button>
                </form>
            </div>
        </div>
    </aside>

    {{-- MOBILE OVERLAY --}}
    <div
        x-show="sidebarOpen"
        x-cloak
        @click="sidebarOpen=false"
        class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm z-30 lg:hidden"
    ></div>

    {{-- MAIN CONTENT AREA --}}
    <div class="flex-1 flex flex-col min-w-0 bg-[#F8FAFC]">

        {{-- HEADER BAR --}}
        <header class="h-16 sticky top-0 z-20 bg-white/90 backdrop-blur border-b border-slate-200 flex items-center justify-between px-4 lg:px-8">
            <div class="flex items-center gap-4">
                <button
                    @click="sidebarOpen = true"
                    class="lg:hidden p-2 rounded-lg text-slate-600 hover:bg-slate-100"
                    aria-label="Toggle Menu"
                >
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h16M4 18h16"/>
                    </svg>
                </button>

                <h1 class="font-display font-bold text-xl text-slate-900">
                    @yield('title', 'Dashboard')
                </h1>
            </div>

            <div class="flex items-center gap-3">
                <span class="hidden sm:inline text-xs font-medium px-3.5 py-1.5 rounded-full bg-slate-100 border border-slate-200 text-slate-600">
                    {{ now()->translatedFormat('l, d F Y') }}
                </span>
            </div>
        </header>

        {{-- CONTENT --}}
        <main class="flex-1 p-4 lg:p-8 overflow-y-auto">
            @include('components.temp-password-alert')

            @if (session('success'))
                <div
                    x-data="{ show: true }"
                    x-show="show"
                    x-init="setTimeout(() => show = false, 5000)"
                    x-transition
                    class="mb-6 flex items-start gap-3 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800 shadow-sm"
                >
                    <svg class="w-5 h-5 mt-0.5 shrink-0 text-emerald-600" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
                    </svg>
                    <span class="font-medium">{{ session('success') }}</span>
                </div>
            @endif

            @yield('content')
        </main>

    </div>

</div>

</body>
</html>