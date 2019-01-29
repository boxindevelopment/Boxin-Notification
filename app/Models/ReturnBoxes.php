<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ReturnBoxes extends Model
{
    protected $table = 'return_boxes';

    protected $fillable = [
        'order_detail_id', 'types_of_pickup_id', 'address', 'longitute', 'latitude', 'date', 'time', 'note', 'status_id', 'deliver_fee', 'driver_name', 'driver_phone', 'time_pickup'
    ];

    public function order_detail()
    {
        return $this->belongsTo('App\Models\OrderDetail', 'order_detail_id', 'id');
    }

    public function type_pickup()
    {
        return $this->belongsTo('App\Models\TypePickup', 'types_of_pickup_id', 'id');
    }

    public function status()
    {
        return $this->belongsTo('App\Models\Status', 'status_id', 'id');
    }

    public function toSearchableArray()
    {
        $url = 'https://boxin-prod-webbackend.azurewebsites.net/';

        if (!is_null($this->order_detail_id)) {
            if (!is_null($this->types_of_duration_id)) {
                $difference = $this->selisih;
                if($difference <= 0){
                    $difference = 0;
                }
                if($difference >= $this->total_time){
                    $difference = $this->total_time;
                }
                $duration = [
                    'id'                => $this->order_detail->type_duration->id,
                    'count_time'        => $difference,
                    'total_time'        => $this->total_time,
                    'duration_storing'  => $this->duration,
                    'name'              => $this->order_detail->type_duration->name,
                    'alias'             => $this->order_detail->type_duration->alias,
                ];
            }

            if(!is_null($this->order_detail->types_of_size_id)){
                $type_size = [
                    'id'        => $this->order_detail->type_size->id,
                    'name'      => $this->order_detail->type_size->name,
                    'size'      => $this->order_detail->type_size->size,
                    'image'     => is_null($this->order_detail->type_size->image) ? null : $url.'images/types_of_size'.'/'.$this->order_detail->type_size->image,
                ];
            }

            $location = [
                'city_id'   => $this->order_detail->order->space->warehouse->area->city->id,
                'city'      => $this->order_detail->order->space->warehouse->area->city->name,
                'area_id'   => $this->order_detail->order->space->warehouse->area->id,
                'area'      => $this->order_detail->order->space->warehouse->area->name,
                'warehouse_id' => $this->order_detail->order->space->warehouse->id,
                'warehouse' => $this->order_detail->order->space->warehouse->name,
                'space_id'  => $this->order_detail->order->space->id,
                'space'     => $this->order_detail->order->space->name,
            ];

            $order_detail = [
                'id'        => $this->order_detail->id,
                'name'      => $this->order_detail->name,
                'amount'    => $this->order_detail->amount,
                'start_date'=> $this->order_detail->start_date,
                'end_date'  => $this->order_detail->end_date,
                // 'status'    => $this->order_detail->status->name,
                'types_of_box_room_id'  => $this->order_detail->types_of_box_room_id,
                'types_of_size_id'      => $type_size,
                'location'  => $location,
                'duration'  => $duration,
            ];
        }

        if (!is_null($this->types_of_pickup_id)) {
            $type_pickup = [
                'id'        => $this->type_pickup->id,
                'name'      => $this->type_pickup->name,
            ];
        }

        $data = [
            'id'                => $this->id,
            'order_detail'      => $order_detail,
            'address'           => $this->address,
            'latitude'          => $this->latitude,
            'longitude'         => $this->longitude,
            'date'              => $this->date,
            'time'              => $this->time_pickup,
            'note'              => $this->note,
            'deliver_fee'       => $this->deliver_fee,
            'driver_name'       => $this->driver_name,
            'driver_phone'      => $this->driver_phone,
            'created_date'      => $this->created_at->format('Y-m-d'),
            'status'            => $this->status->name,
            'type_pickup'       => $type_pickup,
        ];

        return $data;

    }
}
