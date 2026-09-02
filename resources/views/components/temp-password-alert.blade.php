@if (session('temp_password'))
    @php $tp = session('temp_password'); @endphp
    <div x-data="{ show: true, copied: false }" x-show="show" x-transition
         class="mb-6 rounded-xl border border-volt/40 bg-volt/10 px-4 py-4">
        <div class="flex items-start gap-3">
            <svg class="w-5 h-5 mt-0.5 shrink-0 text-volt" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
            </svg>
            <div class="flex-1 min-w-0">
                <p class="text-sm text-white font-medium">Password sementara untuk {{ $tp['name'] }}</p>
                <p class="text-xs text-slate-400 mt-0.5">Segera sampaikan ke member secara langsung/WA pribadi. Notifikasi ini tidak akan hilang otomatis — tutup manual setelah dicatat.</p>
                <div class="mt-3 flex items-center gap-2">
                    <code id="temp-password-{{ md5($tp['password']) }}" class="font-mono text-base font-bold text-volt bg-base border border-base-line rounded-lg px-3 py-1.5 select-all">{{ $tp['password'] }}</code>
                    <button type="button"
                            @click="navigator.clipboard.writeText('{{ $tp['password'] }}'); copied = true; setTimeout(() => copied = false, 2000)"
                            class="text-xs font-medium px-3 py-1.5 rounded-lg border border-base-line text-slate-300 hover:bg-white/5 transition">
                        <span x-show="!copied">Salin</span>
                        <span x-show="copied" x-cloak class="text-volt">Tersalin!</span>
                    </button>
                </div>
            </div>
            <button @click="show = false" class="text-slate-400 hover:text-white shrink-0">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
        </div>
    </div>
@endif
