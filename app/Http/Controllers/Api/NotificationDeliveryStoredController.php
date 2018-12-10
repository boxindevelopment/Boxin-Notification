<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Jobs\Notif\DeliveryStored;
use Illuminate\Http\Request;

class NotificationDeliveryStoredController extends Controller {

	public function __construct() {

	}

	/**
	 * Store a newly created resource in storage.
	 *
	 * @param  \Illuminate\Http\Request  $request
	 * @return \Illuminate\Http\Response
	 */
	public function deliveryStored(Request $request, $user_id)
	{
		$title = "Your items is on the way back to you";
        DeliveryStored::dispatch($user_id, $title)->onQueue('processing');
		return response()->json(['status' => 'success', 'message' => $title], 200);
	}
}
