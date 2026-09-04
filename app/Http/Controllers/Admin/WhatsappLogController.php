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

        return view('admin.whatsapp.index', compact('logs', 'totalSent', 'totalFailed'));
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
