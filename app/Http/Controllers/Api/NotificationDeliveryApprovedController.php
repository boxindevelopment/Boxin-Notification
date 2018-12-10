<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Jobs\Notif\DeliveryApproved;
use Illuminate\Http\Request;

class NotificationDeliveryApprovedController extends Controller {

	public function __construct() {

	}

	/**
	 * Store a newly created resource in storage.
	 *
	 * @param  \Illuminate\Http\Request  $request
	 * @return \Illuminate\Http\Response
	 */
	public function deliveryApproved(Request $request, $user_id)
	{
        $token = DeliveryApproved::dispatch($user_id)->onQueue('processing');
		return response()->json(['status' => 'success', 'message' => "Don't forget to prepare your items!&#013;Our courier will come tomorrow"], 200);

	}

}
