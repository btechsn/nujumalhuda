<?php

use Illuminate\Support\Facades\Route;
use Modules\News\Http\Controllers\ArticleController;
use Modules\News\Http\Controllers\CategoryController;
use Modules\News\Http\Controllers\CommentController;
use Modules\News\Http\Controllers\RssFeedController;

Route::prefix('api/v1/news')->middleware('api')->group(function () {
    
    // Routes publiques
    Route::get('feed', RssFeedController::class)->name('news.feed');

    Route::get('articles', [ArticleController::class, 'index']);
    Route::get('articles/{slug}', [ArticleController::class, 'show'])->name('news.articles.show');
    
    Route::get('categories', [CategoryController::class, 'index']);
    
    Route::get('articles/{id}/comments', [CommentController::class, 'index']);
    
    // Routes authentifiées
    Route::middleware('auth:sanctum')->group(function () {
        Route::post('comments', [CommentController::class, 'store']);
        Route::post('comments/{id}/report', [CommentController::class, 'report']);
    });
});
