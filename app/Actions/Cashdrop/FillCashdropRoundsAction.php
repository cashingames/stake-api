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
        private readonly DropCashdropAction $dropCashdropAction,
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
                if ($round->pooled_amount >= $round->cashdrop->lower_pool_limit) {
                    $this->dropCashdropAction->execute($round);
                }
            });
        });

        $this->updateCashdropFirestore($activeCashdrops->refresh());
    }

    private function updateCashdropFirestore($activeCashdrops)
    {
        $cashdropUpdateArray = [];
        foreach ($activeCashdrops as $cashdropsRound) {
            $cashdropUpdateArray[] = [
                $cashdropsRound->cashdrop->name => [
                    'cashdrop_id' =>  $cashdropsRound->cashdrop_id,
                    'cashdrop_amount' => $cashdropsRound->pooled_amount
                ]
            ];
        }
        $this->firestoreService->updateDocument(
            'cashdrops-updates',
            config('trivia.cashdrops_firestore_document_id'),
            $cashdropUpdateArray
        );
    }
}
