<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\TwitchLoginController;
use Illuminate\Support\Facades\Auth;


Route::get('/', function () {
    return view('index');
})->name('index');

Route::get('/auth', [ TwitchLoginController::class, 'main' ])->name('login');

Route::get('logout', function() {
    Auth::logout();
    return redirect('/');
})->name('logout');

Route::get('auth', 'TwitchLoginController@mainNovo')->name('login');

Route::get('logout', function() {
    Auth::logout();
    return redirect('/');
})->name('logout');
