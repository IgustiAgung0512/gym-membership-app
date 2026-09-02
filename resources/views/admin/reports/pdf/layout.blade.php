@php
    // dompdf paling stabil dengan font DejaVu Sans (bundled default) untuk dukungan karakter Indonesia.
@endphp
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <style>
        @page {
            margin: 90px 30px 60px 30px;
        }
        body {
            font-family: 'DejaVu Sans', sans-serif;
            font-size: 11px;
            color: #1e293b;
        }
        header {
            position: fixed;
            top: -70px;
            left: 0;
            right: 0;
            height: 60px;
            border-bottom: 2px solid #22c55e;
            padding-bottom: 8px;
        }
        header .brand {
            font-size: 18px;
            font-weight: bold;
            color: #14532d;
        }
        header .brand span { color: #16a34a; }
        header .report-title {
            font-size: 13px;
            font-weight: bold;
            color: #1e293b;
            margin-top: 2px;
        }
        header .meta {
            font-size: 9px;
            color: #64748b;
            text-align: right;
        }
        footer {
            position: fixed;
            bottom: -50px;
            left: 0;
            right: 0;
            height: 40px;
            border-top: 1px solid #e2e8f0;
            padding-top: 6px;
            font-size: 9px;
            color: #94a3b8;
            text-align: center;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 8px;
        }
        thead th {
            background: #f0fdf4;
            color: #14532d;
            font-size: 9.5px;
            text-transform: uppercase;
            letter-spacing: 0.3px;
            text-align: left;
            padding: 7px 8px;
            border-bottom: 2px solid #22c55e;
        }
        tbody td {
            padding: 6px 8px;
            border-bottom: 1px solid #e2e8f0;
            font-size: 10px;
            vertical-align: top;
        }
        tbody tr:nth-child(even) { background: #f8fafc; }
        .badge {
            display: inline-block;
            padding: 2px 8px;
            border-radius: 20px;
            font-size: 8.5px;
            font-weight: bold;
        }
        .badge-green { background: #dcfce7; color: #15803d; }
        .badge-gray { background: #f1f5f9; color: #64748b; }
        .badge-red { background: #fee2e2; color: #b91c1c; }
        .summary-box {
            margin-top: 14px;
            padding: 12px 16px;
            background: #f0fdf4;
            border: 1px solid #bbf7d0;
            border-radius: 8px;
        }
        .summary-box .label { font-size: 9px; color: #64748b; text-transform: uppercase; }
        .summary-box .value { font-size: 18px; font-weight: bold; color: #14532d; margin-top: 2px; }
        .text-right { text-align: right; }
        .text-muted { color: #94a3b8; font-size: 9px; }
        .empty-state { text-align: center; padding: 30px 0; color: #94a3b8; }
    </style>
</head>
<body>
    <header>
        <table style="width:100%; border:none;">
            <tr>
                <td style="border:none; padding:0;">
                    <div class="brand">Gym<span>Pulse</span></div>
                    <div class="report-title">@yield('report-title')</div>
                </td>
                <td style="border:none; padding:0;" class="meta">
                    Dibuat: {{ $generatedAt->translatedFormat('d F Y, H:i') }} WIB<br>
                    @yield('report-period', '')
                </td>
            </tr>
        </table>
    </header>

    <footer>
        GymPulse — Sistem Manajemen Membership Gym &middot; Dokumen ini dibuat otomatis oleh sistem
    </footer>

    @yield('content')
</body>
</html>
