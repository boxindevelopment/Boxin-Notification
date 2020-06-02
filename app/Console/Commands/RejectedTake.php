<?php

namespace App\Console\Commands;

use App\Jobs\Notif\SendNotif;
use App\Http\Resources\OrderDetailResource;
use App\Models\OrderTake;
use App\Models\Setting;
use App\Models\OrderDetail;
use App\Models\Box;
use App\Models\SpaceSmall;
use Carbon\Carbon;
use DB;
use Illuminate\Console\Command;

class RejectedTake extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'take:reject';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Rejected take, status pending take to rejected';

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
            }
        } catch (Exception $e) {
            DB::rollback();
        }
    }
}
