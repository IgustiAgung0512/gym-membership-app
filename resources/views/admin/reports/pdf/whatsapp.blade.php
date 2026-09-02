@extends('admin.reports.pdf.layout')

@section('report-title', 'Laporan Log Notifikasi WhatsApp')
@section('report-period', $from->translatedFormat('d M Y') . ' — ' . $to->translatedFormat('d M Y'))

@section('content')
@php
    $sentCount = $logs->where('status', 'sent')->count();
    $failedCount = $logs->where('status', 'failed')->count();
@endphp
<table>
    <thead>
        <tr>
            <th style="width:12%">Tanggal</th>
            <th style="width:22%">Tujuan</th>
            <th style="width:14%">Tipe</th>
            <th style="width:42%">Pesan</th>
            <th style="width:10%">Status</th>
        </tr>
    </thead>
    <tbody>
        @forelse ($logs as $log)
            <tr>
                <td>{{ $log->created_at->format('d/m/Y H:i') }}</td>
                <td>{{ $log->member->user->name ?? '-' }}<br><span class="text-muted">{{ $log->phone }}</span></td>
                <td>{{ ucfirst(str_replace('_', ' ', $log->type)) }}</td>
                <td>{{ \Illuminate\Support\Str::limit($log->message, 80) }}</td>
                <td><span class="badge {{ $log->status == 'sent' ? 'badge-green' : 'badge-red' }}">{{ ucfirst($log->status) }}</span></td>
            </tr>
        @empty
            <tr><td colspan="5" class="empty-state">Tidak ada log notifikasi pada rentang tanggal ini.</td></tr>
        @endforelse
    </tbody>
</table>

<table style="width:100%; border:none; margin-top:14px;">
    <tr>
        <td style="border:none; padding:0; width:50%;">
            <div class="summary-box" style="border-color:#bbf7d0;">
                <div class="label">Berhasil Terkirim</div>
                <div class="value">{{ $sentCount }}</div>
            </div>
        </td>
        <td style="border:none; padding:0 0 0 12px; width:50%;">
            <div class="summary-box" style="background:#fef2f2; border-color:#fecaca;">
                <div class="label" style="color:#b91c1c;">Gagal Terkirim</div>
                <div class="value" style="color:#b91c1c;">{{ $failedCount }}</div>
            </div>
        </td>
    </tr>
</table>
@endsection
