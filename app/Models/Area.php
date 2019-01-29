<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Area extends Model
{

    protected $table = 'areas';

    protected $fillable = [
        'city_id', 'name', 'id_name'
    ];

    protected $searchable = ['id', 'name'];

    public function city()
    {
        return $this->belongsTo('App\Models\City', 'city_id', 'id');
    }

    public function admin()
    {
        return $this->hasMany('App\Models\Admin', 'area_id', 'id');
    }

    public function price()
    {
        return $this->hasMany('App\Models\Price', 'area_id', 'id');
    }

    public function order()
    {
        return $this->hasMany('App\Models\Order', 'area_id', 'id');
    }

    public function deliveryFee()
    {
        return $this->hasMany('App\Models\DeliveryFee', 'area_id', 'id');
    }

}
