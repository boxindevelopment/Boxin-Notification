<?php

namespace App\Http\Controllers\Api;

use App\Jobs\Notif\ConfirmPayment;
use App\Jobs\Notif\SendNotif;
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
        $query->limit(4);
        $orders = $query->get();
        DB::beginTransaction();
        try {
            if(count($orders) > 0){
                $no = 0;
                foreach ($orders as $k => $v) {
                    $no++;
                    Log::info('Order ID:' . $v->id);
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
                    }
                    if(count( $v->order_detail) > 0){
                        $orderDetails =  OrderDetail::select('order_details.*', DB::raw('orders.status_id as status_id'), DB::raw('orders.user_id as user_id'), DB::raw('DATEDIFF(day, order_details.start_date, order_details.end_date) as total_time'), DB::raw('DATEDIFF(day, order_details.start_date, GETDATE()) as selisih'))
                                                    ->leftJoin('orders', 'orders.id', '=', 'order_details.order_id')
                                                    ->where('order_details.id', $v->order_detail[0]->id)
                                                    ->get();
                        if(count($orderDetails) > 0) {
                            $data = OrderDetailResource::collection($orderDetails);
                            $title = 'Your payment has been rejected';
                            SendNotif::dispatch($v->user_id, $title, $data, 'confirm-payment-rejected', 'confirm payment rejected')->onQueue('processing');
                        }
                    }
                }
                DB::commit();
            } else {
                return response()->json(['status' => 'error', 'message' => 'No Order'], 402);
            }
        } catch (Exception $e) {
            DB::rollback();
            return response()->json([
                'status' =>false,
                'message' => $e->getMessage()
            ], 401);
        }
        return response()->json(['status' => 'success', 'message' => 'Order Count : ' . count($orders), 'order' => $orders], 200);
    }

    public function days(Request $request)
    {
        $now = Carbon::now('Asia/Jakarta');
        $beforeDay = $now->addDays('3')->format('Y-m-d H:i:s');
        $timeNow = Carbon::now('Asia/Jakarta')->format('Y-m-d H:i:s');
        $dateNow = Carbon::now('Asia/Jakarta')->format('Y-m-d');
        $query  = OrderDetail::query();
        $query->with('order.pickup_order');
        $query->select('order_details.*', DB::raw('orders.status_id as status_id'), DB::raw('orders.user_id as user_id'), DB::raw('DATEDIFF(day, order_details.start_date, order_details.end_date) as total_time'), DB::raw('DATEDIFF(day, order_details.start_date, GETDATE()) as selisih'));
        $query->where(function ($q) {
                    $q->where('order_details.status_id', 4)
                      ->orWhere('order_details.status_id', 5)
                      ->orWhere('order_details.status_id', 7)
                      ->orWhere('order_details.status_id', 9)
                      ->orWhere('order_details.status_id', 16)
                      ->orWhere('order_details.status_id', 17)
                      ->orWhere('order_details.status_id', 18)
                      ->orWhere('order_details.status_id', 19)
                      ->orWhere('order_details.status_id', 22)
                      ->orWhere('order_details.status_id', 23)
                      ->orWhere('order_details.status_id', 25)
                      ->orWhere('order_details.status_id', 26)
                      ->orWhere('order_details.status_id', 27)
                      ->orWhere('order_details.status_id', 28);
                });
        $query->where('end_date', '<', $beforeDay);
        $query->where('end_date', '>', $timeNow);
        $query->whereRaw("(reminder_date <> '" . $dateNow . "' OR reminder_date IS NULL)");
        $query->leftJoin('orders', 'orders.id', '=', 'order_details.order_id');
        $query->limit(4);
        $orderDetail = $query->get();
        if(count($orderDetail) > 0) {
            foreach($orderDetail as $v){
                $data = new OrderDetailResource($v);
                $box_space = ($data->types_of_box_room_id == 2) ? 'space' : 'box';

                $title = "Your " . $box_space . " with order id " . $data->id_name . " will end on (" . Carbon::parse($data->end_date)->format('d/m/Y') . ")";
                SendNotif::dispatch($data->user_id, $title, $data, 'reminder-expired', 'Reminder expired')->onQueue('processing');
                
                OrderDetail::where('id', $data->id)->update(['reminder_date' => $dateNow]);
            }
			return response()->json(['status' => 'success', 'message' => $title], 200);
        }
    }
}
