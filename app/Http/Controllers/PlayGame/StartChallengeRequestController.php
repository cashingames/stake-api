<?php

namespace App\Http\Controllers\PlayGame;

use App\Http\Controllers\Controller;
use App\Http\ResponseHelpers\ResponseHelper;
use App\Models\ChallengeRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class StartChallengeRequestController extends Controller
{

    public function __invoke(Request $request): \Illuminate\Http\JsonResponse
    {
        $user = $request->user();

        $data = $request->validate([
            'category' => ['required', 'numeric', 'exists:categories,id'],
            'amount' => ['required', 'numeric', 'max:' . $user->wallet->non_withdrawable_balance],
        ]);


        // Deduct amount from user's wallet
        $user->wallet()->update([
            'non_withdrawable_balance' => DB::raw('non_withdrawable_balance - ' . $request->amount)
        ]);

        $requestId = Str::random(20);

        ChallengeRequest::create([
            'challenge_request_id' => $requestId,
            'user_id' => $user->id,
            'username' => $user->username,
            'amount' => $data['amount'],
            'category_id' => $data['category'],
        ]);

        return ResponseHelper::success($requestId);
    }
}
