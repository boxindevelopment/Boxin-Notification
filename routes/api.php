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

Route::group(['namespace' => 'Api'], function () {
    
    Route::group(['middleware' => 'jwt.auth'], function () {
        Route::post('/device-token', ['uses' => 'UsersController@deviceToken', 'as' => 'api.user.postToken']);
        Route::delete('/device-token', ['uses' => 'UsersController@deviceTokenDelete', 'as' => 'api.user.deleteToken']);
    });

});
