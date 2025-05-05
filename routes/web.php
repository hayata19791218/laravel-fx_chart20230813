<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::fallback(function () {
    return redirect('/');
});


Route::post('/logout', function () {
    Auth::logout();
    request()->session()->invalidate();
    request()->session()->regenerateToken();
    return redirect('/login'); // ログインページに戻す
})->name('logout');

Route::controller(ChartController::class)->group(function(){
    Route::get('/', 'index');
    Route::get('/create', 'create')->name('fx.create');
    Route::post('/store', 'store')->name('store');
    Route::get('/admin', 'admin')->name('fx.admin');
});

require __DIR__.'/auth.php';
