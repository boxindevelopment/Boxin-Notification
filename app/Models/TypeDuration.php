<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TypeDuration extends Model
{

    protected $table = 'types_of_duration';

    protected $fillable = [
        'name'
    ];

    protected $searchable = ['id', 'name'];

    public function order_detail()
    {
        return $this->hasMany('App\Models\OrderDetail', 'types_of_duration_id', 'id');
    }
}
