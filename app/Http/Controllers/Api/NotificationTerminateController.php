<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Jobs\Notif\SendNotifAdmin;
use App\Models\ReturnBoxes;
use DB;
use Illuminate\Http\Request;

class NotificationTerminateController extends Controller {

	public function __construct() {

	}

	/**
	 * Store a newly created resource in storage.
	 *
	 * @param  \Illuminate\Http\Request  $request
	 * @return \Illuminate\Http\Response
	 */
	public function terminate(Request $request, $terminate_id)
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
		$terminate =  ReturnBoxes::select('return_boxes.*', 'order_details.id_name', 'users.first_name', 'users.last_name', DB::raw('orders.status_id as status_order_id'), DB::raw('orders.user_id as user_id'))
									->leftJoin('order_details', 'order_details.id', '=', 'return_boxes.order_detail_id')
									->leftJoin('orders', 'orders.id', '=', 'order_details.order_id')
									->leftJoin('users', 'users.id', '=', 'orders.user_id')
									->where('return_boxes.id', $terminate_id)
									->first();

		if($terminate) {
			$title = "user " . $terminate->first_name . " " . $terminate->last_name . ", terminate request no order " . $terminate->id_name;
	        SendNotifAdmin::dispatch($terminate_id, $title, $terminate, 'terminate-request', 'terminate request')->onQueue('processing');
			return response()->json(['status' => 'success', 'message' => $title], 200);
		}
	}
}
