<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DiagController;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/diag', [DiagController::class, 'index']);
Route::get('/diag/cookie-test', [DiagController::class, 'cookieTest']);

