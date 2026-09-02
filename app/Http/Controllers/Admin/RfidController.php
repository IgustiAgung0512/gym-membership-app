<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\RfidCard;
use Illuminate\Http\Request;

class RfidController extends Controller
{
    public function index()
    {
        $cards = RfidCard::with('member.user')->latest()->paginate(15);

        return view('admin.rfid.index', compact('cards'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'uid' => ['required', 'string', 'max:50', 'unique:rfid_cards,uid'],
        ]);

        RfidCard::create(['uid' => $data['uid'], 'status' => 'unassigned']);

        return back()->with('success', 'Kartu RFID baru berhasil didaftarkan ke sistem.');
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
