<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\OrderDetailResource;
// use App\Jobs\Notif\Returned;
use App\Jobs\Notif\SendNotif;
use App\Models\OrderDetail;
use DB;
use Illuminate\Http\Request;

class NotificationReturnedController extends Controller {

	public function __construct() {

	}

	/**
	 * Store a newly created resource in storage.
	 *
	 * @param  \Illuminate\Http\Request  $request
	 * @return \Illuminate\Http\Response
	 */
	public function returned(Request $request, $user_id)
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
		$orderDetails =  OrderDetail::select('order_details.*', DB::raw('orders.status_id as status_id'), DB::raw('orders.user_id as user_id'), DB::raw('DATEDIFF(day, order_details.start_date, order_details.end_date) as total_time'), DB::raw('DATEDIFF(day, order_details.start_date, GETDATE()) as selisih'))
						            ->leftJoin('orders', 'orders.id', '=', 'order_details.order_id')
						            ->where('order_details.id', $request->order_detail_id)
						            ->get();

		if(count($orderDetails) > 0) {

			$data = OrderDetailResource::collection($orderDetails);
			$title = "Thank you for using Boxin Apps";
	        // Returned::dispatch($user_id, $title, $data)->onQueue('processing');
	        SendNotif::dispatch($user_id, $title, $data, 'returned', 'returned')->onQueue('processing');
			return response()->json(['status' => 'success', 'message' => $title], 200);
		}
	}
}
