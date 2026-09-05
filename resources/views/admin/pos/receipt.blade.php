<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Struk #{{ $order->invoice_number }} - GymPulse</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Courier New', Courier, monospace, sans-serif;
        }
        body {
            width: 100%;
            max-width: 320px;
            margin: 0 auto;
            padding: 15px 10px;
            background: #ffffff;
            color: #000000;
            font-size: 12px;
        }
        .text-center { text-align: center; }
        .text-right { text-align: right; }
        .font-bold { font-weight: bold; }
        .divider {
            border-top: 1px dashed #000;
            margin: 8px 0;
        }
        .header {
            margin-bottom: 10px;
        }
        .header h1 {
            font-size: 16px;
            letter-spacing: 1px;
        }
        .header p {
            font-size: 10px;
            margin-top: 2px;
        }
        .info-table {
            width: 100%;
            font-size: 11px;
            margin-bottom: 6px;
        }
        .info-table td {
            padding: 1px 0;
        }
        .items-table {
            width: 100%;
            font-size: 11px;
        }
        .items-table th {
            text-align: left;
            padding-bottom: 4px;
            border-bottom: 1px dashed #000;
        }
        .items-table td {
            padding: 4px 0;
            vertical-align: top;
        }
        .totals-table {
            width: 100%;
            font-size: 11px;
            margin-top: 4px;
        }
        .totals-table td {
            padding: 2px 0;
        }
        .footer {
            margin-top: 12px;
            font-size: 10px;
            text-align: center;
        }
        .no-print {
            margin-bottom: 15px;
            display: flex;
            gap: 8px;
        }
        .no-print button {
            flex: 1;
            padding: 8px;
            background: #0f172a;
            color: #ffffff;
            border: none;
            border-radius: 6px;
            font-weight: bold;
            cursor: pointer;
            font-size: 11px;
        }
        @media print {
            .no-print {
                display: none !important;
            }
            @page {
                margin: 0;
                size: auto;
            }
            body {
                padding: 10px 5px;
            }
        }
    </style>
</head>
<body onload="window.print()">

    <div class="no-print">
        <button onclick="window.print()">🖨️ Cetak Ulang</button>
        <button onclick="window.close()" style="background: #64748b;">✕ Tutup</button>
    </div>

    {{-- Header --}}
    <div class="header text-center">
        <h1>GYMPULSE STORE</h1>
        <p>Smart RFID Fitness Center</p>
        <p>Jl. Kebugaran No. 88, Jakarta</p>
        <p>Telp / WA: 0812-3456-7890</p>
    </div>

    <div class="divider"></div>

    {{-- Order Info --}}
    <table class="info-table">
        <tr>
            <td>No. Nota</td>
            <td>: {{ $order->invoice_number }}</td>
        </tr>
        <tr>
            <td>Waktu</td>
            <td>: {{ $order->created_at->format('d/m/Y H:i') }}</td>
        </tr>
        <tr>
            <td>Kasir</td>
            <td>: {{ $order->cashier->name ?? 'Admin' }}</td>
        </tr>
        <tr>
            <td>Customer</td>
            <td>: {{ $order->customer_name }}</td>
        </tr>
    </table>

    <div class="divider"></div>

    {{-- Items List --}}
    <table class="items-table">
        <thead>
            <tr>
                <th style="width: 50%;">Item</th>
                <th style="width: 15%; text-align: center;">Qty</th>
                <th style="width: 35%; text-align: right;">Total</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($order->items as $item)
                <tr>
                    <td>
                        {{ $item->product_name }}<br>
                        <small style="font-size: 9px; color: #555;">@ Rp{{ number_format($item->unit_price, 0, ',', '.') }}</small>
                    </td>
                    <td style="text-align: center;">{{ $item->quantity }}</td>
                    <td class="text-right">Rp{{ number_format($item->subtotal, 0, ',', '.') }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <div class="divider"></div>

    {{-- Totals --}}
    <table class="totals-table">
        <tr>
            <td>Subtotal</td>
            <td class="text-right">Rp{{ number_format($order->subtotal, 0, ',', '.') }}</td>
        </tr>
        @if ($order->discount > 0)
            <tr>
                <td>Diskon</td>
                <td class="text-right">-Rp{{ number_format($order->discount, 0, ',', '.') }}</td>
            </tr>
        @endif
        <tr class="font-bold" style="font-size: 13px;">
            <td>TOTAL</td>
            <td class="text-right">Rp{{ number_format($order->total_amount, 0, ',', '.') }}</td>
        </tr>
        <tr>
            <td>Metode Bayar</td>
            <td class="text-right uppercase">{{ $order->payment_method_label }}</td>
        </tr>
        @if ($order->payment_method === 'cash')
            <tr>
                <td>Tunai Diterima</td>
                <td class="text-right">Rp{{ number_format($order->cash_received, 0, ',', '.') }}</td>
            </tr>
            <tr class="font-bold">
                <td>Kembalian</td>
                <td class="text-right">Rp{{ number_format($order->cash_change, 0, ',', '.') }}</td>
            </tr>
        @endif
    </table>

    <div class="divider"></div>

    {{-- Footer --}}
    <div class="footer">
        <p>Terima kasih atas kunjungan Anda!</p>
        <p>Keep Hustle & Stay Healthy 💪</p>
        <p style="margin-top: 6px; font-size: 9px;">Powered by GymPulse App</p>
    </div>

</body>
</html>
