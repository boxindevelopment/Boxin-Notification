<?php

namespace App\Console\Commands;

use App\Jobs\Notif\SendNotifAdmin;
use App\Models\OrderBackWarehouse;
use Carbon\Carbon;
use DB;
use Illuminate\Http\Request;
use Log;
use Illuminate\Console\Command;

class CronReturnBox extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'cron:returnBox';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Cron return box';

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
            }
            
        }
    }
}
