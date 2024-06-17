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


Route::controller(ChartController::class)->group(function(){
    Route::get('/', 'index');
    Route::get('/create', 'create')->name('fx.create');
    Route::post('/store', 'store')->name('store');
    Route::get('/admin', 'admin')->name('fx.admin');
    Route::get('/admin/edit/{id}', 'edit')->name('fx.edit');
    Route::put('/admin/update/{value}', 'update')->name('fx.update');
    Route::delete('/adimin/delete/{value}', 'delete')->name('fx.delete');
});

require __DIR__.'/auth.php';
