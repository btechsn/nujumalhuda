<?php

return [
    /*
    |--------------------------------------------------------------------------
    | MediaMTX Configuration
    |--------------------------------------------------------------------------
    |
    | Configuration for MediaMTX streaming server integration.
    | MediaMTX handles RTMP ingest, WHEP (WebRTC), and HLS streaming.
    |
    */

    'base_url' => env('MEDIAMTX_API_URL', env('MEDIAMTX_BASE_URL', 'http://mediamtx:9997')),

    'api_key' => env('MEDIAMTX_API_KEY', ''),

    /*
    |--------------------------------------------------------------------------
    | Ingest Configuration
    |--------------------------------------------------------------------------
    */

    'rtmp_ingest_url' => env('MEDIAMTX_RTMP_URL', env('MEDIAMTX_RTMP_INGEST_URL', 'rtmp://localhost:1935')),

    /*
    |--------------------------------------------------------------------------
    | Playback URLs
    |--------------------------------------------------------------------------
    */

    'whep_url' => env('MEDIAMTX_WHEP_URL', 'https://stream.nujumalhuda.com/whep'),

    'hls_url' => env('MEDIAMTX_HLS_URL', 'https://stream.nujumalhuda.com/hls'),

    /*
    |--------------------------------------------------------------------------
    | VOD (Video On Demand) Configuration
    |--------------------------------------------------------------------------
    */

    'vod_base_url' => env('MEDIAMTX_VOD_BASE_URL', 'https://vod.nujumalhuda.com'),

    'vod_storage_path' => env('MEDIAMTX_VOD_STORAGE_PATH', storage_path('app/recordings')),

    /*
    |--------------------------------------------------------------------------
    | Webhook Configuration
    |--------------------------------------------------------------------------
    */

    'webhook_secret' => env('MEDIAMTX_WEBHOOK_SECRET', ''),

    'webhook_enabled' => env('MEDIAMTX_WEBHOOK_ENABLED', true),

    /*
    |--------------------------------------------------------------------------
    | Channel Configuration
    |--------------------------------------------------------------------------
    */

    'channels' => [
        'main' => [
            'name' => [
                'fr' => 'Canal Principal',
                'en' => 'Main Channel',
                'ar' => 'القناة الرئيسية',
            ],
            'description' => [
                'fr' => 'Diffusion en direct des événements principaux de l\'institut',
                'en' => 'Live streaming of main institute events',
                'ar' => 'البث المباشر للأحداث الرئيسية للمعهد',
            ],
            'max_bitrate' => 5000, // kbps
            'priority' => 100,
        ],

        'recitation' => [
            'name' => [
                'fr' => 'Récitation',
                'en' => 'Recitation',
                'ar' => 'التلاوة',
            ],
            'description' => [
                'fr' => 'Sessions de récitation du Coran par les étudiants',
                'en' => 'Quran recitation sessions by students',
                'ar' => 'جلسات تلاوة القرآن من قبل الطلاب',
            ],
            'max_bitrate' => 3000,
            'priority' => 90,
        ],

        'audio' => [
            'name' => [
                'fr' => 'Audio Seulement',
                'en' => 'Audio Only',
                'ar' => 'الصوت فقط',
            ],
            'description' => [
                'fr' => 'Diffusion audio uniquement pour faible bande passante',
                'en' => 'Audio-only stream for low bandwidth',
                'ar' => 'بث صوتي فقط لنطاق ترددي منخفض',
            ],
            'type' => 'audio',
            'max_bitrate' => 128,
            'priority' => 50,
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Stream Settings
    |--------------------------------------------------------------------------
    */

    'stream' => [
        'auto_archive_after_days' => 30,
        'max_concurrent_viewers' => 10000,
        'enable_recording' => true,
        'recording_format' => 'hls', // hls, mp4
        'thumbnail_interval' => 60, // Generate thumbnail every 60 seconds
    ],

    /*
    |--------------------------------------------------------------------------
    | Chat Settings
    |--------------------------------------------------------------------------
    */

    'chat' => [
        'enabled' => true,
        'max_message_length' => 500,
        'rate_limit_messages' => 5, // messages per minute
        'rate_limit_window' => 60, // seconds
        'auto_moderate_spam_threshold' => 70,
        'banned_words' => [
            // Add inappropriate words here
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Analytics Settings
    |--------------------------------------------------------------------------
    */

    'analytics' => [
        'enabled' => true,
        'collection_interval' => 30, // seconds
        'retention_days' => 90,
    ],
];
