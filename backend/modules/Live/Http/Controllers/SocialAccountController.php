<?php

namespace Modules\Live\Http\Controllers;

use App\Http\Controllers\Controller;
use Modules\Live\Models\SocialAccount;

class SocialAccountController extends Controller
{
    public function index()
    {
        return SocialAccount::query()
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->get()
            ->map(fn (SocialAccount $account) => [
                'platform' => $account->platform,
                'handle' => $account->handle,
                'url' => $account->url,
                'embed_url' => $account->redirect_only ? null : $account->embed_url,
                'redirect_only' => $account->redirect_only,
            ]);
    }
}
