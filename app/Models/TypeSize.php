<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TypeSize extends Model
{

    protected $table = 'types_of_size';

    protected $fillable = [
        'type_of_box_room_id', 'name', 'size', 'image',
    ];

    protected $searchable = ['id', 'name'];

    public function type_box_room()
    {
        return $this->belongsTo('App\Models\TypeBoxRoom', 'types_of_box_room_id', 'id');
    }

    public function order_detail()
    {
        return $this->hasMany('App\Models\OrderDetail', 'types_of_size_id', 'id');
    }

    public function space()
    {
        return $this->hasMany('App\Models\Space', 'types_of_size_id', 'id');
    }

    public function price()
    {
        return $this->hasMany('App\Models\Price', 'types_of_size_id', 'id');
    }

    public function box()
    {
        return $this->hasMany('App\Models\Box', 'types_of_size_id', 'id');
    }

}
