<?php

namespace App\Http\Controllers\PlayGame;

use App\Http\Controllers\Controller;
use App\Http\ResponseHelpers\ResponseHelper;
use App\Models\ChallengeRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use App\Services\Firebase\FirestoreService;

class StartChallengeRequestController extends Controller
{

    public function __invoke(Request $request, FirestoreService $firestore)
    {   
        $user = auth()->user();

        $request->validate([
            'category' => ['required', 'numeric', 'exists:categories,id'],
            'amount' => ['required', 'numeric', 'max:' . $user->wallet->non_withdrawable_balance],
        ]);

        $challenge_request_id = Str::random(20);

        DB::transaction(function () use ($request, $challenge_request_id, $user ) {
          
            $user->wallet->non_withdrawable_balance -= $request->amount;

            ChallengeRequest::create([
                'challenge_request_id' => $challenge_request_id ,
                'user_id' => $user->id,
                'username'=> $user->username,
                'amount' => $request->amount,
                'category_id' => $request->category,
            ]);

            $user->wallet->save();
        });

        $firestoreDatabase = $firestore::connect();
        
        $challengeSession = $firestoreDatabase->collection('challenge-sessions')->document($challenge_request_id);
        $challengeSession->set([
            'username' => $user->username,
            'status' => 'MATCHING',
        ]);
        
        return ResponseHelper::success($challenge_request_id);
    }
}