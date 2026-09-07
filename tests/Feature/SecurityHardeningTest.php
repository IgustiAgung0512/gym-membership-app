<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SecurityHardeningTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test bahwa HTTP Security Headers aktif pada response web.
     */
    public function test_security_headers_are_present(): void
    {
        $response = $this->get('/');

        $response->assertStatus(200);
        $response->assertHeader('X-Frame-Options', 'SAMEORIGIN');
        $response->assertHeader('X-Content-Type-Options', 'nosniff');
        $response->assertHeader('Referrer-Policy', 'strict-origin-when-cross-origin');
    }

    /**
     * Test bahwa rute helper RFID admin terlindungi dan menolak request unauthenticated.
     */
    public function test_unauthenticated_user_cannot_access_rfid_helper_routes(): void
    {
        $response = $this->getJson('/members/latest-rfid');
        $response->assertStatus(401);

        $checkResponse = $this->getJson('/admin/members/check-rfid');
        $checkResponse->assertStatus(401);
    }

    /**
     * Test bahwa brute force login dibatasi (throttled) setelah 5 percobaan salah berturut-turut.
     */
    public function test_login_brute_force_protection(): void
    {
        for ($i = 0; $i < 5; $i++) {
            $this->post('/login', [
                'email' => 'attacker@example.com',
                'password' => 'wrong-password',
            ]);
        }

        // Percobaan ke-6 harus diblokir oleh rate limiter
        $response = $this->post('/login', [
            'email' => 'attacker@example.com',
            'password' => 'wrong-password',
        ]);

        $response->assertSessionHasErrors('email');
        $this->assertStringContainsString('Terlalu banyak percobaan login gagal', session('errors')->first('email'));
    }
}
