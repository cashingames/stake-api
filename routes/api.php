<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\ForgotPasswordController;
use App\Http\Controllers\Auth\ResetPasswordController;



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

Route::post('auth/login', [LoginController::class, 'login'])->middleware('api');
Route::post('auth/register', [RegisterController::class, 'register'])->middleware('api');
Route::post('auth/password/email', [ForgotPasswordController::class,'sendEmail'])->middleware('api');
Route::post('auth/token/verify', [ForgotPasswordController::class,'verifyToken'])->middleware('api');
Route::post('auth/password/reset/{email}', [ResetPasswordController::class, 'reset'])->middleware('api');