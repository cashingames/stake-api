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
use App\Http\Controllers\MessagesController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\LeadersController;
use App\Http\Controllers\TriviaController;

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
Route::post('auth/google/login', [LoginController::class, 'loginWithGoogle']);
Route::post('auth/register', [RegisterController::class, 'register']);
Route::post('auth/username/verify/{username}', [RegisterController::class, 'verifyUsername']);
Route::post('auth/password/email', [ForgotPasswordController::class, 'sendEmail']);
Route::post('auth/token/verify', [ForgotPasswordController::class, 'verifyToken']);
Route::post('auth/password/reset', [ResetPasswordController::class, 'reset']);
Route::post('game/challenge/accept/{challengeId}', [GameController::class, 'acceptChallenge']);
Route::post('game/challenge/decline/{challengeId}', [GameController::class, 'declineChallenge']);

Route::middleware('auth:api')->prefix('v3')->group(
    function () {
        Route::get('user/profile', [UserController::class, 'profile']);
        Route::get('game/common', [GameController::class, 'getCommonData']);
        Route::get('fetch/trivia', [TriviaController::class, 'getTrivia']);
        Route::get('trivia/leaders/{triviaId}', [TriviaController::class, 'getLiveTriviaLeaderboard']);
    }
);

Route::middleware('api')->prefix('v3')->group(
    function () {
        Route::get('game/common', [GameController::class, 'getCommonData']);
        Route::post('paystack/transaction/webhook', [WalletController::class, "paystackWebhook"]);
    }
);

Route::prefix('v2')->group(function () {
    Route::post('client/feedback', [MessagesController::class, 'feedback']);
    Route::get('faq/fetch', [MessagesController::class, 'fetchFaqAndAnswers']);

    Route::middleware('auth:api')->group(function () {
        Route::get('user/me', [UserController::class, 'me']);
        Route::post('me/set/online', [UserController::class, 'setOnline']);

        Route::post('profile/me/edit-personal', [ProfileController::class, 'editPersonalInformation']);
        Route::post('profile/me/edit-bank', [ProfileController::class, 'editBank']);
        Route::post('profile/me/picture', [ProfileController::class, 'addProfilePic']);
        Route::post('profile/me/password/change', [ProfileController::class, 'changePassword']);
        Route::get('profile/me', [UserController::class, 'me']);

        Route::get('wallet/me', [WalletController::class, 'me']);
        Route::get('wallet/me/transactions', [WalletController::class, 'transactions']);
        Route::get('wallet/me/transactions/earnings', [WalletController::class, 'earnings']);
        Route::get('wallet/me/transaction/verify/{reference}', [WalletController::class, "verifyTransaction"]);
        Route::get('wallet/banks', [WalletController::class, 'getBanks']);
        //Route::post('wallet/me/withdrawal/request', [WalletController::class,'withdrawRequest']);
        Route::post('points/buy-boosts/{boostId}', [WalletController::class, 'buyBoostsWithPoints']);
        Route::post('wallet/buy-boosts/{boostId}', [WalletController::class, 'buyBoostsFromWallet']);
        Route::post('plan/subscribe/{planId}', [WalletController::class, 'subscribeToPlan']);


        Route::get('wallet/get/withdrawals', [WalletController::class, 'getWithdrawals']);
        Route::get('me/points', [UserController::class, 'getPoints']);
        Route::get('me/points/log/history', [UserController::class, 'getPointsLog']);
        Route::get('me/boosts', [UserController::class, 'getboosts']);
        Route::get('me/quizzes', [UserController::class, 'quizzes']);

        Route::get('game/boosts', [GameController::class, 'boosts']);
        Route::get('achievements', [GameController::class, 'achievements']);
        Route::post('claim/achievement/{achievementId}', [GameController::class, 'claimAchievement']);
        Route::post('me/achievement', [UserController::class, 'userAchievement']);

        Route::get('game/modes', [GameController::class, 'modes']);
        Route::get('game/types', [GameController::class, 'gameTypes']);
        Route::get('game/types/random', [GameController::class, 'shuffleGameTypes']);
        Route::get('game/categories', [CategoryController::class, 'get']);
        Route::get('game/categories/{gameTypeId}', [CategoryController::class, 'getGameTypeCategories']);
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
        Route::get('leaders/global', [LeadersController::class, 'global']);
        Route::get('leaders/global/{startDate}/{endDate}', [LeadersController::class, 'global']);
        Route::get('leaders/categories', [LeadersController::class, 'categories']);
        Route::get('leaders/categories/{startDate}/{endDate}', [LeadersController::class, 'categories']);

        //updated leaders endpoint
        Route::post('leaders/global', [LeadersController::class, 'globalLeaders']);
        Route::post('leaders/categories', [LeadersController::class, 'categoriesLeaders']);

        Route::get('user/fetch/notifications', [MessagesController::class, 'fetchNotifications']);
        Route::post('user/read/notification/{notificationId}', [MessagesController::class, 'readNotification']);
        Route::post('user/read/all/notifications', [MessagesController::class, 'readAllNotifications']);
    });
});
