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

        $cashier = User::create([
            'name' => 'Staff Kasir Front Desk',
            'email' => 'kasir@gym.test',
            'phone' => '081234567899',
            'password' => Hash::make('password'),
            'role' => 'cashier',
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

        // --- SEED PRODUK GYM STORE ---
        $products = [
            [
                'name' => 'Whey Protein Isolate (1 Scoop)',
                'sku' => 'SUP-WHEY-01',
                'category' => 'supplements',
                'price' => 20000,
                'cost_price' => 12000,
                'stock' => 50,
                'min_stock_alert' => 10,
                'unit' => 'scoop',
                'image' => 'products/whey_protein.jpg',
                'description' => '25g protein murni, rendah lemak & gula. Diseduh dingin setelah latihan.',
            ],
            [
                'name' => 'Creatine Monohydrate (1 Scoop / 5g)',
                'sku' => 'SUP-CREA-01',
                'category' => 'supplements',
                'price' => 10000,
                'cost_price' => 5000,
                'stock' => 60,
                'min_stock_alert' => 15,
                'unit' => 'scoop',
                'image' => 'products/creatine.jpg',
                'description' => 'Meningkatkan tenaga eksplosif dan volume massa otot.',
            ],
            [
                'name' => 'Pre-Workout Energy Drink (Cup)',
                'sku' => 'SUP-PREW-01',
                'category' => 'supplements',
                'price' => 25000,
                'cost_price' => 15000,
                'stock' => 30,
                'min_stock_alert' => 5,
                'unit' => 'cup',
                'image' => 'products/pre_workout.jpg',
                'description' => 'Booster energi & fokus maksimal sebelum angkat beban berat.',
            ],
            [
                'name' => 'Air Mineral Dingin 600ml',
                'sku' => 'DRK-AQ600',
                'category' => 'drinks',
                'price' => 5000,
                'cost_price' => 2800,
                'stock' => 100,
                'min_stock_alert' => 20,
                'unit' => 'botol',
                'image' => 'products/air_mineral.jpg',
                'description' => 'Air mineral segar dingin untuk hidrasi selama berolahraga.',
            ],
            [
                'name' => 'Pocari Sweat Isotonik 500ml',
                'sku' => 'DRK-POC500',
                'category' => 'drinks',
                'price' => 10000,
                'cost_price' => 7000,
                'stock' => 45,
                'min_stock_alert' => 10,
                'unit' => 'botol',
                'image' => 'products/pocari_sweat.jpg',
                'description' => 'Mengganti ion tubuh dan cairan yang hilang melalui keringat.',
            ],
            [
                'name' => 'Hydro Coco Air Kelapa 330ml',
                'sku' => 'DRK-HYD330',
                'category' => 'drinks',
                'price' => 12000,
                'cost_price' => 8500,
                'stock' => 35,
                'min_stock_alert' => 8,
                'unit' => 'botol',
                'image' => 'products/hydro_coco.jpg',
                'description' => 'Elektrolit alami dari air kelapa asli tanpa pemanis buatan.',
            ],
            [
                'name' => 'Protein Bar Cokelat (Fitbar / L-Men)',
                'sku' => 'SNK-PBAR01',
                'category' => 'snacks',
                'price' => 18000,
                'cost_price' => 12000,
                'stock' => 40,
                'min_stock_alert' => 10,
                'unit' => 'pack',
                'image' => 'products/protein_bar.jpg',
                'description' => 'Camilan praktis tinggi protein & serat, pas untuk pengganjal lapar.',
            ],
            [
                'name' => 'Telur Rebus Organik (Pack isi 2)',
                'sku' => 'SNK-EGG02',
                'category' => 'snacks',
                'price' => 8000,
                'cost_price' => 4000,
                'stock' => 25,
                'min_stock_alert' => 5,
                'unit' => 'pack',
                'image' => 'products/boiled_eggs.jpg',
                'description' => 'Sumber protein alami 12g tanpa minyak, siap disantap langsung.',
            ],
            [
                'name' => 'Shaker Bottle GymPulse 700ml',
                'sku' => 'GER-SHK700',
                'category' => 'gear',
                'price' => 85000,
                'cost_price' => 45000,
                'stock' => 20,
                'min_stock_alert' => 5,
                'unit' => 'pcs',
                'image' => 'products/shaker.jpg',
                'description' => 'Botol shaker anti-bocor dengan mixing ball stainless steel.',
            ],
            [
                'name' => 'Handuk Olahraga Microfiber GymPulse',
                'sku' => 'GER-TWL01',
                'category' => 'gear',
                'price' => 45000,
                'cost_price' => 25000,
                'stock' => 25,
                'min_stock_alert' => 5,
                'unit' => 'pcs',
                'image' => 'products/gym_towel.jpg',
                'description' => 'Handuk cepat kering, daya serap tinggi & anti-bakteri.',
            ],
            [
                'name' => 'Lifting Straps Cotton (Sepasang)',
                'sku' => 'GER-STRP01',
                'category' => 'gear',
                'price' => 35000,
                'cost_price' => 18000,
                'stock' => 15,
                'min_stock_alert' => 3,
                'unit' => 'pasang',
                'image' => 'products/lifting_straps.jpg',
                'description' => 'Membantu cengkeraman saat deadlift & pull-up beban berat.',
            ],
        ];

        foreach ($products as $p) {
            \App\Models\Product::create($p);
        }

        $this->command->info('Admin login  : admin@gym.test / password');
        $this->command->info('Member login : budi@gym.test / password');
        $this->command->info('Products seeded successfully!');
    }
}
