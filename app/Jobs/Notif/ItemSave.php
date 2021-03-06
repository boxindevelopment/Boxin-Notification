<?php

namespace App\Jobs\Notif;

use App\Models\Notification;
use App\Models\UserDevice;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use OneSignal;
use Log;

class ItemSave implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $user_id;
    protected $title;
    protected $data;

    public function __construct($user_id, $title, $data)
    {
        $this->user_id  = $user_id;
        $this->title    = $title;
        $this->data     = $data;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {

            $userDevices = UserDevice::where('user_id', $this->user_id)->get();
            $token = $userDevices->pluck('token');
            if($token){
                $params = [];
                $params['include_player_ids'] = $token;//array($userId);
                $params['contents'] = ["en" => $this->title];
                $params['headings'] = ["en" => $this->title];
                $params['data'] = json_decode(json_encode(['type' => 'item-save','detail' => ['message' => $this->title, 'data' => $this->data] ]));
                OneSignal::sendNotificationCustom($params);

                $dataNotif['type'] = 'item save';
                $dataNotif['title'] = $this->title;
                $dataNotif['user_id'] = $this->user_id;
                $dataNotif['notifiable_type'] = 'user';
                $dataNotif['notifiable_id'] = $this->user_id;
                $dataNotif['data'] = json_encode(['type' => 'user','detail' => ['message' => $this->title, 'data' => $this->data] ]);
                Notification::create($dataNotif);

                return $userDevices->pluck('token');
            } else {
                return false;
            }

    }
}
