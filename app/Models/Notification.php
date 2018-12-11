<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Notification extends Model
{
    //
    protected $fillable = [
        'id',
        'type',
        'notifiable_type',
        'user_id',
        'order_id',
        'title',
        'notifiable_id',
        'data',
    ];

    /**
     * [consultant_id Relationship to User]
     * @return [type] [description]
     */
    public function user()
    {
        return $this->belongsTo('App\Models\User', 'user_id');
    }


}
