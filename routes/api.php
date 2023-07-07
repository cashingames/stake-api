<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\GameController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\WalletController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\MessagesController;
use App\Http\Controllers\VerifyOTPController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\FeatureFlagController;
use App\Http\Controllers\FrontEndLogsController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\GetStakingOddsController;
use App\Http\Controllers\GetSingleContestController;
use App\Http\Controllers\WithdrawWinningsController;
use App\Http\Controllers\GetContestDetailsController;
use App\Http\Controllers\Auth\ResetPasswordController;
use App\Http\Controllers\Auth\ForgotPasswordController;
use App\Http\Controllers\RegisterPushDeviceTokenController;
use App\Http\Controllers\PlayGame\EndChallengeGameController;
use App\Http\Controllers\Auth\AuthenticateVerifiedUserController;
use App\Http\Controllers\PlayGame\EndPracticeChallengeGameController;
use App\Http\Controllers\PlayGame\StartSinglePlayerGameController;
use App\Http\Controllers\PlayGame\StartChallengeRequestController;
use App\Http\Controllers\PlayGame\StartPracticeChallengeRequestController;
use App\Http\Controllers\PlayGame\StartSinglePlayerPracticeGameController;

Route::post('auth/register', [RegisterController::class, 'register']);
Route::post('auth/login', [LoginController::class, 'login']);
Route::post('auth/username/verify/{username}', [RegisterController::class, 'verifyUsername']);
Route::post('auth/password/email', [ForgotPasswordController::class, 'sendEmail']);
Route::post('auth/token/verify', [ForgotPasswordController::class, 'verifyToken']);
Route::post('auth/password/reset', [ResetPasswordController::class, 'reset']);
Route::post('auth/user/authenticate', AuthenticateVerifiedUserController::class);
Route::post('auth/register/verify-token', VerifyOTPController::class);
Route::post('auth/register/token/resend', [RegisterController::class, 'resendOTP']);
Route::post('auth/password/token/resend', [ForgotPasswordController::class, 'resendOTP']);

Route::middleware('api')->prefix('v3')->group(
    function () {
        Route::post('paystack/transaction/avjshasahnmsa', [WalletController::class, "paymentEventProcessor"]);
        Route::get('first-time-bonus/fetch', [MessagesController::class, 'fetchFirstTimeBonus']);
        Route::get('feature-flags', [FeatureFlagController::class, 'index'])->middleware(['cacheResponse:300']);
        Route::post('client/feedback', [MessagesController::class, 'feedback']);
        Route::get('faq/fetch', [MessagesController::class, 'fetchFaqAndAnswers'])->middleware(['cacheResponse:300']);
        Route::post('category/icon/save', [CategoryController::class, 'saveCategoryIcon']);
        Route::post('log/frontend-info', FrontEndLogsController::class);
    }
);

Route::middleware(['auth:api'])->prefix('v3')->group(
    function () {
        Route::get('user/profile', [UserController::class, 'profile'])->middleware(['last_active']);
        Route::get('game/common', [GameController::class, 'getCommonData'])->middleware(['cacheResponse:86400']);
        Route::post('fcm/subscriptions', RegisterPushDeviceTokenController::class);
        Route::get('odds/standard', GetStakingOddsController::class);
        Route::get('notifications', [NotificationController::class, 'index']);
        Route::post('notifications/read/{notificationId}', [NotificationController::class, "readNotification"]);
        Route::post('winnings/withdraw', WithdrawWinningsController::class);
        Route::post('profile/me/edit-personal', [ProfileController::class, 'editPersonalInformation']);
        Route::post('profile/me/edit-bank', [ProfileController::class, 'editBank']);
        Route::post('profile/me/picture', [ProfileController::class, 'addProfilePic']);
        Route::post('profile/me/password/change', [ProfileController::class, 'changePassword']);
        Route::get('wallet/me', [WalletController::class, 'me']);
        Route::get('wallet/me/transactions', [WalletController::class, 'transactions']);
        Route::get('wallet/me/transactions/earnings', [WalletController::class, 'earnings']);
        Route::get('wallet/me/transaction/verify/{reference}', [WalletController::class, "verifyTransaction"]);
        Route::get('wallet/banks', [WalletController::class, 'getBanks'])->middleware(['cacheResponse:604800']);
        Route::post('wallet/buy-boosts/{boostId}', [WalletController::class, 'buyBoostsFromWallet']);
        Route::post('game/start/single-player', StartSinglePlayerGameController::class);
        Route::post('game/end/single-player', [GameController::class, 'endSingleGame']);
        Route::post('single-player/practice/start', StartSinglePlayerPracticeGameController::class);
        Route::post('account/delete', [UserController::class, 'deleteAccount']);
        Route::delete('account/delete', [UserController::class, 'deleteAccount']);
        Route::get('contests', GetContestDetailsController::class);
        Route::get('contest/{id}', GetSingleContestController::class)->middleware(['cacheResponse:300']);
        Route::post('challenges/create', StartChallengeRequestController::class);
        Route::post('challenges/submit', EndChallengeGameController::class);
        Route::post('challenges/practice/create', StartPracticeChallengeRequestController::class);
        Route::post('challenges/practice/submit', EndPracticeChallengeGameController::class);
    }
);
