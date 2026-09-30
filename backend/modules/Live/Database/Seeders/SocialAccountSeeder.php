<?php

namespace Modules\Live\Database\Seeders;

use Illuminate\Database\Seeder;
use Modules\Live\Models\SocialAccount;

class SocialAccountSeeder extends Seeder
{
    public function run(): void
    {
        $accounts = [
            [
                'platform' => 'youtube',
                'handle' => '@nujumalhuda',
                'url' => 'https://www.youtube.com/@nujumalhuda',
                'embed_url' => 'https://www.youtube.com/embed/live_stream?channel=UCPLACEHOLDER',
                'redirect_only' => false,
                'sort_order' => 1,
            ],
            [
                'platform' => 'facebook',
                'handle' => 'nujumalhuda',
                'url' => 'https://www.facebook.com/nujumalhuda',
                'embed_url' => null,
                'redirect_only' => false,
                'sort_order' => 2,
            ],
            [
                'platform' => 'instagram',
                'handle' => '@nujumalhuda',
                'url' => 'https://www.instagram.com/nujumalhuda',
                'embed_url' => null,
                'redirect_only' => true,
                'sort_order' => 3,
            ],
            [
                'platform' => 'tiktok',
                'handle' => '@nujumalhuda',
                'url' => 'https://www.tiktok.com/@nujumalhuda',
                'embed_url' => null,
                'redirect_only' => true,
                'sort_order' => 4,
            ],
        ];

        foreach ($accounts as $row) {
            SocialAccount::updateOrCreate(
                ['platform' => $row['platform'], 'handle' => $row['handle']],
                [
                    'url' => $row['url'],
                    'embed_url' => $row['embed_url'],
                    'redirect_only' => $row['redirect_only'],
                    'is_active' => true,
                    'sort_order' => $row['sort_order'],
                ]
            );
        }
    }
}
