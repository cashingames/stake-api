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
Route::post('auth/password/email', [ForgotPasswordController::class, 'sendEmail']);
Route::post('auth/token/verify', [ForgotPasswordController::class, 'verifyToken']);
Route::post('auth/password/reset/{email}', [ResetPasswordController::class, 'reset']);
Route::post('game/challenge/accept/{challengeId}', [GameController::class, 'acceptChallenge']);
Route::post('game/challenge/decline/{challengeId}', [GameController::class, 'declineChallenge']);

Route::prefix('v2')->group(function () {
    Route::post('client/feedback', [EnquiriesController::class, 'feedback']);

    Route::middleware('auth:api')->group(function () {
        Route::get('user/me', [UserController::class, 'me']);
        Route::post('user/me/set-offline', [UserController::class, 'setOffline']);

        Route::post('profile/me/edit-personal', [ProfileController::class, 'editPersonalInformation']);
        Route::post('profile/me/edit-bank', [ProfileController::class, 'editBank']);
        Route::post('profile/me/picture', [ProfileController::class, 'addProfilePic']);
        Route::get('profile/me', [ProfileController::class, 'me']);

        Route::get('wallet/me', [WalletController::class, 'me']);
        Route::get('wallet/me/transactions', [WalletController::class, 'transactions']);
        Route::get('wallet/me/transactions/earnings', [WalletController::class, 'earnings']);
        Route::get('wallet/me/transaction/verify/{reference}', [WalletController::class, "verifyTransaction"]);
        Route::get('wallet/banks', [WalletController::class, 'getBanks']);
        //Route::post('wallet/me/withdrawal/request', [WalletController::class,'withdrawRequest']);
        Route::post('points/buy-boosts/{boostId}', [WalletController::class, 'buyBoostsWithPoints']);
        Route::post('wallet/buy-boosts/{boostId}', [WalletController::class, 'buyBoostsFromWallet']);

        Route::get('wallet/get/withdrawals', [WalletController::class, 'getWithdrawals']);
        Route::get('me/points/{userId}', [UserController::class, 'getPoints']);
        Route::get('me/points/log/history/{userId}', [UserController::class, 'getPointsLog']);
        Route::get('me/boosts/{userId}', [UserController::class, 'getboosts']);
        Route::get('me/quizzes', [UserController::class, 'quizzes']);

        Route::get('game/boosts', [GameController::class, 'boosts']);
        Route::get('achievements', [GameController::class, 'achievements']);
        Route::post('claim/achievement/{achievementId}', [GameController::class, 'claimAchievement']);
        Route::post('me/achievement', [UserController::class, 'userAchievement']);

        Route::get('game/modes', [GameController::class, 'modes']);
        Route::get('game/types', [GameController::class, 'gameTypes']);
        Route::get('game/categories/{gameTypeId}', [CategoryController::class, 'get']);
        Route::get('game/sub-categories/{catId}/{gameTypeId}', [CategoryController::class, 'subCategories']);
        //Route::get('game/all', [CategoryController::class, 'allGames']);
        Route::get('category/times-played/{catId}', [CategoryController::class, 'timesPlayed']);

        Route::get('friends/quizzes', [UserController::class, 'friendQuizzes']);
        Route::get('me/friends', [UserController::class, 'friends']);

        Route::post('game/start/single-player', [GameController::class, 'startSingleGame']);
        Route::post('game/start/challenge', [GameController::class, 'startChallenge']);
        Route::post('game/end/single-player', [GameController::class, 'endSingleGame']);
        Route::post('game/end/challenge', [GameController::class, 'endChallengeGame']);
        Route::post('game/boost/consume/{boostId}', [GameController::class, 'consumeBoost']);

        Route::post('game/challenge/invite', [GameController::class, 'sendChallengeInvite']);
      
    });
});
