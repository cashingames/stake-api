<?php

use App\Http\Controllers\UpdateEmailVerifiedController;
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

Route::get('/redirect-verified-email/{email}', UpdateEmailVerifiedController::class);

