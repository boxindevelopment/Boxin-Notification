<?php

namespace App\Console\Commands;

use App\Jobs\Notif\SendNotifAdmin;
use App\Models\OrderTake;
use Carbon\Carbon;
use DB;
use Illuminate\Http\Request;
use Log;
use Illuminate\Console\Command;

class CronTakeBox extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'cron:takeBox';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Cron take boxes';

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
}
