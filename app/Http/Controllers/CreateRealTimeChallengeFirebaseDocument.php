<?php

namespace App\Http\Controllers;

use App\Models\RealtimeChallengeRequest;
use App\Services\Firebase\RealTimeDatabaseService as FirebaseService;
use Google\Service\GameServices\Realm;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class CreateRealTimeChallengeFirebaseDocument extends BaseController
{

    private $database;

    public function __invoke(Request $request)
    {

        $request->validate([
            'category' => ['required', 'numeric', 'exists:categories,id'],
            'amount' => ['required', 'numeric', 'max:' . $this->user->wallet->non_withdrawable_balance],
        ]);

        $firebase = new FirebaseService();

        $this->database = $firebase::connect();

        $documentId = Str::random(10);

        $challengeRef = $this->database->collection('challenge-sessions')->newDocument();
        $challengeRef->set([
            'username' => $this->user->username,
            'document_id' => $documentId
        ]);

        DB::transaction(function () use ($request, $documentId) {
            $this->user->wallet->non_withdrawable_balance -= $request->amount;

            RealtimeChallengeRequest::create([
                'document_id' => $documentId,
                'user_id' => $this->user->id,
                'amount' => $request->amount,
                'category_id' => $request->category,
            ]);

            $this->user->wallet->save();
        });

        return $this->sendResponse($documentId, "Firebase document created.");
    }
}
