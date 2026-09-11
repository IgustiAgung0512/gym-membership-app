<?php

use App\Http\Middleware\ForcePasswordChange;
use App\Http\Middleware\RoleMiddleware;
use App\Http\Middleware\SecurityHeaders;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

$app = Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        apiPrefix: 'api',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->trustProxies(at: '*');
        $middleware->append(SecurityHeaders::class);

        // Kecualikan endpoint hardware RFID reader dan Webhook dari proteksi CSRF Token (agar tidak error 419 Page Expired)
        $middleware->validateCsrfTokens(except: [
            'rfid/scan',
            'api/rfid/scan',
            'scan_uid',
            'api/scan_uid',
            'members/add',
            'api/members/add',
            'scan',
            'api/scan',
            'v1/*',
            'api/*',
            'payment/webhook',
            'api/payment/webhook',
            'midtrans/notification',
            'api/midtrans/notification',
        ]);

        $middleware->alias([
            'role' => RoleMiddleware::class,
            'force-password-change' => ForcePasswordChange::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })->create();

if (isset($_ENV['VERCEL']) || isset($_SERVER['VERCEL']) || env('VERCEL')) {
    $app->useStoragePath('/tmp/storage');
    $app->useBootstrapPath('/tmp/bootstrap');
}

return $app;