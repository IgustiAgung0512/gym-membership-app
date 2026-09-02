<!DOCTYPE html>
<html lang="id" class="scroll-smooth">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Member') · GymPulse</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = { theme: { extend: {
            fontFamily: { display: ['"Space Grotesk"', 'sans-serif'], sans: ['"Inter"', 'sans-serif'] },
            colors: { base: { DEFAULT: '#0F1115', card: '#171A21', line: '#262A34' }, volt: '#C6FF3D', coral: '#FF5C6C' }
        } } }
    </script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Space+Grotesk:wght@500;600;700&family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js" defer></script>
    <style>body{background:#0F1115}</style>
</head>
<body class="font-sans text-slate-200 antialiased pb-24 lg:pb-0">
<header class="sticky top-0 z-20 bg-base/80 backdrop-blur border-b border-base-line">
    <div class="max-w-3xl mx-auto flex items-center gap-2 px-4 h-16">
        <div class="w-8 h-8 rounded-lg bg-volt flex items-center justify-center text-base font-black">G</div>
        <span class="font-display font-bold text-lg text-white">GymPulse</span>
        <div class="ml-auto flex items-center gap-2">
            <a href="{{ route('member.password.edit') }}" class="text-xs px-3 py-1.5 rounded-full border border-base-line text-slate-400 hover:text-volt hover:border-volt/40 transition-colors">Ganti Password</a>
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button class="text-xs px-3 py-1.5 rounded-full border border-base-line text-slate-400 hover:text-coral hover:border-coral/40 transition-colors">Keluar</button>
            </form>
        </div>
    </div>
</header>
<main class="max-w-3xl mx-auto px-4 py-6">
    @yield('content')
</main>
</body>
</html>
