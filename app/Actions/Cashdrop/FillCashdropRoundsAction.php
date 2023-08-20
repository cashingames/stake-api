<?php

namespace App\Actions\Cashdrop;

use App\Models\User;
use App\Repositories\Cashingames\CashdropRepository;
use App\Services\Firebase\FirestoreService;
use Illuminate\Support\Facades\DB;

class FillCashdropRoundsAction
{
    public function __construct(
        private readonly CashdropRepository $cashdropRepository,
        private readonly FirestoreService $firestoreService,
    ) {
    }

    public function execute(User $user, float $amount)
    {
        $activeCashdrops = $this->cashdropRepository->getActiveCashdrops();
        DB::transaction(function () use ($user, $amount, $activeCashdrops) {
            $activeCashdrops->map(function ($round) use ($user, $amount) {
                $this->cashdropRepository->updateUserCashdropRound(
                    $user->id,
                    $amount,
                    $round
                );
            });
        });

        $this->updateCashdropFirestore($activeCashdrops);
    }

    private function updateCashdropFirestore($activeCashdrops)
    {
        foreach ($activeCashdrops as $cashdropsRound) {
            $this->firestoreService->updateDocument(
                'cashdrops-updates',
                config('trivia.cashdrops_firestore_document_id'),
                [
                    'cashdrop_id' => $cashdropsRound->cashdrop_id,
                    'cashdrop_amount' => $cashdropsRound->pooled_amount
                ]
            );
        }
    }
}
