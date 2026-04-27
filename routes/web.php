<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('crawler');
});

Route::get('/crawler', function () {
    return view('crawler');
})->name('crawler.ui');
