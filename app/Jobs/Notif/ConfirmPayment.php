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
use OneSignal;

class ConfirmPayment implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * User model instance.
     *
     * @var User
     */
    protected $user;

    public function __construct(User $user, $status)
    {
        $this->user = $user;
        $this->status = $status;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
            $userDevices = UserDevice::where('user_id', $this->user->id)->get();
            $title = 'YOur payment has been ' . $this->status;
            $params = [];
            $params['include_player_ids'] = $userDevices->pluck('token');//array($userId);
            $params['contents'] = ["en" => "Boxin Notification"];
            $params['headings'] = ["en" => $title];
            $params['data'] = json_decode(json_encode(['type' => 'confirm-payment-' . $this->status,'detail' => ['message' => $title] ]));
            OneSignal::sendNotificationCustom($params);

            $dataNotif['type'] = 'confirm payment ' . $this->status;
            $dataNotif['title'] = $title;
            $dataNotif['notifiable_type'] = 'user';
            $dataNotif['notifiable_id'] = $this->user->id;
            $dataNotif['data'] = json_encode(['type' => 'user','detail' => ['message' => $title] ]);
            Notification::create($dataNotif);

    }
}
