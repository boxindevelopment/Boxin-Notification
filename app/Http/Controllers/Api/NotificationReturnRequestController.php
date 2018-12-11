<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Jobs\Notif\ReturnRequest;
use Illuminate\Http\Request;

class NotificationReturnRequestController extends Controller {

	public function __construct() {

	}

	/**
	 * Store a newly created resource in storage.
	 *
	 * @param  \Illuminate\Http\Request  $request
	 * @return \Illuminate\Http\Response
	 */
	public function returnRequest(Request $request, $user_id)
	{
		$title = "How do you want to take your items?&#013;pickup/delivery";
        ReturnRequest::dispatch($user_id, $title)->onQueue('processing');
		return response()->json(['status' => 'success', 'message' => $title], 200);
	}
}
