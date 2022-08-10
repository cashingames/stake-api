<?php

use App\Http\Controllers\RedirectUnverifiedUserController;
use App\Services\Firebase\CloudMessagingService;
use Illuminate\Support\Facades\Route;

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

Route::get('/salient-testing', function () {
    return view('welcome');
});

Route::get('/debug-sentry', function () {
    throw new Exception('Sentry error!');
});

Route::get('/redirect-respond-to-challenge', function () {
    return view('redirectToRespondToChallenge');
});
Route::get('/redirect-home', function () {
    return view('redirectToHome');
});
Route::get('/redirect-instructions', function () {
    return view('redirectToInstructions');
});

Route::get('/redirect-verified-email/{email}', RedirectUnverifiedUserController::class);

Route::get('test-fcm', function(){
    $token = request()->get('device_token') ?? "cP4paGIpQOeXWkQsthIHTP:APA91bEiNMKotfTRFTmb6w8Zep19ZvMZQcOTQ-0rSleKC4fvsHMaY7ukTdpOQ81c6VLgr2k-Af2NGHkDlG03-WojelwP0g4nc-QAvIZc1N6VqgbaUnD0G3Ku7gcKm3cbp-_JdiN8vIwd";
    $messenger = new CloudMessagingService(config('services.firebase.server_key'));
    $res = $messenger
    ->setNotification([
        'title' => "Welcome to cashingames",
        'body' => "It's our whole pleasure to have you here",
        'sound' => 'default',
    ])
    ->setData([
        'subject' => "A warm welcome",
        'content' => 'Can be the same body',
        'action_type' => "CHALLENGE",
        'action_id' => 11,
        'experienceId' => 'cashingames/cashingames',
        'scopeKey' => 'cashingames/cashingames',
    ])->setTo($token)
    ->send();
    return $res;
});
