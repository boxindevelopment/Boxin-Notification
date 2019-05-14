<?php

namespace App\Console\Commands;

use App\Http\Resources\OrderDetailResource;
use App\Jobs\Notif\ItemStored;
use App\Models\OrderDetailBox;
use App\Models\Notification;
use App\Models\OrderDetail;
use App\Models\Box;
use App\Models\SpaceSmall;
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
        if((int)$timeNow == 16) {

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

            $queryAfer          = OrderDetailBox::query();
                                $queryAfer->where('status_id', 9);
                                $queryAfer->whereHas('order_detail', function($q) use ($afterDay) {
                                    $q->whereDate('end_date', $afterDay);
                                });
            $orderDetailBoxAfter = $queryAfer->get();
            if(count($orderDetailBoxAfter) > 0){
                foreach ($orderDetailBoxAfter as $kAfter => $vAfter) {
                    if($vAfter->order_detail) {
                        if($vAfter->order_detail->types_of_box_room_id == 1){
                            $box = Box::find($vAfter->order_detail->room_or_box_id);
                            $box->status_id = 10;
                            $box->save();

                            $vAfter->order_detail->status_id = 12;
                            $vAfter->order_detail->save();

                            $vAfter->order_detail->order->status_id = 12;
                            $vAfter->order_detail->order->save();

                            $vAfter->status_id = 10;
                            $vAfter->save();
                        } else {
                            $spaceSmall = SpaceSmall::find($vAfter->order_detail->room_or_box_id);
                            $spaceSmall->status_id = 10;
                            $spaceSmall->save();

                            $vAfter->order_detail->status_id = 12;
                            $vAfter->order_detail->save();

                            $vAfter->order_detail->order->status_id = 12;
                            $vAfter->order_detail->order->save();

                            $vAfter->status_id = 10;
                            $vAfter->save();
                        }
                    }
                }
            }

        }
    }

}
