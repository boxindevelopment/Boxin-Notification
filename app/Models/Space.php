<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Space extends Model
{

    protected $table = 'spaces';

    protected $fillable = [
        'area_id', 'name', 'lat', 'long', 'id_name', 'types_of_size_id', 'status_id',
    ];

    protected $searchable = ['id', 'name'];

    public function area()
    {
        return $this->belongsTo('App\Models\Area', 'area_id', 'id');
    }

    public function type_size()
    {
        return $this->belongsTo('App\Models\TypeSize', 'types_of_size_id', 'id');
    }

    public function box()
    {
        return $this->hasMany('App\Models\Box', 'status_id', 'id');
    }

    public function status()
    {
        return $this->belongsTo('App\Models\Status', 'status_id', 'id');
    }

}
