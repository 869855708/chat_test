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

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

// 获取资源
Route::get('/show', [\App\Http\Controllers\api\UserController::class, 'show'])->middleware('apiAuth');
Route::post('/register', [\App\Http\Controllers\api\UserController::class, 'register']);
Route::post('/login', [\App\Http\Controllers\api\UserController::class, 'login']);
Route::post('/logout', [\App\Http\Controllers\api\UserController::class, 'logout'])->middleware('apiAuth');
Route::post('/refresh', [\App\Http\Controllers\api\UserController::class, 'refresh'])->middleware('apiAuth');



/**
 * Swagger-UI
 */
Route::get('/swagger/json', [\App\Http\Controllers\api\SwaggerController::class, 'getJSON']);
Route::get('/swagger/my-data', [\App\Http\Controllers\api\SwaggerController::class, 'getMyData']);
