<?php

use App\Http\Controllers\Api\RfidScanController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\MemberController;

// Dipanggil oleh alat/perangkat RFID reader (ESP32 dsb), bukan oleh browser.
// Amankan dengan header X-Device-Key (lihat RFID_DEVICE_KEY di .env).
Route::post('/rfid/scan', [RfidScanController::class, 'scan']);
Route::post('/members/add', [MemberController::class, 'store']);
