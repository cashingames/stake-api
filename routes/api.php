<?php

use Illuminate\Http\Request;

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

// Route::middleware('auth:api')->get('/user', function (Request $request) {
//     return $request->user();
// });

// Route::post('register', 'Auth\RegisterController@register');
// Route::post('login', 'API\RegisterController@login');

Route::middleware('auth:api')->group( function () {
    Route::resource('products', 'API\ProductController');
});

// Route::prefix('v1')->group(function(){

//     Route::post('login', 'Auth\LoginController@login');
//     Route::post('register', 'Auth\RegisterController@register');

//     Route::group(['middleware' => 'auth:api'], function(){
//         Route::post('getUser', 'Api\AuthController@getUser');
//     });

// });

Route::group([

    'middleware' => 'api',
    'prefix' => 'auth'

], function ($router) {

    Route::post('login', 'Auth\LoginController@login');
    Route::post('register', 'Auth\RegisterController@register');
    Route::post('logout', 'Auth\LoginController@logout');
    Route::post('refresh', 'Auth\Login@refresh');
    Route::post('me', 'UserController@me');

});
