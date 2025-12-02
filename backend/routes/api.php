<?php

use App\Http\Controllers\SearchController;
use App\Http\Controllers\StatsController;
use Illuminate\Support\Facades\Route;

Route::get('/search', [SearchController::class, 'search']);
Route::get('/people/{id}', [SearchController::class, 'showPerson']);
Route::get('/films/{id}', [SearchController::class, 'showFilm']);
Route::get('/stats', [StatsController::class, 'index']);
