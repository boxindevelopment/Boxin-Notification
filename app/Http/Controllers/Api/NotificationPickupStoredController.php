<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Jobs\Notif\PickupStored;
use Illuminate\Http\Request;

class NotificationPickupStoredController extends Controller {

	public function __construct() {

	}

	/**
	 * Store a newly created resource in storage.
	 *
	 * @param  \Illuminate\Http\Request  $request
	 * @return \Illuminate\Http\Response
	 */
	public function pickupStored(Request $request, $user_id)
	{
		$title = "Don't forget to take your items at Boxin tomorrow";
        PickupStored::dispatch($user_id, $title)->onQueue('processing');
		return response()->json(['status' => 'success', 'message' => $title], 200);

	}
}
