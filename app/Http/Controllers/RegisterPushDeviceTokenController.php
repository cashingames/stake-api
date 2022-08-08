<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\FcmPushSubscription;

class RegisterPushDeviceTokenController extends Controller
{
    public function __invoke(Request $request)
    {
        $this->validate($request, [
            'device_token'    => 'required',
            'topic'   => 'string',

        ]);
        $user = auth()->user();

        $token = $request->device_token;
        $topic = $request->topic;

        FcmPushSubscription::updateOrCreate([
            'user_id' => $user->id,
            'topic' => $topic
        ], [
            'device_token' => $token,
        ]);

        return response()->json([
            'status' => 'success',
            'message' => "Push Subscription stored successfully"
        ]);
    }
}
