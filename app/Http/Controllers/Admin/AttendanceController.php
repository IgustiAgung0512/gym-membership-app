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

        $activeMembers = Member::with(['user', 'package', 'rfidCard'])
            ->where('status', 'active')
            ->get();

        return view('admin.attendance.index', compact('attendances', 'activeMembers', 'date'));
    }

    /**
     * Catat check-in/check-out manual oleh admin (tanpa kartu RFID fisik).
     */
    public function storeManual(Request $request)
    {
        $validated = $request->validate([
            'member_id' => ['nullable', 'exists:members,id'],
            'identifier' => ['nullable', 'string', 'max:100'],
        ]);

        $member = null;
        if (!empty($validated['member_id'])) {
            $member = Member::with('user')->find($validated['member_id']);
        } elseif (!empty($validated['identifier'])) {
            $term = trim($validated['identifier']);
            $member = Member::with('user')
                ->where('member_code', $term)
                ->orWhereHas('user', function ($q) use ($term) {
                    $q->where('phone', $term)->orWhere('email', $term)->orWhere('name', 'like', "%{$term}%");
                })
                ->first();
        }

        if (!$member) {
            if ($request->expectsJson() || $request->ajax()) {
                return response()->json(['success' => false, 'message' => 'Member tidak ditemukan.'], 404);
            }
            return back()->with('error', 'Member tidak ditemukan. Silakan pilih member atau masukkan kata kunci yang valid.');
        }

        if ($member->status !== 'active') {
            if ($request->expectsJson() || $request->ajax()) {
                return response()->json(['success' => false, 'message' => 'Member ' . $member->user->name . ' berstatus ' . strtoupper($member->status) . ' (tidak aktif).'], 422);
            }
            return back()->with('error', 'Member ' . $member->user->name . ' berstatus ' . strtoupper($member->status) . ' dan tidak dapat check-in.');
        }

        $openAttendance = Attendance::where('member_id', $member->id)
            ->whereDate('check_in_at', today())
            ->whereNull('check_out_at')
            ->latest('id')
            ->first();

        if ($openAttendance) {
            $openAttendance->update([
                'check_out_at' => now(),
            ]);

            $message = 'Check-out manual berhasil dicatat untuk ' . $member->user->name . '.';
            $action = 'checkout';
        } else {
            Attendance::create([
                'member_id' => $member->id,
                'rfid_card_id' => $member->rfidCard?->id,
                'method' => 'manual',
                'check_in_at' => now(),
            ]);

            try {
                app(\App\Services\WhatsAppService::class)->sendCheckInNotice($member);
            } catch (\Throwable $e) {
                \Illuminate\Support\Facades\Log::warning('Gagal kirim notifikasi check-in WA: ' . $e->getMessage());
            }

            $message = 'Check-in manual berhasil dicatat untuk ' . $member->user->name . '. Notifikasi WhatsApp otomatis terkirim!';
            $action = 'checkin';
        }

        if ($request->expectsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'action' => $action,
                'message' => $message,
                'member_name' => $member->user->name,
                'member_code' => $member->member_code,
            ]);
        }

        return back()->with('success', $message);
    }
}
