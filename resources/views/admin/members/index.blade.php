@extends('layouts.admin')
@section('title', 'Member')

@section('content')
<div class="flex flex-col sm:flex-row sm:items-center gap-3 mb-6">
    <form method="GET" class="flex-1 flex gap-3">
        <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari nama, telepon, atau kode member..."
               class="flex-1 rounded-xl bg-white border border-slate-300 px-4 py-2.5 text-sm text-slate-900 placeholder:text-slate-400 focus:outline-none focus:ring-2 focus:ring-lime-500 shadow-sm">
        <select name="status" onchange="this.form.submit()" class="rounded-xl bg-white border border-slate-300 px-3 py-2.5 text-sm text-slate-900 focus:outline-none focus:ring-2 focus:ring-lime-500 shadow-sm">
            <option value="">Semua Status</option>
            <option value="active" @selected(request('status')=='active')>Aktif</option>
            <option value="inactive" @selected(request('status')=='inactive')>Nonaktif</option>
            <option value="expired" @selected(request('status')=='expired')>Expired</option>
        </select>
    </form>
    <a href="{{ route('admin.members.create') }}" class="flex items-center justify-center gap-2 rounded-xl bg-slate-900 text-white text-sm font-bold px-4 py-2.5 hover:bg-slate-800 transition whitespace-nowrap shadow-sm">
        <svg class="w-4 h-4 text-lime-400" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/></svg>
        <span>Member Baru</span>
    </a>
</div>

<!-- Mobile cards -->
<div class="grid gap-3 lg:hidden">
    @forelse ($members as $m)
        <div class="bg-white border border-slate-200 rounded-2xl p-4 shadow-sm">
            <div class="flex items-start justify-between gap-2">
                <div class="flex items-center gap-3 min-w-0">
                    @if ($m->photo)
                        <img src="{{ route('admin.members.photo', $m) }}" alt="{{ $m->user->name }}" class="w-10 h-10 rounded-full object-cover shrink-0" onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';">
                        <div class="w-10 h-10 rounded-full bg-lime-100 text-lime-800 hidden items-center justify-center font-bold shrink-0">{{ substr($m->user->name,0,1) }}</div>
                    @else
                        <div class="w-10 h-10 rounded-full bg-lime-100 text-lime-800 flex items-center justify-center font-bold shrink-0">{{ substr($m->user->name,0,1) }}</div>
                    @endif
                    <div class="min-w-0">
                        <p class="text-slate-900 font-semibold truncate">{{ $m->user->name }}</p>
                        <p class="text-xs text-slate-500 font-mono">{{ $m->member_code }} &middot; {{ $m->user->phone }}</p>
                    </div>
                </div>
                @php $badge = ['active'=>'bg-lime-100 text-lime-800 font-semibold','inactive'=>'bg-slate-100 text-slate-600 font-semibold','expired'=>'bg-rose-100 text-rose-800 font-semibold'][$m->status]; @endphp
                <span class="text-xs px-2.5 py-0.5 rounded-full {{ $badge }} shrink-0">{{ ucfirst($m->status) }}</span>
            </div>
            <div class="mt-3 grid grid-cols-2 gap-2 text-xs text-slate-500 border-t border-slate-100 pt-2">
                <p>Paket: <strong class="text-slate-800">{{ $m->package->name ?? '-' }}</strong></p>
                <p>RFID: <strong class="text-slate-800 font-mono">{{ $m->rfidCard->uid ?? 'Belum ada' }}</strong></p>
                <p>Berakhir: <strong class="text-slate-800">{{ optional($m->expire_date)->format('d M Y') ?? '-' }}</strong></p>
            </div>
            <div class="mt-3 flex gap-2">
                <a href="{{ route('admin.members.edit', $m) }}" class="flex-1 text-center text-xs font-semibold rounded-lg border border-slate-200 py-2 text-slate-700 hover:bg-slate-50">Edit</a>
                <form method="POST" action="{{ route('admin.members.renew', $m) }}" class="flex-1"><button class="w-full text-xs font-semibold rounded-lg bg-lime-100 text-lime-800 py-2 hover:bg-lime-200">Perpanjang</button>@csrf</form>
            </div>
        </div>
    @empty
        <p class="text-sm text-slate-500 text-center py-10">Belum ada member.</p>
    @endforelse
