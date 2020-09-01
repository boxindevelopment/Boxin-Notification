<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\OrderDetail;
use App\Http\Resources\OrderDetailResource;
use App\Jobs\Notif\SendNotifAdmin;
use App\Jobs\Notif\SendNotifUser;
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

	public function status(Request $request, $take_id)
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

		$orderTake =  OrderTake::select('order_takes.*', 'order_details.id_name', 'users.first_name', 'users.last_name', DB::raw('orders.status_id as status_order_id'), DB::raw('orders.user_id as user_id'))
                                    ->leftJoin('order_details', 'order_details.id', '=', 'order_takes.order_detail_id')
                                    ->leftJoin('orders', 'orders.id', '=', 'order_details.order_id')
                                    ->leftJoin('users', 'users.id', '=', 'orders.user_id')
						            ->where('order_takes.id', $take_id)
						            ->first();
		if($orderTake) {
			$status = ($orderTake->status_id == 2) ? 'On Delivery' : 'Stored';
			$orderDetails =  OrderDetail::select('order_details.*', DB::raw('orders.status_id as status_id'), DB::raw('orders.user_id as user_id'), DB::raw('DATEDIFF(day, order_details.start_date, order_details.end_date) as total_time'), DB::raw('DATEDIFF(day, order_details.start_date, GETDATE()) as selisih'))
										->leftJoin('orders', 'orders.id', '=', 'order_details.order_id')
										->where('order_details.id', $orderTake->order_detail_id)
										->first();
			$data = New OrderDetailResource($orderDetails);
			$title = "no order " . $orderTake->id_name . ", status take request is " . $status;
	        SendNotifUser::dispatch($orderTake->user_id, $title, $data, 'take-' . $status, 'take ' . $status)->onQueue('processing');
			return response()->json(['status' => 'success', 'message' => $title], 200);
		}
	}

	public function request(Request $request, $take_id)
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
		$orderTake =  OrderTake::select('order_takes.*', 'order_details.id_name', 'order_details.types_of_box_room_id', 'users.first_name', 'users.last_name', DB::raw('orders.status_id as status_order_id'), DB::raw('orders.user_id as user_id'))
                                    ->leftJoin('order_details', 'order_details.id', '=', 'order_takes.order_detail_id')
                                    ->leftJoin('orders', 'orders.id', '=', 'order_details.order_id')
                                    ->leftJoin('users', 'users.id', '=', 'orders.user_id')
						            ->where('order_takes.id', $take_id)
						            ->first();
		if($orderTake) {
			$boxSpace = ($orderTake->types_of_box_room_id == 1) ? 'boxes' : 'space';
			$date = date("d/m/Y", strtotime($orderTake->date));
			$time = $orderTake->time;
			$title = "Customer " . $orderTake->first_name . " " . $orderTake->last_name . ", make a request to take the  " . $boxSpace . ' order '. $orderTake->id_name . ' on ' . $date . ' at ' . substr($time, 0, 5);
	        SendNotifAdmin::dispatch($take_id, $title, $orderTake, 'reminder-take-request', 'a reminder take request')->onQueue('processing');
			return response()->json(['status' => 'success', 'message' => $title], 200);
		}
	}
}
