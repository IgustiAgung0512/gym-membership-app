@extends('layouts.admin')
@section('title', 'Log Notifikasi WhatsApp')

@section('content')
<div class="bg-base-card border border-base-line rounded-2xl overflow-hidden">
    <table class="w-full text-sm">
        <thead>
            <tr class="text-left text-slate-400 border-b border-base-line">
                <th class="px-5 py-3 font-medium">Waktu</th>
                <th class="px-5 py-3 font-medium">Tujuan</th>
                <th class="px-5 py-3 font-medium">Tipe</th>
                <th class="px-5 py-3 font-medium">Pesan</th>
                <th class="px-5 py-3 font-medium">Status</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-base-line">
        @forelse ($logs as $log)
            <tr class="hover:bg-white/[0.02] align-top">
                <td class="px-5 py-3 text-slate-400 whitespace-nowrap">{{ $log->created_at->format('d M H:i') }}</td>
                <td class="px-5 py-3">
                    <p class="text-white">{{ $log->member->user->name ?? '-' }}</p>
                    <p class="text-xs text-slate-500 font-mono">{{ $log->phone }}</p>
                </td>
                <td class="px-5 py-3 text-slate-300">{{ str($log->type)->headline() }}</td>
                <td class="px-5 py-3 text-slate-400 max-w-sm truncate">{{ str($log->message)->limit(60) }}</td>
                <td class="px-5 py-3">
                    <span class="text-xs px-2 py-1 rounded-full {{ $log->status=='sent' ? 'bg-volt/10 text-volt' : 'bg-coral/10 text-coral' }}">{{ ucfirst($log->status) }}</span>
                </td>
            </tr>
        @empty
            <tr><td colspan="5" class="px-5 py-10 text-center text-slate-500">Belum ada notifikasi terkirim.</td></tr>
        @endforelse
        </tbody>
    </table>
</div>
<div class="mt-6">{{ $logs->links() }}</div>
@endsection
