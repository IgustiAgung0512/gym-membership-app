@extends('layouts.admin')
@section('title', 'Paket Membership')

@section('content')
<div x-data="{ showModal: false, editing: null }">
    <div class="flex justify-end mb-6">
        <button @click="showModal = true; editing = null" class="flex items-center gap-2 rounded-xl bg-slate-900 text-white text-sm font-bold px-4 py-2.5 hover:bg-slate-800 transition shadow-sm">
            <svg class="w-4 h-4 text-lime-400" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/></svg>
            <span>Tambah Paket</span>
        </button>
    </div>

    <div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-5">
        @foreach ($packages as $p)
            <div class="bg-white border border-slate-200 rounded-2xl p-6 flex flex-col justify-between shadow-sm">
                <div>
                    <div class="flex items-start justify-between gap-2">
                        <h3 class="font-display font-bold text-slate-900 text-xl">{{ $p->name }}</h3>
                        <span class="text-xs px-2.5 py-0.5 rounded-full font-semibold {{ $p->is_active ? 'bg-lime-100 text-lime-800' : 'bg-slate-100 text-slate-600' }}">
                            {{ $p->is_active ? 'Aktif' : 'Nonaktif' }}
                        </span>
                    </div>
                    <p class="font-display text-2xl font-extrabold text-lime-700 mt-3">
                        Rp{{ number_format($p->price,0,',','.') }}
                    </p>
                    <p class="text-xs text-slate-500 mt-1 font-medium">{{ $p->duration_months }} bulan &middot; {{ $p->members_count }} member aktif</p>
                    <p class="text-xs text-slate-600 mt-3 leading-relaxed">{{ $p->description }}</p>
                </div>
                <div class="pt-5 border-t border-slate-100 mt-4">
                    <button @click="showModal = true; editing = {{ $p->toJson() }}" class="w-full text-xs font-semibold text-slate-700 border border-slate-300 rounded-xl py-2.5 hover:bg-slate-50 transition">
                        Edit Paket
                    </button>
                </div>
            </div>
        @endforeach
    </div>

    <!-- Modal -->
    <div x-show="showModal" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-sm" @keydown.escape.window="showModal=false">
        <div @click.outside="showModal=false" class="bg-white border border-slate-200 rounded-2xl w-full max-w-md p-6 shadow-2xl">
            <h3 class="font-display font-bold text-slate-900 text-lg mb-4" x-text="editing ? 'Edit Paket' : 'Tambah Paket'"></h3>
            <form :action="editing ? `{{ url('admin/packages') }}/${editing.id}` : `{{ route('admin.packages.store') }}`" method="POST" class="space-y-3.5">
                @csrf
                <template x-if="editing"><input type="hidden" name="_method" value="PUT"></template>
                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1.5">Nama Paket</label>
                    <input type="text" name="name" :value="editing ? editing.name : ''" required class="w-full rounded-xl bg-slate-50 border border-slate-300 px-4 py-2.5 text-slate-900 text-sm focus:outline-none focus:bg-white focus:border-lime-600">
                </div>
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1.5">Durasi (bulan)</label>
                        <input type="number" min="1" name="duration_months" :value="editing ? editing.duration_months : ''" required class="w-full rounded-xl bg-slate-50 border border-slate-300 px-4 py-2.5 text-slate-900 text-sm focus:outline-none focus:bg-white focus:border-lime-600">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1.5">Harga (Rp)</label>
                        <input type="number" min="0" name="price" :value="editing ? editing.price : ''" required class="w-full rounded-xl bg-slate-50 border border-slate-300 px-4 py-2.5 text-slate-900 text-sm focus:outline-none focus:bg-white focus:border-lime-600">
                    </div>
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1.5">Deskripsi</label>
                    <textarea name="description" rows="2" class="w-full rounded-xl bg-slate-50 border border-slate-300 px-4 py-2.5 text-slate-900 text-sm focus:outline-none focus:bg-white focus:border-lime-600" x-text="editing ? editing.description : ''"></textarea>
                </div>
                <template x-if="editing">
                    <label class="flex items-center gap-2 text-xs font-semibold text-slate-700">
                        <input type="checkbox" name="is_active" value="1" :checked="editing && editing.is_active" class="rounded border-slate-300 text-lime-600"> Paket aktif (ditampilkan saat pendaftaran)
                    </label>
                </template>
                <div class="flex gap-3 pt-3">
                    <button type="submit" class="flex-1 rounded-xl bg-slate-900 text-white text-sm font-bold py-2.5 hover:bg-slate-800 transition">Simpan</button>
                    <button type="button" @click="showModal=false" class="rounded-xl border border-slate-300 px-5 py-2.5 text-slate-700 text-sm font-medium hover:bg-slate-50 transition">Batal</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
