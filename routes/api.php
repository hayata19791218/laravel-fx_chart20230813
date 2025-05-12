<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/


Route::controller(ChartController::class)->group(function(){
    Route::put('/admin/update/{value}', 'update')->name('fx.update');
    Route::get('/api/existing-dates', function () {
        return Value::pluck('date')->toArray();
    });
    Route::get('/admin/value-data', 'list');
    Route::get('/edit-value/{id}', 'getEditValue')->name('api.value.edit');
    Route::delete('/admin/delete/{value}', 'delete')->name('fx.delete');
    Route::get('/admin/edit/{id}', 'edit')->name('fx.edit');
    Route::post('/save-memo', 'saveMemo');
    Route::get('/get-memo', 'getMemo');
});

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});
