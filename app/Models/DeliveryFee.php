<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DeliveryFee extends Model
{

    protected $table = 'delivery_fee';

    protected $fillable = [
        'area_id', 'fee'
    ];

    public function area()
    {
        return $this->belongsTo('App\Models\Area', 'area_id', 'id');
    }

}
