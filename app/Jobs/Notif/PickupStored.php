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

class PickupStored implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $user_id;
    protected $title;

    public function __construct($user_id, $title)
    {
        $this->user_id = $user_id;
        $this->title = $title;
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
        if($token) {
            $params = [];
            $params['include_player_ids'] = $token;//array($userId);
            $params['contents'] = ["en" => $this->title];
            $params['headings'] = ["en" => $this->title];
            $params['data'] = json_decode(json_encode(['type' => 'pickup-stored','detail' => ['message' => $this->title] ]));
            OneSignal::sendNotificationCustom($params);

            $dataNotif['type'] = 'pickup stored';
            $dataNotif['title'] = $this->title;
            $dataNotif['user_id'] = $this->user_id;
            $dataNotif['notifiable_type'] = 'user';
            $dataNotif['notifiable_id'] = $this->user_id;
            $dataNotif['data'] = json_encode(['type' => 'user','detail' => ['message' => $this->title] ]);
            Notification::create($dataNotif);

            return $userDevices->pluck('token');
        } else {
            return false;
        }

    }
}
