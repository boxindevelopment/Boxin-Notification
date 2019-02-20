<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OrderDetailBox extends Model
{
    protected $table = 'order_detail_boxes';

    protected $fillable = [
        'order_detail_id', 'category_id', 'item_name', 'item_image', 'note', 'status_id'
    ];

    public function order_detail()
    {
        return $this->belongsTo('App\Models\OrderDetail', 'order_detail_id', 'id');
    }

    public function category()
    {
        return $this->belongsTo('App\Models\Category', 'category_id', 'id');
    }

    public function status()
    {
        return $this->belongsTo('App\Models\Status', 'status_id', 'id');
    }

    public function change_box()
    {
        return $this->hasMany('App\Models\ChangeBox', 'order_detail_box_id', 'id');
    }

    public function getUrlAttribute()
    {
        if (!empty($this->item_image)) {
            return asset(config('image.url.detail_item_box') . $this->item_image);
        }

        return null;
    }
}
