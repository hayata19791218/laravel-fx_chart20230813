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

/**
* 20行目:トップページ
* 21行目:値を登録するページ
* 22行目:値の保存
*/
Route::controller(ChartController::class)->group(function(){
    Route::get('/', 'index');
    Route::get('/create', 'create')->name('fx.create');
    Route::post('/store', 'store')->name('store');
});
