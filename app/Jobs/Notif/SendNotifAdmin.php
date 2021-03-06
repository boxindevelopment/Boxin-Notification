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

class SendNotifAdmin implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $id;
    protected $title;
    protected $data;
    protected $type;
    protected $types;

    public function __construct($id, $title, $data, $type, $types)
    {
        $this->id = $id;
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

        $token = UserDevice::where('device', 'web')->whereNotNull('token')->where('token', '<>', 'null')->get()->pluck('token');
        if(count($token) > 0){
            $params = [];
            $params['include_player_ids'] = $token;
            $params['contents'] = ["en" => $this->title];
            $params['headings'] = ["en" => $this->title];
            $params['data'] = json_decode(json_encode(['type' => $this->type,'detail' => ['message' => $this->title, 'data' => $this->data] ]));
            OneSignal::sendNotificationCustom($params);

            $admins = User::where('roles_id', 3)->get();
            if($admins){
                foreach($admins as $kAdmin => $vAdmin){
                    $dataNotifAdmin['type'] = $this->types;
                    $dataNotifAdmin['title'] = $this->title;
                    $dataNotifAdmin['user_id'] = $vAdmin->id;
                    $dataNotifAdmin['send_user'] = $this->id;
                    if($this->data){
                        if($this->data[0]){
                            $dataNotifAdmin['order_id'] = $this->data[0]->order->id;
                        } else {
                            $dataNotifAdmin['order_id'] = $this->data->order_detail_id;
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
