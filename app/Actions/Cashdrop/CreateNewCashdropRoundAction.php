<?php

namespace App\Actions\Cashdrop;

use App\Actions\ActionHelpers\CashdropFirestoreHelper;
use App\Models\Cashdrop;
use App\Repositories\Cashingames\CashdropRepository;
use App\Services\Firebase\FirestoreService;

class CreateNewCashdropRoundAction
{
    public function __construct(
        private readonly CashdropRepository $cashdropRepository,
        private readonly FirestoreService $firestoreService,
        private readonly CashdropFirestoreHelper $cashdropFirestoreHelper
    ) {
    }

    public function execute(Cashdrop $cashdrop, $env): void
    {
        $this->cashdropRepository->createCashdropRound($cashdrop);
        $this->cashdropFirestoreHelper->updateCashdropFirestore($env);
    }
}
