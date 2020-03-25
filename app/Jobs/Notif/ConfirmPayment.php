<?php

namespace App\Jobs\Notif;

use App\Models\Notification;
use App\Models\User;
use App\Models\UserDevice;
use App\Models\Payment;
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
    protected $data;

    public function __construct($user_id, $status, $data)
    {
        $this->user_id = $user_id;
        $this->status = $status;
        $this->data = $data;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {

        $user = User::find($this->user_id);
        if ($user) {
            $userDevices = UserDevice::where('user_id', $user->id)->get();
            $title       = 'Your payment has been ' . $this->status;
            $head        = 'Payment Rejected';
            if ($this->status == 'approved') {
              $head  = 'Payment Approved';
              $title = 'Your payment has been approved. Please remember to use your box/space on your selected date';
            }

            if(count($userDevices) > 0) {
                $token = $userDevices->pluck('token');
                if($token){
                    $adminTokens = UserDevice::where('device', 'web')->get()->pluck('token');
                    if($adminTokens){
                        $count = count($token);
                        foreach($adminTokens as $k => $v){
                            $token[$count] = $v;
                            $count++;
                        }
                    }
                    $params = [];
                    $params['include_player_ids'] = $token;//array($userId);
                    $params['contents'] = ["en" => $title];
                    // $params['headings'] = ["en" => "Boxin Notification confirm payment"];
                    $params['headings'] = ["en" => $head];
                    $params['data'] = json_decode(json_encode(['type' => 'confirm-payment-' . $this->status,'detail' => ['message' => $title, 'data' => $this->data] ]));
                    $onesignal = OneSignal::sendNotificationCustom($params);

                    $dataNotif['type'] = 'confirm payment ' . $this->status;
                    $dataNotif['title'] = $title;
                    $dataNotif['user_id'] = $user->id;
                    $dataNotif['order_id'] = $this->data[0]->order->id;
                    $dataNotif['notifiable_type'] = 'user';
                    $dataNotif['notifiable_id'] = $user->id;
                    $dataNotif['data'] = json_encode(['type' => 'user','detail' => ['message' => $title, 'data' => $this->data] ]);
                    $notification = Notification::create($dataNotif);

                    
                    $admins = User::where('roles_id', 3)->get();
                    if($admins){
                        foreach($admins as $kAdmin => $vAdmin){
                            $dataNotifAdmin['type'] = 'confirm payment ' . $this->status;
                            $dataNotifAdmin['title'] = $title;
                            $dataNotifAdmin['user_id'] = $vAdmin->id;
                            $dataNotifAdmin['order_id'] = $this->data[0]->order->id;
                            $dataNotifAdmin['notifiable_type'] = 'admin';
                            $dataNotifAdmin['notifiable_id'] = $vAdmin->id;
                            $dataNotifAdmin['data'] = json_encode(['type' => 'admin','detail' => ['message' => $title, 'data' => $this->data] ]);
                            $notification = Notification::create($dataNotifAdmin);
                        }
                    }

                    return $notification;
                } else {
                    return false;
                }
            } else {
                return false;
            }
        } else {
            return false;
        }

    }
}
