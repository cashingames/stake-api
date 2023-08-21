<?php

namespace App\Actions\ActionHelpers;

use App\Enums\FirebaseDocumentIds;
use App\Repositories\Cashingames\CashdropRepository;
use App\Services\Firebase\FirestoreService;

class CashdropFirestoreHelper
{

    public function __construct(
        private readonly FirestoreService $firestoreService,
        private readonly CashdropRepository $cashdropRepository,
    ) {
    }
   
    public function updateCashdropFirestore($env)
    {
        $cashdropUpdateArray = [];
        foreach ($this->cashdropRepository->getActiveCashdrops() as $cashdropsRound) {
            $cashdropUpdateArray[] = [
                $cashdropsRound->cashdrop->name => [
                    'cashdrop_id' =>  $cashdropsRound->cashdrop_id,
                    'cashdrop_amount' => $cashdropsRound->pooled_amount
                ]
            ];
        }
        $this->firestoreService->updateDocument(
            'cashdrops-updates',
            FirebaseDocumentIds::CASHDROP,
            $cashdropUpdateArray,
            $env
        );
    }

  
}
