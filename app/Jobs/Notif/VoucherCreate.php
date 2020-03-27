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

class VoucherCreate implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $title;
    protected $head;
    protected $name;

    public function __construct($title, $head, $name)
    {
        $this->title = $title;
        $this->name = $name;
        $this->head = $head;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $userDevices = UserDevice::where('device', '<>' , 'web')->get();
        foreach($userDevices as $value){
            $token = $value->pluck('token');
            if($token){
                $params = [];
                $params['include_player_ids'] = $token;
                $params['contents'] = ["en" => $this->title];
                $params['headings'] = ["en" => $this->title];
                $params['data'] = json_decode(json_encode(['type' => 'Promo','detail' => ['message' => $this->title] ]));
                OneSignal::sendNotificationCustom($params);
                $dataNotif['type'] = 'delivery approved';
                $dataNotif['title'] = $this->title;
                $dataNotif['user_id'] = $value['user_id'];
                $dataNotif['notifiable_type'] = 'user';
                $dataNotif['notifiable_id'] = $value['user_id'];
                $dataNotif['data'] = json_encode(['type' => 'user','detail' => ['message' => $this->title, 'data' => $this->name] ]);
                Notification::create($dataNotif);
                return $token;
            } else {
                return false;
            }

        }
    }
}
