<?php

declare(strict_types=1);

use App\Http\Controllers\Api\ArticleController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\ImageController;
use App\Http\Controllers\Api\OAuthController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
*/

// Auth Routes (Public)
Route::prefix('auth')->group(function () {
    Route::post('/register', [AuthController::class, 'register']);
    Route::post('/login', [AuthController::class, 'login']);
    Route::post('/forgot-password', [AuthController::class, 'forgotPassword']);
    Route::post('/reset-password', [AuthController::class, 'resetPassword']);

    // OAuth Routes
    Route::get('/oauth/{provider}/redirect', [OAuthController::class, 'redirect']);
    Route::post('/oauth/{provider}/callback', [OAuthController::class, 'callback']);
});

// Article Routes (Public - with optional auth support via controller)
Route::get('/articles', [ArticleController::class, 'index']);
Route::get('/articles/{slug}', [ArticleController::class, 'show'])
    ->where('slug', '^(?!drafts$)[a-z0-9-]+$');

// Auth Routes (Protected)
Route::middleware('auth:sanctum')->group(function () {
    Route::prefix('auth')->group(function () {
        Route::post('/logout', [AuthController::class, 'logout']);
        Route::get('/me', [AuthController::class, 'me']);
        Route::post('/email/send-verification', [AuthController::class, 'sendVerificationEmail']);
        Route::get('/email/verify/{id}/{hash}', [AuthController::class, 'verifyEmail'])
            ->middleware('signed')
            ->name('verification.verify');
    });

    // Article Routes (Protected)
    Route::get('/articles/drafts', [ArticleController::class, 'drafts']);
    Route::post('/articles', [ArticleController::class, 'store']);
    Route::put('/articles/{slug}', [ArticleController::class, 'update']);
    Route::delete('/articles/{slug}', [ArticleController::class, 'destroy']);
    Route::post('/articles/{slug}/like', [ArticleController::class, 'like']);
    Route::post('/articles/{slug}/publish', [ArticleController::class, 'publish']);

    // Image Upload
    Route::post('/images/upload', [ImageController::class, 'upload']);
});
