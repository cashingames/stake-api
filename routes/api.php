<?php

// Route::middleware('auth:api')->group( function () {
//     Route::resource('products', 'API\ProductController');
// });

Route::group([
    'middleware' => 'api',
    'prefix' => 'auth'
], function ($router) {
    Route::post('login', 'Auth\LoginController@login');
    Route::post('register', 'Auth\RegisterController@register');
    Route::post('logout', 'Auth\LoginController@logout');
    Route::post('refresh', 'Auth\Login@refresh');
    Route::post('email/is-valid', 'ProfileController@checkEmail');
});

Route::group([
    'middleware' => 'auth:api',
    'prefix' => 'v1'
], function ($router) {
    Route::post('plans/me/subscribe', 'PlanController@subscribe');
    Route::get('plans', 'PlanController@get');
    Route::get('wallet/me/transaction/verify/{reference}', 'WalletController@verifyTransaction');
    Route::get('wallet/me/transactions', 'WalletController@transactions');
    Route::get('wallet/me', 'WalletController@me');
    Route::get('profile/me', 'ProfileController@me');
    Route::get('user/me/plans', 'UserController@plans');
    Route::get('user/me', 'UserController@me');
});

