@extends('admin.reports.pdf.layout')

@section('report-title', 'Laporan Pendapatan')
@section('report-period', $from->translatedFormat('d M Y') . ' — ' . $to->translatedFormat('d M Y'))

@section('content')
<table>
    <thead>
        <tr>
            <th style="width:5%">No</th>
            <th style="width:14%">Tanggal</th>
            <th style="width:18%">Kode Member</th>
            <th style="width:26%">Nama Member</th>
            <th style="width:20%">Paket</th>
            <th style="width:17%" class="text-right">Jumlah</th>
        </tr>
    </thead>
    <tbody>
        @forelse ($payments as $i => $p)
            <tr>
                <td>{{ $i + 1 }}</td>
                <td>{{ $p->payment_date->translatedFormat('d M Y') }}</td>
                <td>{{ $p->member->member_code }}</td>
                <td>{{ $p->member->user->name }}</td>
                <td>{{ $p->package->name ?? '-' }}</td>
                <td class="text-right">Rp{{ number_format($p->amount, 0, ',', '.') }}</td>
            </tr>
        @empty
            <tr><td colspan="6" class="empty-state">Tidak ada transaksi pada rentang tanggal ini.</td></tr>
        @endforelse
    </tbody>
</table>

<div class="summary-box">
    <div class="label">Total Pendapatan ({{ $payments->count() }} transaksi)</div>
    <div class="value">Rp{{ number_format($total, 0, ',', '.') }}</div>
</div>
@endsection
