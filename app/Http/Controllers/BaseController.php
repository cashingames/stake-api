<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller as Controller;
use App\Models\UserPoint;
use Illuminate\Support\Facades\Log;

class BaseController extends Controller
{
    public $user;

    public function __construct()
    {
        $this->user = auth()->user();
        if ($this->user && $this->user->trashed()) {
            auth()->logout();
        }
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

        return response()->json($response, 200);
    }

    public function sendError($errors, $message)
    {
        $response = [
            'success' => false,
            'errors'    => $errors,
            'message' => $message,
        ];

        return response()->json($response, 400);
    }
}
