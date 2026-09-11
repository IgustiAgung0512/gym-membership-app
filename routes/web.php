<?php

use App\Http\Controllers\Admin\AttendanceController;
use App\Http\Controllers\Admin\DashboardController as AdminDashboardController;
use App\Http\Controllers\Admin\MemberController;
use App\Http\Controllers\Admin\OrderController;
use App\Http\Controllers\Admin\PackageController;
use App\Http\Controllers\Admin\PosController;
use App\Http\Controllers\Admin\ProductController;
use App\Http\Controllers\Admin\ReportController;
use App\Http\Controllers\Admin\RfidController;
use App\Http\Controllers\Admin\AdminUserController;
use App\Http\Controllers\Admin\WhatsappLogController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Cashier\AttendanceController as CashierAttendanceController;
use App\Http\Controllers\Cashier\MemberController as CashierMemberController;
use App\Http\Controllers\Cashier\OrderController as CashierOrderController;
use App\Http\Controllers\Cashier\PosController as CashierPosController;
use App\Http\Controllers\Member\DashboardController as MemberDashboardController;
use App\Http\Controllers\Member\PasswordController as MemberPasswordController;
use App\Http\Controllers\Member\PhotoController as MemberPhotoController;
use App\Http\Controllers\Member\StoreController as MemberStoreController;
use App\Http\Controllers\OnlineRegistrationController;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    $packages = collect();
    try {
        $packages = \App\Models\MembershipPackage::where('is_active', true)->orderBy('price', 'asc')->get();
    } catch (\Throwable $e) {
        $packages = collect();
    }

    return view('welcome', compact('packages'));
})->name('home');

