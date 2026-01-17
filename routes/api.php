<?php

declare(strict_types=1);

use App\Http\Controllers\Api\ArticleController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\CommentController;
use App\Http\Controllers\Api\ImageController;
use App\Http\Controllers\Api\NotificationController;
use App\Http\Controllers\Api\OAuthController;
use App\Http\Controllers\Api\SearchController;
use App\Http\Controllers\Api\SettingsController;
use App\Http\Controllers\Api\UserController;
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

// Comment Routes (Public)
Route::get('/articles/{slug}/comments', [CommentController::class, 'index']);

// Search Routes (Public)
Route::get('/search/articles', [SearchController::class, 'articles']);
Route::get('/search/users', [SearchController::class, 'users']);

// User Routes (Public)
Route::get('/users/{username}', [UserController::class, 'show']);
Route::get('/users/{username}/articles', [UserController::class, 'articles']);

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

    // Comment Routes (Protected)
    Route::post('/articles/{slug}/comments', [CommentController::class, 'store']);
    Route::post('/comments/{comment}/replies', [CommentController::class, 'reply']);
    Route::put('/comments/{comment}', [CommentController::class, 'update']);
    Route::delete('/comments/{comment}', [CommentController::class, 'destroy']);
    Route::post('/comments/{comment}/like', [CommentController::class, 'like']);

    // User Routes (Protected)
    Route::put('/users/me', [UserController::class, 'updateProfile']);
    Route::post('/users/{username}/follow', [UserController::class, 'follow']);

    // Settings Routes (Protected)
    Route::prefix('settings')->group(function () {
        Route::put('/account/email', [SettingsController::class, 'updateEmail']);
        Route::put('/account/password', [SettingsController::class, 'updatePassword']);
        Route::delete('/account', [SettingsController::class, 'deleteAccount']);
        Route::get('/notifications', [SettingsController::class, 'getNotificationSettings']);
        Route::put('/notifications', [SettingsController::class, 'updateNotificationSettings']);
    });

    // Notification Routes (Protected)
    Route::prefix('notifications')->group(function () {
        Route::get('/', [NotificationController::class, 'index']);
        Route::get('/unread-count', [NotificationController::class, 'unreadCount']);
        Route::post('/read-all', [NotificationController::class, 'markAllAsRead']);
        Route::post('/{notification}/read', [NotificationController::class, 'markAsRead']);
    });
});
