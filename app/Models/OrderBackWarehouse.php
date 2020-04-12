<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class OrderBackWarehouse extends Model
{
    use SoftDeletes;

    protected $hidden = ['created_at', 'updated_at'];

    protected $fillable = [
        'types_of_pickup_id', 'order_detail_id', 'status_id', 'date', 'time', 'address', 'deliver_fee', 'time_pickup', 'note'
    ];

    public function order_detail()
    {
        return $this->belongsTo('App\Models\OrderDetail', 'order_detail_id', 'id');
    }

    public function type_pickup()
    {
        return $this->belongsTo('App\Models\TypePickup', 'types_of_pickup_id', 'id');
    }

    public function user()
    {
        return $this->belongsTo('App\Models\User', 'user_id', 'id');
    }

    public function status()
    {
        return $this->belongsTo('App\Models\Status', 'status_id', 'id');
    }

    public function order_back_warehouse_payment()
    {
        return $this->hasMany('App\Models\OrderBackWarehousePayment', 'order_back_warehouse_id', 'id');
    }
}
