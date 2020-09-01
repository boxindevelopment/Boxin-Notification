<?php

namespace App\Console\Commands;

use App\Jobs\Notif\SendNotif;
use App\Http\Resources\OrderDetailResource;
use App\Models\Setting;
use App\Models\OrderDetail;
use Carbon\Carbon;
use DB;
use Illuminate\Http\Request;
use Log;
use Illuminate\Console\Command;

class CronExpiredBox extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'cron:expiredBox';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Cron Reminder period Expired box';

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
            Log::info($title);
        }
    }
}
