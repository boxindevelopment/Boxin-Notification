<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TypePickup extends Model
{
    protected $table = 'types_of_pickup';

    protected $fillable = [
        'name'
    ];

    protected $searchable = ['id', 'name'];

    public function pickup_order()
    {
        return $this->hasMany('App\Models\PickupOrder', 'types_of_pickup_id', 'id');
    }

    public function return_box()
    {
        return $this->hasMany('App\Models\ReturnBoxes', 'types_of_pickup_id', 'id');
    }

    public function change_box()
    {
        return $this->hasMany('App\Models\ChangeBoxes', 'types_of_pickup_id', 'id');
    }

}
