<?php

namespace Database\Seeders;

use App\Models\Attendance;
use App\Models\Member;
use App\Models\MembershipPackage;
use App\Models\RfidCard;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $admin = User::create([
            'name' => 'Admin GYM',
            'email' => 'admin@gym.test',
            'phone' => '081234567890',
            'password' => Hash::make('password'),
            'role' => 'admin',
        ]);

        $bulanan = MembershipPackage::create([
            'name' => 'Paket Bulanan',
            'duration_months' => 1,
            'price' => 150000,
            'description' => 'Akses penuh gym selama 1 bulan.',
        ]);

        $triwulan = MembershipPackage::create([
            'name' => 'Paket 3 Bulan',
            'duration_months' => 3,
            'price' => 400000,
            'description' => 'Hemat, akses penuh gym selama 3 bulan.',
        ]);

        $tahunan = MembershipPackage::create([
            'name' => 'Paket Tahunan',
            'duration_months' => 12,
            'price' => 1500000,
            'description' => 'Akses penuh gym selama 1 tahun + free konsultasi trainer.',
        ]);

        // Contoh member demo
        $demoUser = User::create([
            'name' => 'Budi Santoso',
            'email' => 'budi@gym.test',
            'phone' => '081298765432',
            'password' => Hash::make('password'),
            'role' => 'member',
        ]);

        $demoMember = Member::create([
            'user_id' => $demoUser->id,
            'membership_package_id' => $bulanan->id,
            'member_code' => 'GYM-' . now()->format('ym') . '-0001',
            'join_date' => now()->subDays(10),
            'expire_date' => now()->addDays(20),
            'status' => 'active',
        ]);

        $card = RfidCard::create([
            'uid' => 'DEMO1234',
            'member_id' => $demoMember->id,
            'status' => 'assigned',
            'assigned_at' => now(),
        ]);

        foreach (range(5, 0) as $daysAgo) {
            Attendance::create([
                'member_id' => $demoMember->id,
                'rfid_card_id' => $card->id,
                'method' => 'rfid',
                'check_in_at' => now()->subDays($daysAgo)->setTime(7, rand(0, 59)),
            ]);
        }

        $this->command->info('Admin login  : admin@gym.test / password');
        $this->command->info('Member login : budi@gym.test / password');
    }
}
