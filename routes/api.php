<?php

use App\Http\Controllers\Api\MemberController;
use App\Http\Controllers\Api\PaymentWebhookController;
use App\Http\Controllers\Api\RfidScanController;
use Illuminate\Support\Facades\Route;

// Dipanggil oleh alat/perangkat RFID reader (ESP32 dsb).
// Dilindungi dengan rate limiter 120 req/menit untuk mencegah flooding/DoS.
Route::middleware('throttle:api')->group(function () {
    Route::post('/rfid/scan', [RfidScanController::class, 'scan']);
    Route::post('/members/add', [MemberController::class, 'store']);
    Route::post('/scan_uid', [MemberController::class, 'store']);
});

// Endpoint Webhook Payment Gateway (Midtrans dsb)
// Dilindungi dengan rate limiter 60 req/menit & verifikasi tanda tangan kriptografis SHA-512
Route::middleware('throttle:webhook')->group(function () {
    Route::post('/payment/webhook', [PaymentWebhookController::class, 'handle'])->name('api.payment.webhook');
    Route::post('/midtrans/notification', [PaymentWebhookController::class, 'handle'])->name('api.midtrans.notification');
});
