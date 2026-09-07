<?php

namespace Tests\Feature;

use App\Models\Member;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\User;
use App\Services\QrisService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Config;
use Tests\TestCase;

class PaymentSecurityTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Config::set('services.midtrans.server_key', 'test-secret-server-key-2026');
        Config::set('services.midtrans.is_production', false);
    }

    /**
     * Test bahwa Webhook menolak request dengan tanda tangan kriptografis (Signature) yang salah / dipalsukan hacker.
     */
    public function test_webhook_rejects_invalid_cryptographic_signature(): void
    {
        $order = Order::create([
            'invoice_number' => 'MBR-260907-9901',
            'customer_name' => 'Budi Santoso',
            'total_amount' => 50000,
            'subtotal' => 50000,
            'cost_total' => 30000,
            'payment_method' => 'qris',
            'payment_status' => 'pending',
            'pickup_status' => 'ready_for_pickup',
        ]);

        $fakeSignature = 'this_is_a_completely_fake_signature_hash_1234567890';

        $response = $this->postJson('/api/payment/webhook', [
            'order_id' => $order->invoice_number,
            'status_code' => '200',
            'gross_amount' => '50000.00',
            'transaction_status' => 'settlement',
            'signature_key' => $fakeSignature,
        ]);

        $response->assertStatus(403);
        $response->assertJson(['success' => false]);
        $this->assertEquals('pending', $order->fresh()->payment_status);
    }

    /**
     * Test bahwa Webhook menolak request jika nominal pembayaran (gross_amount) tidak cocok dengan harga di database.
     */
    public function test_webhook_rejects_gross_amount_mismatch(): void
    {
        $order = Order::create([
            'invoice_number' => 'MBR-260907-9902',
            'customer_name' => 'Budi Santoso',
            'total_amount' => 50000,
            'subtotal' => 50000,
            'cost_total' => 30000,
            'payment_method' => 'qris',
            'payment_status' => 'pending',
            'pickup_status' => 'ready_for_pickup',
        ]);

        $tamperedAmount = '1000.00'; // Hacker mencoba bayar 1000 rupiah untuk tagihan 50000 rupiah
        $serverKey = 'test-secret-server-key-2026';
        $signature = hash('sha512', $order->invoice_number . '200' . $tamperedAmount . $serverKey);

        $response = $this->postJson('/api/payment/webhook', [
            'order_id' => $order->invoice_number,
            'status_code' => '200',
            'gross_amount' => $tamperedAmount,
            'transaction_status' => 'settlement',
            'signature_key' => $signature,
        ]);

        $response->assertStatus(422);
        $this->assertEquals('pending', $order->fresh()->payment_status);
    }

    /**
     * Test bahwa Webhook menerima notifikasi sah dari Payment Gateway dan mengupdate status menjadi paid.
     */
    public function test_webhook_accepts_valid_signature_and_marks_order_paid(): void
    {
        $order = Order::create([
            'invoice_number' => 'MBR-260907-9903',
            'customer_name' => 'Budi Santoso',
            'total_amount' => 75000,
            'subtotal' => 75000,
            'cost_total' => 45000,
            'payment_method' => 'qris',
            'payment_status' => 'pending',
            'pickup_status' => 'ready_for_pickup',
        ]);

        $serverKey = 'test-secret-server-key-2026';
        $grossAmount = '75000.00';
        $validSignature = hash('sha512', $order->invoice_number . '200' . $grossAmount . $serverKey);

        $response = $this->postJson('/api/payment/webhook', [
            'order_id' => $order->invoice_number,
            'status_code' => '200',
            'gross_amount' => $grossAmount,
            'transaction_status' => 'settlement',
            'signature_key' => $validSignature,
        ]);

        $response->assertStatus(200);
        $response->assertJson(['success' => true]);
        $this->assertEquals('paid', $order->fresh()->payment_status);
    }

    /**
     * Test bahwa jika pembayaran expired/deny, pesanan otomatis dibatalkan dan stok produk dikembalikan ke inventaris.
     */
    public function test_webhook_cancels_order_and_restores_stock_on_expire(): void
    {
        $product = Product::create([
            'name' => 'Whey Protein Test',
            'price' => 100000,
            'cost_price' => 80000,
            'stock' => 10,
            'unit' => 'botol',
            'category' => 'supplements',
            'is_active' => true,
        ]);

        $order = Order::create([
            'invoice_number' => 'MBR-260907-9904',
            'customer_name' => 'Budi Santoso',
            'total_amount' => 100000,
            'subtotal' => 100000,
            'cost_total' => 80000,
            'payment_method' => 'qris',
            'payment_status' => 'pending',
            'pickup_status' => 'ready_for_pickup',
        ]);

        OrderItem::create([
            'order_id' => $order->id,
            'product_id' => $product->id,
            'product_name' => $product->name,
            'unit_price' => 100000,
            'cost_price' => 80000,
            'quantity' => 2,
            'subtotal' => 200000,
        ]);

        $serverKey = 'test-secret-server-key-2026';
        $grossAmount = '100000.00';
        $validSignature = hash('sha512', $order->invoice_number . '200' . $grossAmount . $serverKey);

        $response = $this->postJson('/api/payment/webhook', [
            'order_id' => $order->invoice_number,
            'status_code' => '200',
            'gross_amount' => $grossAmount,
            'transaction_status' => 'expire',
            'signature_key' => $validSignature,
        ]);

        $response->assertStatus(200);
        $this->assertEquals('cancelled', $order->fresh()->payment_status);
        // Stok harus bertambah 2 kembali menjadi 12
        $this->assertEquals(12, $product->fresh()->stock);
    }

    /**
     * Test IDOR: Member tidak bisa melihat status, mensimulasikan bayar, atau membatalkan pesanan milik member lain.
     */
    public function test_member_cannot_access_or_cancel_other_members_order_idor(): void
    {
        $userA = User::factory()->create(['role' => 'member']);
        $memberA = Member::create([
            'user_id' => $userA->id,
            'member_code' => 'MBR-0001',
            'join_date' => now(),
            'expire_date' => now()->addMonth(),
            'status' => 'active',
        ]);

        $userB = User::factory()->create(['role' => 'member']);
        $memberB = Member::create([
            'user_id' => $userB->id,
            'member_code' => 'MBR-0002',
            'join_date' => now(),
            'expire_date' => now()->addMonth(),
            'status' => 'active',
        ]);

        $orderOfB = Order::create([
            'invoice_number' => 'MBR-260907-9905',
            'member_id' => $memberB->id,
            'customer_name' => 'Member B',
            'total_amount' => 50000,
            'subtotal' => 50000,
            'cost_total' => 30000,
            'payment_method' => 'qris',
            'payment_status' => 'pending',
            'pickup_status' => 'ready_for_pickup',
        ]);

        // Login sebagai Member A dan coba manipulasi pesanan Member B
        $responseStatus = $this->actingAs($userA)->getJson("/member/store/orders/{$orderOfB->id}/status");
        $responseStatus->assertStatus(403);

        $responseCancel = $this->actingAs($userA)->postJson("/member/store/orders/{$orderOfB->id}/cancel");
        $responseCancel->assertStatus(403);

        $responseSimulate = $this->actingAs($userA)->postJson("/member/store/orders/{$orderOfB->id}/simulate");
        $responseSimulate->assertStatus(403);
    }

    /**
     * Test integritas EMVCo CRC-16 Checksum pada QrisService.
     */
    public function test_qris_service_emvco_crc16_integrity(): void
    {
        $order = Order::create([
            'invoice_number' => 'MBR-260907-9906',
            'customer_name' => 'Budi Santoso',
            'total_amount' => 15000,
            'subtotal' => 15000,
            'cost_total' => 10000,
            'payment_method' => 'qris',
            'payment_status' => 'pending',
            'pickup_status' => 'ready_for_pickup',
        ]);

        $qrisData = QrisService::generate($order);

        $this->assertNotEmpty($qrisData['qr_string']);
        $this->assertNotEmpty($qrisData['signature']);
        $this->assertNotEmpty($qrisData['crc16']);

        // Verifikasi bahwa checksum pada payload QRIS 100% valid secara matematis
        $isValidChecksum = QrisService::verifyPayloadChecksum($qrisData['qr_string']);
        $this->assertTrue($isValidChecksum);

        // Jika string QRIS diubah 1 karakter saja oleh peretas (misal nominal diubah), checksum harus gagal
        $tamperedPayload = substr($qrisData['qr_string'], 0, 20) . '9' . substr($qrisData['qr_string'], 21);
        $this->assertFalse(QrisService::verifyPayloadChecksum($tamperedPayload));
    }

    /**
     * Test bahwa mode simulasi pembayaran otomatis DITOLAK (403) di mode Production.
     */
    public function test_simulate_qris_blocked_in_production(): void
    {
        Config::set('services.midtrans.is_production', true);

        $user = User::factory()->create(['role' => 'member']);
        $member = Member::create([
            'user_id' => $user->id,
            'member_code' => 'MBR-0003',
            'join_date' => now(),
            'expire_date' => now()->addMonth(),
            'status' => 'active',
        ]);

        $order = Order::create([
            'invoice_number' => 'MBR-260907-9907',
            'member_id' => $member->id,
            'customer_name' => 'Member Test',
            'total_amount' => 50000,
            'subtotal' => 50000,
            'cost_total' => 30000,
            'payment_method' => 'qris',
            'payment_status' => 'pending',
            'pickup_status' => 'ready_for_pickup',
        ]);

        $response = $this->actingAs($user)->postJson("/member/store/orders/{$order->id}/simulate");
        $response->assertStatus(403);
        $response->assertJson(['success' => false]);
        $this->assertEquals('pending', $order->fresh()->payment_status);
    }
}
