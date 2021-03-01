<?php
namespace App\Http\Controllers\Wordpress;
use Illuminate\Support\Facades\Route;

Route::get('faqs', [FaqsController::class, 'index']);

Route::prefix('/agency')->group(function () {
    Route::get('/index', [AgencyController::class, 'index']);
    Route::get('/index_properties', [AgencyController::class, 'index_properties']);
    Route::get('/show', [AgencyController::class, 'show']);
});

Route::prefix('/agent')->group(function () {
    Route::get('/index_properties', [AgencyController::class, 'index_properties']);
    Route::get('/show', [AgencyController::class, 'show']);
});

Route::prefix('/city')->group(function () {
    Route::get('/index', [CityController::class, 'index']);
});
Route::prefix('/package')->group(function () {
    Route::get('/index', [PackageController::class, 'index']);
});

Route::prefix('/properties')->group(function () {
    Route::get('/index_type', [PropertiesController::class, 'index_type']);
    Route::get('/show', [PropertiesController::class, 'show']);
    Route::get('/properties_type', [PropertiesController::class, 'properties_type']);
    Route::get('/search', [PropertiesController::class, 'search']);
});

Route::prefix('/favorites')->group(function () {
    Route::get('/index', [FavoritesController::class, 'index']);
    Route::post('/store', [FavoritesController::class, 'store']);
});

Route::prefix('/user')->group(function () {
    Route::post('/store', [UserController::class, 'store']);
    Route::get('/show', [UserController::class, 'show']);
});

Route::prefix('/review')->group(function () {
    Route::post('/store', [ReviewController::class, 'store']);
    Route::post('/index', [ReviewController::class, 'index']);
});

Route::prefix('/save_search')->group(function () {
    Route::get('/delete', [SaveSearchController::class, 'delete']);
});