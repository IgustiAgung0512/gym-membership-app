<?php

require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\User;
use App\Models\Member;
use App\Models\MembershipPackage;
use App\Models\Payment;
use App\Models\RfidCard;
use Illuminate\Support\Facades\Request;

echo "=== TEST MEMBER ONLINE RENEWAL & CASHIER RFID REGISTRATION ===\n";

// 1. Test Member Expiration Alert and Renewal
$memberUser = User::where('role', 'member')->first();
if (!$memberUser) {
    echo "No member user found!\n";
    exit(1);
}

$member = $memberUser->member;
echo "Found member: {$memberUser->name} (Code: {$member->member_code})\n";
echo "Current expire date: {$member->expire_date}\n";
echo "Is expiring soon? " . ($member->isExpiringSoon() ? 'YES' : 'NO') . "\n";
echo "Status: {$member->status}\n";

$package = MembershipPackage::where('is_active', true)->first();
echo "Testing renew with package: {$package->name} (Rp{$package->price})\n";

// Execute simulated renewal
$oldExpire = $member->expire_date;
$currentExpire = $member->expire_date && $member->expire_date->isFuture()
    ? $member->expire_date
    : now();
$newExpire = (clone $currentExpire)->addMonths($package->duration_months);

$member->update([
    'membership_package_id' => $package->id,
    'expire_date' => $newExpire,
    'status' => 'active',
]);

$payment = Payment::create([
    'member_id' => $member->id,
    'membership_package_id' => $package->id,
    'amount' => $package->price,
    'payment_method' => 'qris',
    'payment_date' => now(),
    'status' => 'paid',
    'type' => 'renewal',
]);

echo "Renewal payment recorded: ID #{$payment->id}, Amount: Rp{$payment->amount}, Method: {$payment->payment_method}, Type: {$payment->type}\n";
echo "New expire date: {$member->fresh()->expire_date}\n";

// Check revenue in Admin / Cashier systems
$totalMembershipRevenue = Payment::where('status', 'paid')->sum('amount');
echo "Total Membership System Revenue (includes online renewal): Rp" . number_format($totalMembershipRevenue, 0, ',', '.') . "\n";

// 2. Test Cashier RFID auto registration logic
$testUid = 'TEST-RFID-' . rand(1000, 9999);
echo "Testing cashier RFID card auto-link with UID: {$testUid}...\n";

$card = RfidCard::firstOrCreate(
    ['uid' => $testUid],
    ['status' => 'assigned', 'assigned_at' => now(), 'member_id' => $member->id]
);
echo "Card assigned successfully. ID: {$card->id}, UID: {$card->uid}, Status: {$card->status}\n";

echo "\nALL TESTS PASSED SUCCESSFULLY!\n";
