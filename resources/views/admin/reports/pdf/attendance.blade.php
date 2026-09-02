@extends('admin.reports.pdf.layout')

@section('report-title', 'Laporan Absensi / Check-in')
@section('report-period', $from->translatedFormat('d M Y') . ' — ' . $to->translatedFormat('d M Y'))

@section('content')
<table>
    <thead>
        <tr>
            <th style="width:5%">No</th>
            <th style="width:15%">Tanggal</th>
            <th style="width:10%">Jam</th>
            <th style="width:18%">Kode Member</th>
            <th style="width:32%">Nama</th>
            <th style="width:10%">Metode</th>
            <th style="width:10%">Kartu RFID</th>
        </tr>
    </thead>
    <tbody>
        @forelse ($attendances as $i => $a)
            <tr>
                <td>{{ $i + 1 }}</td>
                <td>{{ $a->check_in_at->translatedFormat('d M Y') }}</td>
                <td>{{ $a->check_in_at->format('H:i:s') }}</td>
                <td>{{ $a->member->member_code }}</td>
                <td>{{ $a->member->user->name }}</td>
                <td>
                    <span class="badge {{ $a->method == 'rfid' ? 'badge-green' : 'badge-gray' }}">{{ $a->method == 'rfid' ? 'RFID' : 'Manual' }}</span>
                </td>
                <td>{{ $a->rfidCard->uid ?? '-' }}</td>
            </tr>
        @empty
            <tr><td colspan="7" class="empty-state">Tidak ada check-in pada rentang tanggal ini.</td></tr>
        @endforelse
    </tbody>
</table>

<div class="summary-box">
    <div class="label">Total Check-in</div>
    <div class="value">{{ $attendances->count() }} kunjungan</div>
</div>
@endsection
