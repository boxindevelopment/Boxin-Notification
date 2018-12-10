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
        ConfirmPayment::dispatch($user_id, 'approved')->onQueue('processing');
		return response()->json(['status' => 'success', 'message' => 'Your payment has been approved'], 200);

	}
}
