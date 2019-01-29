<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Status extends Model
{

    protected $table = 'status';

    protected $fillable = [
        'name'
    ];

    protected $searchable = ['id', 'name'];

    public function order()
    {
        return $this->hasMany('App\Models\Order', 'status_id', 'id');
    }

    public function pickup_order()
    {
        return $this->hasMany('App\Models\PickupOrder', 'status_id', 'id');
    }

    public function detail_order()
    {
        return $this->hasMany('App\Models\DetailOrder', 'status_id', 'id');
    }

    public function payment()
    {
        return $this->hasMany('App\Models\Payment', 'status_id', 'id');
    }

    public function return_box_payment()
    {
        return $this->hasMany('App\Models\ReturnBoxPayment', 'status_id', 'id');
    }

    public function space()
    {
        return $this->hasMany('App\Models\Space', 'status_id', 'id');
    }

    public function box()
    {
        return $this->hasMany('App\Models\Box', 'status_id', 'id');
    }

    public function change_box()
    {
        return $this->hasMany('App\Models\ChangeBox', 'status_id', 'id');
    }

    public function order_detail_box()
    {
        return $this->hasMany('App\Models\OrderDetailBox', 'status_id', 'id');
    }

    public function change_box_payment()
    {
        return $this->hasMany('App\Models\ChangeBoxPayment', 'status_id', 'id');
    }

    public function voucher()
    {
        return $this->hasMany('App\Models\Voucher', 'status_id', 'id');
    }

    public function banner()
    {
        return $this->hasMany('App\Models\Banner', 'status_id', 'id');
    }
}
