<?php

namespace App\Console\Commands;


use App\Jobs\Notif\SendNotif;
use App\Http\Resources\OrderDetailResource;
use App\Models\ReturnBoxes;
use App\Models\Setting;
use App\Models\OrderDetail;
use App\Models\Box;
use App\Models\SpaceSmall;
use Carbon\Carbon;
use DB;
use Illuminate\Console\Command;

class RejectedTerminate extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'terminate:reject';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Rejected terminate, status pending terminate to rejected';

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
    }
}
