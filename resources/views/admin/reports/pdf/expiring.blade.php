@extends('admin.reports.pdf.layout')

@section('report-title', 'Laporan Member Segera Berakhir')
@section('report-period', $days . ' hari ke depan (dari ' . $generatedAt->translatedFormat('d M Y') . ')')

@section('content')
<table>
    <thead>
        <tr>
            <th style="width:5%">No</th>
            <th style="width:16%">Kode Member</th>
            <th style="width:24%">Nama</th>
            <th style="width:16%">No. WhatsApp</th>
            <th style="width:18%">Paket</th>
            <th style="width:12%">Berakhir</th>
            <th style="width:9%">Sisa Hari</th>
        </tr>
    </thead>
    <tbody>
        @forelse ($members as $i => $m)
            <tr>
                <td>{{ $i + 1 }}</td>
                <td>{{ $m->member_code }}</td>
                <td>{{ $m->user->name }}</td>
                <td>{{ $m->user->phone }}</td>
                <td>{{ $m->package->name ?? '-' }}</td>
                <td>{{ $m->expire_date->format('d/m/Y') }}</td>
                <td><span class="badge badge-red">{{ max($m->daysRemaining(), 0) }} hari</span></td>
            </tr>
        @empty
            <tr><td colspan="7" class="empty-state">Tidak ada member yang akan berakhir dalam periode ini.</td></tr>
        @endforelse
    </tbody>
</table>

<div class="summary-box">
    <div class="label">Perlu Ditindaklanjuti</div>
    <div class="value">{{ $members->count() }} member</div>
</div>
@endsection
