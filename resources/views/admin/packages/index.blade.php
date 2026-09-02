@extends('layouts.admin')
@section('title', 'Paket Membership')

@section('content')
<div x-data="{ showModal: false, editing: null }">
    <div class="flex justify-end mb-6">
        <button @click="showModal = true; editing = null" class="flex items-center gap-2 rounded-xl bg-volt text-base text-sm font-semibold px-4 py-2.5 hover:brightness-95 transition">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/></svg>
            Tambah Paket
        </button>
    </div>

    <div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-4">
        @foreach ($packages as $p)
            <div class="bg-base-card border border-base-line rounded-2xl p-5 flex flex-col">
                <div class="flex items-start justify-between">
                    <h3 class="font-display font-semibold text-white text-lg">{{ $p->name }}</h3>
                    <span class="text-xs px-2 py-1 rounded-full {{ $p->is_active ? 'bg-volt/10 text-volt' : 'bg-slate-500/10 text-slate-400' }}">{{ $p->is_active ? 'Aktif' : 'Nonaktif' }}</span>
                </div>
                <p class="font-display text-2xl font-bold text-volt mt-3">Rp{{ number_format($p->price,0,',','.') }}</p>
                <p class="text-xs text-slate-500 mt-1">{{ $p->duration_months }} bulan &middot; {{ $p->members_count }} member</p>
                <p class="text-sm text-slate-400 mt-3 flex-1">{{ $p->description }}</p>
                <button @click="showModal = true; editing = {{ $p->toJson() }}" class="mt-4 text-sm text-slate-300 border border-base-line rounded-xl py-2 hover:bg-white/5 transition">Edit Paket</button>
            </div>
        @endforeach
    </div>

    <!-- Modal -->
    <div x-show="showModal" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/60" @keydown.escape.window="showModal=false">
        <div @click.outside="showModal=false" class="bg-base-card border border-base-line rounded-2xl w-full max-w-md p-6">
            <h3 class="font-display font-semibold text-white text-lg mb-4" x-text="editing ? 'Edit Paket' : 'Tambah Paket'"></h3>
            <form :action="editing ? `{{ url('admin/packages') }}/${editing.id}` : `{{ route('admin.packages.store') }}`" method="POST" class="space-y-3">
                @csrf
                <template x-if="editing"><input type="hidden" name="_method" value="PUT"></template>
                <div>
                    <label class="block text-sm text-slate-400 mb-1.5">Nama Paket</label>
                    <input type="text" name="name" :value="editing ? editing.name : ''" required class="w-full rounded-xl bg-base border border-base-line px-4 py-2.5 text-white focus:outline-none focus:ring-2 focus:ring-volt/50">
                </div>
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-sm text-slate-400 mb-1.5">Durasi (bulan)</label>
                        <input type="number" min="1" name="duration_months" :value="editing ? editing.duration_months : ''" required class="w-full rounded-xl bg-base border border-base-line px-4 py-2.5 text-white focus:outline-none focus:ring-2 focus:ring-volt/50">
                    </div>
                    <div>
                        <label class="block text-sm text-slate-400 mb-1.5">Harga (Rp)</label>
                        <input type="number" min="0" name="price" :value="editing ? editing.price : ''" required class="w-full rounded-xl bg-base border border-base-line px-4 py-2.5 text-white focus:outline-none focus:ring-2 focus:ring-volt/50">
                    </div>
                </div>
                <div>
                    <label class="block text-sm text-slate-400 mb-1.5">Deskripsi</label>
                    <textarea name="description" rows="2" class="w-full rounded-xl bg-base border border-base-line px-4 py-2.5 text-white focus:outline-none focus:ring-2 focus:ring-volt/50" x-text="editing ? editing.description : ''"></textarea>
                </div>
                <template x-if="editing">
                    <label class="flex items-center gap-2 text-sm text-slate-400">
                        <input type="checkbox" name="is_active" value="1" :checked="editing && editing.is_active" class="rounded border-base-line bg-base text-volt"> Paket aktif (ditampilkan saat pendaftaran)
                    </label>
                </template>
                <div class="flex gap-3 pt-2">
                    <button type="submit" class="flex-1 rounded-xl bg-volt text-base font-semibold py-2.5 hover:brightness-95 transition">Simpan</button>
                    <button type="button" @click="showModal=false" class="rounded-xl border border-base-line px-5 py-2.5 text-slate-300 hover:bg-white/5 transition">Batal</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
