<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\MoviesController;
use App\Http\Controllers\SearchController;

Route::prefix('movie')->group(function () {
    Route::get('/form', [MoviesController::class, 'showForm'])->name('movies.form.show');
    Route::post('/process', [MoviesController::class, 'processForm'])->name('movies.form.process');
});

Route::prefix('search')->group(function() {
    Route::get('/list', [SearchController::class, 'list'])->name('searches.list');
});
