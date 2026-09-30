<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Broadcast;

/*
|--------------------------------------------------------------------------
| Broadcast Channels — Announcements
|--------------------------------------------------------------------------
*/

// Canal public : toutes les annonces
Broadcast::channel('announcements', function () {
    return true;
});
