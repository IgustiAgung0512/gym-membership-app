@extends('layouts.admin')
@section('title', 'Kartu RFID')

@section('content')
<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    <div class="lg:col-span-2">
        
        {{-- MOBILE CARDS VIEW (Responsif di HP) --}}
        <div class="grid gap-3 lg:hidden">
            @forelse ($cards as $c)
                <div class="bg-white border border-slate-200 rounded-2xl p-4 shadow-sm">
                    <div class="flex items-start justify-between gap-2">
                        <div>
                            <span class="text-[10px] uppercase font-semibold text-slate-400">UID KARTU</span>
                            <p class="font-mono font-bold text-slate-900 text-base">{{ $c->uid }}</p>
                        </div>
                        @php $badge = ['assigned'=>'bg-lime-100 text-lime-800 font-semibold','unassigned'=>'bg-slate-100 text-slate-600 font-semibold','blocked'=>'bg-rose-100 text-rose-800 font-semibold'][$c->status]; @endphp
                        <span class="text-xs px-2.5 py-0.5 rounded-full {{ $badge }} shrink-0">
                            {{ ucfirst($c->status) }}
                        </span>
                    </div>

                    <div class="mt-2.5 pt-2.5 border-t border-slate-100 flex items-center justify-between text-xs">
                        <span class="text-slate-500">Pemilik:</span>
                        <strong class="text-slate-800">{{ $c->member->user->name ?? 'Belum terhubung' }}</strong>
                    </div>

                    <div class="mt-3 pt-2.5 border-t border-slate-100 flex items-center justify-end gap-2">
                        <form method="POST" action="{{ route('admin.rfid.toggle-block', $c) }}" class="flex-1">
                            @csrf
                            <button class="w-full text-xs font-semibold py-2 px-3 rounded-lg border {{ $c->status=='blocked' ? 'bg-lime-50 text-lime-800 border-lime-200 hover:bg-lime-100' : 'bg-rose-50 text-rose-700 border-rose-200 hover:bg-rose-100' }} transition">
                                {{ $c->status=='blocked' ? 'Buka Blokir' : 'Blokir' }}
                            </button>
                        </form>
                        <form method="POST" action="{{ route('admin.rfid.destroy', $c) }}" onsubmit="return confirm('Hapus kartu ini?')">
                            @csrf @method('DELETE')
                            <button class="text-xs font-semibold py-2 px-3.5 rounded-lg border border-slate-200 bg-white text-slate-600 hover:text-rose-600 hover:bg-rose-50 transition">
                                Hapus
                            </button>
                        </form>
                    </div>
                </div>
            @empty
                <div class="bg-white border border-slate-200 rounded-2xl p-8 text-center text-slate-500 text-sm shadow-sm">
                    Belum ada kartu RFID terdaftar.
                </div>
            @endforelse
        </div>

        {{-- DESKTOP TABLE VIEW (Layar Laptop / PC) --}}
        <div class="hidden lg:block bg-white border border-slate-200 rounded-2xl overflow-hidden shadow-sm">
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="text-left bg-slate-50 text-slate-600 border-b border-slate-200">
                            <th class="px-5 py-3.5 font-semibold text-xs uppercase tracking-wider">UID Kartu</th>
                            <th class="px-5 py-3.5 font-semibold text-xs uppercase tracking-wider">Pemilik</th>
                            <th class="px-5 py-3.5 font-semibold text-xs uppercase tracking-wider">Status</th>
                            <th class="px-5 py-3.5 font-semibold text-xs uppercase tracking-wider text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                    @forelse ($cards as $c)
                        <tr class="hover:bg-slate-50/80 transition">
                            <td class="px-5 py-3.5 font-mono font-bold text-slate-900">{{ $c->uid }}</td>
                            <td class="px-5 py-3.5 text-slate-700 font-medium">{{ $c->member->user->name ?? '—' }}</td>
                            <td class="px-5 py-3.5">
                                @php $badge = ['assigned'=>'bg-lime-100 text-lime-800 font-semibold','unassigned'=>'bg-slate-100 text-slate-600 font-semibold','blocked'=>'bg-rose-100 text-rose-800 font-semibold'][$c->status]; @endphp
                                <span class="text-xs px-2.5 py-0.5 rounded-full {{ $badge }}">{{ ucfirst($c->status) }}</span>
                            </td>
                            <td class="px-5 py-3.5">
                                <div class="flex items-center justify-end gap-3 text-xs">
                                    <form method="POST" action="{{ route('admin.rfid.toggle-block', $c) }}">
                                        @csrf
                                        <button class="font-semibold {{ $c->status=='blocked' ? 'text-lime-700' : 'text-rose-600' }} hover:underline">
                                            {{ $c->status=='blocked' ? 'Buka Blokir' : 'Blokir' }}
                                        </button>
                                    </form>
                                    <form method="POST" action="{{ route('admin.rfid.destroy', $c) }}" onsubmit="return confirm('Hapus kartu ini?')">
                                        @csrf @method('DELETE')
                                        <button class="font-semibold text-slate-500 hover:underline">Hapus</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="4" class="px-5 py-10 text-center text-slate-500">Belum ada kartu RFID terdaftar.</td></tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <div class="mt-5">{{ $cards->links() }}</div>
    </div>

    {{-- SIDEBAR FORM & INTEGRASI --}}
    <div class="space-y-6">
        <div class="bg-white border border-slate-200 rounded-2xl p-5 lg:p-6 shadow-sm">
            <h3 class="font-display font-bold text-slate-900 text-lg mb-1">Daftarkan Kartu Baru</h3>
            <p class="text-xs text-slate-500 mb-4">Tempelkan kartu ke reader lalu salin UID yang muncul, atau ketik manual.</p>
            <form method="POST" action="{{ route('admin.rfid.store') }}" class="space-y-3">
                @csrf
                <input type="text" name="uid" required placeholder="Contoh: A1B2C3D4" class="w-full rounded-xl bg-slate-50 border border-slate-300 px-4 py-2.5 text-slate-900 font-mono text-sm focus:outline-none focus:bg-white focus:border-lime-600">
                @error('uid')<p class="text-xs text-rose-600 font-medium">{{ $message }}</p>@enderror
                <button class="w-full rounded-xl bg-slate-900 text-white font-display font-bold text-sm py-2.5 hover:bg-slate-800 transition shadow-sm mt-2">
                    Simpan Kartu
                </button>
            </form>
        </div>

        <div class="bg-white border border-slate-200 rounded-2xl p-5 lg:p-6 shadow-sm">
            <h3 class="font-display font-bold text-slate-900 text-base mb-2">Integrasi Perangkat RFID</h3>
            <p class="text-xs text-slate-600 leading-relaxed">Alat reader (ESP32/ESP8266 + RC522) memanggil endpoint berikut setiap kartu ditempelkan:</p>
            <div class="mt-3 rounded-xl bg-slate-900 p-3.5 font-mono text-xs text-lime-400 overflow-x-auto shadow-inner">
                POST {{ url('/api/rfid/scan') }}<br>
                Header: X-Device-Key: &lt;RFID_DEVICE_KEY&gt;<br>
                Body: {"uid": "A1B2C3D4"}
            </div>
            <p class="text-xs text-slate-500 mt-3">Atur <code class="text-slate-800 font-semibold">RFID_DEVICE_KEY</code> di file <code class="text-slate-800 font-semibold">.env</code> agar hanya alat resmi yang bisa mengirim data absensi.</p>
        </div>
    </div>
</div>
@endsection
