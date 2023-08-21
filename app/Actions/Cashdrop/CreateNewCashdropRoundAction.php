<?php

namespace App\Actions\Cashdrop;

use App\Models\Cashdrop;
use App\Repositories\Cashingames\CashdropRepository;
use App\Services\Firebase\FirestoreService;

class CreateNewCashdropRoundAction
{
    public function __construct(
        private readonly CashdropRepository $cashdropRepository,
        private readonly FirestoreService $firestoreService,
    ) {
    }

    public function execute(Cashdrop $cashdrop): void
    {
        $newCashdropRound = $this->cashdropRepository->createCashdropRound($cashdrop);
        $this->firestoreService->updateDocument(
            'cashdrops-updates',
            config('trivia.cashdrops_firestore_document_id'),
            [
                'cashdrop_id' => $cashdrop->id,
                'cashdrop_amount' => $newCashdropRound->pooled_amount
            ]
        );
    }
}
