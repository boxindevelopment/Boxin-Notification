<?php

namespace App\Jobs\Notif;

use App\Models\Notification;
use App\Models\UserDevice;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Log;
use OneSignal;

class DeliveryApproved implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $user_id;

    public function __construct($user_id)
    {
        $this->user_id = $user_id;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {

            $userDevices = UserDevice::where('user_id', $this->user_id)->get();
            $title = "Don't forget to prepare your items!&#013;Our courier will come tomorrow";
            $params = [];
            $params['include_player_ids'] = $userDevices->pluck('token');//array($userId);
            $params['contents'] = ["en" => $title];
            $params['headings'] = ["en" => $title];
            $params['data'] = json_decode(json_encode(['type' => 'delivery-approved','detail' => ['message' => $title] ]));
            OneSignal::sendNotificationCustom($params);

            $dataNotif['type'] = 'delivery approved';
            $dataNotif['title'] = $title;
            $dataNotif['user_id'] = $this->user_id;
            $dataNotif['notifiable_type'] = 'user';
            $dataNotif['notifiable_id'] = $this->user_id;
            $dataNotif['data'] = json_encode(['type' => 'user','detail' => ['message' => $title, 'data' => $this->data] ]);
            Notification::create($dataNotif);

            return $userDevices->pluck('token');

    }
}
