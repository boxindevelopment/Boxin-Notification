<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ReturnBoxPayment extends Model
{

    protected $table = 'return_box_payments';

    protected $fillable = [
        'order_detail_id', 'user_id', 'payment_type', 'bank', 'amount', 'image_transfer', 'status_id', 'id_name'
    ];

    public function order_detail()
    {
        return $this->belongsTo('App\Models\OrderDetail', 'order_detail_id', 'id');
    }

    public function user()
    {
        return $this->belongsTo('App\Models\User', 'user_id', 'id');
    }

    public function status()
    {
        return $this->belongsTo('App\Models\Status', 'status_id', 'id');
    }

}
