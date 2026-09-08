<?php

// Pastikan direktori storage di /tmp sudah dibuat untuk Vercel Serverless
$storagePaths = [
    '/tmp/storage',
    '/tmp/storage/app',
    '/tmp/storage/app/public',
    '/tmp/storage/framework',
    '/tmp/storage/framework/cache',
    '/tmp/storage/framework/cache/data',
    '/tmp/storage/framework/sessions',
    '/tmp/storage/framework/views',
    '/tmp/storage/logs',
];

foreach ($storagePaths as $path) {
    if (!is_dir($path)) {
        mkdir($path, 0777, true);
    }
}

// Redirect cache dan views Laravel ke /tmp (karena filesystem Vercel read-only)
putenv('VIEW_COMPILED_PATH=/tmp/storage/framework/views');
putenv('APP_CONFIG_CACHE=/tmp/storage/framework/config.php');
putenv('APP_SERVICES_CACHE=/tmp/storage/framework/services.php');
putenv('APP_PACKAGES_CACHE=/tmp/storage/framework/packages.php');
putenv('APP_ROUTES_CACHE=/tmp/storage/framework/routes.php');
putenv('APP_EVENTS_CACHE=/tmp/storage/framework/events.php');

// Forward request ke public/index.php Laravel
require __DIR__ . '/../public/index.php';
