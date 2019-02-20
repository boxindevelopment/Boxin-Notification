<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Box extends Model
{
    protected $table = 'boxes';

    protected $fillable = [
        'shelves_id', 'types_of_size_id', 'name', 'barcode', 'location', 'size', 'price', 'status_id', 'id_name'
    ];

    protected $hidden = ['created_at', 'updated_at'];

   public function shelves()
    {
        return $this->belongsTo('App\Models\Shelves', 'shelves_id', 'id');
    }

    public function type_size()
    {
        return $this->belongsTo('App\Models\TypeSize', 'types_of_size_id', 'id');
    }

    public function order_detail()
    {
        return $this->hasMany('App\Models\OrderDetail', 'room_or_box_id', 'id');
    }

    public function status()
    {
        return $this->belongsTo('App\Models\Status', 'status_id', 'id');
    }

}
