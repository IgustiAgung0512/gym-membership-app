<!DOCTYPE html>
<html lang="id" class="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Live Check-in · GymPulse</title>
    <link rel="icon" type="image/svg+xml" href="{{ asset('favicon.svg') }}">
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://unpkg.com/lucide@latest"></script>
    <script>
        tailwind.config = {
            darkMode: 'class',
            theme: { extend: {
                fontFamily: { display: ['"Plus Jakarta Sans"', 'sans-serif'], sans: ['"Plus Jakarta Sans"', 'sans-serif'] },
                colors: {
                    base: { DEFAULT: '#070a12', card: '#0f172a', line: '#1e293b' },
                    brand: { 50: '#f0fdf4', 400: '#4ade80', 500: '#22c55e', 600: '#16a34a', 900: '#14532d', glow: '#4ade80' },
                }
            } }
        }
    </script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js" defer></script>
    <style>
        body { background-color: #070a12; }
        [x-cloak] { display: none !important; }
        .glass-panel { background: rgba(15, 23, 42, 0.75); backdrop-filter: blur(12px); border: 1px solid rgba(255, 255, 255, 0.07); }
        .glow-emerald { box-shadow: 0 0 60px -10px rgba(34, 197, 94, 0.4); }
    </style>
</head>
<body class="font-sans text-slate-100 antialiased h-screen overflow-hidden selection:bg-brand-500 selection:text-slate-950" x-data="liveCheckin()" x-init="init()">

    <!-- Header -->
    <div class="flex items-center justify-between px-10 py-6">
        <div class="flex items-center gap-3">
            <div class="p-2.5 bg-gradient-to-tr from-brand-600 to-brand-500 text-slate-950 rounded-xl shadow-md shadow-brand-500/20">
                <i data-lucide="dumbbell" class="w-6 h-6 stroke-[2.5]"></i>
            </div>
            <span class="font-black text-2xl tracking-wide text-white">GYMPULSE</span>
        </div>
        <div class="flex items-center gap-4">
            <span class="flex items-center gap-2 px-3.5 py-1.5 rounded-full bg-brand-500/10 border border-brand-500/20 text-brand-400 text-xs font-medium">
                <span class="w-2 h-2 rounded-full bg-brand-500 animate-pulse"></span> Live
            </span>
            <span class="text-slate-400 text-sm" x-text="clock"></span>
        </div>
    </div>

    <!-- Main stage -->
    <div class="h-[calc(100vh-96px)] flex items-center justify-center px-10">
        <template x-if="!current">
            <div class="text-center">
                <div class="inline-flex items-center justify-center w-20 h-20 rounded-3xl bg-slate-900 border border-slate-800 mb-6">
                    <i data-lucide="scan-line" class="w-9 h-9 text-slate-600"></i>
                </div>
                <p class="font-display text-3xl font-bold text-slate-400">Menunggu member check-in...</p>
                <p class="text-slate-600 mt-2">Tempelkan kartu RFID di pintu masuk untuk memulai.</p>
            </div>
        </template>

        <template x-if="current">
            <div :key="current.id"
                 x-show="show"
                 x-transition:enter="transition ease-out duration-500"
                 x-transition:enter-start="opacity-0 scale-90"
                 x-transition:enter-end="opacity-100 scale-100"
                 class="glass-panel rounded-[2.5rem] px-12 py-10 text-center glow-emerald relative overflow-hidden">
                <div class="absolute -top-16 -right-16 w-40 h-40 bg-brand-500/20 rounded-full blur-3xl"></div>
                <div class="relative mx-auto w-56 h-56 md:w-72 md:h-72 rounded-[2rem] overflow-hidden border-4 border-brand-500 shadow-lg shadow-brand-500/30">
                    <img :src="current.photo_url" class="w-full h-full object-cover" alt="">
                </div>
                <span class="relative inline-flex items-center gap-1.5 mt-6 px-3 py-1 rounded-full text-xs font-bold uppercase tracking-wider bg-brand-500/10 text-brand-400 border border-brand-500/20">
                    <i data-lucide="check" class="w-3.5 h-3.5 stroke-[3]"></i> Akses Diterima
                </span>
                <h1 class="relative font-display font-extrabold text-4xl md:text-6xl text-white mt-3" x-text="current.member_name"></h1>
                <p class="relative text-slate-400 font-mono text-lg mt-2" x-text="current.member_code"></p>
                <div class="relative mt-6 inline-flex items-center gap-2 bg-brand-500/10 border border-brand-500/30 rounded-full px-6 py-2.5">
                    <i data-lucide="check-circle-2" class="w-5 h-5 text-brand-400"></i>
                    <span class="text-brand-400 font-semibold text-lg">Check-in berhasil &middot; <span x-text="current.time"></span></span>
                </div>
            </div>
        </template>
    </div>

<script>
function liveCheckin() {
    return {
        queue: [],
        current: null,
        show: false,
        clock: '',
        lastId: 0,
        pollUrl: '{{ route('admin.attendance.poll') }}',

        async init() {
            this.tickClock();
            setInterval(() => this.tickClock(), 1000);

            try {
                const res = await fetch(`${this.pollUrl}?after_id=999999999`);
                const data = await res.json();
                this.lastId = data.latest_id || 0;
            } catch (e) { this.lastId = 0; }

            lucide.createIcons();
            // Polling setiap 1 detik agar absensi langsung muncul di layar live monitor
            setInterval(() => this.poll(), 1000);
        },

        tickClock() {
            this.clock = new Date().toLocaleString('id-ID', {
                weekday: 'long', day: 'numeric', month: 'long', hour: '2-digit', minute: '2-digit', second: '2-digit'
            });
        },

        async poll() {
            try {
                const res = await fetch(`${this.pollUrl}?after_id=${this.lastId}`);
                const data = await res.json();
                this.lastId = data.latest_id ?? this.lastId;
                (data.checkins || []).forEach(c => this.queue.push(c));
                this.processQueue();
            } catch (e) { /* coba lagi di polling berikutnya */ }
        },

        processQueue() {
            if (this.current || this.queue.length === 0) return;
            const next = this.queue.shift();
            this.current = next;
            this.show = true;
            this.$nextTick(() => lucide.createIcons());

            setTimeout(() => {
                this.show = false;
                setTimeout(() => {
                    this.current = null;
                    this.processQueue();
                }, 500);
            }, 5000);
        },
    };
}
</script>
</body>
</html>
