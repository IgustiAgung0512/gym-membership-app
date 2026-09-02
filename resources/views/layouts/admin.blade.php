<!DOCTYPE html>
<html lang="id" x-data="{ sidebarOpen: false }" class="scroll-smooth">

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>@yield('title', 'Admin') · GymPulse</title>


    {{-- =====================================================
        TAILWIND
    ====================================================== --}}

    <script src="https://cdn.tailwindcss.com"></script>

    <script>

        tailwind.config = {

            theme: {

                extend: {

                    fontFamily: {

                        display: [
                            '"Space Grotesk"',
                            'sans-serif'
                        ],

                        sans: [
                            '"Inter"',
                            'sans-serif'
                        ],

                    },

                    colors: {

                        base: {
                            DEFAULT: '#F5F4EE',
                            card: '#FFFFFF',
                            line: '#E5E9E2'
                        },

                        volt: '#C6FF3D',

                        coral: '#E9856A',

                    }

                }

            }

        }

    </script>


    {{-- =====================================================
        FONT
    ====================================================== --}}

    <link
        rel="preconnect"
        href="https://fonts.googleapis.com"
    >

    <link
        href="https://fonts.googleapis.com/css2?family=Space+Grotesk:wght@500;600;700&family=Inter:wght@400;500;600;700&display=swap"
        rel="stylesheet"
    >

    @vite('resources/css/admin.css')

@stack('styles')


    {{-- =====================================================
        ALPINE
    ====================================================== --}}

    <script
        src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"
        defer
    ></script>


    {{-- =====================================================
        CHART JS
    ====================================================== --}}

    <script
        src="https://cdn.jsdelivr.net/npm/chart.js@4"
    ></script>


    {{-- =====================================================
        LIGHT THEME
    ====================================================== --}}

    

</head>


<body class="font-sans text-slate-200 antialiased">


<div class="min-h-screen flex">


    {{-- =====================================================
        SIDEBAR
    ====================================================== --}}

    <aside
        :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full lg:translate-x-0'"
        class="fixed z-40 inset-y-0 left-0 w-64 bg-base-card border-r border-base-line transform transition-transform duration-200 ease-in-out lg:static lg:translate-x-0 flex flex-col"
    >


        {{-- LOGO --}}

        <div class="h-16 flex items-center gap-2 px-5 border-b border-base-line">

            <div
                class="w-8 h-8 rounded-lg bg-volt flex items-center justify-center text-base font-black text-base"
            >
                G
            </div>

            <span
                class="font-display font-bold text-lg tracking-tight text-white"
            >
                GymPulse
            </span>

        </div>


        {{-- NAVIGATION --}}

        <nav
            class="flex-1 overflow-y-auto py-4 px-3 space-y-1 text-sm"
        >

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

                <a
                    href="{{ route($item['route']) }}"
                    class="flex items-center gap-3 px-3 py-2.5 rounded-xl transition-colors {{ request()->routeIs($item['route'].'*') ? 'bg-volt text-base font-semibold' : 'text-slate-300 hover:bg-white/5' }}"
                >

                    <svg
                        class="w-5 h-5 shrink-0"
                        fill="none"
                        stroke="currentColor"
                        stroke-width="1.8"
                        viewBox="0 0 24 24"
                    >

                        <path
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            d="{{ $item['icon'] }}"
                        />

                    </svg>

                    <span>
                        {{ $item['label'] }}
                    </span>

                </a>

            @endforeach

        </nav>


        {{-- USER --}}

        <div class="p-3 border-t border-base-line">

            <div class="flex items-center gap-3 px-2 py-2">

                <div
                    class="w-9 h-9 rounded-full bg-volt/20 text-volt flex items-center justify-center font-semibold"
                >
                    {{ substr(auth()->user()->name,0,1) }}
                </div>

                <div class="flex-1 min-w-0">

                    <p class="text-sm font-medium text-white truncate">
                        {{ auth()->user()->name }}
                    </p>

                    <p class="text-xs text-slate-400">
                        Administrator
                    </p>

                </div>

            </div>


            {{-- LOGOUT --}}

            <form
                method="POST"
                action="{{ route('logout') }}"
            >

                @csrf

                <button
                    class="w-full mt-1 flex items-center gap-2 px-3 py-2 rounded-xl text-sm text-coral hover:bg-coral/10 transition-colors"
                >

                    <svg
                        class="w-4 h-4"
                        fill="none"
                        stroke="currentColor"
                        stroke-width="1.8"
                        viewBox="0 0 24 24"
                    >

                        <path
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"
                        />

                    </svg>

                    Keluar

                </button>

            </form>

        </div>

    </aside>


    {{-- MOBILE OVERLAY --}}

    <div
        x-show="sidebarOpen"
        x-cloak
        @click="sidebarOpen=false"
        class="fixed inset-0 bg-black/60 z-30 lg:hidden"
    ></div>


    {{-- =====================================================
        MAIN
    ====================================================== --}}

    <div class="flex-1 flex flex-col min-w-0">


        {{-- HEADER --}}

        <header
            class="h-16 sticky top-0 z-20 bg-base/80 backdrop-blur border-b border-base-line flex items-center gap-4 px-4 lg:px-8"
        >

            <button
                @click="sidebarOpen = true"
                class="lg:hidden text-slate-300"
            >

                <svg
                    class="w-6 h-6"
                    fill="none"
                    stroke="currentColor"
                    stroke-width="2"
                    viewBox="0 0 24 24"
                >

                    <path
                        stroke-linecap="round"
                        stroke-linejoin="round"
                        d="M4 6h16M4 12h16M4 18h16"
                    />

                </svg>

            </button>


            <h1
                class="font-display font-semibold text-xl text-white"
            >
                @yield('title', 'Dashboard')
            </h1>


            <div class="ml-auto flex items-center gap-3">

                <span
                    class="hidden sm:inline text-xs px-3 py-1.5 rounded-full bg-base-card border border-base-line text-slate-400"
                >
                    {{ now()->translatedFormat('l, d F Y') }}
                </span>

            </div>

        </header>


        {{-- =================================================
            CONTENT
        ================================================== --}}

        <main class="flex-1 p-4 lg:p-8">

            @include('components.temp-password-alert')

            {{-- SUCCESS --}}

            @if (session('success'))

                <div
                    x-data="{ show: true }"
                    x-show="show"
                    x-init="setTimeout(() => show = false, 5000)"
                    x-transition
                    class="mb-6 flex items-start gap-3 rounded-xl border border-volt/30 bg-volt/10 px-4 py-3 text-sm text-volt"
                >

                    <svg
                        class="w-5 h-5 mt-0.5 shrink-0"
                        fill="none"
                        stroke="currentColor"
                        stroke-width="2"
                        viewBox="0 0 24 24"
                    >

                        <path
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            d="M5 13l4 4L19 7"
                        />

                    </svg>

                    <span>
                        {{ session('success') }}
                    </span>

                </div>

            @endif


            @yield('content')

        </main>

    </div>

</div>


</body>
</html>