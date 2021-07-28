<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\ForgotPasswordController;
use App\Http\Controllers\Auth\ResetPasswordController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\WalletController;
use App\Http\Controllers\EnquiriesController;


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

Route::get('v2/user/me', [UserController::class, 'me'])->middleware('auth:api');

Route::post('v2/profile/me/edit-personal', [ProfileController::class, 'editPersonalInformation'])->middleware('auth:api');
Route::post('v2/profile/me/edit-bank', [ProfileController::class,'editBank'])->middleware('auth:api');
Route::post('v2/profile/me/picture', [ProfileController::class,'addProfilePic'])->middleware('auth:api');
Route::get('v2/profile/me', [ProfileController::class,'me'])->middleware('auth:api');

Route::get('v2/wallet/me', [WalletController::class, 'me'])->middleware('auth:api');
Route::get('v2/wallet/me/transactions', [WalletController::class, 'transactions'])->middleware('auth:api');
Route::get('v2/wallet/me/transactions/earnings', [WalletController::class, 'earnings'])->middleware('auth:api');
Route::get('v2/wallet/me/transaction/verify/{reference}', [WalletController::class, "verifyTransaction"])->middleware('auth:api');
Route::post('v2/wallet/me/withdrawal/request', [WalletController::class,'withdrawRequest'])->middleware('auth:api');

Route::get('v2/wallet/get/withdrawals', [WalletController::class,'getWithdrawals'])->middleware('auth:api');

Route::post('v2/client/feedback', [EnquiriesController::class, 'feedback']);