</div>

<!-- Desktop table -->
<div class="hidden lg:block bg-white border border-slate-200 rounded-2xl overflow-hidden shadow-sm">
    <table class="w-full text-sm">
        <thead>
            <tr class="text-left bg-slate-50 text-slate-600 border-b border-slate-200">
                <th class="px-5 py-3.5 font-semibold text-xs uppercase tracking-wider">Member</th>
                <th class="px-5 py-3.5 font-semibold text-xs uppercase tracking-wider">Paket</th>
                <th class="px-5 py-3.5 font-semibold text-xs uppercase tracking-wider">RFID UID</th>
                <th class="px-5 py-3.5 font-semibold text-xs uppercase tracking-wider">Berakhir</th>
                <th class="px-5 py-3.5 font-semibold text-xs uppercase tracking-wider">Status</th>
                <th class="px-5 py-3.5 font-semibold text-xs uppercase tracking-wider text-right">Aksi</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-slate-100">
        @forelse ($members as $m)
            <tr class="hover:bg-slate-50/80 transition">
                <td class="px-5 py-3.5">
                    <div class="flex items-center gap-3">
                        @if ($m->photo)
                            <img src="{{ route('admin.members.photo', $m) }}" alt="{{ $m->user->name }}" class="w-9 h-9 rounded-full object-cover shrink-0" onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';">
                            <div class="w-9 h-9 rounded-full bg-lime-100 text-lime-800 hidden items-center justify-center font-bold text-sm shrink-0">{{ substr($m->user->name,0,1) }}</div>
                        @else
                            <div class="w-9 h-9 rounded-full bg-lime-100 text-lime-800 flex items-center justify-center font-bold text-sm shrink-0">{{ substr($m->user->name,0,1) }}</div>
                        @endif
                        <div>
                            <p class="text-slate-900 font-semibold">{{ $m->user->name }}</p>
                            <p class="text-xs text-slate-500 font-mono">{{ $m->member_code }} &middot; {{ $m->user->phone }}</p>
                        </div>
                    </div>
                </td>
                <td class="px-5 py-3.5 text-slate-700 font-medium">{{ $m->package->name ?? '-' }}</td>
                <td class="px-5 py-3.5 text-slate-700 font-mono text-xs">{{ $m->rfidCard->uid ?? '—' }}</td>
                <td class="px-5 py-3.5 text-slate-700">{{ optional($m->expire_date)->format('d M Y') ?? '-' }}</td>
                <td class="px-5 py-3.5">
                    @php $badge = ['active'=>'bg-lime-100 text-lime-800 font-semibold','inactive'=>'bg-slate-100 text-slate-600 font-semibold','expired'=>'bg-rose-100 text-rose-800 font-semibold'][$m->status]; @endphp
                    <span class="text-xs px-2.5 py-0.5 rounded-full {{ $badge }}">{{ ucfirst($m->status) }}</span>
                </td>
                <td class="px-5 py-3.5">
                    <div class="flex items-center justify-end gap-3 text-xs">
                        <form method="POST" action="{{ route('admin.members.renew', $m) }}"><button class="font-semibold text-lime-700 hover:underline">Perpanjang</button>@csrf</form>
                        <a href="{{ route('admin.members.edit', $m) }}" class="font-semibold text-slate-700 hover:underline">Edit</a>
                        <form method="POST" action="{{ route('admin.members.destroy', $m) }}" onsubmit="return confirm('Hapus member ini?')">
                            @csrf @method('DELETE')
                            <button class="font-semibold text-rose-600 hover:underline">Hapus</button>
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
