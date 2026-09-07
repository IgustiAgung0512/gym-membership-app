<?php

namespace App\Providers;

use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Str;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Rate limiter untuk proteksi brute force login (maksimal 5 kali percobaan gagal per menit)
        RateLimiter::for('login', function (Request $request) {
            $throttleKey = Str::transliterate(Str::lower($request->input('email')) . '|' . $request->ip());
            return Limit::perMinute(5)->by($throttleKey);
        });

        // Rate limiter untuk API scan RFID (120 req/menit per IP: sangat aman untuk Arduino, menangkal DoS)
        RateLimiter::for('api', function (Request $request) {
            return Limit::perMinute(120)->by($request->ip());
        });

        // Rate limiter untuk pergantian password member
        RateLimiter::for('password-update', function (Request $request) {
            return Limit::perMinute(5)->by($request->user()?->id ?: $request->ip());
        });

        // Rate limiter untuk Webhook Notifikasi Pembayaran (60 req/menit per IP)
        RateLimiter::for('webhook', function (Request $request) {
            return Limit::perMinute(60)->by($request->ip());
        });

        // Rate limiter untuk Checkout Pesanan (15 transaksi/menit per user/IP)
        RateLimiter::for('checkout', function (Request $request) {
            return Limit::perMinute(15)->by($request->user()?->id ?: $request->ip());
        });
    }
}
