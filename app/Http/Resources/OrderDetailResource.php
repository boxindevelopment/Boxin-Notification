<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class OrderDetailResource extends JsonResource
{

    public function toArray($request)
    {
        $url = (env('DB_DATABASE') == 'coredatabase') ? 'https://boxin-dev-webbackend.azurewebsites.net/' : 'https://boxin-prod-webbackend.azurewebsites.net/';

        if (!is_null($this->order_id)) {
            $order = [
                'id'        => $this->order->id,
                'total'     => $this->order->total,
            ];
        }

        if (!is_null($this->types_of_size_id)) {
            $type_size = [
                'id'        => $this->type_size->id,
                'name'      => $this->type_size->name,
                'size'      => $this->type_size->size,
                'image'     => is_null($this->type_size->image) ? null : $url.'images/types_of_size'.'/'.$this->type_size->image,
            ];
        }

        if (!is_null($this->types_of_box_room_id)) {
            $type_box_room = [
                'id'        => $this->type_box_room->id,
                'name'      => $this->type_box_room->name,
            ];
        }

        if (!is_null($this->types_of_duration_id)) {
            $difference = $this->selisih;
            if($difference <= 0){
                $difference = 0;
            }
            if($difference >= $this->total_time){
                $difference = $this->total_time;
            }
            $duration = [
                'id'                => $this->type_duration->id,
                'count_time'        => $difference,
                'total_time'        => $this->total_time,
                'duration_storing'  => $this->duration,
                'name'              => $this->type_duration->name,
                'alias'             => $this->type_duration->alias,
            ];
        }

        $pick_up = null;
        if(!is_null($this->order->pickup_order)){

            if($this->order->pickup_order->type_pickup->id == 1){
                $pick_up = [
                    'pickup_id'         => $this->order->pickup_order->id,
                    'address'           => $this->order->pickup_order->address,
                    'date'              => $this->order->pickup_order->date,
                    'note'              => $this->order->pickup_order->note,
                    'pickup_fee'        => intval($this->order->pickup_order->pickup_fee),
                    'driver_name'       => $this->order->pickup_order->driver_name,
                    'driver_phone'      => $this->order->pickup_order->driver_phone,
                    'time_pickup'       => $this->order->pickup_order->time_pickup,
                    'type_pickup_id'    => $this->order->pickup_order->type_pickup->id,
                    'type_pickup_name'  => $this->order->pickup_order->type_pickup->name,
                ];
            }

        }

        $payment = null;
        if(!is_null($this->order->payment)){

            $payment = [
                'id'         => $this->order->payment->id,
                'order_id'   => $this->order->payment->order_id,
                'status_id'  => $this->order->payment->status->id,
                'status'     => $this->order->payment->status->name,
            ];

        }

        $return_box = null;
        if($this->return_box){
            foreach ($this->return_box as $k => $return_box) {
                if($return_box->type_pickup->id == 1){
                    $return_box = [
                        'return_box_id'     => $return_box->id,
                        'address'           => $return_box->address,
                        'date'              => $return_box->date,
                        'note'              => $return_box->note,
                        'deliver_fee'       => intval($return_box->deliver_fee),
                        'driver_name'       => $return_box->driver_name,
                        'driver_phone'      => $return_box->driver_phone,
                        'time_pickup'       => $return_box->time_pickup,
                        'type_pickup_id'    => $return_box->type_pickup->id,
                        'type_pickup_name'  => $return_box->type_pickup->name,
                    ];
                }
            }
        }

        $return_box_payment = null;
        if($this->return_box_payment){
            foreach ($this->return_box_payment as $k => $return_box_payment) {
                $return_box_payment = [
                    'id'                => $return_box_payment->id,
                    'status_id'         => $return_box_payment->status->id,
                    'status'            => $return_box_payment->status->name,
                ];
            }
        }

        $change_box = null;
        if($this->change_box){
            foreach ($this->change_box as $k => $change_box) {
                if($change_box->type_pickup->id == 1){
                    $change_box = [
                        'change_box_id'     => $change_box->id,
                        'address'           => $change_box->address,
                        'date'              => $change_box->date,
                        'note'              => $change_box->note,
                        'deliver_fee'       => intval($change_box->deliver_fee),
                        'driver_name'       => $change_box->driver_name,
                        'driver_phone'      => $change_box->driver_phone,
                        'time_pickup'       => $change_box->time_pickup,
                        'type_pickup_id'    => $change_box->type_pickup->id,
                        'type_pickup_name'  => $change_box->type_pickup->name,
                    ];
                }
            }
        }

        $change_box_payment = null;
        if($this->change_box_payment){
            foreach ($this->change_box_payment as $k => $change_box_payment) {
                $change_box_payment = [
                    'id'                => $change_box_payment->id,
                    'status_id'         => $change_box_payment->status->id,
                    'status'            => $change_box_payment->status->name,
                ];
            }
        }

        $location = [
            'city_id'                   => $this->order->area->city->id,
            'city'                      => $this->order->area->city->name,
            'area_id'                   => $this->order->area->id,
            'area'                      => $this->order->area->name,
        ];

        $data = [
            'id'                        => $this->id,
            'code'                      => $this->id_name,
            'name'                      => $this->name,
            'amount'                    => $this->amount,
            'start_date'                => $this->start_date,
            'end_date'                  => $this->end_date,
            'status_id'                 => $this->status->id,
            'status'                    => $this->status->name,
            'types_of_box_room_id'      => $type_box_room,
            'types_of_size'             => $type_size,
            'order'                     => $order,
            'location'                  => $location,
            'duration'                  => $duration,
            'pickup'                    => $pick_up,
            'payment'                   => $payment,
            'return_box'                => $return_box,
            'return_box_payment'        => $return_box_payment,
            'change_box'                => $change_box,
            'change_box_payment'        => $change_box_payment,
        ];

        return $data;
    }
}
