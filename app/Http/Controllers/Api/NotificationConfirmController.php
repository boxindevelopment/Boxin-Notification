<?php

namespace App\Http\Controllers\Api;

use Auth;
use DB;
use App\Models\Notification;
use App\Http\Controllers\Controller;
use App\Jobs\Notif\ConfirmPayment;
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
		$status = ($request->status_id == 7) ? 'approved' : 'rejected';
        $confirm = ConfirmPayment::dispatch($user_id, $status)->onQueue('processing');
		return response()->json(['status' => 'success', 'message' => 'Your payment has been ' . $status], 200);

	}
}