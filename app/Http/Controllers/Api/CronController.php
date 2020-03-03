<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Jobs\Contact\StoreContactJob;
use App\Models\Order;
use App\Models\OrderDetail;
use App\Models\PickupOrder;
use App\Models\Box;
use App\Models\SpaceSmall;
use DB;
use Carbon\Carbon;
use Log;
use Illuminate\Http\Request;

class CronController extends Controller
{

    /**
     * @var
     */

    public function __construct()
    {
    }

    public function minutes(Request $request)
    {
        $now = Carbon::now('Asia/Jakarta');
        $beforeDay = $now->addMinutes('-60')->format('Y-m-d H:i:s');
        $timeNow = Carbon::now('Asia/Jakarta')->format('Y-m-d H:i:s');
        $query  = Order::query();
        $query->with('pickup_order', 'order_detail');
        $query->where('status_id', 14);
        $query->where('created_at', '<', $beforeDay);
        $orders = $query->get();
        if(count($orders) > 0){
            foreach ($orders as $k => $v) {
                Log::info('id order : ' . $v->id);
                foreach ($v->order_detail as $key => $d) {
                    if($d->types_of_box_room_id == 1){
                        $box = BOX::where('id', $d->room_or_box_id)->first();
                        // $orders[$k]->order_detail[$key]->box = $box;
                    } else if($d->types_of_box_room_id == 2){
                        $space = SpaceSmall::where('id', $d->room_or_box_id)->first();
                        // $orders[$k]->order_detail[$key]->space = $space;
                    }
                    Log::info('code : ' . $d->id_name);
                    // Box::where('id', $room_or_box_id)->update(['status_id' => 10]);
                    // SpaceSmall::where('id', $room_or_box_id)->update(['status_id' => 10]);
                }
            }
            return response()->json(['status' => 'success', 'message' => 'Order Count : ' . count($orders), 'order' => $orders], 200);
        } else {
            Log::info('No Order');
            return response()->json(['status' => 'error', 'message' => 'No Order'], 402);
        }
        return true;
    }
}
