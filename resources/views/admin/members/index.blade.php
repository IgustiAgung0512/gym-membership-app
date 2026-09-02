@extends('layouts.admin')
@section('title', 'Member')

@section('content')
<div class="flex flex-col sm:flex-row sm:items-center gap-3 mb-6">
    <form method="GET" class="flex-1 flex gap-3">
        <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari nama, telepon, atau kode member..."
               class="flex-1 rounded-xl bg-base-card border border-base-line px-4 py-2.5 text-sm text-white placeholder:text-slate-500 focus:outline-none focus:ring-2 focus:ring-volt/50">
        <select name="status" onchange="this.form.submit()" class="rounded-xl bg-base-card border border-base-line px-3 py-2.5 text-sm text-white focus:outline-none">
            <option value="">Semua Status</option>
            <option value="active" @selected(request('status')=='active')>Aktif</option>
            <option value="inactive" @selected(request('status')=='inactive')>Nonaktif</option>
            <option value="expired" @selected(request('status')=='expired')>Expired</option>
        </select>
    </form>
    <a href="{{ route('admin.members.create') }}" class="flex items-center justify-center gap-2 rounded-xl bg-volt text-base text-sm font-semibold px-4 py-2.5 hover:brightness-95 transition whitespace-nowrap">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/></svg>
        Member Baru
    </a>
</div>

<!-- Mobile cards -->
<div class="grid gap-3 lg:hidden">
    @forelse ($members as $m)
        <div class="bg-base-card border border-base-line rounded-2xl p-4">
            <div class="flex items-start justify-between gap-2">
                <div class="flex items-center gap-3 min-w-0">
                    <div class="w-10 h-10 rounded-full bg-volt/10 text-volt flex items-center justify-center font-semibold shrink-0">{{ substr($m->user->name,0,1) }}</div>
                    <div class="min-w-0">
                        <p class="text-white font-medium truncate">{{ $m->user->name }}</p>
                        <p class="text-xs text-slate-500">{{ $m->member_code }} &middot; {{ $m->user->phone }}</p>
                    </div>
                </div>
                @php $badge = ['active'=>'bg-volt/10 text-volt','inactive'=>'bg-slate-500/10 text-slate-400','expired'=>'bg-coral/10 text-coral'][$m->status]; @endphp
                <span class="text-xs px-2 py-1 rounded-full {{ $badge }} shrink-0">{{ ucfirst($m->status) }}</span>
            </div>
            <div class="mt-3 grid grid-cols-2 gap-2 text-xs text-slate-400">
                <p>Paket: <span class="text-slate-200">{{ $m->package->name ?? '-' }}</span></p>
                <p>RFID: <span class="text-slate-200">{{ $m->rfidCard->uid ?? 'Belum ada' }}</span></p>
                <p>Berakhir: <span class="text-slate-200">{{ optional($m->expire_date)->format('d M Y') ?? '-' }}</span></p>
            </div>
            <div class="mt-3 flex gap-2">
                <a href="{{ route('admin.members.edit', $m) }}" class="flex-1 text-center text-xs font-medium rounded-lg border border-base-line py-2 text-slate-300">Edit</a>
                <form method="POST" action="{{ route('admin.members.renew', $m) }}" class="flex-1"><button class="w-full text-xs font-medium rounded-lg bg-volt/10 text-volt py-2">Perpanjang</button>@csrf</form>
            </div>
        </div>
    @empty
        <p class="text-sm text-slate-500 text-center py-10">Belum ada member.</p>
    @endforelse
</div>

<!-- Desktop table -->
<div class="hidden lg:block bg-base-card border border-base-line rounded-2xl overflow-hidden">
    <table class="w-full text-sm">
        <thead>
            <tr class="text-left text-slate-400 border-b border-base-line">
                <th class="px-5 py-3 font-medium">Member</th>
                <th class="px-5 py-3 font-medium">Paket</th>
                <th class="px-5 py-3 font-medium">RFID</th>
                <th class="px-5 py-3 font-medium">Berakhir</th>
                <th class="px-5 py-3 font-medium">Status</th>
                <th class="px-5 py-3 font-medium text-right">Aksi</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-base-line">
        @forelse ($members as $m)
            <tr class="hover:bg-white/[0.02]">
                <td class="px-5 py-3">
                    <div class="flex items-center gap-3">
                        <div class="w-9 h-9 rounded-full bg-volt/10 text-volt flex items-center justify-center font-semibold">{{ substr($m->user->name,0,1) }}</div>
                        <div>
                            <p class="text-white font-medium">{{ $m->user->name }}</p>
                            <p class="text-xs text-slate-500">{{ $m->member_code }} &middot; {{ $m->user->phone }}</p>
                        </div>
                    </div>
                </td>
                <td class="px-5 py-3 text-slate-300">{{ $m->package->name ?? '-' }}</td>
                <td class="px-5 py-3 text-slate-300">{{ $m->rfidCard->uid ?? '—' }}</td>
                <td class="px-5 py-3 text-slate-300">{{ optional($m->expire_date)->format('d M Y') ?? '-' }}</td>
                <td class="px-5 py-3">
                    @php $badge = ['active'=>'bg-volt/10 text-volt','inactive'=>'bg-slate-500/10 text-slate-400','expired'=>'bg-coral/10 text-coral'][$m->status]; @endphp
                    <span class="text-xs px-2 py-1 rounded-full {{ $badge }}">{{ ucfirst($m->status) }}</span>
                </td>
                <td class="px-5 py-3">
                    <div class="flex items-center justify-end gap-2">
                        <form method="POST" action="{{ route('admin.members.renew', $m) }}"><button class="text-xs font-medium text-volt hover:underline">Perpanjang</button>@csrf</form>
                        <a href="{{ route('admin.members.edit', $m) }}" class="text-xs font-medium text-slate-300 hover:underline">Edit</a>
                        <form method="POST" action="{{ route('admin.members.destroy', $m) }}" onsubmit="return confirm('Hapus member ini?')">
                            @csrf @method('DELETE')
                            <button class="text-xs font-medium text-coral hover:underline">Hapus</button>
                        </form>
                    </div>
                </td>
            </tr>
        @empty
            <tr><td colspan="6" class="px-5 py-10 text-center text-slate-500">Belum ada member.</td></tr>
        @endforelse
        </tbody>
    </table>
</div>

<div class="mt-6">{{ $members->links() }}</div>
@endsection
