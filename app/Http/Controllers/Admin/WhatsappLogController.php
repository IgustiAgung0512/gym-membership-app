<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\WhatsappLog;
use Illuminate\Http\Request;

class WhatsappLogController extends Controller
{
    public function index(Request $request)
    {
        $query = WhatsappLog::with('member.user')->latest();

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $logs = $query->paginate(20)->withQueryString();

        $totalSent = WhatsappLog::where('status', 'sent')->count();
        $totalFailed = WhatsappLog::where('status', 'failed')->count();
        $hasToken = !empty(config('services.whatsapp.token') ?: env('WHATSAPP_API_TOKEN') ?: env('WHATSAPP_TOKEN') ?: env('FONNTE_TOKEN'));

        return view('admin.whatsapp.index', compact('logs', 'totalSent', 'totalFailed', 'hasToken'));
    }

    /**
     * Test kirim pesan WhatsApp dari admin dashboard
     */
    public function testSend(Request $request, \App\Services\WhatsAppService $whatsApp)
    {
        $data = $request->validate([
            'phone' => ['required', 'string', 'max:25'],
            'message' => ['required', 'string', 'max:1000'],
        ]);

        $reflection = new \ReflectionClass($whatsApp);
        $method = $reflection->getMethod('send');
        $method->setAccessible(true);
        $sent = $method->invoke($whatsApp, null, $data['phone'], $data['message'], 'test');

        if ($sent) {
            return back()->with('success', 'Uji coba pesan WhatsApp BERHASIL dikirim ke ' . $data['phone'] . '!');
        }

        $lastLog = WhatsappLog::latest()->first();
        $reason = $lastLog?->response ?: 'Periksa konfigurasi token WhatsApp Anda.';

        return back()->with('error', 'Gagal kirim WhatsApp: ' . $reason);
    }

    /**
     * Hapus satu log notifikasi whatsapp
     */
    public function destroy(WhatsappLog $log)
    {
        $log->delete();

        return back()->with('success', 'Log WhatsApp berhasil dihapus.');
    }

    /**
     * Hapus semua log yang berstatus terkirim (sent)
     */
    public function clearSent()
    {
        $count = WhatsappLog::where('status', 'sent')->delete();

        return back()->with('success', "Berhasil menghapus {$count} log WhatsApp berstatus terkirim.");
    }

    /**
     * Hapus semua log WhatsApp
     */
    public function clearAll()
    {
        $count = WhatsappLog::count();
        WhatsappLog::truncate();

        return back()->with('success', "Berhasil membersihkan seluruh ({$count}) log WhatsApp.");
    }
}
