<?php

return [
    'orange_sms' => [
        'client_id' => env('ORANGE_SMS_CLIENT_ID'),
        'client_secret' => env('ORANGE_SMS_CLIENT_SECRET'),
        'sender_name' => env('ORANGE_SMS_SENDER_NAME', 'NujumAlHuda'),
        'sender_address' => env('ORANGE_SMS_SENDER_ADDRESS'),
    ],

    'wave' => [
        'key' => env('WAVE_API_KEY'),
        'secret' => env('WAVE_API_SECRET'),
        'webhook_secret' => env('WAVE_WEBHOOK_SECRET'),
        'base_url' => env('WAVE_BASE_URL', 'https://api.wave.com'),
        'success_url' => env('WAVE_SUCCESS_URL', env('FRONTEND_URL', 'https://nujumalhuda.com') . '/dons/succes'),
        'error_url' => env('WAVE_ERROR_URL', env('FRONTEND_URL', 'https://nujumalhuda.com') . '/dons/echec'),
    ],

    'orange_money' => [
        'merchant_key' => env('ORANGE_MONEY_MERCHANT_KEY'),
        'client_id' => env('ORANGE_MONEY_CLIENT_ID'),
        'client_secret' => env('ORANGE_MONEY_CLIENT_SECRET'),
        'base_url' => env('ORANGE_MONEY_BASE_URL', 'https://api.orange.com/orange-money-webpay/dev/v1'),
        'return_url' => env('ORANGE_MONEY_RETURN_URL', env('FRONTEND_URL', 'https://nujumalhuda.com') . '/dons/succes'),
        'cancel_url' => env('ORANGE_MONEY_CANCEL_URL', env('FRONTEND_URL', 'https://nujumalhuda.com') . '/dons/echec'),
        'notif_url' => env('ORANGE_MONEY_NOTIF_URL', env('APP_URL', 'https://nujumalhuda.com') . '/api/v1/payments/orange-money/webhook'),
    ],

    'webpush' => [
        'public_key' => env('VAPID_PUBLIC_KEY'),
        'private_key' => env('VAPID_PRIVATE_KEY'),
        'subject' => env('VAPID_SUBJECT', 'mailto:tech@nujumalhuda.com'),
    ],
];
