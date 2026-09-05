@extends('admin.reports.pdf.layout')

@section('report-title', 'Laporan Penjualan Produk (Gym Store)')
@section('report-period', $from->translatedFormat('d M Y') . ' — ' . $to->translatedFormat('d M Y'))

@section('content')
<table>
    <thead>
        <tr>
            <th style="width:4%">No</th>
            <th style="width:13%">Tanggal</th>
            <th style="width:18%">No. Invoice</th>
            <th style="width:20%">Customer</th>
            <th style="width:15%">Metode Bayar</th>
            <th style="width:15%" class="text-right">Total (Rp)</th>
            <th style="width:15%" class="text-right">Laba (Rp)</th>
        </tr>
    </thead>
    <tbody>
        @forelse ($orders as $i => $order)
            <tr>
                <td>{{ $i + 1 }}</td>
                <td>{{ $order->created_at->translatedFormat('d M Y H:i') }}</td>
                <td><strong>{{ $order->invoice_number }}</strong></td>
                <td>{{ $order->customer_name }}</td>
                <td>{{ $order->payment_method_label }}</td>
                <td class="text-right">Rp{{ number_format($order->total_amount, 0, ',', '.') }}</td>
                <td class="text-right">Rp{{ number_format($order->profit, 0, ',', '.') }}</td>
            </tr>
        @empty
            <tr><td colspan="7" class="empty-state">Tidak ada transaksi penjualan produk pada rentang tanggal ini.</td></tr>
        @endforelse
    </tbody>
</table>

<div class="summary-box">
    <div class="label">Total Omzet Penjualan Produk ({{ $orders->count() }} transaksi)</div>
    <div class="value">Rp{{ number_format($totalRevenue, 0, ',', '.') }}</div>
    <div class="label" style="margin-top: 5px;">Total Laba Bersih Toko: <strong>Rp{{ number_format($totalProfit, 0, ',', '.') }}</strong></div>
</div>
@endsection
