<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Attendance;
use App\Models\Member;
use Illuminate\Http\Request;

class AttendanceController extends Controller
{
    /**
     * Daftar riwayat check-in (halaman "Absensi / Check-in"), difilter per tanggal.
     */
    public function index(Request $request)
    {
        $date = $request->input('date', today()->format('Y-m-d'));

        $attendances = Attendance::with(['member.user', 'rfidCard'])
            ->whereDate('check_in_at', $date)
            ->latest('check_in_at')
            ->paginate(20)
            ->withQueryString();

        return view('admin.attendance.index', compact('attendances'));
    }

    /**
     * Catat check-in/check-out manual oleh admin (tanpa kartu RFID).
     *
     * Memakai pola "sesi terbuka" yang sama seperti scan RFID
     * (lihat App\Http\Controllers\Api\MemberController@store):
     * - Kalau member sedang punya sesi yang belum check-out -> tap/submit ini = CHECK-OUT.
     * - Kalau tidak ada sesi terbuka -> submit ini = CHECK-IN baru.
     */
    public function storeManual(Request $request)
    {
        $validated = $request->validate([
            'member_id' => ['required', 'exists:members,id'],
        ], [
            'member_id.required' => 'Silakan pilih member terlebih dahulu.',
            'member_id.exists' => 'Member tidak ditemukan.',
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
                'method' => 'manual',
                'check_in_at' => now(),
            ]);

            $message = 'Check-in manual berhasil dicatat untuk ' . $member->user->name . '.';
        }

        return back()->with('success', $message);
    }
}
