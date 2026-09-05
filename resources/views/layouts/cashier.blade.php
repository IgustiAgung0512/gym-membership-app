<!DOCTYPE html>
<html lang="id" class="scroll-smooth">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Kasir POS') · GymPulse Cashier</title>

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
</head>

<body class="font-sans text-slate-800 bg-slate-100 min-h-screen flex flex-col antialiased">

    {{-- TOPBAR CASHIER --}}
    <header class="bg-[#0F172A] border-b border-slate-800 text-white sticky top-0 z-30 shadow-md">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex items-center justify-between h-16">
                
                {{-- LEFT: BRAND & BADGE --}}
                <div class="flex items-center gap-6">
                    <a href="{{ route('cashier.pos.index') }}" class="flex items-center gap-2.5">
                        <div class="w-8 h-8 rounded-lg bg-lime-400 text-slate-950 flex items-center justify-center font-display font-black text-base shadow-sm">
                            G
                        </div>
                        <div class="leading-tight">
                            <span class="font-display font-bold text-lg text-white tracking-tight">
                                GymPulse<span class="text-lime-400">.</span>
                            </span>
                            <span class="block text-[10px] uppercase font-bold tracking-widest text-lime-400">
                                POS Kasir
                            </span>
                        </div>
                    </a>

                    {{-- NAV TABS --}}
                    <nav class="hidden md:flex items-center gap-1">
                        <a
                            href="{{ route('cashier.pos.index') }}"
                            class="flex items-center gap-2 px-3.5 py-2 rounded-xl text-xs font-bold transition {{ request()->routeIs('cashier.pos*') ? 'bg-lime-500 text-slate-950 shadow-sm' : 'text-slate-300 hover:text-white hover:bg-slate-800' }}"
                        >
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                            <span>Kasir POS</span>
                        </a>

                        <a
                            href="{{ route('cashier.orders.index') }}"
                            class="flex items-center gap-2 px-3.5 py-2 rounded-xl text-xs font-bold transition {{ request()->routeIs('cashier.orders*') ? 'bg-lime-500 text-slate-950 shadow-sm' : 'text-slate-300 hover:text-white hover:bg-slate-800' }}"
                        >
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                            <span>Riwayat Shift Transaksi</span>
                        </a>

                        <a
                            href="{{ route('cashier.members.index') }}"
                            class="flex items-center gap-2 px-3.5 py-2 rounded-xl text-xs font-bold transition {{ request()->routeIs('cashier.members*') ? 'bg-lime-500 text-slate-950 shadow-sm' : 'text-slate-300 hover:text-white hover:bg-slate-800' }}"
                        >
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M17 20h5v-2a4 4 0 00-3-3.87M9 20H4v-2a4 4 0 013-3.87m6-8a4 4 0 11-8 0 4 4 0 018 0zm6 3a4 4 0 11-8 0 4 4 0 018 0z"/></svg>
                            <span>Member & Pendaftaran</span>
                        </a>

                        <a
                            href="{{ route('cashier.attendance.index') }}"
                            class="flex items-center gap-2 px-3.5 py-2 rounded-xl text-xs font-bold transition {{ request()->routeIs('cashier.attendance*') ? 'bg-lime-500 text-slate-950 shadow-sm' : 'text-slate-300 hover:text-white hover:bg-slate-800' }}"
                        >
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                            <span>Monitor RFID</span>
                        </a>
                    </nav>
                </div>

                {{-- RIGHT: CASHIER INFO & LOGOUT --}}
                <div class="flex items-center gap-3">
                    <div class="hidden sm:flex items-center gap-2 px-3 py-1 rounded-full bg-slate-800/90 border border-slate-700/80 text-xs text-slate-300">
                        <span class="w-2 h-2 rounded-full bg-emerald-400 animate-pulse"></span>
                        <span>Kasir: <strong class="text-white">{{ auth()->user()->name }}</strong></span>
                    </div>

                    @if (auth()->user()->isAdmin())
                        <a href="{{ route('admin.dashboard') }}" class="text-xs font-bold text-slate-300 hover:text-lime-400 bg-slate-800 px-3 py-1.5 rounded-lg border border-slate-700 transition">
                            👑 Mode Admin
                        </a>
                    @endif

                    <form action="{{ route('logout') }}" method="POST" class="inline">
                        @csrf
                        <button type="submit" class="flex items-center gap-1.5 text-xs font-semibold px-3 py-1.5 rounded-xl bg-rose-500/10 text-rose-300 hover:bg-rose-500 hover:text-white transition border border-rose-500/20" onclick="return confirm('Keluar dari sesi kasir?')">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/></svg>
                            <span>Keluar</span>
                        </button>
                    </form>
                </div>

            </div>

            {{-- MOBILE NAV TABS --}}
            <div class="flex md:hidden items-center justify-around py-2 border-t border-slate-800 text-xs">
                <a href="{{ route('cashier.pos.index') }}" class="font-bold {{ request()->routeIs('cashier.pos*') ? 'text-lime-400' : 'text-slate-400' }}">
                    🛒 POS Kasir
                </a>
                <a href="{{ route('cashier.orders.index') }}" class="font-bold {{ request()->routeIs('cashier.orders*') ? 'text-lime-400' : 'text-slate-400' }}">
                    📋 Riwayat
                </a>
                <a href="{{ route('cashier.members.index') }}" class="font-bold {{ request()->routeIs('cashier.members*') ? 'text-lime-400' : 'text-slate-400' }}">
                    👥 Member
                </a>
                <a href="{{ route('cashier.attendance.index') }}" class="font-bold {{ request()->routeIs('cashier.attendance*') ? 'text-lime-400' : 'text-slate-400' }}">
                    🪪 RFID
                </a>
            </div>
        </div>
    </header>

    {{-- MAIN CONTENT --}}
    <main class="flex-1 w-full max-w-7xl mx-auto px-3 sm:px-6 lg:px-8 py-5">
        {{-- FLASH ALERTS --}}
        @if (session('success'))
            <div class="mb-4 p-4 rounded-xl bg-emerald-500/10 border border-emerald-500/20 text-emerald-800 text-sm flex items-center justify-between">
                <span>{{ session('success') }}</span>
                <button type="button" onclick="this.parentElement.remove()" class="text-emerald-700 hover:text-emerald-900">&times;</button>
            </div>
        @endif

        @if (session('error'))
            <div class="mb-4 p-4 rounded-xl bg-rose-500/10 border border-rose-500/20 text-rose-800 text-sm flex items-center justify-between">
                <span>{{ session('error') }}</span>
                <button type="button" onclick="this.parentElement.remove()" class="text-rose-700 hover:text-rose-900">&times;</button>
            </div>
        @endif

        @yield('content')
    </main>

    @stack('scripts')
</body>
</html>
