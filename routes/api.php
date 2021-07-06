<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\PostController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\UserController;
use App\Http\Middleware\ApiAuthMiddleware;

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

Route::middleware('auth:api')->get('/user', function (Request $request) {
    return $request->user();
});

Route::apiResource('posts', PostController::class);
Route::apiResource('categories', CategoryController::class);

/* USERS MODULE */
Route::get('user/avatar/{filename}', [UserController::class, 'getImage']);
Route::get('user/detail/{id}', [UserController::class, 'detail']);
Route::post('register', [UserController::class, 'register']);
Route::post('login', [UserController::class, 'login']);
Route::post('user/upload', [UserController::class, 'upload'])->middleware(ApiAuthMiddleware::class);
Route::put('user/update', [UserController::class, 'update']);






