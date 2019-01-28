<?php

namespace App\Jobs\Notif;

use App\Models\Notification;
use App\Models\User;
use App\Models\UserDevice;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Log;
use OneSignal;

class ConfirmPayment implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $user_id;
    protected $status;

    public function __construct($user_id, $status)
    {
        $this->user_id = $user_id;
        $this->status = $status;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {

        $user = User::find($this->user_id);
        if($user){
            $userDevices = UserDevice::where('user_id', $user->id)->get();
            $title = 'Your payment has been ' . $this->status;
            $params = [];
            $params['include_player_ids'] = $userDevices->pluck('token');//array($userId);
            $params['contents'] = ["en" => $title];
            $params['headings'] = ["en" => "Boxin Notification confirm payment"];
            $params['data'] = json_decode(json_encode(['type' => 'confirm-payment-' . $this->status,'detail' => ['message' => $title] ]));
            OneSignal::sendNotificationCustom($params);

            $dataNotif['type'] = 'confirm payment ' . $this->status;
            $dataNotif['title'] = $title;
            $dataNotif['user_id'] = $user->id;
            $dataNotif['notifiable_type'] = 'user';
            $dataNotif['notifiable_id'] = $user->id;
            $dataNotif['data'] = json_encode(['type' => 'user','detail' => ['message' => $title] ]);
            $notification = Notification::create($dataNotif);

            return $notification;
        } else {
            return false;
        }

    }
}
