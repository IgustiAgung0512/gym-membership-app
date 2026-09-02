@extends('layouts.admin')
@section('title', 'Kartu RFID')

@section('content')
<div class="grid lg:grid-cols-3 gap-6">
    <div class="lg:col-span-2">
        <div class="bg-base-card border border-base-line rounded-2xl overflow-hidden">
            <table class="w-full text-sm">
                <thead>
                    <tr class="text-left text-slate-400 border-b border-base-line">
                        <th class="px-5 py-3 font-medium">UID Kartu</th>
                        <th class="px-5 py-3 font-medium">Pemilik</th>
                        <th class="px-5 py-3 font-medium">Status</th>
                        <th class="px-5 py-3 font-medium text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-base-line">
                @forelse ($cards as $c)
                    <tr class="hover:bg-white/[0.02]">
                        <td class="px-5 py-3 font-mono text-slate-200">{{ $c->uid }}</td>
                        <td class="px-5 py-3 text-slate-300">{{ $c->member->user->name ?? '—' }}</td>
                        <td class="px-5 py-3">
                            @php $badge = ['assigned'=>'bg-volt/10 text-volt','unassigned'=>'bg-slate-500/10 text-slate-400','blocked'=>'bg-coral/10 text-coral'][$c->status]; @endphp
                            <span class="text-xs px-2 py-1 rounded-full {{ $badge }}">{{ ucfirst($c->status) }}</span>
                        </td>
                        <td class="px-5 py-3">
                            <div class="flex items-center justify-end gap-2">
                                <form method="POST" action="{{ route('admin.rfid.toggle-block', $c) }}">
                                    @csrf
                                    <button class="text-xs font-medium {{ $c->status=='blocked' ? 'text-volt' : 'text-coral' }} hover:underline">{{ $c->status=='blocked' ? 'Buka Blokir' : 'Blokir' }}</button>
                                </form>
                                <form method="POST" action="{{ route('admin.rfid.destroy', $c) }}" onsubmit="return confirm('Hapus kartu ini?')">
                                    @csrf @method('DELETE')
                                    <button class="text-xs font-medium text-slate-400 hover:underline">Hapus</button>
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
        <div class="mt-6">{{ $cards->links() }}</div>
    </div>

    <div class="space-y-6">
        <div class="bg-base-card border border-base-line rounded-2xl p-5">
            <h3 class="font-display font-semibold text-white mb-1">Daftarkan Kartu Baru</h3>
            <p class="text-xs text-slate-500 mb-4">Tempelkan kartu ke reader lalu salin UID yang muncul, atau ketik manual.</p>
            <form method="POST" action="{{ route('admin.rfid.store') }}" class="space-y-3">
                @csrf
                <input type="text" name="uid" required placeholder="Contoh: A1B2C3D4" class="w-full rounded-xl bg-base border border-base-line px-4 py-2.5 text-white font-mono focus:outline-none focus:ring-2 focus:ring-volt/50">
                @error('uid')<p class="text-xs text-coral">{{ $message }}</p>@enderror
                <button class="w-full rounded-xl bg-volt text-base font-semibold py-2.5 hover:brightness-95 transition">Simpan Kartu</button>
            </form>
        </div>

        <div class="bg-base-card border border-base-line rounded-2xl p-5">
            <h3 class="font-display font-semibold text-white mb-2">Integrasi Perangkat RFID</h3>
            <p class="text-xs text-slate-400 leading-relaxed">Alat reader (ESP32/ESP8266 + RC522) memanggil endpoint berikut setiap kartu discan:</p>
            <div class="mt-3 rounded-xl bg-base border border-base-line p-3 font-mono text-xs text-volt overflow-x-auto">
                POST {{ url('/api/rfid/scan') }}<br>
                Header: X-Device-Key: &lt;RFID_DEVICE_KEY&gt;<br>
                Body: {"uid": "A1B2C3D4"}
            </div>
            <p class="text-xs text-slate-500 mt-3">Atur <code class="text-slate-300">RFID_DEVICE_KEY</code> di file <code class="text-slate-300">.env</code> agar hanya alat resmi yang bisa mengirim data.</p>
        </div>
    </div>
</div>
@endsection
