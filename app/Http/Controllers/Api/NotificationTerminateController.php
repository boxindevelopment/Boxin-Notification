<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Jobs\Notif\SendNotifAdmin;
use App\Jobs\Notif\SendNotifUser;
use App\Models\OrderDetail;
use App\Http\Resources\OrderDetailResource;
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

	public function status(Request $request, $terminate_id)
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
		if($request->status_id != 16 && $request->status_id != 28){
            return response()->json([
                'status' => false,
                'message' => 'status_id required only number 16 dan 28'
			], 422);
		}
		$status = ($request->status_id == 16) ? 'Terminate Requested' : 'Terminated';

		$terminate =  ReturnBoxes::select('return_boxes.*', 'order_details.id_name', 'order_details.types_of_box_room_id', 'order_details.order_id', 'users.first_name', 'users.last_name', DB::raw('orders.status_id as status_order_id'), DB::raw('orders.user_id as user_id'))
									->leftJoin('order_details', 'order_details.id', '=', 'return_boxes.order_detail_id')
									->leftJoin('orders', 'orders.id', '=', 'order_details.order_id')
									->leftJoin('users', 'users.id', '=', 'orders.user_id')
									->where('return_boxes.id', $terminate_id)
									->first();
		if($terminate) {
			$orderDetails =  OrderDetail::select('order_details.*', DB::raw('orders.status_id as status_id'), DB::raw('orders.user_id as user_id'), DB::raw('DATEDIFF(day, order_details.start_date, order_details.end_date) as total_time'), DB::raw('DATEDIFF(day, order_details.start_date, GETDATE()) as selisih'))
										->leftJoin('orders', 'orders.id', '=', 'order_details.order_id')
										->where('order_details.id', $terminate->order_detail_id)
										->first();
			$data = New OrderDetailResource($orderDetails);
			$boxSpaces = ($terminate->types_of_box_room_id == 16) ? 'Terminate Requested' : 'Terminated';
			$title = "no order " . $terminate->id_name . ", status terminate " . $boxSpaces . " is " . $status;
	        SendNotifUser::dispatch($terminate->user_id, $title, $data, 'terminate-' . $status, 'terminate ' . $status)->onQueue('processing');
			return response()->json(['status' => 'success', 'message' => $title], 200);
		}
	}

	
	public function request(Request $request, $terminate_id)
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
		$terminate =  ReturnBoxes::select('return_boxes.*', 'order_details.id_name', 'order_details.types_of_box_room_id', 'users.first_name', 'users.last_name', DB::raw('orders.status_id as status_order_id'), DB::raw('orders.user_id as user_id'))
									->leftJoin('order_details', 'order_details.id', '=', 'return_boxes.order_detail_id')
									->leftJoin('orders', 'orders.id', '=', 'order_details.order_id')
									->leftJoin('users', 'users.id', '=', 'orders.user_id')
									->where('return_boxes.id', $terminate_id)
									->first();

		if($terminate) {
			$boxSpace = ($terminate->types_of_box_room_id == 1) ? 'boxes' : 'space';
			$date = date("d/m/Y", strtotime($terminate->date));
			$time = $terminate->time;
			$title = "Customer " . $terminate->first_name . " " . $terminate->last_name . ", make a request to terminate the  " . $boxSpace . ' order '. $terminate->id_name . ' on ' . $date . ' at ' . substr($time, 0, 5);
	        SendNotifAdmin::dispatch($terminate_id, $title, $terminate, 'reminder-terminate-request', 'a reminder terminate request')->onQueue('processing');
			return response()->json(['status' => 'success', 'message' => $title], 200);
		}
	}
}
