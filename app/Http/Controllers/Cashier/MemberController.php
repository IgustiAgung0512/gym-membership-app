<?php

namespace App\Http\Controllers\Cashier;

use App\Http\Controllers\Controller;
use App\Models\Member;
use App\Models\MembershipPackage;
use App\Models\Payment;
use App\Models\RfidCard;
use App\Models\User;
use App\Services\WhatsAppService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
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
            ->paginate(15)
            ->withQueryString();

        $packages = MembershipPackage::where('is_active', true)->get();

        return view('cashier.members.index', compact('members', 'packages'));
    }

    public function create()
    {
        $packages = MembershipPackage::where('is_active', true)->get();
        $unassignedCards = RfidCard::where('status', 'unassigned')->get();

        return view('cashier.members.create', compact('packages', 'unassignedCards'));
    }

    public function store(Request $request, WhatsAppService $whatsApp)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:100',
            'email' => 'required|email|unique:users,email',
            'phone' => 'required|string|max:20',
            'membership_package_id' => 'required|exists:membership_packages,id',
            'rfid_uid' => 'nullable|string|max:50',
            'rfid_card_id' => 'nullable|exists:rfid_cards,id',
            'photo' => 'nullable|image|max:2048',
            'payment_method' => 'required|in:cash,transfer,qris',
        ]);

        $package = MembershipPackage::findOrFail($validated['membership_package_id']);
        $rawPassword = Str::random(8);

        $member = DB::transaction(function () use ($validated, $package, $rawPassword, $request) {
            $user = User::create([
                'name' => $validated['name'],
                'email' => $validated['email'],
                'phone' => $validated['phone'],
                'password' => Hash::make($rawPassword),
                'role' => 'member',
                'must_change_password' => true,
            ]);

            $photoPath = null;
            if ($request->hasFile('photo')) {
                $photoPath = $request->file('photo')->store('members/photos', 'public');
            }

            $memberCode = 'GYM-' . now()->format('ym') . '-' . str_pad(Member::count() + 1, 4, '0', STR_PAD_LEFT);

            $member = Member::create([
                'user_id' => $user->id,
                'membership_package_id' => $package->id,
                'member_code' => $memberCode,
                'join_date' => now(),
                'expire_date' => now()->addMonths($package->duration_months),
                'status' => 'active',
                'photo' => $photoPath,
            ]);

            if ($request->filled('rfid_uid')) {
                $uid = trim($request->rfid_uid);
                $card = RfidCard::firstOrCreate(
                    ['uid' => $uid],
                    ['status' => 'assigned', 'assigned_at' => now(), 'member_id' => $member->id]
                );
                if (!$card->wasRecentlyCreated) {
                    $card->update([
                        'member_id' => $member->id,
                        'status' => 'assigned',
                        'assigned_at' => now(),
                    ]);
                }
            } elseif (!empty($validated['rfid_card_id'])) {
                RfidCard::where('id', $validated['rfid_card_id'])->update([
                    'member_id' => $member->id,
                    'status' => 'assigned',
                    'assigned_at' => now(),
                ]);
            }

            Payment::create([
                'member_id' => $member->id,
                'membership_package_id' => $package->id,
                'amount' => $package->price,
                'payment_method' => $validated['payment_method'],
                'payment_date' => now(),
                'status' => 'paid',
                'type' => 'registration',
            ]);

            return $member;
        });

        // Kirim notifikasi WA
        $whatsApp->sendRegistrationSuccess($member, $rawPassword);

        return redirect()->route('cashier.members.index')
            ->with('success', "Member {$member->user->name} ({$member->member_code}) berhasil didaftarkan.");
    }

    public function renew(Request $request, Member $member, WhatsAppService $whatsApp)
    {
        $validated = $request->validate([
            'membership_package_id' => 'required|exists:membership_packages,id',
            'payment_method' => 'required|in:cash,transfer,qris',
        ]);

        $package = MembershipPackage::findOrFail($validated['membership_package_id']);

        DB::transaction(function () use ($member, $package, $validated) {
            $currentExpire = $member->expire_date && $member->expire_date->isFuture()
                ? $member->expire_date
                : now();

            $newExpire = (clone $currentExpire)->addMonths($package->duration_months);

            $member->update([
                'membership_package_id' => $package->id,
                'expire_date' => $newExpire,
                'status' => 'active',
            ]);

            Payment::create([
                'member_id' => $member->id,
                'membership_package_id' => $package->id,
                'amount' => $package->price,
                'payment_method' => $validated['payment_method'],
                'payment_date' => now(),
                'status' => 'paid',
                'type' => 'renewal',
            ]);
        });

        $whatsApp->sendRenewalSuccess($member->fresh());

        return redirect()->route('cashier.members.index')
            ->with('success', "Perpanjangan member {$member->user->name} berhasil diproses.");
    }

    /**
     * Tampilkan foto member (untuk kasir) langsung dari storage,
     * tanpa bergantung pada symlink public/storage.
     */
    public function photo(Member $member)
    {
        if (! $member->photo || ! Storage::disk('public')->exists($member->photo)) {
            abort(404);
        }

        return Storage::disk('public')->response($member->photo);
    }
}
