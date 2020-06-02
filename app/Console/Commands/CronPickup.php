<?php

namespace App\Console\Commands;

use App\Jobs\Notif\SendNotifAdmin;
use App\Models\PickupOrder;
use App\Models\OrderDetail;
use Carbon\Carbon;
use DB;
use Illuminate\Http\Request;
use Log;
use Illuminate\Console\Command;

class CronPickup extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'cron:pickup';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Cron Pickup order';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
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
