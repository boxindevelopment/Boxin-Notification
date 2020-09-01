<?php

namespace App\Console\Commands;

use App\Jobs\Notif\SendNotifAdmin;
use App\Models\ReturnBoxes;
use Carbon\Carbon;
use DB;
use Illuminate\Http\Request;
use Log;
use Illuminate\Console\Command;

class CronTerminateBox extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'cron:terminateBox';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Cron Terminate boxes';

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
}
