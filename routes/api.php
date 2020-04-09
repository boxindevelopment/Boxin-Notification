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
        Route::get('/notifications', ['uses' => 'NotificationController@index', 'as' => 'api.notification.index']);
    });

    //1/2 Admin PaymentController function update status = 7 (Approved), 8 = (Rejected)
    Route::post('/confirm-payment/{user_id}', ['uses' => 'NotificationConfirmController@confirmPayment', 'as' => 'api.notification.confirm.payment']);
    Route::post('/delivery/approved/{user_id}', ['uses' => 'NotificationDeliveryApprovedController@deliveryApproved', 'as' => 'api.notification.delivery.approved']);
    Route::post('/pickup/approved/{user_id}', ['uses' => 'NotificationPickupApprovedController@pickupApproved', 'as' => 'api.notification.pickup.approved']);
    //5. Admin PickupController function update status = 12
    Route::post('/item-save/{user_id}', ['uses' => 'NotificationItemSaveController@itemSave', 'as' => 'api.notification.item.save']);
    Route::post('/item-stored/{user_id}', ['uses' => 'NotificationItemStoredController@itemStored', 'as' => 'api.notification.item.stored']);
    //4. Admin PickupController function update status = 2
    Route::post('/delivery/stored/{user_id}', ['uses' => 'NotificationDeliveryStoredController@deliveryStored', 'as' => 'api.notification.delivery.stored']);
    Route::post('/pickup/stored/{user_id}', ['uses' => 'NotificationPickupStoredController@pickupStored', 'as' => 'api.notification.pickup.stored']);
    Route::post('/return-request/{user_id}', ['uses' => 'NotificationReturnRequestController@returnRequest', 'as' => 'api.notification.return.request']);
    Route::post('/cron/return-request/{user_id}', ['uses' => 'NotificationReturnRequestController@returnRequestCron', 'as' => 'api.notification.return.requestcron']);
    //9. Admin ReturnBoxesController function update status = 12
    Route::post('/returned/{user_id}', ['uses' => 'NotificationReturnedController@returned', 'as' => 'api.notification.returned']);
    
    Route::post('/take/{take_id}', ['uses' => 'NotificationTakeController@take', 'as' => 'api.notification.take']);
    Route::post('/extend', ['uses' => 'NotificationExtendController@extend', 'as' => 'api.notification.extend']);
    Route::post('/backwarehouse/{return_id}', ['uses' => 'NotificationBackWarehouseController@backwarehouse', 'as' => 'api.notification.backwarehouse']);
    Route::post('/terminate/{terminate_id}', ['uses' => 'NotificationTerminateController@terminate', 'as' => 'api.notification.terminate']);

    Route::get('/payment-email/{order_id}', ['uses' => 'NotificationEmailPaymentController@send_email', 'as' => 'api.notification.email.payment']);

    Route::post('/contact', ['uses' => 'ContactController@store', 'as' => 'api.contact.store']);
    Route::post('/cron-minutes', ['uses' => 'CronController@minutes', 'as' => 'api.cron.minutes']);
    Route::post('/cron-days', ['uses' => 'CronController@days', 'as' => 'api.cron.days']);

    Route::post('/cron/take', ['uses' => 'CronController@takeBoxes', 'as' => 'api.cron.takeBoxes']);
    Route::post('/cron/return', ['uses' => 'CronController@returnBoxes', 'as' => 'api.cron.returnBoxes']);
    Route::post('/cron/terminate', ['uses' => 'CronController@terminate', 'as' => 'api.cron.terminate']);
    
    Route::post('/pickup/status/{pickup_id}', ['uses' => 'NotificationPickupController@status', 'as' => 'api.notification.pickup.take']);
    Route::post('/take/status/{take_id}', ['uses' => 'NotificationTakeController@status', 'as' => 'api.notification.take.status']);
    Route::post('/return/status/{return_id}', ['uses' => 'NotificationBackWarehouseController@status', 'as' => 'api.notification.backwarehouse.status']);
    Route::post('/terminate/status/{terminate_id}', ['uses' => 'NotificationTerminateController@status', 'as' => 'api.notification.terminate.status']);
    
    Route::post('/create-voucher', ['uses' => 'NotificationVoucherController@createvoucher', 'as' => 'api.notification.create.voucher']);

    Route::post('/take/request/{take_id}', ['uses' => 'NotificationTakeController@request', 'as' => 'api.notification.take.request']);
    Route::post('/backwarehouse/request/{take_id}', ['uses' => 'NotificationBackWarehouseController@request', 'as' => 'api.notification.backwarehouse.request']);
    Route::post('/terminate/request/{take_id}', ['uses' => 'NotificationTerminateController@request', 'as' => 'api.notification.terminate.request']);
});
