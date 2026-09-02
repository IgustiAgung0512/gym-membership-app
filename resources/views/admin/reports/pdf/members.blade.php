@extends('admin.reports.pdf.layout')

@section('report-title', 'Laporan Data Member' . ($status ? ' — Status: ' . ucfirst($status) : ''))
@section('report-period', 'Total: ' . $members->count() . ' member')

@section('content')
<table>
    <thead>
        <tr>
            <th style="width:5%">No</th>
            <th style="width:16%">Kode Member</th>
            <th style="width:20%">Nama</th>
            <th style="width:14%">No. WhatsApp</th>
            <th style="width:16%">Paket</th>
            <th style="width:12%">RFID UID</th>
            <th style="width:10%">Berakhir</th>
            <th style="width:7%">Status</th>
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
                <td>{{ $m->rfidCard->uid ?? '-' }}</td>
                <td>{{ optional($m->expire_date)->format('d/m/Y') ?? '-' }}</td>
                <td>
                    @php $badge = ['active'=>'badge-green','inactive'=>'badge-gray','expired'=>'badge-red'][$m->status]; @endphp
                    <span class="badge {{ $badge }}">{{ ucfirst($m->status) }}</span>
                </td>
            </tr>
        @empty
            <tr><td colspan="8" class="empty-state">Tidak ada data member untuk filter ini.</td></tr>
        @endforelse
    </tbody>
</table>
@endsection
