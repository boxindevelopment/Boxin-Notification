<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\Resource;

class NotificationResource extends Resource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request
     * @return array
     */
    public function toArray($request)
    {
        $data =json_decode($this->data);
        if(is_array($data->detail->data)){
            $data->detail->data = $data->detail->data[0];
        } else {
            $data->detail->data = $data->detail->data;
        }
        return [
            'id'                => $this->id,
            'type'              => $this->type,
            'notifiable_type'   => $this->notifiable_type,
            'order_id'          => $this->order_id,
            'transaction_id'    => $this->transaction_id,
            'voucher_id'        => $this->voucher_id,
            'notifiable_id'     => $this->notifiable_id,
            'title'             => $this->title,
            'data'              => $data,
            'created_at'        => $this->created_at,
        ];
    }
}
