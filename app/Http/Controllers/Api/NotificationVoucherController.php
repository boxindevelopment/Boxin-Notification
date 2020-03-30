<?php

namespace App\Http\Controllers\Api;

use Auth;
use DB;
use App\Models\UserDevice;
use App\Models\OrderDetail;
use App\Models\Notification;
use App\Http\Controllers\Controller;
use App\Jobs\Notif\VoucherCreate;
use App\Http\Resources\OrderDetailResource;
use Illuminate\Http\Request;
use OneSignal;

class NotificationVoucherController extends Controller {

	public function __construct() {

	}

	/**
	 * Store a newly created resource in storage.
	 *
	 * @param  \Illuminate\Http\Request  $request
	 * @return \Illuminate\Http\Response
	 */
	public function createvoucher(Request $request)
	{
        $title = "New promo is waiting for you!";
        $head = 'New Promo';
		$name = $request->name; 
		$code = $request->code;
		$id = $request->id;
        $voucher = VoucherCreate::dispatch($title, $head, $name, $code, $id)->onQueue('processing');
        return response()->json(['status' => 'success', 'message' => 'Promo'], 200);
	}
}
