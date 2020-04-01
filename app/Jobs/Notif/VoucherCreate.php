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
    protected $code;
    protected $id;

    public function __construct($title, $head, $name, $code, $id)
    {
        $this->title = $title;
        $this->name = $name;
        $this->head = $head;
        $this->code = $code;
        $this->id = $id;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $userDevices = UserDevice::where('device', '<>' , 'web')->get();
        $token = [];
        foreach($userDevices as $value){
            $token = $value->pluck('token');
        }
        if($token){
            $params = [];
            $params['include_player_ids'] = $token;
            $params['contents'] = ["en" => $this->name];
            $params['headings'] = ["en" => $this->title];
            $params['data'] = json_decode(json_encode(['type' => 'Voucher','detail' => [
                'message' => $this->title,'name' => $this->name ,'code' => $this->code, 'id' => $this->id
            ] ]));
            OneSignal::sendNotificationCustom($params);
            $dataNotif['type'] = 'Voucher';
            $dataNotif['title'] = $this->title;
            $dataNotif['user_id'] = $value['user_id'];
            $dataNotif['notifiable_type'] = 'Voucher';
            $dataNotif['notifiable_id'] = $value['user_id'];
            $dataNotif['data'] = json_encode(['type' => 'Voucher','detail' => ['message' => $this->title, 'name' => $this->name] ]);
            Notification::create($dataNotif);
            return $token;
        } else {
            return false;
        }
    }
}