<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\RfidCard;
use App\Models\ScanUid;
use Illuminate\Http\Request;

class RfidController extends Controller
{
    public function index()
    {
        $cards = RfidCard::with('member.user')->latest()->paginate(15);

        return view('admin.rfid.index', compact('cards'));
    }

    public function latest()
    {
        $scan = ScanUid::latest('id')->first();

        return response()->json([
            'uid' => $scan?->uid,
        ]);
    }

    public function check(Request $request)
    {
        $uid = trim($request->uid);
        if (!$uid) {
            return response()->json(['exists' => false, 'message' => 'UID kosong.']);
        }

        $card = RfidCard::with('member.user')->where('uid', $uid)->first();
        if ($card) {
            $memberName = $card->member?->user?->name;
            return response()->json([
                'exists' => true,
                'status' => $card->status,
                'member_name' => $memberName,
                'message' => $memberName 
                    ? "Kartu {$uid} sudah terdaftar dan digunakan oleh member {$memberName}."
                    : "Kartu {$uid} sudah terdaftar di sistem dengan status " . ucfirst($card->status) . ".",
            ]);
        }

        return response()->json([
            'exists' => false,
            'message' => "Kartu {$uid} belum terdaftar. Siap disimpan ke sistem!",
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'uid' => ['required', 'string', 'max:50', 'unique:rfid_cards,uid'],
        ]);

        RfidCard::create(['uid' => trim($data['uid']), 'status' => 'unassigned']);

        return back()->with('success', 'Kartu RFID baru (' . $data['uid'] . ') berhasil didaftarkan ke sistem.');
    }

    public function toggleBlock(RfidCard $card)
    {
        $card->update(['status' => $card->status === 'blocked' ? ($card->member_id ? 'assigned' : 'unassigned') : 'blocked']);

        return back()->with('success', 'Status kartu diperbarui.');
    }

    public function destroy(RfidCard $card)
    {
        $card->delete();

        return back()->with('success', 'Kartu RFID dihapus dari sistem.');
    }
}
