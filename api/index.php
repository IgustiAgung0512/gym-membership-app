<?php

// Tampilkan error jika ada masalah saat booting
ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');
error_reporting(E_ALL);

// Pastikan semua direktori temporary yang dibutuhkan Laravel di /tmp sudah dibuat
$storageDirs = [
    '/tmp/storage',
    '/tmp/storage/app',
    '/tmp/storage/app/public',
    '/tmp/storage/framework',
    '/tmp/storage/framework/cache',
    '/tmp/storage/framework/cache/data',
    '/tmp/storage/framework/sessions',
    '/tmp/storage/framework/views',
    '/tmp/storage/logs',
    '/tmp/bootstrap',
    '/tmp/bootstrap/cache',
];

foreach ($storageDirs as $dir) {
    if (!is_dir($dir)) {
        @mkdir($dir, 0777, true);
    }
}

// Sinkronisasi Environment Variables dari Vercel ke $_ENV dan putenv
foreach ($_SERVER as $key => $value) {
    if (is_string($value) && !isset($_ENV[$key])) {
        $_ENV[$key] = $value;
        putenv("{$key}={$value}");
    }
}

putenv('VERCEL=1');
$_ENV['VERCEL'] = '1';
$_SERVER['VERCEL'] = '1';

if (empty($_ENV['APP_DEBUG']) && empty($_SERVER['APP_DEBUG']) && !getenv('APP_DEBUG')) {
    putenv('APP_DEBUG=true');
    $_ENV['APP_DEBUG'] = 'true';
    $_SERVER['APP_DEBUG'] = 'true';
}

if (empty($_ENV['APP_KEY']) && empty($_SERVER['APP_KEY']) && !getenv('APP_KEY')) {
    putenv('APP_KEY=base64:9y5s5FaJvVAJg48uDyS3JYXJr+EWKrv2XPAeN4dDBgQ=');
    $_ENV['APP_KEY'] = 'base64:9y5s5FaJvVAJg48uDyS3JYXJr+EWKrv2XPAeN4dDBgQ=';
    $_SERVER['APP_KEY'] = 'base64:9y5s5FaJvVAJg48uDyS3JYXJr+EWKrv2XPAeN4dDBgQ=';
}

if (empty($_ENV['DB_CONNECTION']) && empty($_SERVER['DB_CONNECTION']) && !getenv('DB_CONNECTION')) {
    putenv('DB_CONNECTION=mysql');
    $_ENV['DB_CONNECTION'] = 'mysql';
    $_SERVER['DB_CONNECTION'] = 'mysql';
}

// Arahkan log ke stderr Vercel
putenv('LOG_CHANNEL=stderr');
$_ENV['LOG_CHANNEL'] = 'stderr';
$_SERVER['LOG_CHANNEL'] = 'stderr';

// Set cache paths ke /tmp
putenv('VIEW_COMPILED_PATH=/tmp/storage/framework/views');
putenv('APP_CONFIG_CACHE=/tmp/bootstrap/cache/config.php');
putenv('APP_SERVICES_CACHE=/tmp/bootstrap/cache/services.php');
putenv('APP_PACKAGES_CACHE=/tmp/bootstrap/cache/packages.php');
putenv('APP_ROUTES_CACHE=/tmp/bootstrap/cache/routes.php');
putenv('APP_EVENTS_CACHE=/tmp/bootstrap/cache/events.php');

// Forward request ke public/index.php Laravel dengan try-catch agar error detail langsung terlihat
try {
    require __DIR__ . '/../public/index.php';
} catch (\Throwable $e) {
    http_response_code(500);
    echo '<div style="font-family: monospace; padding: 20px; background: #fff5f5; color: #9b1c1c; border: 1px solid #f87171; border-radius: 8px; margin: 20px;">';
    echo '<h2 style="margin-top:0;">⚠️ Laravel Error pada Vercel</h2>';
    echo '<p><strong>Message:</strong> ' . htmlspecialchars($e->getMessage()) . '</p>';
    echo '<p><strong>File:</strong> ' . htmlspecialchars($e->getFile()) . ' (baris ' . $e->getLine() . ')</p>';
    echo '<pre style="background: #fee2e2; padding: 10px; overflow-x: auto; border-radius: 4px;">' . htmlspecialchars($e->getTraceAsString()) . '</pre>';
    echo '</div>';
}
