<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\WhatsappLog;

class WhatsappLogController extends Controller
{
    public function index()
    {
        $logs = WhatsappLog::with('member.user')->latest()->paginate(20);

        return view('admin.whatsapp.index', compact('logs'));
    }
}
