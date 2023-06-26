<?php

use App\Enums\PushNotificationType;
use Illuminate\Support\Facades\Route;
use App\Services\Firebase\CloudMessagingService;
use App\Http\Controllers\RedirectUnverifiedUserController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/
Route::get('/redirect-verified-email/{email}', RedirectUnverifiedUserController::class);

Route::get('test-fcm', function(){
    $token = request()->get('device_token') ?? "cP4paGIpQOeXWkQsthIHTP:APA91bEiNMKotfTRFTmb6w8Zep19ZvMZQcOTQ-0rSleKC4fvsHMaY7ukTdpOQ81c6VLgr2k-Af2NGHkDlG03-WojelwP0g4nc-QAvIZc1N6VqgbaUnD0G3Ku7gcKm3cbp-_JdiN8vIwd";
    $messenger = new CloudMessagingService(config('services.firebase.server_key'));
    $res = $messenger
    ->setNotification([
        'title' => "Welcome to cashingames",
        'body' => "It's our whole pleasure to have you here",
        'sound' => 'default',
        'data' => [
            'heading' => "You have been invited to a new challenge",
            'desc' => "Your friend, from notification",
            'action_type' => PushNotificationType::Challenge,
            'action_id' => 194
        ]
    ])
    ->setData([
        'title' => "You have been invited to a new challenge",
        'body' => "Your friend, Sade has just sent you a challenge invite",
        'action_type' => PushNotificationType::Challenge,
        'action_id' => 194
    ])->setTo($token)
    ->send();
    return $res;
});

