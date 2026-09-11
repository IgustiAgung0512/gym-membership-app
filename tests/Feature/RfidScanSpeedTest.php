<?php

namespace Tests\Feature;

use App\Models\Member;
use App\Models\MembershipPackage;
use App\Models\RfidCard;
use App\Models\User;
use App\Services\WhatsAppService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RfidScanSpeedTest extends TestCase
{
    use RefreshDatabase;

    public function test_rfid_scan_responds_successfully_and_records_checkin(): void
    {
        $package = MembershipPackage::create([
            'name' => 'Monthly Pro',
            'duration_months' => 1,
            'price' => 150000,
            'is_active' => true,
        ]);

        $user = User::factory()->create([
            'name' => 'Budi Santoso',
            'phone' => '081234567890',
            'role' => 'member',
        ]);

        $member = Member::create([
            'user_id' => $user->id,
            'membership_package_id' => $package->id,
            'member_code' => 'MBR-001',
            'join_date' => now(),
            'expire_date' => now()->addDays(30),
            'status' => 'active',
        ]);

        $card = RfidCard::create([
            'uid' => 'CARD998877',
            'member_id' => $member->id,
            'status' => 'assigned',
            'assigned_at' => now(),
        ]);

        $response = $this->postJson('/api/rfid/scan', [
            'uid' => 'CARD998877',
        ], [
            'X-Device-Key' => config('services.rfid.device_key') ?: '',
        ]);

        $response->assertStatus(200);
        $response->assertJson([
            'status' => 'success',
            'action' => 'checkin',
            'member_name' => 'Budi Santoso',
            'member_code' => 'MBR-001',
        ]);

        $this->assertDatabaseHas('attendances', [
            'member_id' => $member->id,
            'rfid_card_id' => $card->id,
            'method' => 'rfid',
        ]);
    }

    public function test_rfid_scan_accepts_colon_formatted_uid_and_various_routes(): void
    {
        $package = MembershipPackage::create([
            'name' => 'Monthly Pro',
            'duration_months' => 1,
            'price' => 150000,
            'is_active' => true,
        ]);

        $user = User::factory()->create([
            'name' => 'Ahmad Fauzi',
            'phone' => '081299998888',
            'role' => 'member',
        ]);

        $member = Member::create([
            'user_id' => $user->id,
            'membership_package_id' => $package->id,
            'member_code' => 'MBR-002',
            'join_date' => now(),
            'expire_date' => now()->addDays(30),
            'status' => 'active',
        ]);

        // Simpan di DB format bersih atau format colon
        $card = RfidCard::create([
            'uid' => 'C1B8C8A3',
            'member_id' => $member->id,
            'status' => 'assigned',
            'assigned_at' => now(),
        ]);

        $headers = ['X-Device-Key' => config('services.rfid.device_key') ?: ''];

        // 1. Test scan via /api/rfid/scan dengan UID ber-colon dari hardware (:C1:B8:C8:A3)
        $res1 = $this->postJson('/api/rfid/scan', [
            'uid' => ':C1:B8:C8:A3',
        ], $headers);
        $res1->assertStatus(200);
        $res1->assertJson(['status' => 'success', 'action' => 'checkin']);

        // 2. Test scan via direct route /rfid/scan (tanpa /api prefix)
        $res2 = $this->postJson('/rfid/scan', [
            'uid' => 'C1:B8:C8:A3',
        ], $headers);
        $res2->assertStatus(200);

        // 3. Test scan via /scan_uid
        $res3 = $this->postJson('/scan_uid', [
            'uid' => 'C1:B8:C8:A3',
        ], $headers);
        $res3->assertStatus(200);
    }
}
