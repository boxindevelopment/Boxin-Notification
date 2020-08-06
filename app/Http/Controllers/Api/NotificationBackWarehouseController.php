<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\OrderDetail;
use App\Http\Resources\OrderDetailResource;
use App\Jobs\Notif\SendNotifAdmin;
use App\Jobs\Notif\SendNotifUser;
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

	public function status(Request $request, $return_id)
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
		if($request->status_id != 2 && $request->status_id != 4 && $request->status_id != 26){
            return response()->json([
                'status' => false,
                'message' => 'status_id required only number 2 dan 4'
			], 422);
		}

		$orderBackWarehouse =  OrderBackWarehouse::select('order_back_warehouses.*', 'order_details.id_name', 'order_details.types_of_box_room_id', 'users.first_name', 'users.last_name', DB::raw('orders.status_id as status_order_id'), DB::raw('orders.user_id as user_id'))
                                    ->leftJoin('order_details', 'order_details.id', '=', 'order_back_warehouses.order_detail_id')
                                    ->leftJoin('orders', 'orders.id', '=', 'order_details.order_id')
                                    ->leftJoin('users', 'users.id', '=', 'orders.user_id')
						            ->where('order_back_warehouses.id', $return_id)
						            ->first();
		if($orderBackWarehouse) {
			$status = ($orderBackWarehouse->status_id == 2) ? 'On Delivery' : (($orderBackWarehouse->status_id == 26) ? 'Return Request' : 'Stored');
			$orderDetails =  OrderDetail::select('order_details.*', DB::raw('orders.status_id as status_id'), DB::raw('orders.user_id as user_id'), DB::raw('DATEDIFF(day, order_details.start_date, order_details.end_date) as total_time'), DB::raw('DATEDIFF(day, order_details.start_date, GETDATE()) as selisih'))
										->leftJoin('orders', 'orders.id', '=', 'order_details.order_id')
										->where('order_details.id', $orderBackWarehouse->order_detail_id)
										->first();
			$data = New OrderDetailResource($orderDetails);
			$boxSpaces = ($orderBackWarehouse->types_of_box_room_id == 1) ? 'box' : 'space';
			$title = "no order " . $orderBackWarehouse->id_name . ", status return ".$boxSpaces." is " . $status;
	        SendNotifUser::dispatch($orderBackWarehouse->user_id, $title, $data, 'return-' . $status, 'return ' . $status)->onQueue('processing');
			return response()->json(['status' => 'success', 'message' => $title], 200);
		}
	}

	public function request(Request $request, $return_id)
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
		$orderBackWarehouse =  OrderBackWarehouse::select('order_back_warehouses.*', 'order_details.id_name', 'order_details.types_of_box_room_id', 'users.first_name', 'users.last_name', DB::raw('orders.status_id as status_order_id'), DB::raw('orders.user_id as user_id'))
                                    ->leftJoin('order_details', 'order_details.id', '=', 'order_back_warehouses.order_detail_id')
                                    ->leftJoin('orders', 'orders.id', '=', 'order_details.order_id')
                                    ->leftJoin('users', 'users.id', '=', 'orders.user_id')
						            ->where('order_back_warehouses.id', $return_id)
						            ->first();

		if($orderBackWarehouse) {

			$boxSpace = ($orderBackWarehouse->types_of_box_room_id == 1) ? 'boxes' : 'space';
			$date = date("d/m/Y", strtotime($orderBackWarehouse->date));
			$time = $orderBackWarehouse->time;
			$title = "Customer " . $orderBackWarehouse->first_name . " " . $orderBackWarehouse->last_name . ", make a request to return the  " . $boxSpace . ' order '. $orderBackWarehouse->id_name . ' on ' . $date . ' at ' . substr($time, 0, 5);
	        SendNotifAdmin::dispatch($return_id, $title, $orderBackWarehouse, 'reminder-return-request', 'a reminder return request')->onQueue('processing');
			return response()->json(['status' => 'success', 'message' => $title], 200);
		}
	}

}
