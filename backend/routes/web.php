<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return response()->json([
        'name' => 'Nujum Al-Huda Institute Center',
    ]);
});
