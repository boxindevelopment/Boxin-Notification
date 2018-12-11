<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Jobs\Notif\Returned;
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
		$title = "Thank you for using Boxin Apps";
        Returned::dispatch($user_id, $title)->onQueue('processing');
		return response()->json(['status' => 'success', 'message' => $title], 200);
	}
}
