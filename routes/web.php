<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('pages.home');
})->name('home');

// Auth Routes (Prototype)
Route::get('/login', function () {
    return view('pages.auth.login');
})->name('login');

Route::get('/register', function () {
    return view('pages.auth.register');
})->name('register');

Route::get('/forgot-password', function () {
    return view('pages.auth.forgot-password');
})->name('password.request');

// Article Routes (Prototype)
Route::get('/articles', function () {
    return view('pages.articles.index');
})->name('articles.index');

Route::get('/articles/{slug}', function (string $slug) {
    return view('pages.articles.show', ['slug' => $slug]);
})->name('articles.show');

Route::get('/write', function () {
    return view('pages.articles.write');
})->name('articles.create');

Route::get('/articles/{slug}/edit', function (string $slug) {
    return view('pages.articles.edit', ['slug' => $slug]);
})->name('articles.edit');

// Search Routes (Prototype)
Route::get('/search', function () {
    return view('pages.search');
})->name('search');

// User Routes (Prototype)
Route::get('/me/articles', function () {
    return view('pages.me.articles');
})->name('me.articles');

Route::get('/@{username}', function (string $username) {
    return view('pages.profile.show', ['username' => $username]);
})->name('profile.show');

Route::get('/settings', function () {
    return view('pages.settings.index');
})->name('settings');
