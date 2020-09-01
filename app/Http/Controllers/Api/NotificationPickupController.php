<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Jobs\Notif\SendNotifAdmin;
use App\Jobs\Notif\SendNotifUser;
use App\Models\PickupOrder;
use DB;
use Illuminate\Http\Request;

class NotificationPickupController extends Controller {

	public function __construct() {

	}

	public function status(Request $request, $pickup_id)
	{

        $validator = \Validator::make($request->all(), [
            'status_id'   		=> 'required',
            'order_detail_id'   => 'required',
        ]);

        if($validator->fails()) {
            return response()->json([
                'status' => false,
                'message' => $validator->errors()
			], 422);
		}
		if($request->status_id != 2 && $request->status_id != 4){
            return response()->json([
                'status' => false,
                'message' => 'status_id required only number 2 dan 4'
			], 422);
		}

		$pickup =  PickupOrder::select('pickup_orders.*', 'order_details.id_name', 'order_details.types_of_box_room_id', 'order_details.order_id', 'users.first_name', 'users.last_name', DB::raw('orders.status_id as status_order_id'), DB::raw('orders.user_id as user_id'))
									->leftJoin('orders', 'orders.id', '=', 'pickup_orders.order_id')
									->leftJoin('order_details', 'orders.id', '=', 'order_details.order_id')
									->leftJoin('users', 'users.id', '=', 'orders.user_id')
									->where('pickup_orders.id', $pickup_id)
									->first();

		$status = ($pickup->status_id == 2) ? 'On Delivery' : 'Stored';

		if($pickup) {
			$title = "no order " . $pickup->id_name . ", status pickup order is " . $status;
	        SendNotifUser::dispatch($pickup->user_id, $title, $pickup, 'pickup-' . $status, 'pickup ' . $status)->onQueue('processing');
			return response()->json(['status' => 'success', 'message' => $title], 200);
		}
	}
}
