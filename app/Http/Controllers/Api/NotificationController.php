<?php

namespace App\Http\Controllers\Admin;

use Auth;
use DB;
use App\Http\Controllers\Controller;
use App\Jobs\Notif\ConfirmPayment;
use Illuminate\Http\Request;
use OneSignal;

class NotificationController extends Controller {

	public function __construct() {

	}

	/**
	 * Store a newly created resource in storage.
	 *
	 * @param  \Illuminate\Http\Request  $request
	 * @return \Illuminate\Http\Response
	 */
	public function confirmPayment(Request $request)
	{
        ConfirmPayment::dispatch($request->user, 'approved')->onQueue('processing');
	}
}
