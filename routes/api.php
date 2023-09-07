<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\GameController;
use App\Http\Controllers\AchievementBadeController;
use App\Http\Controllers\AddUserCategoryController;
use App\Http\Controllers\AdsRewardController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\WalletController;
use App\Http\Controllers\LeadersController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\MessagesController;
use App\Http\Controllers\VerifyOTPController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\FrontEndLogsController;
use App\Http\Controllers\SocialSignInController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\Auth\ResetPasswordController;
use App\Http\Controllers\Auth\ForgotPasswordController;
use App\Http\Controllers\RegisterPushDeviceTokenController;
use App\Http\Controllers\Auth\AuthenticateVerifiedUserController;
use App\Http\Controllers\ClaimHallmarkLevelRewardsController;
use App\Http\Controllers\ClaimUserRewardController;
use App\Http\Controllers\FeatureFlagController;
use App\Http\Controllers\GetBubbleBlitzGameModesController;
use App\Http\Controllers\GetDailyObjectiveController;
use App\Http\Controllers\MissUserRewardController;
use App\Http\Controllers\PlayGame\StartSinglePlayerGameController;
use App\Http\Controllers\GetGameController;
use App\Http\Controllers\GetUserCategoriesController;
use App\Http\Controllers\RemoveUserCategoryController;
use App\Http\Controllers\UpdateGuestPlayerProfileController;

Route::post('auth/register', [RegisterController::class, 'register']);
Route::post('auth/login', [LoginController::class, 'login']);
Route::post('auth/social-login/authenticate', [SocialSignInController::class, 'authenticateUser']);
Route::post('auth/social-login/create-account', [SocialSignInController::class, 'createUser']);
Route::post('auth/password/email', [ForgotPasswordController::class, 'sendEmail']);
Route::post('auth/token/verify', [ForgotPasswordController::class, 'verifyToken']);
Route::post('auth/password/reset', [ResetPasswordController::class, 'reset']);
Route::post('auth/user/authenticate', AuthenticateVerifiedUserController::class);
Route::post('auth/register/verify-token', VerifyOTPController::class);
Route::post('auth/register/token/resend', [RegisterController::class, 'resendOTP']);

Route::middleware('api')->prefix('v3')->group(
    function () {
        Route::post('client/feedback', [MessagesController::class, 'feedback']);
        Route::post('log/frontend-info', FrontEndLogsController::class);
        Route::get('feature-flags', [FeatureFlagController::class, 'index'])->middleware(['cacheResponse:300']);
    }
);

Route::middleware(['auth:api'])->prefix('v3')->group(
    function () {
        Route::get('user/profile', [UserController::class, 'profile'])->middleware(['last_active']);
        Route::get('game/common', [GameController::class, 'getCommonData'])->middleware(['cacheResponse:300']);
        Route::get('achievement-badges', [AchievementBadeController::class, 'getAchievements']);

        Route::post('fcm/subscriptions', RegisterPushDeviceTokenController::class);
        Route::post('profile/me/edit-personal', [ProfileController::class, 'editPersonalInformation']);
        Route::post('profile/me/picture', [ProfileController::class, 'addProfilePic']);
        Route::post('guest/profile/update', UpdateGuestPlayerProfileController::class);
        Route::post('profile/me/password/change', [ProfileController::class, 'changePassword']);
        Route::post('referrer/update', [ProfileController::class, 'updateReferrer']);
        Route::post('purchased/item', [WalletController::class, 'itemPurchased']);
        Route::post('game/start/single-player', StartSinglePlayerGameController::class);
        Route::post('game/end/single-player', [GameController::class, 'endSingleGame']);
        Route::post('leaders/global', [LeadersController::class, 'globalLeaders']);
        Route::post('leaders/categories', [LeadersController::class, 'categoriesLeaders']);
        Route::post('account/delete', [UserController::class, 'deleteAccount']);
        Route::delete('account/delete', [UserController::class, 'deleteAccount']);
        Route::post('user-reward/claim', ClaimUserRewardController::class);
        Route::post('user-reward/miss', MissUserRewardController::class);
        Route::post('ads-reward/award', AdsRewardController::class);
        Route::get('games', GetGameController::class);
        Route::get('bubble-blitz/modes', GetBubbleBlitzGameModesController::class);
        Route::post('trivia-quest/add-categories', AddUserCategoryController::class);
        Route::post('trivia-quest/remove-categories', RemoveUserCategoryController::class);
        Route::post('levels-reward/claim', ClaimHallmarkLevelRewardsController::class);
        Route::get('trivia-quest/daily-objectives', GetDailyObjectiveController::class);
        Route::get('trivia-quest/user-categories', GetUserCategoriesController::class);
    }
);



