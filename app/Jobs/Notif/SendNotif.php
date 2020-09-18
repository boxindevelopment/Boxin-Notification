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
use Log;

class SendNotif implements ShouldQueue
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

        $userDevices = UserDevice::where('user_id', $this->user_id)->whereNotNull('token')->where('token', '<>', 'null')->get();
        $token = $userDevices->pluck('token');
        if(count($token) > 0){
            $adminTokens = UserDevice::where('device', 'web')->whereNotNull('token')->where('token', '<>', 'null')->get()->pluck('token');
            if($adminTokens){
                $count = count($token);
                foreach($adminTokens as $k => $v){
		    Log::info('Token'.$count.':' . $v);
		    if($v){
                      $token[$count] = $v;
                      $count++;
		    }
                }
            }
            $params = [];
            $params['include_player_ids'] = $token;
	    Log::info(json_encode($token));
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

            $admins = User::where('roles_id', 3)->get();
            if($admins){
                foreach($admins as $kAdmin => $vAdmin){
                    $dataNotifAdmin['type'] = $this->types;
                    $dataNotifAdmin['title'] = $this->title;
                    $dataNotifAdmin['user_id'] = $vAdmin->id;
                    $dataNotifAdmin['send_user'] = $this->user_id;
                    if($this->data){
                        if($this->data[0]){
                            $dataNotifAdmin['order_id'] = $this->data[0]->order->id;
                        } else {
                            $dataNotifAdmin['order_id'] = $this->data->order->id;
                        }
                    }
                    $dataNotifAdmin['notifiable_type'] = 'admin';
                    $dataNotifAdmin['notifiable_id'] = $vAdmin->id;
                    $dataNotifAdmin['data'] = json_encode(['type' => 'admin','detail' => ['message' => $this->title, 'data' => $this->data] ]);
                    $notification = Notification::create($dataNotifAdmin);
                }
            }

            return $token;
        } else {
            return false;
        }

    }
}
