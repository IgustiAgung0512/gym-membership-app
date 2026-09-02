<?php

return [

    'postmark' => [
        'token' => env('POSTMARK_TOKEN'),
    ],

    'resend' => [
        'key' => env('RESEND_KEY'),
    ],

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

    'slack' => [
        'notifications' => [
            'bot_user_oauth_token' => env('SLACK_BOT_USER_OAUTH_TOKEN'),
            'channel' => env('SLACK_BOT_USER_DEFAULT_CHANNEL'),
        ],
    ],

    // Gateway WhatsApp (default Fonnte: https://fonnte.com). Ganti url bila pakai provider lain.
    'whatsapp' => [
        'url' => env('WHATSAPP_API_URL', 'https://api.fonnte.com/send'),
        'token' => env('WHATSAPP_API_TOKEN'),
    ],

    // Kunci sederhana yang dikirim alat RFID lewat header X-Device-Key agar endpoint /api/rfid/scan
    // tidak bisa dipanggil sembarang orang. Kosongkan untuk menonaktifkan pengecekan (tidak disarankan).
    'rfid' => [
        'device_key' => env('RFID_DEVICE_KEY'),
    ],

];
