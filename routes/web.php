<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('pages.home');
})->name('home');

// Article Routes (Prototype)
Route::get('/articles/{slug}', function (string $slug) {
    return view('pages.articles.show', ['slug' => $slug]);
})->name('articles.show');
