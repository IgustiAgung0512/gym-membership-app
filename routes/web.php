<?php

use App\Http\Controllers\Admin\AttendanceController;
use App\Http\Controllers\Admin\DashboardController as AdminDashboardController;
use App\Http\Controllers\Admin\MemberController;
use App\Http\Controllers\Admin\PackageController;
use App\Http\Controllers\Admin\ReportController;
use App\Http\Controllers\Admin\RfidController;
use App\Http\Controllers\Admin\WhatsappLogController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Member\DashboardController as MemberDashboardController;
use App\Http\Controllers\Member\PasswordController as MemberPasswordController;
use App\Http\Controllers\Member\PhotoController as MemberPhotoController;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    if (Auth::check()) {
        return redirect(Auth::user()->isAdmin() ? route('admin.dashboard') : route('member.dashboard'));
    }
    return redirect()->route('login');
});

Route::middleware('guest')->group(function () {
    Route::get('/login', [LoginController::class, 'create'])->name('login');
    Route::post('/login', [LoginController::class, 'store']);
});

Route::post('/logout', [LoginController::class, 'destroy'])->middleware('auth')->name('logout');

Route::middleware(['auth', 'role:admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/dashboard', [AdminDashboardController::class, 'index'])->name('dashboard');

    Route::get('/members', [MemberController::class, 'index'])->name('members.index');
    Route::get('/members/create', [MemberController::class, 'create'])->name('members.create');
    Route::post('/members', [MemberController::class, 'store'])->name('members.store');
    Route::get('/members/{member}/photo', [MemberController::class, 'photo'])->name('members.photo');
    Route::get('/members/{member}/edit', [MemberController::class, 'edit'])->name('members.edit');
    Route::put('/members/{member}', [MemberController::class, 'update'])->name('members.update');
    Route::delete('/members/{member}', [MemberController::class, 'destroy'])->name('members.destroy');
    Route::post('/members/{member}/renew', [MemberController::class, 'renew'])->name('members.renew');
    Route::post('/members/{member}/reset-password', [MemberController::class, 'resetPassword'])->name('members.reset-password');

    Route::get('/packages', [PackageController::class, 'index'])->name('packages.index');
    Route::post('/packages', [PackageController::class, 'store'])->name('packages.store');
    Route::put('/packages/{package}', [PackageController::class, 'update'])->name('packages.update');
    Route::delete('/packages/{package}', [PackageController::class, 'destroy'])->name('packages.destroy');

    Route::get('/rfid', [RfidController::class, 'index'])->name('rfid.index');
    Route::post('/rfid', [RfidController::class, 'store'])->name('rfid.store');
    Route::post('/rfid/{card}/toggle-block', [RfidController::class, 'toggleBlock'])->name('rfid.toggle-block');
    Route::delete('/rfid/{card}', [RfidController::class, 'destroy'])->name('rfid.destroy');

    Route::get('/attendance', [AttendanceController::class, 'index'])->name('attendance.index');
    Route::post('/attendance/manual', [AttendanceController::class, 'storeManual'])->name('attendance.manual');

    Route::get('/whatsapp-logs', [WhatsappLogController::class, 'index'])->name('whatsapp.index');

    Route::prefix('reports')->name('reports.')->group(function () {
        Route::get('/', [ReportController::class, 'index'])->name('index');
        Route::get('/members/pdf', [ReportController::class, 'membersPdf'])->name('members.pdf');
        Route::get('/attendance/pdf', [ReportController::class, 'attendancePdf'])->name('attendance.pdf');
        Route::get('/revenue/pdf', [ReportController::class, 'revenuePdf'])->name('revenue.pdf');
        Route::get('/expiring/pdf', [ReportController::class, 'expiringPdf'])->name('expiring.pdf');
        Route::get('/whatsapp/pdf', [ReportController::class, 'whatsappPdf'])->name('whatsapp.pdf');
    });

    Route::get(
        '/dashboard/latest-rfid-checkin',
        [AdminDashboardController::class, 'latestRfidCheckin']
    )->name('dashboard.latest-rfid-checkin');
});

Route::middleware(['auth', 'role:member', 'force-password-change'])->prefix('member')->name('member.')->group(function () {
    Route::get('/dashboard', [MemberDashboardController::class, 'index'])->name('dashboard');
    Route::get('/photo', [MemberPhotoController::class, 'show'])->name('photo.show');
    Route::post('/photo', [MemberPhotoController::class, 'update'])->name('photo.update');
    Route::get('/password', [MemberPasswordController::class, 'edit'])->name('password.edit');
    Route::put('/password', [MemberPasswordController::class, 'update'])->name('password.update');
});

Route::get('/members/latest-rfid', [MemberController::class, 'latestRfid'])
    ->name('admin.members.latest-rfid');

Route::get('/admin/members/check-rfid', 
    [MemberController::class, 'checkRfid']
)->name('admin.members.check-rfid');


