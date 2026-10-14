<?php

use App\Http\Controllers\SiteController;
use Illuminate\Support\Facades\Route;

Route::get('/', [SiteController::class, 'home'])->name('home');
Route::redirect('/index.html', '/');

Route::redirect('/segment', '/')->name('segment');
Route::get('/segment.html', fn () => redirect()->route('home'));

Route::redirect('/register', '/')->name('register');
Route::post('/register', fn () => redirect()->route('home'))->name('register.store');
Route::redirect('/register/success', '/')->name('register.success');

Route::redirect('/payment', '/')->name('payment');
Route::post('/payment', fn () => redirect()->route('home'))->name('payment.lookup');
Route::get('/payment/{reference_code}', fn () => redirect()->route('home'))->name('payment.show');
Route::post('/payment/{reference_code}/transaction', fn () => redirect()->route('home'))->name('payment.transaction');

Route::get('/test', function () {});
