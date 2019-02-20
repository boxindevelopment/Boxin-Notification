<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Shelves extends Model
{

    protected $table = 'shelves';

    protected $fillable = [
        'space_id', 'name', 'id_name'
    ];

    public function space()
    {
        return $this->belongsTo('App\Models\Space', 'space_id', 'id');
    }

    public function box()
    {
        return $this->hasMany('App\Models\Box', 'shelves_id', 'id');
    }

    public function order_detail()
    {
        return $this->hasMany('App\Models\OrderDetail', 'room_or_box_id', 'id');
    }

}
