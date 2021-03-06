<?php

namespace App\Http\Controllers\Api;

use App\Jobs\Notif\ConfirmPayment;
use App\Jobs\Notif\SendNotif;
use App\Jobs\Notif\SendNotifAdmin;
use App\Http\Controllers\Controller;
use App\Http\Resources\OrderDetailResource;
use App\Models\Order;
use App\Models\Setting;
use App\Models\OrderDetail;
use App\Models\PickupOrder;
use App\Models\Box;
use App\Models\SpaceSmall;
use App\Models\OrderTake;
use App\Models\ReturnBoxes;
use App\Models\ReturnBoxPayment;
use App\Models\OrderBackWarehouse;
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
        $setting = Setting::where('name', 'duration_payment_order')->first();
        $day = $setting->value;
        $beforeDay = $now->addHours('-' . $day)->format('Y-m-d H:i:s');
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

    public function minuteTakes(Request $request)
    {
        $now = Carbon::now('Asia/Jakarta');
        $setting = Setting::where('name', 'duration_payment_take_return_terminate')->first();
        $hour = $setting->value;
        $beforeDay = $now->addHours('-' . $hour)->format('Y-m-d H:i:s');
        $timeNow = Carbon::now('Asia/Jakarta')->format('Y-m-d H:i:s');
        $query  = OrderTake::query();
        $query->with('order_detail', 'order_take_payment');
        $query->where('status_id', 14);
        $query->where('created_at', '<', $beforeDay);
        $query->limit(4);
        $takes = $query->get();
        DB::beginTransaction();
        try {
            if(count($takes) > 0){
                $no = 0;
                foreach ($takes as $k => $v) {
                    $no++;
                    //Status cancelled orders
                    $v->status_id = 24;
                    $v->save();

                    foreach ($v->order_take_payment as $key => $d) {
                        //Status cancelled order detail
                        $d->status_id = 24;
                        $d->save();
                    }

                    //Status cancelled order pickup
                    $v->order_detail->status_id = 4;
                    $v->order_detail->save();

                    if($v->order_detail){
                        $orderDetails =  OrderDetail::select('order_details.*', 'order_takes.id as order_take_id', DB::raw('orders.status_id as status_id'), DB::raw('orders.user_id as user_id'), DB::raw('DATEDIFF(day, order_details.start_date, order_details.end_date) as total_time'), DB::raw('DATEDIFF(day, order_details.start_date, GETDATE()) as selisih'))
                                                    ->leftJoin('orders', 'orders.id', '=', 'order_details.order_id')
                                                    ->leftJoin('order_takes', 'order_takes.order_detail', '=', 'order_details.id')
                                                    ->where('order_details.id', $v->order_detail->id)
                                                    ->where('order_takes.id', $v->id)
                                                    ->get();
                        if(count($orderDetails) > 0) {
                            $data = OrderDetailResource::collection($orderDetails);
                            $title = 'Your payment has been rejected';
                            SendNotif::dispatch($v->user_id, $title, $data, 'confirm-payment-take-rejected', 'confirm payment take rejected')->onQueue('processing');
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
        return response()->json(['status' => 'success', 'message' => 'Order Take Count : ' . count($takes), 'order' => $takes], 200);
    }

    public function minuteReturn(Request $request)
    {
        $now = Carbon::now('Asia/Jakarta');
        $setting = Setting::where('name', 'duration_payment_take_return_terminate')->first();
        $hour = $setting->value;
        $beforeDay = $now->addHours('-' . $hour)->format('Y-m-d H:i:s');
        $timeNow = Carbon::now('Asia/Jakarta')->format('Y-m-d H:i:s');
        $query  = OrderBackWarehouse::query();
        $query->with('order_detail', 'order_back_warehouse_payment');
        $query->where('status_id', 14);
        $query->where('created_at', '<', $beforeDay);
        $query->limit(4);
        $returnBoxes = $query->get();
        DB::beginTransaction();
        try {
            if(count($returnBoxes) > 0){
                $no = 0;
                foreach ($returnBoxes as $k => $v) {
                    $no++;
                    //Status cancelled orders
                    $v->status_id = 24;
                    $v->save();

                    foreach ($v->order_back_warehouse_payment as $key => $d) {
                        //Status cancelled order detail
                        $d->status_id = 24;
                        $d->save();
                    }

                    //Status cancelled order pickup
                    $v->order_detail->status_id = 4;
                    $v->order_detail->save();

                    if($v->order_detail){
                        $orderDetails =  OrderDetail::select('order_details.*', 'order_back_warehouses.id as order_back_warehouse_id', DB::raw('orders.status_id as status_id'), DB::raw('orders.user_id as user_id'), DB::raw('DATEDIFF(day, order_details.start_date, order_details.end_date) as total_time'), DB::raw('DATEDIFF(day, order_details.start_date, GETDATE()) as selisih'))
                                                    ->leftJoin('orders', 'orders.id', '=', 'order_details.order_id')
                                                    ->leftJoin('order_back_warehouses', 'order_back_warehouses.order_detail_id', '=', 'order_details.id')
                                                    ->where('order_details.id', $v->order_detail->id)
                                                    ->where('order_back_warehouses.id', $v->id)
                                                    ->get();
                        if(count($orderDetails) > 0) {
                            $data = OrderDetailResource::collection($orderDetails);
                            $title = 'Your payment has been rejected';
                            SendNotif::dispatch($v->user_id, $title, $data, 'confirm-payment-return-boxes-rejected', 'confirm payment return boxes rejected')->onQueue('processing');
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
        return response()->json(['status' => 'success', 'message' => 'Order return Count : ' . count($returnBoxes), 'data' => $returnBoxes], 200);
    }

    public function minuteTerminate(Request $request)
    {
        $now = Carbon::now('Asia/Jakarta');
        $setting = Setting::where('name', 'duration_payment_take_return_terminate')->first();
        $hour = $setting->value;
        $beforeDay = $now->addHours('-' . $hour)->format('Y-m-d H:i:s');
        $timeNow = Carbon::now('Asia/Jakarta')->format('Y-m-d H:i:s');
        $query  = ReturnBoxes::query();
        $query->with('order_detail.order');
        $query->where('status_id', 14);
        $query->where('created_at', '<', $beforeDay);
        $query->limit(4);
        $terminateBoxes = $query->get();
        DB::beginTransaction();
        try {
            if(count($terminateBoxes) > 0){
                $no = 0;
                foreach ($terminateBoxes as $k => $v) {
                    $no++;
                    //Status cancelled orders
                    $v->status_id = 24;
                    $v->save();

                    $returnBoxPayment = ReturnBoxPayment::where('order_detail_id', $v->order_detail_id)->get();
                    if(count($returnBoxPayment) > 0){
                        foreach ($returnBoxPayment as $key => $d) {
                            $d->status_id = 24;
                            $d->save();
                        }
                    }

                    //Status cancelled order pickup
                    $v->order_detail->status_id = 4;
                    $v->order_detail->save();

                    if($v->order_detail){
                        $orderDetails =  OrderDetail::select('order_details.*', 'return_boxes.id as return_boxe_id', DB::raw('orders.status_id as status_id'), DB::raw('orders.user_id as user_id'), DB::raw('DATEDIFF(day, order_details.start_date, order_details.end_date) as total_time'), DB::raw('DATEDIFF(day, order_details.start_date, GETDATE()) as selisih'))
                                                    ->leftJoin('orders', 'orders.id', '=', 'order_details.order_id')
                                                    ->leftJoin('return_boxes', 'return_boxes.order_detail_id', '=', 'order_details.id')
                                                    ->where('order_details.id', $v->order_detail->id)
                                                    ->where('return_boxes.id', $v->id)
                                                    ->get();
                        if(count($orderDetails) > 0) {
                            $data = OrderDetailResource::collection($orderDetails);
                            $title = 'Your payment has been rejected';
                            SendNotif::dispatch($v->order_detail->order->user_id, $title, $data, 'confirm-payment-terminate-rejected', 'confirm payment terminate rejected')->onQueue('processing');
                        }
                    }
                }
                DB::commit();
            } else {
                return response()->json(['status' => 'error', 'message' => 'No Order Terminate'], 402);
            }
        } catch (Exception $e) {
            DB::rollback();
            return response()->json([
                'status' =>false,
                'message' => $e->getMessage()
            ], 401);
        }
        return response()->json(['status' => 'success', 'message' => 'Terminate Count : ' . count($terminateBoxes), 'data' => $terminateBoxes], 200);
    }

    public function days(Request $request)
    {
        $now = Carbon::now('Asia/Jakarta');
        $setting = Setting::where('name', 'reminder_period_expired_box')->first();
        $day = $setting->value;
        $beforeDay = $now->addDays($day)->format('Y-m-d H:i:s');
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

    public function takeBoxes(Request $request)
    {
        $dateTime = Carbon::now('Asia/Jakarta')->format('Y-m-d H:i');
        
        $dateTake = Carbon::now('Asia/Jakarta')->format('Y-m-d');
        $orderTakes = OrderTake::select('order_takes.*', 'order_details.id_name', 'order_details.types_of_box_room_id', 'users.first_name', 'users.last_name', DB::raw('orders.status_id as status_order_id'), DB::raw('orders.user_id as user_id'))
        ->leftJoin('order_details', 'order_details.id', '=', 'order_takes.order_detail_id')
        ->leftJoin('orders', 'orders.id', '=', 'order_details.order_id')
        ->leftJoin('users', 'users.id', '=', 'orders.user_id')
        ->where('order_takes.date', $dateTake)
        ->where('order_takes.status_id', 27)
        ->whereNull('order_takes.notif')
        ->orderBy('order_takes.id', 'asc')
        ->get();
        if(count($orderTakes) > 0) {
            foreach($orderTakes as $orderTake){
                Log::info($dateTake . ' Take Data Time' . $dateTime . ' ID : ' . $orderTake->id);
                $boxSpace = ($orderTake->types_of_box_room_id == 1) ? 'boxes' : 'space';
                $date = date("d/m/Y", strtotime($orderTake->date));
                $time = $orderTake->time;
                $title = "Customer " . $orderTake->first_name . " " . $orderTake->last_name . ", make a request to take the  " . $boxSpace . ' order '. $orderTake->id_name . ' on ' . $date . ' at ' . substr($time, 0, 5);
                SendNotifAdmin::dispatch($orderTake->id, $title, $orderTake, 'reminder-take-request', 'a reminder take request')->onQueue('processing');
                $orderTake->notif = 1;
                $orderTake->save();
            }
            return response()->json(['status' => 'success', 'message' => $title], 200);
        }

    }

    public function returnBoxes(Request $request)
    {
        $dateTime = Carbon::now('Asia/Jakarta')->format('Y-m-d H:i');
        
        $dateBackWarehouse = Carbon::now('Asia/Jakarta')->format('Y-m-d');
        $orderBackWarehouses =  OrderBackWarehouse::select('order_back_warehouses.*', 'order_details.id_name', 'order_details.types_of_box_room_id', 'users.first_name', 'users.last_name', DB::raw('orders.status_id as status_order_id'), DB::raw('orders.user_id as user_id'))
                                    ->leftJoin('order_details', 'order_details.id', '=', 'order_back_warehouses.order_detail_id')
                                    ->leftJoin('orders', 'orders.id', '=', 'order_details.order_id')
                                    ->leftJoin('users', 'users.id', '=', 'orders.user_id')
                                    ->where('order_back_warehouses.date', $dateBackWarehouse)
                                    ->where('order_back_warehouses.status_id', 26)
                                    ->whereNull('order_back_warehouses.notif')
                                    ->orderBy('order_back_warehouses.id', 'asc')
									->get();
		if(count($orderBackWarehouses) > 0) {
            foreach($orderBackWarehouses as $orderBackWarehouse){
                Log::info($dateBackWarehouse . ' Return Data Time' . $dateTime . ' ID : ' . $orderBackWarehouse->id);
                $boxSpace = ($orderBackWarehouse->types_of_box_room_id == 1) ? 'boxes' : 'space';
                $date = date("d/m/Y", strtotime($orderBackWarehouse->date));
                $time = $orderBackWarehouse->time;
                $title = "Customer " . $orderBackWarehouse->first_name . " " . $orderBackWarehouse->last_name . ", make a request to return the  " . $boxSpace . ' order '. $orderBackWarehouse->id_name . ' on ' . $date . ' at ' . substr($time, 0, 5);
                SendNotifAdmin::dispatch($orderBackWarehouse->id, $title, $orderBackWarehouse, 'reminder-return-request', 'a reminder return request')->onQueue('processing');
                $orderBackWarehouse->notif = 1;
                $orderBackWarehouse->save();
                return response()->json(['status' => 'success', 'message' => $title], 200);
            }
            
        }

    }

    public function terminate(Request $request)
    {
        $dateTime = Carbon::now('Asia/Jakarta')->format('Y-m-d H:i');
        $dateTerminate = Carbon::now('Asia/Jakarta')->format('Y-m-d');
        $terminates =  ReturnBoxes::select('return_boxes.*', 'order_details.id_name', 'order_details.types_of_box_room_id', 'users.first_name', 'users.last_name', DB::raw('orders.status_id as status_order_id'), DB::raw('orders.user_id as user_id'))
        ->leftJoin('order_details', 'order_details.id', '=', 'return_boxes.order_detail_id')
        ->leftJoin('orders', 'orders.id', '=', 'order_details.order_id')
        ->leftJoin('users', 'users.id', '=', 'orders.user_id')
        ->where('return_boxes.date', $dateTerminate)
        ->where('return_boxes.status_id', 16)
        ->whereNull('return_boxes.notif')
        ->orderBy('return_boxes.id', 'asc')
        ->get();
		if(count($terminates) > 0) {
            foreach($terminates as $terminate){
                Log::info($dateTerminate . 'Terminate Data Time' . $dateTime . ' ID : ' . $terminate->id);
                $boxSpace = ($terminate->types_of_box_room_id == 1) ? 'boxes' : 'space';
                $date = date("d/m/Y", strtotime($terminate->date));
                $time = $terminate->time;
                $title = "Customer " . $terminate->first_name . " " . $terminate->last_name . ", make a request to terminate the  " . $boxSpace . ' order '. $terminate->id_name . ' on ' . $date . ' at ' . substr($time, 0, 5);
                SendNotifAdmin::dispatch($terminate->id, $title, $terminate, 'reminder-terminate-request', 'a reminder terminate request')->onQueue('processing');
                $terminate->notif = 1;
                $terminate->save();
            }
            return response()->json(['status' => 'success', 'message' => $title], 200);
        }
        
    }

    public function pickupOrder(Request $request)
    {
        $dateTime = Carbon::now('Asia/Jakarta')->format('Y-m-d H:i');
        $datePickup = Carbon::now('Asia/Jakarta')->format('Y-m-d');
        $pickupOrders =  PickupOrder::select('pickup_orders.*', 'users.first_name', 'users.last_name', DB::raw('orders.status_id as status_order_id'), DB::raw('orders.user_id as user_id'))
        ->leftJoin('orders', 'orders.id', '=', 'pickup_orders.order_id')
        ->leftJoin('users', 'users.id', '=', 'orders.user_id')
        ->where('pickup_orders.date', $datePickup)
        ->where('pickup_orders.status_id', 5)
        ->whereNull('pickup_orders.notif')
        ->orderBy('pickup_orders.id', 'asc')
        ->get();
		if(count($pickupOrders) > 0) {
            foreach($pickupOrders as $pickupOrder){
                Log::info($datePickup . 'Pickup Order Data Time' . $dateTime . ' ID : ' . $pickupOrder->id);
                $date = date("d/m/Y", strtotime($pickupOrder->date));
                $time = $pickupOrder->time;
                $orderDetail = OrderDetail::where('order_id', $pickupOrder->order_id)->first();
                $title = "Customer " . $pickupOrder->first_name . " " . $pickupOrder->last_name . ", make a request to pickup Order the order ". $orderDetail->id_name . ' on ' . $date . ' at ' . substr($time, 0, 5);
                SendNotifAdmin::dispatch($pickupOrder->id, $title, $pickupOrder, 'reminder-pickup-order-request', 'a reminder pickup order request')->onQueue('processing');
                $pickupOrder->notif = 1;
                $pickupOrder->save();
            }
            return response()->json(['status' => 'success', 'message' => $title], 200);
        }
        
    }
}
