<?php

Route::group([
    'namespace' => 'App\Http\Controllers\Web',
], function () {
    Route::get('/', 'DummyController')->name('home');
    Route::get('/register', 'DummyController')->name('register');
    Route::get('/login', 'DummyController')->name('login');
    Route::get('/logout', 'DummyController')->name('logout');
    Route::get('/reset-password', 'DummyController')->name('password.reset');
    Route::get('/verify-email', 'DummyController')->name('verification.notice');
    Route::get('/dashboard', 'DummyController')->name('dashboard');
});
