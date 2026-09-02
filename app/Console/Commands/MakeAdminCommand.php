<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class MakeAdminCommand extends Command
{
    /**
     * Contoh pakai:
     *   php artisan make:admin
     *   php artisan make:admin --name="Admin GYM" --email=admin@gym.test --password=rahasia123
     *
     * Kalau email sudah ada, akun tersebut akan di-upgrade jadi admin & password direset
     * (aman dijalankan berkali-kali, tidak menghapus data lain).
     */
    protected $signature = 'make:admin {--name=} {--email=} {--password=}';

    protected $description = 'Buat akun admin baru, atau jadikan akun yang sudah ada sebagai admin, tanpa perlu reset database';

    public function handle(): int
    {
        $name = $this->option('name') ?: $this->ask('Nama admin');
        $email = $this->option('email') ?: $this->ask('Email admin');
        $password = $this->option('password') ?: $this->secret('Password admin (min. 6 karakter)');

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
                'password' => Hash::make($password),
                'role' => 'admin',
                'must_change_password' => false,
            ]
        );

        $this->info("Akun admin siap dipakai:");
        $this->line("  Email    : {$user->email}");
        $this->line("  Password : (sesuai yang barusan kamu masukkan)");

        return self::SUCCESS;
    }
}
