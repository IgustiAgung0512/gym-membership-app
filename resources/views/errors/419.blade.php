<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>419 - Sesi Kedaluwarsa · GymPulse</title>
    <link rel="icon" type="image/svg+xml" href="{{ asset('favicon.svg') }}">
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Space+Grotesk:wght@600;700&family=Inter:wght@400;500;600&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Inter', sans-serif; }
        .font-display { font-family: 'Space Grotesk', sans-serif; }
    </style>
</head>
<body class="bg-slate-950 text-slate-100 min-h-screen flex items-center justify-center p-6 antialiased">
    <div class="max-w-md w-full bg-slate-900 border border-slate-800 rounded-3xl p-8 text-center shadow-2xl relative overflow-hidden">
        <div class="absolute -top-24 -left-24 w-48 h-48 bg-sky-500/10 rounded-full blur-3xl pointer-events-none"></div>
        <div class="w-16 h-16 bg-sky-500/10 border border-sky-500/20 text-sky-400 rounded-2xl flex items-center justify-center mx-auto mb-6 text-2xl font-bold">
            ⏳
        </div>
        <span class="inline-block px-3 py-1 bg-sky-500/10 text-sky-400 text-xs font-semibold rounded-full uppercase tracking-wider mb-3">
            Error 419 · Page Expired
        </span>
        <h1 class="text-2xl font-bold font-display text-white mb-2">Sesi Telah Berakhir</h1>
        <p class="text-slate-400 text-sm mb-6 leading-relaxed">
            Halaman ini telah terbuka terlalu lama atau token keamanan formulir Anda telah kedaluwarsa demi keamanan akun.
        </p>
        <div class="flex flex-col sm:flex-row gap-3 justify-center">
            <button onclick="window.location.reload()" class="inline-flex items-center justify-center px-5 py-2.5 bg-lime-500 hover:bg-lime-400 text-slate-950 text-sm font-semibold rounded-xl transition-colors">
                Muat Ulang Halaman
            </button>
            <a href="{{ route('login') }}" class="inline-flex items-center justify-center px-5 py-2.5 bg-slate-800 hover:bg-slate-700 text-slate-200 text-sm font-medium rounded-xl transition-colors">
                Ke Halaman Login
            </a>
        </div>
    </div>
</body>
</html>
