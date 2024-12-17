<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Admin Routes
|--------------------------------------------------------------------------
|
| Here is where you can register admin routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "admin" middleware group. Now create something great!
|
*/

Route::prefix('project')->group(function () {

    Route::prefix('estimation')->group(function () {

        Route::match(['get', 'post'], 'duplicateQuoteCard/{id}', 'QuoteCard@duplicateQuoteCard')
            ->name('estimation.duplicateQuoteCard');

        Route::resource('', 'EstimationController')
            ->parameters(['' => 'estimation'])
            ->names('estimation');
    });

});