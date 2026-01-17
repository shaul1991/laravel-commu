<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('pages.home');
})->name('home');

// Guest Only Routes (redirect to home if authenticated)
Route::middleware('guest')->group(function () {
    Route::get('/login', function () {
        return view('pages.auth.login');
    })->name('login');

    Route::get('/register', function () {
        return view('pages.auth.register');
    })->name('register');

    Route::get('/forgot-password', function () {
        return view('pages.auth.forgot-password');
    })->name('password.request');
});

// Public Routes
Route::get('/articles', function () {
    return view('pages.articles.index');
})->name('articles.index');

Route::get('/articles/{slug}', function (string $slug) {
    return view('pages.articles.show', ['slug' => $slug]);
})->name('articles.show');

Route::get('/search', function () {
    return view('pages.search');
})->name('search');

Route::get('/@{username}', function (string $username) {
    return view('pages.profile.show', ['username' => $username]);
})->name('profile.show');

// Auth Required Routes (redirect to login if not authenticated)
Route::middleware('auth')->group(function () {
    Route::get('/write', function () {
        return view('pages.articles.write');
    })->name('articles.create');

    Route::get('/articles/{slug}/edit', function (string $slug) {
        return view('pages.articles.edit', ['slug' => $slug]);
    })->name('articles.edit');

    Route::get('/me/articles', function () {
        return view('pages.me.articles');
    })->name('me.articles');

    Route::get('/settings', function () {
        return view('pages.settings.index');
    })->name('settings');
});
