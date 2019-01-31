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

class PickupApproved implements ShouldQueue
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
            $params = [];
            $params['include_player_ids'] = $userDevices->pluck('token');//array($userId);
            $params['contents'] = ["en" => $this->title];
            $params['headings'] = ["en" => $this->title];
            $params['data'] = json_decode(json_encode(['type' => 'pickup-approved','detail' => ['message' => $this->title] ]));
            OneSignal::sendNotificationCustom($params);

            $dataNotif['type'] = 'pickup approved';
            $dataNotif['title'] = $this->title;
            $dataNotif['user_id'] = $this->user_id;
            $dataNotif['notifiable_type'] = 'user';
            $dataNotif['notifiable_id'] = $this->user_id;
            $dataNotif['data'] = json_encode(['type' => 'user','detail' => ['message' => $this->title, 'data' => $this->data] ]);
            Notification::create($dataNotif);

            return $userDevices->pluck('token');

    }
}
