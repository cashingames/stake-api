<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller as Controller;
use App\Models\UserPoint;
use Illuminate\Support\Facades\Log;

class BaseController extends Controller
{
    public $token;
    public $user;
    public $MINIMUM_GAME_BOOST_SCORE;

    function __construct()
    {
        $this->user = auth()->user();
        if ($this->user && $this->user->trashed()) {
            auth()->logout(true);
        }

        // setting minimum game score
        $this->MINIMUM_GAME_BOOST_SCORE = config("trivia.minimum_game_boost_score");
    }

    /**
     * success response method.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function sendResponse($result, $message)
    {
        $response = [
            'success' => true,
            'data'    => $result,
            'message' => $message,
        ];

        Log::info("API Response success "
            . $this->user?->username . " from endpoint  "
            .  url()->current() . " response " . json_encode($result));


        return response()->json($response, 200);
    }

    public function sendError($errors, $message)
    {
        $response = [
            'success' => false,
            'errors'    => $errors,
            'message' => $message,
        ];

        Log::info("API Response error "
            .  $this->user?->username . " from endpoint  "
            . url()->current() . " response " . json_encode($errors));


        return response()->json($response, 400);
    }

    public function creditPoints($userId, $points, $description)
    {

        //create point traffic log
        UserPoint::create([
            'user_id' => $userId,
            'value' => $points,
            'description' => $description,
            'point_flow_type' => 'POINTS_ADDED'
        ]);
    }

    public function subtractPoints($userId, $points, $description)
    {

        //create point traffic log
        UserPoint::create([
            'user_id' => $userId,
            'value' => $points,
            'description' => $description,
            'point_flow_type' => 'POINTS_SUBTRACTED'
        ]);
    }
}
