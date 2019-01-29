<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ChangeBox extends Model
{
    protected $table = 'change_boxes';

    protected $fillable = [
        'order_detail_id', 'order_detail_box_id', 'types_of_pickup_id', 'address', 'date', 'time_pickup', 'note', 'status_id', 'deliver_fee', 'driver_name', 'driver_phone',
    ];

    public function order_detail()
    {
        return $this->belongsTo('App\Models\OrderDetail', 'order_detail_id', 'id');
    }

    public function order_detail_box()
    {
        return $this->belongsTo('App\Models\OrderDetailBox', 'order_detail_box_id', 'id');
    }

    public function type_pickup()
    {
        return $this->belongsTo('App\Models\TypePickup', 'types_of_pickup_id', 'id');
    }

    public function status()
    {
        return $this->belongsTo('App\Models\Status', 'status_id', 'id');
    }

}
