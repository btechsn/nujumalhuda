<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Modules Configuration
    |--------------------------------------------------------------------------
    |
    | Configuration des modules de l'application Nujum Al-Huda Institute Center
    |
    */

    'modules' => [
        'core' => [
            'name' => 'Core',
            'namespace' => 'Modules\Core',
            'provider' => \Modules\Core\Providers\CoreServiceProvider::class,
            'enabled' => true,
        ],
        'announcements' => [
            'name' => 'Announcements',
            'namespace' => 'Modules\Announcements',
            'provider' => \Modules\Announcements\Providers\AnnouncementsServiceProvider::class,
            'enabled' => true,
        ],
        'education' => [
            'name' => 'Education',
            'namespace' => 'Modules\Education',
            'provider' => \Modules\Education\EducationServiceProvider::class,
            'enabled' => true,
        ],
        'mosque' => [
            'name' => 'Mosque',
            'namespace' => 'Modules\Mosque',
            'provider' => \Modules\Mosque\MosqueServiceProvider::class,
            'enabled' => true,
        ],
        'news' => [
            'name' => 'News',
            'namespace' => 'Modules\News',
            'provider' => \Modules\News\NewsServiceProvider::class,
            'enabled' => true,
        ],
        'live' => [
            'name' => 'Live',
            'namespace' => 'Modules\Live',
            'provider' => \Modules\Live\LiveServiceProvider::class,
            'enabled' => true,
        ],
        'resources' => [
            'name' => 'Resources',
            'namespace' => 'Modules\Resources',
            'provider' => \Modules\Resources\ResourcesServiceProvider::class,
            'enabled' => true,
        ],
        'academics' => [
            'name' => 'Academics',
            'namespace' => 'Modules\Academics',
            'provider' => \Modules\Academics\AcademicsServiceProvider::class,
            'enabled' => true,
        ],
        'community' => [
            'name' => 'Community',
            'namespace' => 'Modules\Community',
            'provider' => \Modules\Community\CommunityServiceProvider::class,
            'enabled' => true,
        ],
        'dahira' => [
            'name' => 'Dahira',
            'namespace' => 'Modules\Dahira',
            'provider' => \Modules\Dahira\DahiraServiceProvider::class,
            'enabled' => true,
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Modules à venir (Phases futures)
    |--------------------------------------------------------------------------
    */

    'future_modules' => [
    ],
];
