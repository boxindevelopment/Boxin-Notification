<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use DB;
use App\Mail\EmailPayment;
use App\Models\Order;
use Mail;

class NotificationEmailPaymentController extends Controller
{
    public function __construct() {

    }

    public function send_email($id)
    {
      $order = Order::find($id);
      if (empty($order)) {
        return response()->json([
          'status' => false,
          'message' => 'Data not found'
        ]);
      }
      $title = 'Your purchase order.';
      Mail::to($order->user->email)->send(new EmailPayment($order->id, 'Order Invoice'));
      return response()->json(['status' => 'success', 'message' => $title]);
    }
}
