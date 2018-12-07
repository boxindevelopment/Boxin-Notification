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

Route::middleware('auth:api')->get('/user', function (Request $request) {
    return $request->user();
});

Route::group(['namespace' => 'Api'], function () {

    Route::prefix('user')->middleware('auth:api')->group(function() {
        Route::post('/device-token', ['uses' => 'UserDeviceController@deviceToken', 'as' => 'api.user_device.postToken']);
        Route::delete('/device-token', ['uses' => 'UserDeviceController@deviceTokenDelete', 'as' => 'api.user_device.deleteToken']);
    });

    Route::post('/confirm-payment/{user_id}', ['uses' => 'NotificationController@confirmPayment', 'as' => 'api.notification.confirmPayment']);
    Route::post('/delivery/approved', ['uses' => 'NotificationController@DeliveryApproved', 'as' => 'api.notification.DeliveryApproved']);
    Route::post('/pickup/approved', ['uses' => 'NotificationController@pickupApproved', 'as' => 'api.notification.pickupApproved']);
    Route::post('/item-save', ['uses' => 'NotificationController@itemSave', 'as' => 'api.notification.itemSave']);
    Route::post('/item-stored', ['uses' => 'NotificationController@itemStored', 'as' => 'api.notification.itemStored']);
    Route::post('/delivery/stored', ['uses' => 'NotificationController@DeliveryStored', 'as' => 'api.notification.DeliveryStored']);
    Route::post('/pickup/stored', ['uses' => 'NotificationController@pickupStored', 'as' => 'api.notification.pickupStored']);
    Route::post('/return-request', ['uses' => 'NotificationController@returnRequest', 'as' => 'api.notification.returnRequest']);
    Route::post('/returned', ['uses' => 'NotificationController@returned', 'as' => 'api.notification.returned']);

});
