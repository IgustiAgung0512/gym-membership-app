<?php

namespace App\Http\Controllers\Cashier;

use App\Http\Controllers\Controller;
use App\Models\Attendance;
use App\Models\Member;
use Illuminate\Http\Request;

class AttendanceController extends Controller
{
    public function index(Request $request)
    {
        $date = $request->input('date', today()->format('Y-m-d'));

        $attendances = Attendance::with(['member.user', 'rfidCard'])
            ->whereDate('check_in_at', $date)
            ->latest('check_in_at')
            ->paginate(20)
            ->withQueryString();

        $activeMembers = Member::with('user')->where('status', 'active')->get();

        return view('cashier.attendance.index', compact('attendances', 'activeMembers', 'date'));
    }

    public function storeManual(Request $request)
    {
        $validated = $request->validate([
            'member_id' => ['required', 'exists:members,id'],
        ]);

        $member = Member::with('user')->findOrFail($validated['member_id']);

        $openAttendance = Attendance::where('member_id', $member->id)
            ->whereNull('check_out_at')
            ->latest('id')
            ->first();

        if ($openAttendance) {
            $openAttendance->update([
                'check_out_at' => now(),
            ]);
            $message = 'Check-out manual berhasil dicatat untuk ' . $member->user->name . '.';
        } else {
            Attendance::create([
                'member_id' => $member->id,
                'rfid_card_id' => $member->rfidCard?->id,
                'check_in_at' => now(),
            ]);

            try {
                app(\App\Services\WhatsAppService::class)->sendCheckInNotice($member);
            } catch (\Throwable $e) {
                \Illuminate\Support\Facades\Log::warning('Gagal kirim notifikasi check-in WA: ' . $e->getMessage());
            }

            $message = 'Check-in manual berhasil dicatat untuk ' . $member->user->name . '.';
        }

        return redirect()->route('cashier.attendance.index')->with('success', $message);
    }
}
