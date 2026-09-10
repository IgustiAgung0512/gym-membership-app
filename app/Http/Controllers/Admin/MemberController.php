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
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

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
        $memberId = $request->member_id;

        if (!$uid) {
            return response()->json([
                'valid' => true,
                'exists' => false,
                'message' => 'UID RFID belum terdeteksi.'
            ]);
        }

        $card = RfidCard::with('member.user')
            ->where('uid', $uid)
            ->first();

        if ($card) {
            if ($memberId && $card->member_id == $memberId) {
                return response()->json([
                    'valid' => true,
                    'is_current' => true,
                    'message' => "Kartu {$uid} adalah kartu aktif milik member ini."
                ]);
            }

            if (!$card->member_id) {
                return response()->json([
                    'valid' => true,
                    'status' => 'unassigned',
                    'message' => "Kartu {$uid} berstatus Unassigned (tersedia untuk digunakan)."
                ]);
            }

            $memberName = $card->member?->user?->name ?? 'member lain';

            return response()->json([
                'valid' => false,
                'exists' => true,
                'message' => "UID {$uid} sudah digunakan oleh member {$memberName}."
            ]);
        }

        return response()->json([
            'valid' => true,
            'status' => 'new',
            'message' => "Kartu RFID baru tersedia: {$uid}"
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
        $unassignedCards = RfidCard::where('status', 'unassigned')
            ->orWhere('member_id', $member->id)
            ->orWhere('uid', $member->rfid_uid)
            ->orderBy('uid', 'asc')
            ->get();

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
                Rule::unique('members', 'rfid_uid')->ignore($member->id),
            ],
            'expire_date' => ['nullable', 'date'],
        ]);

        $member->user->update(['name' => $data['name'], 'phone' => $data['phone']]);

        $member->update([
            'membership_package_id' => $data['membership_package_id'],
            'address' => $data['address'] ?? null,
            'status' => $data['status'],
            'rfid_uid' => $data['rfid_uid'] ?? null,
            'expire_date' => $data['expire_date'] ?? $member->expire_date,
        ]);

        if (! empty($data['rfid_uid'])) {
            // Lepaskan kartu lama yang sebelumnya terhubung ke member ini jika berbeda
            RfidCard::where('member_id', $member->id)
                ->where('uid', '!=', $data['rfid_uid'])
                ->update(['member_id' => null, 'status' => 'unassigned', 'assigned_at' => null]);

            // Tautkan kartu baru yang dipilih
            RfidCard::updateOrCreate(
                ['uid' => $data['rfid_uid']],
                ['member_id' => $member->id, 'status' => 'assigned', 'assigned_at' => now()]
            );
        } else {
            // Jika dikosongkan (lepas kartu)
            RfidCard::where('member_id', $member->id)->update([
                'member_id' => null,
                'status' => 'unassigned',
                'assigned_at' => null,
            ]);
        }

        return redirect()->route('admin.members.index')->with('success', 'Data member berhasil diperbarui.');
    }

    /**
     * Tampilkan foto member (untuk admin) langsung dari storage,
     * tanpa bergantung pada symlink public/storage.
     *
     * Kalau foto tersimpan sebagai URL penuh (disk 's3' / Supabase Storage),
     * langsung redirect ke URL publiknya. Kalau masih path relatif lama
     * (disk lokal 'public'), tetap di-serve lewat controller.
     */
    public function photo(Member $member)
    {
        if (! $member->photo) {
            abort(404);
        }

        if (str_starts_with($member->photo, 'http://') || str_starts_with($member->photo, 'https://')) {
            return redirect($member->photo);
        }

        if (! Storage::disk('public')->exists($member->photo)) {
            abort(404);
        }

        return Storage::disk('public')->response($member->photo);
    }

    public function destroy(Member $member)
    {
        $member->user()->delete();
        $member->delete();

        return back()->with('success', 'Member berhasil dihapus.');
    }

    /**
     * Admin mereset password member ke password sementara baru,
     * mengirimkan notifikasi password baru via WhatsApp ke member,
     * dan menandai must_change_password = true supaya member wajib menggantinya saat login berikutnya.
     */
    public function resetPassword(Member $member, WhatsAppService $whatsapp)
    {
        $tempPassword = Str::password(10, symbols: false);

        $member->user->update([
            'password' => Hash::make($tempPassword),
            'must_change_password' => true,
        ]);

        try {
            $whatsapp->sendPasswordResetNotice($member->load('user'), $tempPassword);
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::warning('Gagal kirim WhatsApp reset password: ' . $e->getMessage());
        }

        return back()
            ->with('success', "Password member {$member->user->name} berhasil direset dan dikirimkan ke WhatsApp ({$member->user->phone}).")
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