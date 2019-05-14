<?php

namespace App\Console\Commands;

use App\Http\Resources\OrderDetailResource;
use App\Jobs\Notif\ItemStored;
use App\Models\OrderDetailBox;
use App\Models\Notification;
use App\Models\OrderDetail;
use Carbon\Carbon;
use DB;
use Log;

use Illuminate\Console\Command;

class ExpiredBox extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'Notif:ExpiredBox';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Notification Expired Box';

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


        $now = Carbon::now();
        $beforeDay = $now->addDays('-1')->format('Y-m-d');
        $now = Carbon::now();
        $afterDay = $now->addDays('1');
        $timeNow = Carbon::now('Asia/Jakarta')->format('H');
        if((int)$timeNow == 9) {

            $query          = OrderDetailBox::query();
                              $query->where('status_id', 9);
                              $query->whereRaw("[id] NOT IN (SELECT [notifiable_id] FROM [notifications] WHERE [notifiable_type] = 'OrderDetailBox')");
                              $query->whereHas('order_detail', function($q) use ($beforeDay) {
                                  $q->whereDate('end_date', $beforeDay);
                              });
            $orderDetailBox = $query->get();

            if(count($orderDetailBox) > 0){
                foreach ($orderDetailBox as $k => $v) {
                    if($v->order_detail){
                        $notification = Notification::where('notifiable_type', 'OrderDetailBox')
                                                    ->where('notifiable_id', $v->id)
                                                    ->get();
                		$orderDetails     =  OrderDetail::select('order_details.*', DB::raw('orders.status_id as status_id'), DB::raw('orders.user_id as user_id'), DB::raw('DATEDIFF(day, order_details.start_date, order_details.end_date) as total_time'), DB::raw('DATEDIFF(day, order_details.start_date, GETDATE()) as selisih'))
                						            ->leftJoin('orders', 'orders.id', '=', 'order_details.order_id')
                						            ->where('order_details.id', $v->order_detail->id)
                						            ->get();
                        $data             = OrderDetailResource::collection($orderDetails);
                		$title            = "Tomorrow is the last day of your storage";
                        $token            = ItemStored::dispatch($v->order_detail->order->user_id, $title, $data, $v)->onQueue('processing');
                    }
                }
            }

        }

    }
}
