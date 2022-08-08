<?php

use App\Http\Controllers\Auth\AuthenticateVerifiedUserController;
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
use App\Http\Controllers\ChallengeInviteStatusController;
use App\Http\Controllers\EndChallengeGameController;
use App\Http\Controllers\FrontEndLogsController;
use App\Http\Controllers\GetChallengeDetailsController;
use App\Http\Controllers\GetChallengeLeaderboardController;
use App\Http\Controllers\GetFriendsController;
use App\Http\Controllers\LeadersController;
use App\Http\Controllers\LiveTriviaStatusController;
use App\Http\Controllers\GetLiveTriviaLeaderboardController;
use App\Http\Controllers\GetRecentLiveTriviaController;
use App\Http\Controllers\GetUserChallengeController;
use App\Http\Controllers\PlayGroundController;
use App\Http\Controllers\RegisterPushDeviceTokenController;
use App\Http\Controllers\SendChallengeInviteController;
use App\Http\Controllers\SocialSignInController;
use App\Http\Controllers\StartChallengeGameController;
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

Route::get('playground', PlayGroundController::class);
Route::post('auth/register', [RegisterController::class, 'register']);
Route::post('auth/login', [LoginController::class, 'login']);
Route::post('auth/social-login/authenticate', [SocialSignInController::class, 'authenticateUser']);
Route::post('auth/social-login/create-account', [SocialSignInController::class, 'createUser']);
Route::post('auth/username/verify/{username}', [RegisterController::class, 'verifyUsername']);
Route::post('auth/password/email', [ForgotPasswordController::class, 'sendEmail']);
Route::post('auth/token/verify', [ForgotPasswordController::class, 'verifyToken']);
Route::post('auth/password/reset', [ResetPasswordController::class, 'reset']);
Route::post('auth/user/authenticate', AuthenticateVerifiedUserController::class);

Route::middleware('api')->prefix('v3')->group(
    function () {
        Route::post('paystack/transaction/webhook', [WalletController::class, "paymentEventProcessor"]);
        Route::post('paystack/reconcile/payments', [WalletController::class, "paymentsTransactionsReconciler"]);
        Route::get('first-time-bonus/fetch', [MessagesController::class, 'fetchFirstTimeBonus']);
    }
);

Route::middleware('auth:api')->prefix('v3')->group(
    function () {
        Route::get('user/profile', [UserController::class, 'profile']);
        Route::get('user/search/friends', GetFriendsController::class);
        Route::get('game/common', [GameController::class, 'getCommonData']);
        Route::get('fetch/trivia', [TriviaController::class, 'getTrivia']);
        Route::get('live-trivia/recent', GetRecentLiveTriviaController::class);
        Route::get('trivia/leaders/{triviaId}', [TriviaController::class, 'getLiveTriviaLeaderboard']);
        Route::get('live-trivia/{id}/leaderboard', GetLiveTriviaLeaderboardController::class);
        Route::get('game/common', [GameController::class, 'getCommonData']);
        Route::get('live-trivia/status', LiveTriviaStatusController::class); //gets the most recent upcoming/running/closed
        Route::get('live-trivia/{id}/status', LiveTriviaStatusController::class); //@TODO 
        Route::post('challenge/send-invite', SendChallengeInviteController::class);
        Route::post('challenge/invite/respond', ChallengeInviteStatusController::class);
        Route::get('challenge/{challengeId}/details', GetChallengeDetailsController::class);
        Route::post('challenge/start/game', StartChallengeGameController::class);
        Route::post('challenge/end/game', EndChallengeGameController::class);
        Route::get('challenge/{challengeId}/leaderboard', GetChallengeLeaderboardController::class);
        Route::get('user/challenges', GetUserChallengeController::class);
        Route::post('log/frontend-info', FrontEndLogsController::class);
        Route::post('fcm/subscriptions', RegisterPushDeviceTokenController::class);
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
        Route::get('category/times-played/{catId}', [CategoryController::class, 'timesPlayed']);

        Route::get('me/friends', [UserController::class, 'friends']);

        Route::post('game/start/single-player', [GameController::class, 'startSingleGame']);
        Route::post('game/end/single-player', [GameController::class, 'endSingleGame']);
        Route::post('game/boost/consume/{boostId}', [GameController::class, 'consumeBoost']);

        //updated leaders endpoint
        Route::post('leaders/global', [LeadersController::class, 'globalLeaders']);
        Route::post('leaders/categories', [LeadersController::class, 'categoriesLeaders']);

        Route::get('user/fetch/notifications', [MessagesController::class, 'fetchNotifications']);
        Route::post('user/read/notification/{notificationId}', [MessagesController::class, 'readNotification']);
        Route::post('user/read/all/notifications', [MessagesController::class, 'readAllNotifications']);
    });
});
