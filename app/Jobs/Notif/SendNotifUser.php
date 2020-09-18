<?php

namespace App\Jobs\Notif;

use App\Models\Notification;
use App\Models\UserDevice;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use OneSignal;

class SendNotifUser implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $user_id;
    protected $title;
    protected $data;
    protected $type;
    protected $types;

    public function __construct($user_id, $title, $data, $type, $types)
    {
        $this->user_id = $user_id;
        $this->title = $title;
        $this->data = $data;
        $this->type = $type;
        $this->types = $types;
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
        if(count($token) > 0){
            $params = [];
            $params['include_player_ids'] = $token;
            $params['contents'] = ["en" => $this->title];
            $params['headings'] = ["en" => $this->title];
            $params['data'] = json_decode(json_encode(['type' => $this->type,'detail' => ['message' => $this->title, 'data' => $this->data] ]));
            OneSignal::sendNotificationCustom($params);

            $dataNotif['type'] = $this->types;
            $dataNotif['title'] = $this->title;
            $dataNotif['user_id'] = $this->user_id;
            $dataNotif['notifiable_type'] = 'user';
            $dataNotif['notifiable_id'] = $this->user_id;
            $dataNotif['data'] = json_encode(['type' => 'user','detail' => ['message' => $this->title, 'data' => $this->data] ]);
            Notification::create($dataNotif);

            return $token;
        } else {
            return false;
        }

    }
}
