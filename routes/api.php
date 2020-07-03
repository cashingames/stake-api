<?php

use Illuminate\Support\Facades\Route;

Route::post('auth/login', 'Auth\LoginController@login')->middleware('api');
Route::post('auth/register', 'Auth\RegisterController@register')->middleware('api');
Route::post('auth/logout', 'Auth\LoginController@logout')->middleware('api');
Route::post('auth/refresh', 'Auth\Login@refresh')->middleware('api');
Route::post('auth/email/is-valid', 'ProfileController@checkEmail')->middleware('api');
Route::post('auth/password/email', 'Auth\ForgotPasswordController@sendEmail')->middleware('api');
Route::post('auth/password/verify-token', 'Auth\ForgotPasswordController@verifyToken')->middleware('api');
Route::post('auth/password/reset', 'Auth\ResetPasswordController@reset')->middleware('api');

Route::get('v1/game/leaders', 'GameController@leaders')->middleware('auth:api');
Route::get('v1/game/rank', 'GameController@rank')->middleware('auth:api');
Route::post('v1/game/end/{sessionToken}', 'GameController@end')->middleware('auth:api');
Route::post('v1/game/fetch-question/{sessionToken}', 'GameController@saveQuestionResponse')->middleware('auth:api');
Route::get('v1/game/fetch-question/{sessionToken}', 'GameController@fetchQuestion')->middleware('auth:api');
Route::post('v1/game/fetch-submit-question/{sessionToken}', 'GameController@fetchSubmitQuestion')->middleware('auth:api');
Route::post('v1/game/start', 'GameController@start')->middleware('auth:api');
Route::get('v1/categories', 'CategoryController@get')->middleware('auth:api');
Route::post('v1/plans/me/subscribe', 'PlanController@subscribe')->middleware('auth:api');
Route::get('v1/plans', 'PlanController@get')->middleware('auth:api');
Route::get('v1/wallet/me/transaction/verify/{reference}', 'WalletController@verifyTransaction')->middleware('auth:api');
Route::get('v1/wallet/me/transactions', 'WalletController@transactions')->middleware('auth:api');
Route::get('v1/wallet/me', 'WalletController@me')->middleware('auth:api');
Route::get('v1/wallet/banks', 'WalletController@getBanks')->middleware('auth:api');
Route::post('v1/profile/me/edit', 'ProfileController@edit')->middleware('auth:api');
Route::post('v1/profile/me/edit-personal', 'ProfileController@editPersonalInformation')->middleware('auth:api');
Route::post('v1/profile/me/edit-bank', 'ProfileController@editBank')->middleware('auth:api');
Route::post('v1/profile/me/picture', 'ProfileController@addProfilePic')->middleware('auth:api');
Route::get('v1/profile/me', 'ProfileController@me')->middleware('auth:api');

Route::get('v1/user/me/plans', 'UserController@plans')->middleware('auth:api');
Route::get('v1/user/me', 'UserController@me')->middleware('auth:api');
Route::post('v1/log/error', 'UserController@logError')->middleware('auth:api');
Route::post('v1/voucher/consume/{code}','VoucherController@consume')->middleware('auth:api');
Route::post('v1/voucher/generate', 'VoucherController@generate');
