<?php

declare(strict_types=1);

use App\Http\Controllers\Api\ArticleController;
use App\Http\Controllers\Api\AuthController;
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
});

// Article Routes (Public - with optional auth support via controller)
Route::get('/articles', [ArticleController::class, 'index']);
Route::get('/articles/{slug}', [ArticleController::class, 'show']);

// Auth Routes (Protected)
Route::middleware('auth:sanctum')->group(function () {
    Route::prefix('auth')->group(function () {
        Route::post('/logout', [AuthController::class, 'logout']);
        Route::get('/me', [AuthController::class, 'me']);
    });

    // Article Routes (Protected)
    Route::post('/articles', [ArticleController::class, 'store']);
    Route::put('/articles/{slug}', [ArticleController::class, 'update']);
    Route::delete('/articles/{slug}', [ArticleController::class, 'destroy']);
    Route::post('/articles/{slug}/like', [ArticleController::class, 'like']);
});
