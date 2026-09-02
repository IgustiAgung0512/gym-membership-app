<?php

use App\Models\Member;
use App\Services\WhatsAppService;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Tandai member yang sudah lewat expire_date, lalu kirim reminder H-3 setiap hari jam 9 pagi.
Schedule::call(function () {
    Member::where('status', 'active')
        ->whereNotNull('expire_date')
        ->where('expire_date', '<', now())
        ->update(['status' => 'expired']);

    Member::where('status', 'active')
        ->whereDate('expire_date', now()->addDays(3)->toDateString())
        ->each(fn ($member) => app(WhatsAppService::class)->sendExpiryReminder($member));
})->dailyAt('09:00');
