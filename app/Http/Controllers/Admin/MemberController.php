<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Member;
use App\Models\MembershipPackage;
use App\Models\RfidCard;
use App\Models\User;
use App\Services\WhatsAppService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class MemberController extends Controller
{
    public function index(Request $request)
    {
        $members = Member::with('user', 'package', 'rfidCard')
            ->when($request->search, function ($q, $search) {
                $q->whereHas('user', fn ($u) => $u->where('name', 'like', "%{$search}%")
                    ->orWhere('phone', 'like', "%{$search}%"))
                    ->orWhere('member_code', 'like', "%{$search}%");
            })
            ->when($request->status, fn ($q, $status) => $q->where('status', $status))
            ->latest('join_date')
            ->paginate(10)
            ->withQueryString();

        return view('admin.members.index', compact('members'));
    }

    public function create()
    {
        $packages = MembershipPackage::where('is_active', true)->get();
        $unassignedCards = RfidCard::where('status', 'unassigned')->get();

        return view('admin.members.create', compact('packages', 'unassignedCards'));
    }

    public function latestRfid()
    {
        $scan = DB::table('scan_uids')
            ->latest('id')
            ->first();

        return response()->json([
            'uid' => $scan?->uid,
        ]);
    }

    public function checkRfid(Request $request)
{
    $uid = trim($request->uid);

    if (!$uid) {
        return response()->json([
            'exists' => false,
            'message' => 'UID RFID belum terdeteksi.'
        ]);
    }

    $card = RfidCard::with('member.user')
        ->where('uid', $uid)
        ->first();

    if ($card) {
        $memberName = $card->member?->user?->name;

        return response()->json([
            'exists' => true,
            'message' => $memberName
                ? "UID {$uid} sudah digunakan oleh member {$memberName}."
                : "UID {$uid} sudah terdaftar pada kartu RFID lain."
        ]);
    }

    return response()->json([
        'exists' => false,
        'message' => "Kartu RFID tersedia: {$uid}"
    ]);
}

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'unique:users,email'],
            'phone' => ['required', 'string', 'max:20'],
            'address' => ['nullable', 'string'],
            'birth_date' => ['nullable', 'date'],
            'gender' => ['nullable', 'in:L,P'],
            'membership_package_id' => ['required', 'exists:membership_packages,id'],
            'rfid_uid' => [
                'nullable',
                'string',
                'max:50',
            ],
        ]);

        $member = DB::transaction(function () use ($data) {
            $tempPassword = Str::password(10, symbols: false);

            $user = User::create([
                'name' => $data['name'],
                'email' => $data['email'],
                'phone' => $data['phone'],
                'password' => Hash::make($tempPassword),
                'role' => 'member',
                'must_change_password' => true,
            ]);

            $package = MembershipPackage::find($data['membership_package_id']);

            $member = Member::create([
                'user_id' => $user->id,
                'membership_package_id' => $package->id,
                'member_code' => 'GYM-' . now()->format('ym') . '-' . str_pad(Member::count() + 1, 4, '0', STR_PAD_LEFT),
                'address' => $data['address'] ?? null,
                'birth_date' => $data['birth_date'] ?? null,
                'gender' => $data['gender'] ?? null,
                'join_date' => now(),
                'expire_date' => now()->addMonths($package->duration_months),
                'status' => 'active',
            ]);

            $member->payments()->create([
                'membership_package_id' => $package->id,
                'amount' => $package->price,
                'payment_date' => now(),
                'status' => 'paid',
            ]);

            if (!empty($data['rfid_uid'])) {

                RfidCard::create([
                    'uid' => $data['rfid_uid'],
                    'member_id' => $member->id,
                    'status' => 'assigned',
                    'assigned_at' => now(),
                ]);
            }

            $member->temp_password = $tempPassword;

            return $member;
        });

        app(WhatsAppService::class)->sendRegistrationNotice($member->load('user', 'package'));

        return redirect()->route('admin.members.index')
            ->with('success', "Member {$member->member_code} berhasil didaftarkan.")
            ->with('temp_password', ['name' => $member->user->name, 'password' => $member->temp_password]);
    }

    public function edit(Member $member)
    {
        $member->load('user', 'rfidCard');
        $packages = MembershipPackage::where('is_active', true)->get();
        $unassignedCards = RfidCard::where('status', 'unassigned')->orWhere('member_id', $member->id)->get();

        return view('admin.members.edit', compact('member', 'packages', 'unassignedCards'));
    }

    public function update(Request $request, Member $member)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'phone' => ['required', 'string', 'max:20'],
            'address' => ['nullable', 'string'],
            'membership_package_id' => ['required', 'exists:membership_packages,id'],
            'status' => ['required', 'in:active,inactive,expired'],
            'rfid_uid' => [
                'nullable',
                'string',
                'max:50',
                'unique:members,rfid_uid',
            ],
            'expire_date' => ['nullable', 'date'],
        ]);

        $member->user->update(['name' => $data['name'], 'phone' => $data['phone']]);

        $member->update([
            'membership_package_id' => $data['membership_package_id'],
            'address' => $data['address'] ?? null,
            'status' => $data['status'],
            'expire_date' => $data['expire_date'] ?? $member->expire_date,
        ]);

        if (! empty($data['rfid_uid'])) {
            RfidCard::where('member_id', $member->id)->update(['member_id' => null, 'status' => 'unassigned']);
            RfidCard::updateOrCreate(
                ['uid' => $data['rfid_uid']],
                ['member_id' => $member->id, 'status' => 'assigned', 'assigned_at' => now()]
            );
        }

        return redirect()->route('admin.members.index')->with('success', 'Data member berhasil diperbarui.');
    }

    public function destroy(Member $member)
    {
        $member->user()->delete();
        $member->delete();

        return back()->with('success', 'Member berhasil dihapus.');
    }

    /**
     * Admin mereset password member ke password sementara baru,
     * dan menandai must_change_password = true supaya member wajib menggantinya saat login berikutnya.
     */
    public function resetPassword(Member $member)
    {
        $tempPassword = Str::password(10, symbols: false);

        $member->user->update([
            'password' => Hash::make($tempPassword),
            'must_change_password' => true,
        ]);

        return back()
            ->with('success', 'Password member berhasil direset.')
            ->with('temp_password', ['name' => $member->user->name, 'password' => $tempPassword]);
    }

    public function renew(Member $member)
    {
        $package = $member->package;
        $base = $member->expire_date && $member->expire_date->isFuture() ? $member->expire_date : now();

        $member->update([
            'expire_date' => $base->copy()->addMonths($package->duration_months ?? 1),
            'status' => 'active',
        ]);

        $member->payments()->create([
            'membership_package_id' => $package->id ?? null,
            'amount' => $package->price ?? 0,
            'payment_date' => now(),
            'status' => 'paid',
        ]);

        return back()->with('success', 'Membership berhasil diperpanjang.');
    }
}
