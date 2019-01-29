<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Order extends Model
{
    use SoftDeletes;

    protected $table = 'orders';

    protected $fillable = [
        'user_id', 'area_id', 'total', 'qty', 'status_id'
    ];

    public function user()
    {
        return $this->belongsTo('App\Models\User', 'user_id', 'id');
    }

    public function area()
    {
        return $this->belongsTo('App\Models\Area', 'area_id', 'id');
    }

    public function status()
    {
        return $this->belongsTo('App\Models\Status', 'status_id', 'id');
    }

    public function order_detail()
    {
        return $this->hasMany('App\Models\OrderDetail', 'order_id', 'id');
    }

    public function pickup_order()
    {
        return $this->hasOne('App\Models\PickupOrder', 'order_id', 'id');
    }

}
