<?php

namespace App\Jobs\Notif;

use App\Models\Notification;
use App\Models\UserDevice;
use App\Models\OrderDetailBox;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use OneSignal;
use Log;

class ItemStored implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $user_id;
    protected $title;
    protected $data;
    protected $orderDetailBox;

    public function __construct($user_id, $title, $data, OrderDetailBox $orderDetailBox)
    {
        $this->user_id          = $user_id;
        $this->title            = $title;
        $this->data             = $data;
        $this->orderDetailBox   = $orderDetailBox;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {

            $userDevices = UserDevice::where('user_id', $this->user_id)->get();
            if($userDevices){
                $params = [];
                $params['include_player_ids'] = $userDevices->pluck('token');//array($userId);
                if($params['include_player_ids']){
                    $params['contents'] = ["en" => $this->title];
                    $params['headings'] = ["en" => $this->title];
                    $params['data'] = json_decode(json_encode(['type' => 'item-stored','detail' => ['message' => $this->title, 'data' => $this->data] ]));
                    OneSignal::sendNotificationCustom($params);

                    $dataNotif['type'] = 'item stored';
                    $dataNotif['title'] = $this->title;
                    $dataNotif['user_id'] = $this->user_id;
                    $dataNotif['order_id'] = $this->data[0]->order->id;
                    $dataNotif['notifiable_type'] = 'OrderDetailBox';
                    $dataNotif['notifiable_id'] = $this->orderDetailBox->id;
                    $dataNotif['data'] = json_encode(['type' => 'user','detail' => ['message' => $this->title, 'data' => $this->data] ]);
                    Notification::create($dataNotif);
                    \Log::info('Send Notif success');
                    \Log::info('Token:' . $userDevices->pluck('token'));

                    return $userDevices->pluck('token');
                }
            }

    }
}
