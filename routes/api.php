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
use App\Http\Controllers\GameController;
use App\Http\Controllers\EnquiriesController;
use App\Http\Controllers\CategoryController;


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

Route::post('auth/login', [LoginController::class, 'login']);
Route::post('auth/register', [RegisterController::class, 'register']);
Route::post('auth/password/email', [ForgotPasswordController::class,'sendEmail']);
Route::post('auth/token/verify', [ForgotPasswordController::class,'verifyToken']);
Route::post('auth/password/reset/{email}', [ResetPasswordController::class, 'reset']);

Route::post('v2/client/feedback', [EnquiriesController::class, 'feedback']);

Route::get('v2/user/me', [UserController::class, 'me'])->middleware('auth:api');

Route::post('v2/profile/me/edit-personal', [ProfileController::class, 'editPersonalInformation'])->middleware('auth:api');
Route::post('v2/profile/me/edit-bank', [ProfileController::class,'editBank'])->middleware('auth:api');
Route::post('v2/profile/me/picture', [ProfileController::class,'addProfilePic'])->middleware('auth:api');
Route::get('v2/profile/me', [ProfileController::class,'me'])->middleware('auth:api');

Route::get('v2/wallet/me', [WalletController::class, 'me'])->middleware('auth:api');
Route::get('v2/wallet/me/transactions', [WalletController::class, 'transactions'])->middleware('auth:api');
Route::get('v2/wallet/me/transactions/earnings', [WalletController::class, 'earnings'])->middleware('auth:api');
Route::get('v2/wallet/me/transaction/verify/{reference}', [WalletController::class, "verifyTransaction"])->middleware('auth:api');
Route::get('v2/wallet/banks', [WalletController::class, 'getBanks'])->middleware('auth:api');
//Route::post('v2/wallet/me/withdrawal/request', [WalletController::class,'withdrawRequest'])->middleware('auth:api');
Route::post('v2/points/buy-boosts/{boostId}',[WalletController::class, 'buyBoostsWithPoints'])->middleware('auth:api');
Route::post('v2/wallet/buy-boosts/{boostId}',[WalletController::class, 'buyBoostsFromWallet'])->middleware('auth:api');

Route::get('v2/wallet/get/withdrawals', [WalletController::class,'getWithdrawals'])->middleware('auth:api');
Route::get('v2/me/points/{userId}',[UserController::class, 'getPoints'])->middleware('auth:api');
Route::get('v2/me/points/log/history/{userId}',[UserController::class, 'getPointsLog'])->middleware('auth:api');
Route::get('v2/me/boosts/{userId}',[UserController::class, 'getboosts'])->middleware('auth:api');
Route::get('v2/me/quizzes', [UserController::class, 'quizzes'])->middleware('auth:api');

Route::get('v2/game/boosts',[GameController::class, 'boosts'])->middleware('auth:api');
Route::get('v2/achievements',[GameController::class, 'achievements'])->middleware('auth:api');

Route::get('v2/game/modes', [GameController::class, 'modes'])->middleware('auth:api');
Route::get('v2/game/types', [GameController::class, 'gameTypes'])->middleware('auth:api');
Route::get('v2/game/categories/{gameTypeId}', [CategoryController::class, 'get'])->middleware('auth:api');
Route::get('v2/game/sub-categories/{catId}/{gameTypeId}', [CategoryController::class, 'subCategories'])->middleware('auth:api');
//Route::get('v2/game/all', [CategoryController::class, 'allGames'])->middleware('auth:api');

Route::get('v2/friends/quizzes', [UserController::class, 'friendQuizzes'])->middleware('auth:api');
Route::get('v2/me/friends', [UserController::class, 'friends'])->middleware('auth:api');