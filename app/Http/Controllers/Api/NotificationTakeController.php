<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\OrderDetailResource;
use App\Jobs\Notif\SendNotifAdmin;
use App\Models\OrderTake;
use DB;
use Illuminate\Http\Request;

class NotificationTakeController extends Controller {

	public function __construct() {

	}

	/**
	 * Store a newly created resource in storage.
	 *
	 * @param  \Illuminate\Http\Request  $request
	 * @return \Illuminate\Http\Response
	 */
	public function take(Request $request, $take_id)
	{

        $validator = \Validator::make($request->all(), [
            'order_detail_id'   => 'required',
        ]);

        if($validator->fails()) {
            return response()->json([
                'status' => false,
                'message' => $validator->errors()
            ]);
        }
		$orderTake =  OrderTake::select('order_takes.*', 'order_details.id_name', 'users.first_name', 'users.last_name', DB::raw('orders.status_id as status_order_id'), DB::raw('orders.user_id as user_id'))
                                    ->leftJoin('order_details', 'order_details.id', '=', 'order_takes.order_detail_id')
                                    ->leftJoin('orders', 'orders.id', '=', 'order_details.order_id')
                                    ->leftJoin('users', 'users.id', '=', 'orders.user_id')
						            ->where('order_takes.id', $take_id)
						            ->first();

		if($orderTake) {
			$title = "user " . $orderTake->first_name . " " . $orderTake->last_name . ", take request no order " . $orderTake->id_name;
	        SendNotifAdmin::dispatch($take_id, $title, $orderTake, 'take-request', 'take request')->onQueue('processing');
			return response()->json(['status' => 'success', 'message' => $title], 200);
		}
	}
}