// --- ONLINE MEMBER REGISTRATION (LANDING PAGE CHECKOUT) ---
Route::prefix('register/online')->name('register.online.')->group(function () {
    Route::post('/initiate', [OnlineRegistrationController::class, 'initiate'])->name('initiate')->middleware('throttle:checkout');
    Route::get('/{invoice}/status', [OnlineRegistrationController::class, 'status'])->name('status');
    Route::post('/{invoice}/simulate', [OnlineRegistrationController::class, 'simulate'])->name('simulate');
    Route::post('/{invoice}/cancel', [OnlineRegistrationController::class, 'cancel'])->name('cancel');
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
    Route::post('/members/{member}/assign-rfid', [MemberController::class, 'assignRfid'])->name('members.assign-rfid');

    Route::get('/packages', [PackageController::class, 'index'])->name('packages.index');
    Route::post('/packages', [PackageController::class, 'store'])->name('packages.store');
    Route::put('/packages/{package}', [PackageController::class, 'update'])->name('packages.update');
    Route::delete('/packages/{package}', [PackageController::class, 'destroy'])->name('packages.destroy');

    Route::get('/rfid', [RfidController::class, 'index'])->name('rfid.index');
    Route::get('/rfid/latest', [RfidController::class, 'latest'])->name('rfid.latest');
    Route::get('/rfid/check', [RfidController::class, 'check'])->name('rfid.check');
    Route::post('/rfid', [RfidController::class, 'store'])->name('rfid.store');
    Route::post('/rfid/{card}/toggle-block', [RfidController::class, 'toggleBlock'])->name('rfid.toggle-block');
    Route::delete('/rfid/{card}', [RfidController::class, 'destroy'])->name('rfid.destroy');

    Route::get('/attendance', [AttendanceController::class, 'index'])->name('attendance.index');
    Route::post('/attendance/manual', [AttendanceController::class, 'storeManual'])->name('attendance.manual');

    Route::get('/whatsapp-logs', [WhatsappLogController::class, 'index'])->name('whatsapp.index');
    Route::delete('/whatsapp-logs/clear-sent', [WhatsappLogController::class, 'clearSent'])->name('whatsapp.clear-sent');
    Route::delete('/whatsapp-logs/clear-all', [WhatsappLogController::class, 'clearAll'])->name('whatsapp.clear-all');
    Route::delete('/whatsapp-logs/{log}', [WhatsappLogController::class, 'destroy'])->name('whatsapp.destroy');

    // --- GYM STORE & POS ---
    Route::get('/pos', [PosController::class, 'index'])->name('pos.index');
    Route::post('/pos/checkout', [PosController::class, 'checkout'])->name('pos.checkout');
    Route::get('/pos/receipt/{order}', [PosController::class, 'receipt'])->name('pos.receipt');
    Route::get('/pos/orders/{order}/status', [PosController::class, 'orderStatus'])->name('pos.order-status');
    Route::post('/pos/orders/{order}/simulate-qris', [PosController::class, 'simulateQris'])->name('pos.simulate-qris');
    Route::post('/pos/orders/{order}/cancel', [PosController::class, 'cancelOrder'])->name('pos.cancel-order');
    Route::get('/pos/member-pickups', [PosController::class, 'getMemberPickups'])->name('pos.member-pickups');
    Route::post('/pos/orders/{order}/pickup', [PosController::class, 'markAsPickedUp'])->name('pos.mark-picked-up');

    Route::resource('products', ProductController::class)->except(['create', 'show', 'edit']);
    Route::post('/products/{product}/restock', [ProductController::class, 'restock'])->name('products.restock');

    Route::get('/orders', [OrderController::class, 'index'])->name('orders.index');
    Route::get('/orders/{order}', [OrderController::class, 'show'])->name('orders.show');
    Route::delete('/orders/{order}', [OrderController::class, 'destroy'])->name('orders.destroy');

    Route::prefix('reports')->name('reports.')->group(function () {
        Route::get('/', [ReportController::class, 'index'])->name('index');
        Route::get('/members/pdf', [ReportController::class, 'membersPdf'])->name('members.pdf');
        Route::get('/attendance/pdf', [ReportController::class, 'attendancePdf'])->name('attendance.pdf');
        Route::get('/revenue/pdf', [ReportController::class, 'revenuePdf'])->name('revenue.pdf');
        Route::get('/orders/pdf', [ReportController::class, 'ordersPdf'])->name('orders.pdf');
        Route::get('/expiring/pdf', [ReportController::class, 'expiringPdf'])->name('expiring.pdf');
        Route::get('/whatsapp/pdf', [ReportController::class, 'whatsappPdf'])->name('whatsapp.pdf');
    });

    Route::get(
        '/dashboard/latest-rfid-checkin',
        [AdminDashboardController::class, 'latestRfidCheckin']
    )->name('dashboard.latest-rfid-checkin');

    // --- USER MANAGEMENT ---
    Route::prefix('user-management')->name('user-management.')->group(function () {
        Route::get('/', [AdminUserController::class, 'index'])->name('index');
        Route::post('/admin', [AdminUserController::class, 'storeAdmin'])->name('admin.store');
        Route::post('/cashier', [AdminUserController::class, 'storeCashier'])->name('cashier.store');
        Route::post('/{user}/password', [AdminUserController::class, 'changePassword'])->name('change-password');
        Route::delete('/admin/{user}', [AdminUserController::class, 'destroyAdmin'])->name('admin.destroy');
        Route::delete('/cashier/{user}', [AdminUserController::class, 'destroyCashier'])->name('cashier.destroy');
    });

    // --- SYSTEM MIGRATIONS RUNNER ---
    Route::get('/system/migrate', function () {
        try {
            \Illuminate\Support\Facades\Artisan::call('migrate', ['--force' => true]);
            $output = \Illuminate\Support\Facades\Artisan::output();
            return response()->json([
                'success' => true,
                'message' => 'Migrasi database berhasil dijalankan!',
                'output'  => $output,
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal menjalankan migrasi: ' . $e->getMessage(),
            ], 500);
        }
    })->name('system.migrate');
});

// --- CASHIER WORKSTATION (Direct entry to POS) ---
Route::middleware(['auth', 'role:cashier,admin'])->prefix('cashier')->name('cashier.')->group(function () {
    Route::get('/', [CashierPosController::class, 'index'])->name('dashboard'); // Direct entry into POS!
    Route::get('/pos', [CashierPosController::class, 'index'])->name('pos.index');
    Route::post('/pos/checkout', [CashierPosController::class, 'checkout'])->name('pos.checkout');
    Route::get('/pos/receipt/{order}', [CashierPosController::class, 'receipt'])->name('pos.receipt');
    Route::get('/pos/orders/{order}/status', [CashierPosController::class, 'orderStatus'])->name('pos.order-status');
    Route::post('/pos/orders/{order}/simulate-qris', [CashierPosController::class, 'simulateQris'])->name('pos.simulate-qris');
    Route::post('/pos/orders/{order}/cancel', [CashierPosController::class, 'cancelOrder'])->name('pos.cancel-order');
    Route::get('/pos/member-pickups', [CashierPosController::class, 'getMemberPickups'])->name('pos.member-pickups');
    Route::post('/pos/orders/{order}/pickup', [CashierPosController::class, 'markAsPickedUp'])->name('pos.mark-picked-up');

    Route::get('/orders', [CashierOrderController::class, 'index'])->name('orders.index');
    Route::get('/orders/{order}', [CashierOrderController::class, 'show'])->name('orders.show');

    Route::get('/members', [CashierMemberController::class, 'index'])->name('members.index');
    Route::get('/members/create', [CashierMemberController::class, 'create'])->name('members.create');
    Route::post('/members', [CashierMemberController::class, 'store'])->name('members.store');
    Route::get('/members/{member}/photo', [CashierMemberController::class, 'photo'])->name('members.photo');
    Route::post('/members/{member}/renew', [CashierMemberController::class, 'renew'])->name('members.renew');
    Route::post('/members/{member}/assign-rfid', [CashierMemberController::class, 'assignRfid'])->name('members.assign-rfid');

    Route::get('/attendance', [CashierAttendanceController::class, 'index'])->name('attendance.index');
    Route::post('/attendance/manual', [CashierAttendanceController::class, 'storeManual'])->name('attendance.manual');
});

