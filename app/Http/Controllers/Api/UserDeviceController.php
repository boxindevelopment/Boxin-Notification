<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\UserDevice;
use Mail;
use Validator;
use DB;

class UserDeviceController extends Controller
{

    /**
     * @var
     */

    public function __construct()
    {
    }

    public function deviceToken(Request $request)
    {
        $users = $request->user();

        try {

            $request->validate([
                'token' => 'required',
                'device' => 'required',
            ]);

            $device = UserDevice::where('user_id', $users->id)->where('token', $request->token)->count();
            if($device < 1){
                $device = UserDevice::create(['user_id' => $users->id,
                                              'token' => $request->input('token'),
                                              'device' => $request->input('device')]);
            }

            return response()->json([
                'message' => 'Device success creaeted',
                'data' => $device
            ], 200);

        } catch (ValidatorException $e) {
            return response()->json($e);
        }

    }

    public function deviceTokenDelete(Request $request)
    {

    }

}
