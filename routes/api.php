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

    Route::post('/confirm-payment/{user_id}', ['uses' => 'NotificationConfirmController@confirmPayment', 'as' => 'api.notification.confirm.payment']);
    Route::post('/delivery/approved/{user_id}', ['uses' => 'NotificationDeliveryApprovedController@deliveryApproved', 'as' => 'api.notification.delivery.approved']);
    Route::post('/pickup/approved/{user_id}', ['uses' => 'NotificationPickupApprovedController@pickupApproved', 'as' => 'api.notification.pickup.approved']);
    Route::post('/item-save/{user_id}', ['uses' => 'NotificationItemSaveController@itemSave', 'as' => 'api.notification.item.save']);
    Route::post('/item-stored/{user_id}', ['uses' => 'NotificationItemStoredController@itemStored', 'as' => 'api.notification.item.stored']);
    Route::post('/delivery/stored/{user_id}', ['uses' => 'NotificationDeliveryStoredController@deliveryStored', 'as' => 'api.notification.delivery.stored']);
    Route::post('/pickup/stored/{user_id}', ['uses' => 'NotificationPickupStoredController@pickupStored', 'as' => 'api.notification.pickup.stored']);
    Route::post('/return-request/{user_id}', ['uses' => 'NotificationReturnRequestController@returnRequest', 'as' => 'api.notification.return.request']);
    Route::post('/returned/{user_id}', ['uses' => 'NotificationReturnedController@returned', 'as' => 'api.notification.returned']);

});
