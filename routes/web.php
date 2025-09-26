<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\MoviesController;
use App\Http\Controllers\SearchController;

Route::prefix('movie')->group(function () {
    Route::get('/form', [SearchController::class, 'showForm'])->name('search.form.show');
    Route::post('/process', [SearchController::class, 'processForm'])->name('search.form.process');
});

Route::prefix('search')->group(function() {
    Route::get('/list', [SearchController::class, 'list'])->name('search.list');
    Route::get('/report/{id}', [SearchController::class, 'report'])->name('search.report');
    Route::get('/test', [SearchController::class, 'test'])->name('search.test'); //TODO: remove
});
