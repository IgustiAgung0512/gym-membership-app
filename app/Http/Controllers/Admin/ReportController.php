<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Attendance;
use App\Models\Member;
use App\Models\Payment;
use App\Models\WhatsappLog;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;

class ReportController extends Controller
{
    /**
     * Halaman hub laporan — admin memilih jenis laporan & rentang tanggal di sini,
     * lalu diarahkan ke masing-masing route *.pdf untuk mengunduh filenya.
     */
    public function index()
    {
        $totalMembers = Member::count();
        $activeMembers = Member::where('status', 'active')->count();
        $revenueThisMonth = Payment::whereMonth('payment_date', now()->month)
            ->whereYear('payment_date', now()->year)
            ->where('status', 'paid')
            ->sum('amount');
        $expiringSoonCount = Member::where('status', 'active')
            ->whereNotNull('expire_date')
            ->whereBetween('expire_date', [now()->startOfDay(), now()->addDays(7)->endOfDay()])
            ->count();

        // Tren pendapatan 6 bulan terakhir, untuk grafik batang.
        $revenueLast6Months = collect(range(5, 0))->map(function ($monthsAgo) {
            $date = now()->subMonths($monthsAgo);

            $total = Payment::whereMonth('payment_date', $date->month)
                ->whereYear('payment_date', $date->year)
                ->where('status', 'paid')
                ->sum('amount');

            return [
                'label' => $date->translatedFormat('M Y'),
                'total' => (float) $total,
            ];
        });

        // Distribusi status member, untuk grafik donat.
        $memberStatusCounts = [
            'active' => Member::where('status', 'active')->count(),
            'inactive' => Member::where('status', 'inactive')->count(),
            'expired' => Member::where('status', 'expired')->count(),
        ];

        return view('admin.reports.index', [
            'defaultFrom' => now()->startOfMonth()->format('Y-m-d'),
            'defaultTo' => now()->format('Y-m-d'),
            'totalMembers' => $totalMembers,
            'activeMembers' => $activeMembers,
            'revenueThisMonth' => $revenueThisMonth,
            'expiringSoonCount' => $expiringSoonCount,
            'revenueLast6Months' => $revenueLast6Months,
            'memberStatusCounts' => $memberStatusCounts,
        ]);
    }

    /**
     * Laporan Data Member — daftar seluruh member beserta paket, status, dan kartu RFID.
     * Bisa difilter berdasarkan status (aktif/nonaktif/expired).
     */
    public function membersPdf(Request $request)
    {
        $status = $request->query('status');

        $members = Member::with('user', 'package', 'rfidCard')
            ->when($status, fn ($q) => $q->where('status', $status))
            ->orderBy('member_code')
            ->get();

        $pdf = Pdf::loadView('admin.reports.pdf.members', [
            'members' => $members,
            'status' => $status,
            'generatedAt' => now(),
        ])->setPaper('a4', 'portrait');

        return $pdf->download('laporan-member-' . now()->format('Ymd-His') . '.pdf');
    }

    /**
     * Laporan Absensi / Check-in — rekap kehadiran member dalam rentang tanggal tertentu.
     */
    public function attendancePdf(Request $request)
    {
        [$from, $to] = $this->resolveDateRange($request);

        $attendances = Attendance::with('member.user', 'rfidCard')
            ->whereBetween('check_in_at', [$from->copy()->startOfDay(), $to->copy()->endOfDay()])
            ->orderBy('check_in_at')
            ->get();

        $pdf = Pdf::loadView('admin.reports.pdf.attendance', [
            'attendances' => $attendances,
            'from' => $from,
            'to' => $to,
            'generatedAt' => now(),
        ])->setPaper('a4', 'portrait');

        return $pdf->download('laporan-absensi-' . $from->format('Ymd') . '-' . $to->format('Ymd') . '.pdf');
    }

    /**
     * Laporan Pendapatan — rekap pembayaran (pendaftaran & perpanjangan) dalam rentang tanggal,
     * lengkap dengan total keseluruhan.
     */
    public function revenuePdf(Request $request)
    {
        [$from, $to] = $this->resolveDateRange($request);

        $payments = Payment::with('member.user', 'package')
            ->whereBetween('payment_date', [$from, $to])
            ->where('status', 'paid')
            ->orderBy('payment_date')
            ->get();

        $pdf = Pdf::loadView('admin.reports.pdf.revenue', [
            'payments' => $payments,
            'total' => $payments->sum('amount'),
            'from' => $from,
            'to' => $to,
            'generatedAt' => now(),
        ])->setPaper('a4', 'portrait');

        return $pdf->download('laporan-pendapatan-' . $from->format('Ymd') . '-' . $to->format('Ymd') . '.pdf');
    }

    /**
     * Laporan Member Segera Berakhir — daftar member aktif yang masa membership-nya
     * akan habis dalam N hari ke depan (default 7), supaya admin bisa follow-up perpanjangan.
     */
    public function expiringPdf(Request $request)
    {
        $days = (int) $request->query('days', 7);

        $members = Member::with('user', 'package')
            ->where('status', 'active')
            ->whereNotNull('expire_date')
            ->whereBetween('expire_date', [now()->startOfDay(), now()->addDays($days)->endOfDay()])
            ->orderBy('expire_date')
            ->get();

        $pdf = Pdf::loadView('admin.reports.pdf.expiring', [
            'members' => $members,
            'days' => $days,
            'generatedAt' => now(),
        ])->setPaper('a4', 'portrait');

        return $pdf->download('laporan-member-expiring-' . now()->format('Ymd-His') . '.pdf');
    }

    /**
     * Laporan Log Notifikasi WhatsApp — rekap pengiriman notifikasi WA (terkirim/gagal)
     * dalam rentang tanggal, berguna untuk audit apakah notifikasi member berjalan lancar.
     */
    public function whatsappPdf(Request $request)
    {
        [$from, $to] = $this->resolveDateRange($request);

        $logs = WhatsappLog::with('member.user')
            ->whereBetween('created_at', [$from->copy()->startOfDay(), $to->copy()->endOfDay()])
            ->orderBy('created_at')
            ->get();

        $pdf = Pdf::loadView('admin.reports.pdf.whatsapp', [
            'logs' => $logs,
            'from' => $from,
            'to' => $to,
            'generatedAt' => now(),
        ])->setPaper('a4', 'portrait');

        return $pdf->download('laporan-whatsapp-' . $from->format('Ymd') . '-' . $to->format('Ymd') . '.pdf');
    }

    protected function resolveDateRange(Request $request): array
    {
        $from = $request->query('from')
            ? \Carbon\Carbon::parse($request->query('from'))
            : now()->startOfMonth();

        $to = $request->query('to')
            ? \Carbon\Carbon::parse($request->query('to'))
            : now();

        return [$from, $to];
    }
}
