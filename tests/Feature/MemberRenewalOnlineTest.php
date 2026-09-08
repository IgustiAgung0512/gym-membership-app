<?php

namespace Tests\Feature;

use App\Models\Member;
use App\Models\MembershipPackage;
use App\Models\Payment;
use App\Models\User;
use App\Services\QrisService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Config;
use Tests\TestCase;

class MemberRenewalOnlineTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;
    protected Member $member;
    protected MembershipPackage $package1Month;
    protected MembershipPackage $package3Months;

    protected function setUp(): void
    {
        parent::setUp();

        Config::set('services.midtrans.server_key', 'test-secret-server-key-2026');
        Config::set('services.midtrans.is_production', false);

        $this->package1Month = MembershipPackage::create([
            'name' => 'Paket Bulanan',
            'duration_months' => 1,
            'price' => 150000,
            'is_active' => true,
        ]);

        $this->package3Months = MembershipPackage::create([
            'name' => 'Paket 3 Bulan',
            'duration_months' => 3,
            'price' => 400000,
            'is_active' => true,
        ]);

        $this->user = User::factory()->create([
            'name' => 'Budi Santoso',
            'email' => 'budi@gym.test',
            'role' => 'member',
            'phone' => '08123456789',
        ]);

        $this->member = Member::create([
            'user_id' => $this->user->id,
            'membership_package_id' => $this->package1Month->id,
            'member_code' => 'MBR-0001',
            'join_date' => now()->subMonth(),
            'expire_date' => now()->addDays(2), // expiring soon
            'status' => 'active',
        ]);
    }

    /**
     * Test bahwa member dapat menginisiasi perpanjangan online dan mendapatkan payload QRIS dinamis.
     */
    public function test_member_can_initiate_online_renewal_and_get_qris_payload(): void
    {
        $response = $this->actingAs($this->user)->postJson(route('member.renew.initiate'), [
            'membership_package_id' => $this->package3Months->id,
            'payment_method' => 'qris',
        ]);

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'package' => [
                'id' => $this->package3Months->id,
                'name' => 'Paket 3 Bulan',
            ],
        ]);

        $data = $response->json();
        $this->assertNotEmpty($data['qris']['qr_url']);
        $this->assertNotEmpty($data['qris']['qr_string']);
        $this->assertStringStartsWith('RNW-', $data['payment']['invoice_number']);

        $this->assertDatabaseHas('payments', [
            'member_id' => $this->member->id,
            'membership_package_id' => $this->package3Months->id,
            'amount' => 400000,
            'payment_method' => 'qris',
            'status' => 'pending',
            'type' => 'renewal',
        ]);
    }

    /**
     * Test polling status pembayaran perpanjangan membership.
     */
    public function test_member_can_poll_renewal_status(): void
    {
        $payment = Payment::create([
            'invoice_number' => 'RNW-260907-0001',
            'member_id' => $this->member->id,
            'membership_package_id' => $this->package1Month->id,
            'amount' => 150000,
            'payment_method' => 'qris',
            'status' => 'pending',
            'type' => 'renewal',
            'payment_date' => now(),
        ]);

        $response = $this->actingAs($this->user)->getJson(route('member.renew.status', $payment->id));

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'status' => 'pending',
            'is_paid' => false,
        ]);
    }

    /**
     * Test bahwa simulasi QRIS sukses memperpanjang masa aktif sesuai durasi paket dan mengaktifkan member.
     */
    public function test_simulate_qris_renewal_extends_membership_and_activates_rfid_gate(): void
    {
        $initialExpire = now()->addDays(5);
        $this->member->update([
            'expire_date' => $initialExpire,
            'status' => 'active',
        ]);

        $payment = Payment::create([
            'invoice_number' => 'RNW-260907-0002',
            'member_id' => $this->member->id,
            'membership_package_id' => $this->package3Months->id,
            'amount' => 400000,
            'payment_method' => 'qris',
            'status' => 'pending',
            'type' => 'renewal',
            'payment_date' => now(),
        ]);

        $response = $this->actingAs($this->user)->postJson(route('member.renew.simulate', $payment->id));

        $response->assertStatus(200);
        $response->assertJson(['success' => true]);

        // Verifikasi status Payment menjadi 'paid'
        $this->assertEquals('paid', $payment->fresh()->status);

        // Verifikasi masa aktif bertambah 3 bulan dari tanggal awal (addDays(5) + 3 months)
        $expectedExpire = (clone $initialExpire)->addMonths(3);
        $this->assertEquals($expectedExpire->format('Y-m-d'), $this->member->fresh()->expire_date->format('Y-m-d'));
        $this->assertEquals('active', $this->member->fresh()->status);
        $this->assertEquals($this->package3Months->id, $this->member->fresh()->membership_package_id);
    }

    /**
     * Test jika member sudah kadaluwarsa (expired / in the past), perpanjangan dimulai dari waktu sekarang (now()).
     */
    public function test_expired_member_renewal_extends_from_now(): void
    {
        $pastExpire = now()->subDays(10);
        $this->member->update([
            'expire_date' => $pastExpire,
            'status' => 'expired',
        ]);

        $payment = Payment::create([
            'invoice_number' => 'RNW-260907-0003',
            'member_id' => $this->member->id,
            'membership_package_id' => $this->package1Month->id,
            'amount' => 150000,
            'payment_method' => 'qris',
            'status' => 'pending',
            'type' => 'renewal',
            'payment_date' => now(),
        ]);

        $response = $this->actingAs($this->user)->postJson(route('member.renew.simulate', $payment->id));

        $response->assertStatus(200);
        $expectedExpire = now()->addMonths(1);
        $this->assertEquals($expectedExpire->format('Y-m-d'), $this->member->fresh()->expire_date->format('Y-m-d'));
        $this->assertEquals('active', $this->member->fresh()->status);
    }

    /**
     * Test bahwa simulasi perpanjangan diblokir (403) di mode Production.
     */
    public function test_simulate_renewal_blocked_in_production(): void
    {
        Config::set('services.midtrans.is_production', true);

        $payment = Payment::create([
            'invoice_number' => 'RNW-260907-0004',
            'member_id' => $this->member->id,
            'membership_package_id' => $this->package1Month->id,
            'amount' => 150000,
            'payment_method' => 'qris',
            'status' => 'pending',
            'type' => 'renewal',
            'payment_date' => now(),
        ]);

        $response = $this->actingAs($this->user)->postJson(route('member.renew.simulate', $payment->id));

        $response->assertStatus(403);
        $response->assertJson(['success' => false]);
        $this->assertEquals('pending', $payment->fresh()->status);
    }

    /**
     * Test IDOR Protection: Member tidak dapat mengakses atau memanipulasi perpanjangan milik member lain.
     */
    public function test_member_cannot_access_or_simulate_other_members_renewal_idor(): void
    {
        $otherUser = User::factory()->create(['role' => 'member']);
        $otherMember = Member::create([
            'user_id' => $otherUser->id,
            'member_code' => 'MBR-0002',
            'join_date' => now(),
            'expire_date' => now()->addMonth(),
            'status' => 'active',
        ]);

        $paymentOfOther = Payment::create([
            'invoice_number' => 'RNW-260907-0005',
            'member_id' => $otherMember->id,
            'membership_package_id' => $this->package1Month->id,
            'amount' => 150000,
            'payment_method' => 'qris',
            'status' => 'pending',
            'type' => 'renewal',
            'payment_date' => now(),
        ]);

        // User pertama coba akses status & simulasi milik user kedua
        $responseStatus = $this->actingAs($this->user)->getJson(route('member.renew.status', $paymentOfOther->id));
        $responseStatus->assertStatus(403);

        $responseSimulate = $this->actingAs($this->user)->postJson(route('member.renew.simulate', $paymentOfOther->id));
        $responseSimulate->assertStatus(403);

        $responseCancel = $this->actingAs($this->user)->postJson(route('member.renew.cancel', $paymentOfOther->id));
        $responseCancel->assertStatus(403);
    }

    /**
     * Test pembatalan transaksi perpanjangan pending.
     */
    public function test_member_can_cancel_pending_renewal(): void
    {
        $payment = Payment::create([
            'invoice_number' => 'RNW-260907-0006',
            'member_id' => $this->member->id,
            'membership_package_id' => $this->package1Month->id,
            'amount' => 150000,
            'payment_method' => 'qris',
            'status' => 'pending',
            'type' => 'renewal',
            'payment_date' => now(),
        ]);

        $response = $this->actingAs($this->user)->postJson(route('member.renew.cancel', $payment->id));

        $response->assertStatus(200);
        $this->assertEquals('cancelled', $payment->fresh()->status);
    }

    /**
     * Test webhook payment gateway Midtrans memproses perpanjangan membership otomatis.
     */
    public function test_webhook_settles_renewal_and_extends_membership(): void
    {
        $payment = Payment::create([
            'invoice_number' => 'RNW-260907-0007',
            'member_id' => $this->member->id,
            'membership_package_id' => $this->package3Months->id,
            'amount' => 400000,
            'payment_method' => 'qris',
            'status' => 'pending',
            'type' => 'renewal',
            'payment_date' => now(),
        ]);

        $serverKey = 'test-secret-server-key-2026';
        $grossAmount = '400000.00';
        $validSignature = hash('sha512', $payment->invoice_number . '200' . $grossAmount . $serverKey);

        $response = $this->postJson('/api/payment/webhook', [
            'order_id' => $payment->invoice_number,
            'status_code' => '200',
            'gross_amount' => $grossAmount,
            'transaction_status' => 'settlement',
            'signature_key' => $validSignature,
        ]);

        $response->assertStatus(200);
        $response->assertJson(['success' => true]);

        $this->assertEquals('paid', $payment->fresh()->status);
        $this->assertEquals('active', $this->member->fresh()->status);
    }
}
