<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\UserDevice;
use Tymon\JWTAuth\Facades\JWTAuth;
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
        $users = JWTAuth::parseToken()->authenticate();

        try {

            $request->validate([
                'token' => 'required',
            ]);

            $device = UserDevice::where('user_id', $users->id)->where('token', $request->token)->count();
            if($device > 0){
                  return response()->json([
                      'message' => 'Device token has been created'
                  ], 200);
            }

            $userToken = UserDevice::create(['user_id' => $users->id, 'token' => $request->input('token')]);

            return response()->json([
                'message' => 'Device success creaeted',
                'data' => $userToken
            ], 200);

        } catch (ValidatorException $e) {
            return response()->json($e);
        }

    }

    public function deviceTokenDelete(Request $request)
    {

    }

}
