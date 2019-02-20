<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TypeBoxRoom extends Model
{

    protected $table = 'types_of_box_room';

    protected $fillable = [
        'name'
    ];

    protected $searchable = ['id', 'name'];

    public function order_detail()
    {
        return $this->hasMany('App\Models\OrderDetail', 'types_of_box_room_id', 'id');
    }

    public function price()
    {
        return $this->hasMany('App\Models\Price', 'types_of_box_room_id', 'id');
    }

    public function type_size()
    {
        return $this->hasMany('App\Models\TypeSize', 'types_of_box_room_id', 'id');
    }

}
