<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Price extends Model
{
    protected $table = 'prices';

    protected $fillable = [
        'types_of_box_room_id', 'types_of_size_id', 'types_of_duration_id', 'price', 'area_id'
    ];

    public function type_box_room()
    {
        return $this->belongsTo('App\Models\TypeBoxRoom', 'types_of_box_room_id', 'id');
    }

    public function type_duration()
    {
        return $this->belongsTo('App\Models\TypeDuration', 'types_of_duration_id', 'id');
    }

    public function type_size()
    {
        return $this->belongsTo('App\Models\TypeSize', 'types_of_size_id', 'id');
    }

    public function area()
    {
        return $this->belongsTo('App\Models\Area', 'area_id', 'id');
    }

}
