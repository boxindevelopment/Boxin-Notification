<?php

namespace App\Http\Controllers\Api;

use App\Jobs\Notif\ConfirmPayment;
use App\Http\Controllers\Controller;
use App\Http\Resources\OrderDetailResource;
use App\Models\Order;
use App\Models\OrderDetail;
use App\Models\PickupOrder;
use App\Models\Box;
use App\Models\SpaceSmall;
use Carbon\Carbon;
use DB;
use Illuminate\Http\Request;
use Log;

class CronController extends Controller
{

    /**
     * @var
     */

    public function __construct()
    {
    }

    public function minutes(Request $request)
    {
        $now = Carbon::now('Asia/Jakarta');
        $beforeDay = $now->addMinutes('-60')->format('Y-m-d H:i:s');
        $timeNow = Carbon::now('Asia/Jakarta')->format('Y-m-d H:i:s');
        $query  = Order::query();
        $query->with('pickup_order', 'order_detail');
        $query->where('status_id', 14);
        $query->where('created_at', '<', $beforeDay);
        $orders = $query->get();
        DB::beginTransaction();
        try {
            if(count($orders) > 0){
                foreach ($orders as $k => $v) {
                    //Status cancelled orders
                    $v->status_id = 24;
                    $v->save();

                    //Status cancelled order pickup
                    $v->pickup_order->status_id = 24;
                    $v->pickup_order->save();

                    foreach ($v->order_detail as $key => $d) {
                        //Status cancelled order detail
                        $d->status_id = 24;
                        $d->save();

                        if($d->types_of_box_room_id == 1){
                            //Status empty box
                            Box::where('id',  $d->room_or_box_id)->update(['status_id' => 10]);
                        } else if($d->types_of_box_room_id == 2){
                            //Status empty space
                            SpaceSmall::where('id', $d->room_or_box_id)->update(['status_id' => 10]);
                        }

                        Log::info('code : ' . $d->id_name);

                    }
                    if(count( $v->order_detail) > 0){
                        $status = 'rejected';
                        $orderDetails =  OrderDetail::select('order_details.*', DB::raw('orders.status_id as status_id'), DB::raw('orders.user_id as user_id'), DB::raw('DATEDIFF(day, order_details.start_date, order_details.end_date) as total_time'), DB::raw('DATEDIFF(day, order_details.start_date, GETDATE()) as selisih'))
                                                    ->leftJoin('orders', 'orders.id', '=', 'order_details.order_id')
                                                    ->where('order_details.id', $v->order_detail[0]->id)
                                                    ->get();
                        if(count($orderDetails) > 0) {
                            $data = OrderDetailResource::collection($orderDetails);
                            $confirm = ConfirmPayment::dispatch($v->user_id, $status, $data)->onQueue('processing');
                        }
                    }
                }
                return response()->json(['status' => 'success', 'message' => 'Order Count : ' . count($orders), 'order' => $orders], 200);
            } else {
                Log::info('No Order');
                return response()->json(['status' => 'error', 'message' => 'No Order'], 402);
            }
        } catch (Exception $e) {
            Log::info('===================================ERRORR=========================');
            Log::info(json_encode($e));
            Log::info('===================================END============================');
            DB::rollback();
            return response()->json([
                'status' =>false,
                'message' => $e->getMessage()
            ], 401);
        }
    }
}
