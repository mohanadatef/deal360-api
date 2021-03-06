<?php
namespace App\Http\Controllers\Wordpress;
use Illuminate\Support\Facades\Route;

Route::get('faqs', [FaqsController::class, 'index']);

Route::prefix('/agency')->group(function () {
    Route::get('/index', [AgencyController::class, 'index']);
    Route::get('/index_properties', [AgencyController::class, 'index_properties']);
    Route::get('/show', [AgencyController::class, 'show']);
    Route::get('/all', [AgencyController::class, 'all_agency']);
});

Route::prefix('/agent')->group(function () {
    Route::get('/index', [AgencyController::class, 'all_agent']);
    Route::get('/index_properties', [AgencyController::class, 'index_properties']);
    Route::get('/show', [AgencyController::class, 'show']);
});
Route::prefix('/user')->group(function () {
    Route::get('/all', [AgencyController::class, 'all_user']);
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
    Route::get('/index_favorites', [PropertiesController::class, 'index_favorites']);
    Route::post('/store_favorites', [PropertiesController::class, 'store_favorites']);
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