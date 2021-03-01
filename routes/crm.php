<?php
namespace App\Http\Controllers\Crm;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
//header('Access-Control-Allow-Origin: *');
//header('Access-Control-Allow-Credentials: true');
//header('Accept: application/json');
//header('Access-Control-Allow-Methods: GET, PUT, POST, DELETE, OPTIONS');
//header('Access-Control-Max-Age: 3600');
//header('Access-Control-Allow-Headers: Origin, X-Requested-With, Content-Type, Accept');

/*
|--------------------------------------------------------------------------
| Crm Routes
|--------------------------------------------------------------------------
|
| Here is where you can register Crm routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your Crm!
|
*/

//Auth
Route::post('login',[UserController::class, 'login']);
Route::post('register',[UserController::class, 'register']);

Route::group(['middleware' => 'auth:api'], function(){
    //User
    Route::post('getUser',[UserController::class, 'getUser']);
    Route::post('updateUserData',[UserController::class, 'updateUserData']);
    Route::post('updateUserPassword',[UserController::class, 'updateUserPassword']);
    Route::post('updateUserImage',[UserController::class, 'updateUserImage']);


    //Get All Teams
    Route::get('getAllTeams',[TeamsController::class,'getAllTeams']);

    //Posts
    Route::resource('properties',PropertiesController::class);
    Route::get('getData',[PropertiesController::class,'getData']);
    Route::post('changePropertyStatus',[PropertiesController::class, 'changePropertyStatus']);

    //Tasks
    //chanage permater id to request mohanad
    Route::get('getPropertiesTasks',[TasksController::class,'getPropertiesTasks']);
    Route::post('assignTasks',[TasksController::class,'assignTasks']);
    Route::post('changeTaskStatus',[TasksController::class,'changeTaskStatus']);
    Route::delete('deleteTask',[TasksController::class,'deleteTask']);

});

Route::get('getAllPosts',[PostController::class, 'getAllPosts']);