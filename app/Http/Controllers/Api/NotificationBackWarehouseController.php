<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\OrderDetailResource;
use App\Jobs\Notif\SendNotifAdmin;
use App\Models\OrderBackWarehouse;
use DB;
use Illuminate\Http\Request;

class NotificationBackWarehouseController extends Controller {

	public function __construct() {

	}

	/**
	 * Store a newly created resource in storage.
	 *
	 * @param  \Illuminate\Http\Request  $request
	 * @return \Illuminate\Http\Response
	 */
	public function backwarehouse(Request $request, $return_id)
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
		$orderBackWarehouse =  OrderBackWarehouse::select('order_back_warehouses.*', 'order_details.id_name', 'users.first_name', 'users.last_name', DB::raw('orders.status_id as status_order_id'), DB::raw('orders.user_id as user_id'))
                                    ->leftJoin('order_details', 'order_details.id', '=', 'order_back_warehouses.order_detail_id')
                                    ->leftJoin('orders', 'orders.id', '=', 'order_details.order_id')
                                    ->leftJoin('users', 'users.id', '=', 'orders.user_id')
						            ->where('order_back_warehouses.id', $return_id)
						            ->first();

		if($orderBackWarehouse) {

			$title = "user " . $orderBackWarehouse->first_name . " " . $orderBackWarehouse->last_name . ", return request no order " . $orderBackWarehouse->id_name;
	        SendNotifAdmin::dispatch($return_id, $title, $orderBackWarehouse, 'return-request', 'return request')->onQueue('processing');
			return response()->json(['status' => 'success', 'message' => $title], 200);
		}
	}
}
