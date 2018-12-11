<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Jobs\Notif\ItemStored;
use Illuminate\Http\Request;

class NotificationItemStoredController extends Controller {

	public function __construct() {

	}

	/**
	 * Store a newly created resource in storage.
	 *
	 * @param  \Illuminate\Http\Request  $request
	 * @return \Illuminate\Http\Response
	 */
	public function itemStored(Request $request, $user_id)
	{
		$title = "Tomorrow is the last day of your storage";
        $token = ItemStored::dispatch($user_id, $title)->onQueue('processing');
		return response()->json(['status' => 'success', 'message' => $title], 200);

	}
}
