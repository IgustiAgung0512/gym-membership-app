<?php

namespace Tests\Feature;

use App\Models\Member;
use App\Models\MembershipPackage;
use App\Models\PendingRegistration;
use App\Models\RfidCard;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class OnlineMemberRegistrationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
    }

    public function test_can_initiate_online_registration_and_receive_qris()
    {
        $package = MembershipPackage::create([
            'name' => '1 Bulan Starter',
            'duration_months' => 1,
            'price' => 250000,
            'is_active' => true,
        ]);

        $response = $this->postJson(route('register.online.initiate'), [
            'membership_package_id' => $package->id,
            'name' => 'Budi Online',
            'email' => 'budi.online@example.com',
            'phone' => '081234567890',
            'password' => 'secret123',
            'gender' => 'male',
            'address' => 'Jl. Merdeka No. 10',
        ]);

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'success',
            'invoice_number',
            'package',
            'registration',
            'qris',
            'message',
        ]);

        $invoice = $response->json('invoice_number');

        $this->assertDatabaseHas('pending_registrations', [
            'invoice_number' => $invoice,
            'email' => 'budi.online@example.com',
            'name' => 'Budi Online',
            'gender' => 'L',
            'status' => 'pending',
            'amount' => 250000,
        ]);

        // Member/User should NOT exist before payment is settled
        $this->assertDatabaseMissing('users', [
            'email' => 'budi.online@example.com',
        ]);
        $this->assertEquals(0, Member::count());
    }

    public function test_can_check_status_of_pending_registration()
    {
        $package = MembershipPackage::create([
            'name' => '1 Bulan Starter',
            'duration_months' => 1,
            'price' => 250000,
            'is_active' => true,
        ]);

        $pending = PendingRegistration::create([
            'invoice_number' => 'REG-260911-0001',
            'membership_package_id' => $package->id,
            'name' => 'Budi Online',
            'email' => 'budi.online@example.com',
            'phone' => '081234567890',
            'password' => Hash::make('secret123'),
            'gender' => 'L',
            'amount' => 250000,
            'status' => 'pending',
            'expires_at' => now()->addMinutes(15),
        ]);

        $response = $this->getJson(route('register.online.status', ['invoice' => $pending->invoice_number]));
        $response->assertStatus(200);
        $response->assertJson([
            'status' => 'pending',
        ]);
    }

    public function test_can_simulate_payment_which_creates_user_and_member_without_rfid()
    {
        $package = MembershipPackage::create([
            'name' => '1 Bulan Starter',
            'duration_months' => 1,
            'price' => 250000,
            'is_active' => true,
        ]);

        $pending = PendingRegistration::create([
            'invoice_number' => 'REG-260911-0002',
            'membership_package_id' => $package->id,
            'name' => 'Dewi Lestari',
            'email' => 'dewi@example.com',
            'phone' => '08987654321',
            'password' => Hash::make('password123'),
            'gender' => 'P',
            'amount' => 250000,
            'status' => 'pending',
            'expires_at' => now()->addMinutes(15),
        ]);

        $response = $this->postJson(route('register.online.simulate', ['invoice' => $pending->invoice_number]));
        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'member_name' => 'Dewi Lestari',
            'package_name' => '1 Bulan Starter',
        ]);

        // Verify Pending Registration is marked paid
        $this->assertDatabaseHas('pending_registrations', [
            'invoice_number' => 'REG-260911-0002',
            'status' => 'paid',
        ]);

        // Verify User was created
        $user = User::where('email', 'dewi@example.com')->first();
        $this->assertNotNull($user);
        $this->assertEquals('member', $user->role);
        $this->assertTrue(Hash::check('password123', $user->password));

        // Verify Member was created without an RFID card
        $member = Member::where('user_id', $user->id)->first();
        $this->assertNotNull($member);
        $this->assertNull($member->rfidCard);
        $this->assertEquals('active', $member->status);
        $this->assertEquals('P', $member->gender);
        $this->assertEquals($package->id, $member->membership_package_id);

        // Verify Payment was created
        $this->assertDatabaseHas('payments', [
            'member_id' => $member->id,
            'type' => 'registration',
            'payment_method' => 'qris',
            'status' => 'paid',
            'amount' => 250000,
        ]);

        // Checking status again should return paid and member code
        $statusResponse = $this->getJson(route('register.online.status', ['invoice' => $pending->invoice_number]));
        $statusResponse->assertStatus(200);
        $statusResponse->assertJson([
            'status' => 'paid',
            'member_name' => 'Dewi Lestari',
            'member_code' => $member->member_code,
        ]);
    }

    public function test_admin_and_cashier_can_assign_rfid_card_to_online_registered_member()
    {
        $package = MembershipPackage::create([
            'name' => '1 Bulan Starter',
            'duration_months' => 1,
            'price' => 250000,
            'is_active' => true,
        ]);

        $admin = User::create([
            'name' => 'Admin Test',
            'email' => 'admin@test.com',
            'phone' => '08111111111',
            'password' => Hash::make('admin123'),
            'role' => 'admin',
        ]);

        $cashier = User::create([
            'name' => 'Cashier Test',
            'email' => 'cashier@test.com',
            'phone' => '08222222222',
            'password' => Hash::make('cashier123'),
            'role' => 'cashier',
        ]);

        $memberUser = User::create([
            'name' => 'Online Member',
            'email' => 'onlinemember@test.com',
            'phone' => '08333333333',
            'password' => Hash::make('member123'),
            'role' => 'member',
        ]);

        $member = Member::create([
            'user_id' => $memberUser->id,
            'member_code' => 'GYM-2026-9999',
            'gender' => 'L',
            'membership_package_id' => $package->id,
            'join_date' => now(),
            'start_date' => now(),
            'expire_date' => now()->addMonth(),
            'status' => 'active',
            'rfid_card_id' => null, // No RFID card initially
        ]);

        // 1. Admin assigns RFID card
        $response = $this->actingAs($admin)
            ->post(route('admin.members.assign-rfid', $member), [
                'rfid_uid' => 'C1:B8:C8:A3',
            ]);

        $response->assertRedirect(route('admin.members.index'));
        $response->assertSessionHas('success');

        $member->refresh();
        $this->assertNotNull($member->rfidCard);
        $this->assertEquals('C1:B8:C8:A3', $member->rfidCard->uid);

        // 2. Cashier can update/reassign RFID card
        $response2 = $this->actingAs($cashier)
            ->post(route('cashier.members.assign-rfid', $member), [
                'rfid_uid' => ':A1:B2:C3:D4',
            ]);

        $response2->assertRedirect(route('cashier.members.index'));
        $member->refresh();
        $this->assertEquals('A1:B2:C3:D4', $member->rfidCard->uid);
    }
}
