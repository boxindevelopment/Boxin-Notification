<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Jobs\Contact\StoreContactJob;

class ContactController extends Controller
{

    /**
     * @var
     */

    public function __construct()
    {
    }

    public function store(Request $request)
    {

        try {

            $validator = \Validator::make($request->all(), [
                'first_name' => 'required',
                'email'      => 'required',
                'phone'      => 'required',
                'message'    => 'required',
            ]);

            if($validator->fails()) {
                return response()->json([
                    'status' => false,
                    'message' => $validator->errors()
                ]);
            }

            $data = $request->only('first_name', 'last_name', 'email', 'phone', 'message');
            $confirm = StoreContactJob::dispatch($data)->onQueue('processing');
            return response()->json(['message' => 'Contact success send and creaeted']);

        } catch (ValidatorException $e) {
            return response()->json($e);
        }
    }
}