Route::middleware(['auth', 'role:member', 'force-password-change'])->prefix('member')->name('member.')->group(function () {
    Route::get('/dashboard', [MemberDashboardController::class, 'index'])->name('dashboard');
    Route::get('/latest-attendance', [MemberDashboardController::class, 'latestAttendance'])->name('latest-attendance');
    Route::post('/renew', [MemberDashboardController::class, 'renew'])->name('renew');
    Route::post('/renew/initiate', [MemberDashboardController::class, 'initiateRenewal'])->name('renew.initiate')->middleware('throttle:checkout');
    Route::get('/renew/{payment}/status', [MemberDashboardController::class, 'renewalStatus'])->name('renew.status');
    Route::post('/renew/{payment}/simulate', [MemberDashboardController::class, 'simulateRenewal'])->name('renew.simulate');
    Route::post('/renew/{payment}/cancel', [MemberDashboardController::class, 'cancelRenewal'])->name('renew.cancel');
    Route::get('/store', [MemberStoreController::class, 'index'])->name('store.index');
    Route::post('/store/checkout', [MemberStoreController::class, 'checkout'])->name('store.checkout')->middleware('throttle:checkout');
    Route::get('/store/orders/{order}/status', [MemberStoreController::class, 'orderStatus'])->name('store.order-status');
    Route::post('/store/orders/{order}/simulate', [MemberStoreController::class, 'simulateQris'])->name('store.simulate-qris');
    Route::post('/store/orders/{order}/cancel', [MemberStoreController::class, 'cancelOrder'])->name('store.cancel-order');
    Route::get('/store/receipt/{order}', [MemberStoreController::class, 'receipt'])->name('store.receipt');
    Route::get('/store/my-orders', [MemberStoreController::class, 'myOrders'])->name('store.my-orders');
    Route::get('/photo', [MemberPhotoController::class, 'show'])->name('photo.show');
    Route::post('/photo', [MemberPhotoController::class, 'update'])->name('photo.update');
    Route::get('/password', [MemberPasswordController::class, 'edit'])->name('password.edit');
    Route::put('/password', [MemberPasswordController::class, 'update'])->name('password.update');
});

// Helper AJAX RFID untuk pendaftaran/edit member (hanya bisa diakses oleh Admin & Kasir yang login)
Route::middleware(['auth', 'role:admin,cashier'])->group(function () {
    Route::get('/members/latest-rfid', [MemberController::class, 'latestRfid'])
        ->name('admin.members.latest-rfid');

    Route::get('/admin/members/check-rfid', 
        [MemberController::class, 'checkRfid']
    )->name('admin.members.check-rfid');
});

// Endpoint hardware reader fallback (jika alat ESP32/Arduino memanggil URL langsung tanpa /api)
Route::middleware('throttle:api')->group(function () {
    Route::match(['get', 'post'], '/rfid/scan', [\App\Http\Controllers\Api\RfidScanController::class, 'scan']);
    Route::match(['get', 'post'], '/scan_uid', [\App\Http\Controllers\Api\MemberController::class, 'store']);
    Route::match(['get', 'post'], '/members/add', [\App\Http\Controllers\Api\MemberController::class, 'store']);
    Route::match(['get', 'post'], '/scan', [\App\Http\Controllers\Api\RfidScanController::class, 'scan']);
    Route::match(['get', 'post'], '/v1/rfid/scan', [\App\Http\Controllers\Api\RfidScanController::class, 'scan']);
    Route::match(['get', 'post'], '/v1/scan_uid', [\App\Http\Controllers\Api\MemberController::class, 'store']);
    Route::match(['get', 'post'], '/v1/members/add', [\App\Http\Controllers\Api\MemberController::class, 'store']);
});



