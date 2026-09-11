<?php

namespace Tests\Feature;

use App\Models\Member;
use App\Models\MembershipPackage;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class MemberPhotoAndSecurityUiTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test tampilan form ganti password member memiliki petunjuk dan validasi minlength 8 karakter.
     */
    public function test_member_password_form_shows_8_characters_hint_and_enforces_rule(): void
    {
        $user = User::factory()->create([
            'role' => 'member',
            'must_change_password' => true,
        ]);

        $response = $this->actingAs($user)->get(route('member.password.edit'));
        $response->assertStatus(200);
        $response->assertSee('Minimal 8 karakter.');
        $response->assertSee('minlength="8"', false);
        $response->assertDontSee('Minimal 6 karakter.');

        // Test password kurang dari 8 karakter ditolak
        $invalidSubmit = $this->actingAs($user)->put(route('member.password.update'), [
            'password' => '123456',
            'password_confirmation' => '123456',
        ]);
        $invalidSubmit->assertSessionHasErrors('password');
        $this->assertEquals('Password baru minimal harus 8 karakter.', session('errors')->first('password'));

        // Test password 8 karakter diterima
        $validSubmit = $this->actingAs($user)->put(route('member.password.update'), [
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);
        $validSubmit->assertRedirect(route('member.dashboard'));
        $this->assertFalse($user->fresh()->must_change_password);
    }

    /**
     * Test kasir dapat melihat foto member langsung via streaming route.
     */
    public function test_cashier_can_access_member_photo_streaming(): void
    {
        Storage::fake('public');

        $cashier = User::factory()->create(['role' => 'cashier']);
        $memberUser = User::factory()->create(['role' => 'member']);
        $package = MembershipPackage::create([
            'name' => 'Paket Bulanan',
            'duration_months' => 1,
            'price' => 150000,
            'is_active' => true,
        ]);

        // Simpan fake file foto di storage disk
        $path = 'members/photos/avatar.jpg';
        Storage::disk('public')->put($path, 'fake-image-content-bytes');

        $member = Member::create([
            'user_id' => $memberUser->id,
            'membership_package_id' => $package->id,
            'member_code' => 'GYM-2609-9999',
            'status' => 'active',
            'join_date' => now(),
            'expire_date' => now()->addMonth(),
            'photo' => $path,
        ]);

        // Halaman list member kasir harus memuat URL route foto kasir
        $indexResponse = $this->actingAs($cashier)->get(route('cashier.members.index'));
        $indexResponse->assertStatus(200);
        $indexResponse->assertSee(route('cashier.members.photo', $member));

        // Kasir dapat mengambil gambar via streaming endpoint
        $photoResponse = $this->actingAs($cashier)->get(route('cashier.members.photo', $member));
        $photoResponse->assertStatus(200);

        // Guest tidak dapat mengakses foto member
        $this->post('/logout');
        $guestResponse = $this->get(route('cashier.members.photo', $member));
        $guestResponse->assertRedirect('/login');
    }

    /**
     * Test tampilan member dashboard memuat check-in & check-out pada kartu hitam dan endpoint polling berjalan.
     */
    public function test_member_dashboard_displays_checkin_and_checkout_with_polling(): void
    {
        $memberUser = User::factory()->create(['role' => 'member']);
        $package = MembershipPackage::create([
            'name' => 'Paket Bulanan',
            'duration_months' => 1,
            'price' => 150000,
            'is_active' => true,
        ]);

        $member = Member::create([
            'user_id' => $memberUser->id,
            'membership_package_id' => $package->id,
            'member_code' => 'GYM-2609-0001',
            'status' => 'active',
            'join_date' => now(),
            'expire_date' => now()->addMonth(),
        ]);

        // Simpan riwayat absensi check-in hari ini (sedang latihan)
        $attendance = \App\Models\Attendance::create([
            'member_id' => $member->id,
            'check_in_at' => now()->subMinutes(30),
            'method' => 'rfid',
        ]);

        $response = $this->actingAs($memberUser)->get(route('member.dashboard'));
        $response->assertStatus(200);
        $response->assertSee('Check-In');
        $response->assertSee('Check-Out');
        $response->assertSee('Sedang Latihan');

        // Test endpoint polling kehadiran
        $pollResponse = $this->actingAs($memberUser)->get(route('member.latest-attendance'));
        $pollResponse->assertStatus(200);
        $pollResponse->assertJson([
            'success' => true,
            'has_attendance' => true,
            'is_today' => true,
            'is_training' => true,
        ]);
    }

    /**
     * Test admin dashboard latest rfid polling response menyertakan recent_checkins dan today_checkins untuk auto-refresh.
     */
    public function test_admin_dashboard_latest_rfid_returns_recent_checkins_for_auto_refresh(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $memberUser = User::factory()->create(['role' => 'member']);
        $package = MembershipPackage::create([
            'name' => 'Paket Bulanan',
            'duration_months' => 1,
            'price' => 150000,
            'is_active' => true,
        ]);

        $member = Member::create([
            'user_id' => $memberUser->id,
            'membership_package_id' => $package->id,
            'member_code' => 'GYM-2609-0002',
            'status' => 'active',
            'join_date' => now(),
            'expire_date' => now()->addMonth(),
        ]);

        \App\Models\Attendance::create([
            'member_id' => $member->id,
            'check_in_at' => now()->subMinutes(15),
            'method' => 'manual',
        ]);

        $response = $this->actingAs($admin)->get(route('admin.dashboard.latest-rfid-checkin'));
        $response->assertStatus(200);
        $response->assertJsonStructure([
            'exists',
            'recent_checkins',
            'today_checkins',
        ]);
        $this->assertEquals(1, $response->json('today_checkins'));
        $this->assertNotEmpty($response->json('recent_checkins'));
    }
}
