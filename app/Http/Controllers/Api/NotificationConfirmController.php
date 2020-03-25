<?php

namespace App\Http\Controllers\Api;

use Auth;
use DB;
use App\Models\OrderDetail;
use App\Models\Notification;
use App\Http\Controllers\Controller;
use App\Jobs\Notif\SendNotif;
use App\Http\Resources\OrderDetailResource;
use Illuminate\Http\Request;
use OneSignal;

class NotificationConfirmController extends Controller {

	public function __construct() {

	}

	/**
	 * Store a newly created resource in storage.
	 *
	 * @param  \Illuminate\Http\Request  $request
	 * @return \Illuminate\Http\Response
	 */
	public function confirmPayment(Request $request, $user_id)
	{

    $validator = \Validator::make($request->all(), [
        'status_id'       => 'required',
        'order_detail_id' => 'required',
    ]);

    if($validator->fails()) {
        return response()->json([
            'status' => false,
            'message' => $validator->errors()
        ]);
    }

    $status = 'approved';
    if ($request->status_id == 8) {
      $status = 'rejected';
    }
    
    $orderDetails =  OrderDetail::select('order_details.*', DB::raw('orders.status_id as status_id'), DB::raw('orders.user_id as user_id'), DB::raw('DATEDIFF(day, order_details.start_date, order_details.end_date) as total_time'), DB::raw('DATEDIFF(day, order_details.start_date, GETDATE()) as selisih'))->leftJoin('orders', 'orders.id', '=', 'order_details.order_id')->where('order_details.id', $request->order_detail_id)->get();
		if(count($orderDetails) > 0) {
      $data = OrderDetailResource::collection($orderDetails);
      
      $title       = 'Your payment has been ' . $status;
      $head        = 'Payment Rejected';
      if ($status == 'approved') {
        $head  = 'Payment Approved';
        $title = 'Your payment has been approved. Please remember to use your box/space on your selected date';
      }
      $confirm = SendNotif::dispatch($user_id, $title, $data, 'confirm-payment-' . $status, 'confirm payment ' . $status)->onQueue('processing');
      if ($status == 'approved') {
        return response()->json(['status' => 'success', 'message' => 'Your payment has been approved. Please remember to use your box/space from ' . date('d M Y', strtotime($orderDetails->first()->start_date))], 200);
      }

      return response()->json(['status' => 'success', 'message' => 'Your payment has been ' . $status . $request->status_id], 200);
		}

	}
}
