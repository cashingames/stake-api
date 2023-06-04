<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\GameController;
use App\Http\Controllers\AchievementBadeController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\TriviaController;
use App\Http\Controllers\WalletController;
use App\Http\Controllers\LeadersController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\MessagesController;
use App\Http\Controllers\VerifyOTPController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\GetFriendsController;
use App\Http\Controllers\PlayGroundController;
use App\Http\Controllers\FrontEndLogsController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\SocialSignInController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\GetStakingOddsController;
use App\Http\Controllers\EndChallengeGameController;
use App\Http\Controllers\GetUserChallengeController;
use App\Http\Controllers\LiveTriviaStatusController;
use App\Http\Controllers\Auth\ResetPasswordController;
use App\Http\Controllers\StartChallengeGameController;
use App\Http\Controllers\Auth\ForgotPasswordController;
use App\Http\Controllers\GetChallengeDetailsController;
use App\Http\Controllers\GetRecentLiveTriviaController;
use App\Http\Controllers\SendChallengeInviteController;
use App\Http\Controllers\ChallengeInviteStatusController;
use App\Http\Controllers\GetChallengeLeaderboardController;
use App\Http\Controllers\RegisterPushDeviceTokenController;
use App\Http\Controllers\GetLiveTriviaLeaderboardController;
use App\Http\Controllers\Auth\AuthenticateVerifiedUserController;
use App\Http\Controllers\ChallengeGlobalLeadersController;
use App\Http\Controllers\ClaimUserRewardController;
use App\Http\Controllers\FeatureFlagController;
use App\Http\Controllers\GetContestDetailsController;
use App\Http\Controllers\GetSingleContestController;
use App\Http\Controllers\GetSingleLiveTriviaController;
use App\Http\Controllers\GetStakersSessionController;
use App\Http\Controllers\LiveTriviaEntrancePaymentController;
use App\Http\Controllers\WithdrawWinningsController;
use App\Http\Controllers\PlayGame\StartSinglePlayerGameController;
use App\Http\Controllers\SendOtpToEmailController;
use App\Http\Controllers\EmailOtpVerificationController;
use App\Http\Controllers\PlayGame\StartChallengeRequestController;
use App\Http\Controllers\V3\GetGlobalLeaderboardController;

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
Route::post('auth/register/verify-token', VerifyOTPController::class);
Route::post('auth/register/token/resend', [RegisterController::class, 'resendOTP']);

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
        Route::get('user/search/friends', GetFriendsController::class);
        Route::get('game/common', [GameController::class, 'getCommonData'])->middleware(['cacheResponse:300']);
        Route::get('achievement-badges', [AchievementBadeController::class, 'getAchievements']);
        Route::get('fetch/trivia', [TriviaController::class, 'getTrivia'])->middleware(['cacheResponse:300']);
        Route::get('live-trivia/recent', GetRecentLiveTriviaController::class)->middleware(['cacheResponse:60']);
        Route::get('trivia/leaders/{triviaId}', [TriviaController::class, 'getLiveTriviaLeaderboard'])->middleware(['cacheResponse:300']);
        Route::get('live-trivia/{id}/leaderboard', GetLiveTriviaLeaderboardController::class)->middleware(['cacheResponse:300']);
        Route::get('live-trivia/{id}/get', GetSingleLiveTriviaController::class)->middleware(['cacheResponse:300']);
        Route::get('live-trivia/status', LiveTriviaStatusController::class)->middleware(['cacheResponse:300']);
        Route::post('live-trivia/entrance/pay', LiveTriviaEntrancePaymentController::class);
        Route::get('live-trivia/{id}/status', LiveTriviaStatusController::class);
        Route::post('challenge/send-invite', SendChallengeInviteController::class);
        Route::post('challenge/invite/respond', ChallengeInviteStatusController::class);
        Route::get('challenge/{challengeId}/details', GetChallengeDetailsController::class)->middleware(['cacheResponse:60']);
        Route::post('challenge/start/game', StartChallengeGameController::class);
        Route::post('challenge/end/game', EndChallengeGameController::class);
        Route::get('challenge/{challengeId}/leaderboard', GetChallengeLeaderboardController::class);
        Route::post('challenge/leaders/global', ChallengeGlobalLeadersController::class);
        Route::get('user/challenges', GetUserChallengeController::class)->middleware(['cacheResponse:300']);
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
        Route::post('points/buy-boosts/{boostId}', [WalletController::class, 'buyBoostsWithPoints']);
        Route::post('wallet/buy-boosts/{boostId}', [WalletController::class, 'buyBoostsFromWallet']);
        Route::post('purchased/item', [WalletController::class, 'itemPurchased']);
        Route::post('plan/subscribe/{planId}', [WalletController::class, 'subscribeToPlan']);
        Route::post('claim/achievement/{achievementId}', [GameController::class, 'claimAchievement']);
        Route::post('game/start/single-player', StartSinglePlayerGameController::class);
        Route::post('game/end/single-player', [GameController::class, 'endSingleGame']);
        Route::post('leaders/global', GetGlobalLeaderboardController::class);
        Route::post('leaders/categories', [LeadersController::class, 'categoriesLeaders']);
        Route::post('account/delete', [UserController::class, 'deleteAccount']);
        Route::delete('account/delete', [UserController::class, 'deleteAccount']);
        Route::get('contests', GetContestDetailsController::class);
        Route::get('contest/{id}', GetSingleContestController::class)->middleware(['cacheResponse:300']);
        Route::get('stakers/sessions/recent', GetStakersSessionController::class)->middleware(['cacheResponse:300']);
        Route::post('stakers/email/verify', EmailOtpVerificationController::class);
        Route::post('stakers/otp/send', SendOtpToEmailController::class);
        Route::post('challenges/create', StartChallengeRequestController::class);
        Route::post('challenges/submit', App\Http\Controllers\PlayGame\EndChallengeGameController::class);
        Route::post('claim/user-reward', ClaimUserRewardController::class);
    }
);

