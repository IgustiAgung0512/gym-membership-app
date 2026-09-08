<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class MakeCashierCommand extends Command
{
    /**
     * Contoh pakai:
     *   php artisan make:cashier
     *   php artisan make:cashier --name="Staff Kasir" --email=kasir@gym.test --phone=081234567899 --password=password123
     *
     * Kalau email sudah ada, akun tersebut akan di-update menjadi cashier & password direset.
     */
    protected $signature = 'make:cashier {--name=} {--email=} {--phone=} {--password=}';

    protected $description = 'Buat akun kasir front-desk baru, atau jadikan akun yang sudah ada sebagai kasir, tanpa perlu reset database';

    public function handle(): int
    {
        $name = $this->option('name') ?: $this->ask('Nama kasir');
        $email = $this->option('email') ?: $this->ask('Email kasir');
        $phone = $this->option('phone') ?: $this->ask('No. WhatsApp / HP kasir (opsional)');
        $password = $this->option('password') ?: $this->secret('Password kasir (min. 6 karakter)');

        $validator = Validator::make(
            compact('name', 'email', 'password'),
            [
                'name' => ['required', 'string', 'max:255'],
                'email' => ['required', 'email'],
                'password' => ['required', 'string', 'min:6'],
            ]
        );

        if ($validator->fails()) {
            foreach ($validator->errors()->all() as $error) {
                $this->error($error);
            }

            return self::FAILURE;
        }

        $user = User::updateOrCreate(
            ['email' => $email],
            [
                'name' => $name,
                'phone' => $phone ?: null,
                'password' => Hash::make($password),
                'role' => 'cashier',
                'must_change_password' => false,
            ]
        );

        $this->info("✅ Akun kasir berhasil dibuat & siap dipakai:");
        $this->line("  Nama     : {$user->name}");
        $this->line("  Email    : {$user->email}");
        $this->line("  Role     : kasir (cashier)");
        $this->line("  Password : (sesuai yang barusan Anda masukkan)");
        $this->line("");
        $this->line("Silakan login di http://127.0.0.1:8000/login untuk mengakses Terminal Kasir POS.");

        return self::SUCCESS;
    }
}
