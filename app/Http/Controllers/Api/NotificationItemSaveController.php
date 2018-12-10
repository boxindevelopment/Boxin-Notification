<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Jobs\Notif\ItemSave;
use Illuminate\Http\Request;

class NotificationItemSaveController extends Controller {

	public function __construct() {

	}

	/**
	 * Store a newly created resource in storage.
	 *
	 * @param  \Illuminate\Http\Request  $request
	 * @return \Illuminate\Http\Response
	 */
	public function itemSave(Request $request, $user_id)
	{
		$title = "Congratulation! Your items has been stored";
        $token = ItemSave::dispatch($user_id, $title)->onQueue('processing');
		return response()->json(['status' => 'success', 'message' => $title], 200);

	}

}
