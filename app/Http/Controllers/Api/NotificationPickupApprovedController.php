<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Jobs\Notif\PickupApproved;
use Illuminate\Http\Request;

class NotificationPickupApprovedController extends Controller {

	public function __construct() {

	}

	/**
	 * Store a newly created resource in storage.
	 *
	 * @param  \Illuminate\Http\Request  $request
	 * @return \Illuminate\Http\Response
	 */
	public function pickupApproved(Request $request, $user_id)
	{
		$title = "Don't forget to prepare your items and bring them to Boxin tomorrow";
    	PickupApproved::dispatch($user_id, $title)->onQueue('processing');
		return response()->json(['status' => 'success', 'message' => $title], 200);
	}
}
