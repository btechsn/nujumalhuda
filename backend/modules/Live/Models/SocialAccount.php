<?php

namespace Modules\Live\Models;

use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Model;

class SocialAccount extends Model
{
    use HasUlids;

    protected $fillable = [
        'platform', 'handle', 'url', 'embed_url', 'redirect_only', 'is_active', 'sort_order',
    ];

    protected $casts = [
        'redirect_only' => 'boolean',
        'is_active' => 'boolean',
    ];

    protected static function booted(): void
    {
        static::saving(function (SocialAccount $account): void {
            if (in_array($account->platform, ['tiktok', 'instagram'], true)) {
                $account->redirect_only = true;
                $account->embed_url = null;
            }
        });
    }
}
