<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Resources\NotificationResource;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Notification;
use Validator;
use DB;

class NotificationController extends Controller
{

    /**
     * @var
     */

    public function __construct()
    {
    }

    public function index(Request $request)
    {
        $users = $request->user();

        try {

            $notification = Notification::select("notifications.*", DB::raw("transaction_logs.id as transaction_id"), DB::raw("orders.voucher_id as voucher_id"))
                                            ->leftJoin('transaction_logs', 'transaction_logs.order_id', '=', 'notifications.order_id')
                                            ->leftJoin('orders', 'orders.id', '=', 'notifications.order_id')
                                            ->where('notifications.user_id', $users->id)
                                            ->orderBy('notifications.created_at', 'desc')
                                            ->paginate(15);
            // return response()->json($notification);
            return NotificationResource::collection($notification);

        } catch (ValidatorException $e) {
            return response()->json($e);
        }

    }

}
