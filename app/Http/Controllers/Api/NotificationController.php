<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Resources\NotificationResource;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Notification;
use Validator;

class NotificationController extends Controller
{

    /**
     * @var
     */

    public function __construct()
    {
    }

    public function index(Request $request)
    {
        $users = $request->user();

        try {

            $notification = Notification::where('user_id', $users->id)->where('created_at', 'desc')->paginate(15);
            // return response()->json($notification);
            return NotificationResource::collection($notification);

        } catch (ValidatorException $e) {
            return response()->json($e);
        }

    }

}
