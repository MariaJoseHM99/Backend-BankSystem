<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\LoginController;

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

Route::post('/account/signUp', [LoginController::class,'signUp']);
Route::post('/account/login',[LoginController::class,'login']);

Route::group(['middleware' => 'auth:api'],function(){
    //LOGIN CONTROLLER
Route::get('/account/logout',[LoginController::class,'logout']);
});
