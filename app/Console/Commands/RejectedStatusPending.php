<?php

namespace App\Console\Commands;

use App\Jobs\Notif\SendNotif;
use App\Http\Resources\OrderDetailResource;
use App\Models\Order;
use App\Models\Setting;
use App\Models\OrderDetail;
use App\Models\Box;
use App\Models\SpaceSmall;
use Carbon\Carbon;
use DB;
use Illuminate\Console\Command;

class RejectedStatusPending extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'status:reject';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Rejected order, status pending payment order to rejected';

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
                error_log('Masuk data');
                $no = 0;
                foreach ($orders as $k => $v) {
                    $no++;
                    error_log($no . 'Masuk data' . $v->status_id);
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
                            error_log($no . 'Your payment has been rejected' . $v->status_id);
                            $data = OrderDetailResource::collection($orderDetails);
                            $title = 'Your payment has been rejected';
                            SendNotif::dispatch($v->user_id, $title, $data, 'confirm-payment-rejected', 'confirm payment rejected')->onQueue('processing');
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