Route::prefix('v2')->group(function () {
    Route::post('client/feedback', [MessagesController::class, 'feedback']);
    Route::get('faq/fetch', [MessagesController::class, 'fetchFaqAndAnswers']);

    Route::middleware(['auth:api'])->group(function () {
        Route::post('profile/me/edit-personal', [ProfileController::class, 'editPersonalInformation']);
        Route::post('profile/me/edit-bank', [ProfileController::class, 'editBank']);
        Route::post('profile/me/picture', [ProfileController::class, 'addProfilePic']);
        Route::post('profile/me/password/change', [ProfileController::class, 'changePassword']);

        Route::get('wallet/me', [WalletController::class, 'me']);
        Route::get('wallet/me/transactions', [WalletController::class, 'transactions']);
        Route::get('wallet/me/transactions/earnings', [WalletController::class, 'earnings']);
        Route::get('wallet/me/transaction/verify/{reference}', [WalletController::class, "verifyTransaction"]);
        Route::get('wallet/banks', [WalletController::class, 'getBanks']);
        Route::post('points/buy-boosts/{boostId}', [WalletController::class, 'buyBoostsWithPoints']);
        Route::post('wallet/buy-boosts/{boostId}', [WalletController::class, 'buyBoostsFromWallet']);
        Route::post('plan/subscribe/{planId}', [WalletController::class, 'subscribeToPlan']);
       Route::post('claim/achievement/{achievementId}', [GameController::class, 'claimAchievement']);
        Route::post('game/start/single-player', StartSinglePlayerGameController::class);
        Route::post('game/end/single-player', [GameController::class, 'endSingleGame']);

        //updated leaders endpoint
        Route::post('leaders/global', [LeadersController::class, 'globalLeaders']);
        Route::post('leaders/categories', [LeadersController::class, 'categoriesLeaders']);

        Route::get('user/fetch/notifications', [MessagesController::class, 'fetchNotifications']);
        Route::post('user/read/notification/{notificationId}', [MessagesController::class, 'readNotification']);
        Route::post('user/read/all/notifications', [MessagesController::class, 'readAllNotifications']);
    });
});
