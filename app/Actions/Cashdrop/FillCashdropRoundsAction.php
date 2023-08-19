<?php

namespace App\Actions\Cashdrop;

use App\Models\User;
use App\Repositories\Cashingames\CashdropRepository;
use Illuminate\Support\Facades\DB;

class FillCashdropRoundsAction
{
    public function __construct(
        private readonly CashdropRepository $cashdropRepository,
    ) {
    }

    public function execute(User $user, float $amount)
    {

        DB::transaction(function () use ($user, $amount) {
            $this->cashdropRepository->getActiveCashdrops()->map(function ($round) use ($user, $amount) { 
                $this->cashdropRepository->updateUserCashdropRound(
                    $user->id,
                    $amount,
                    $round
                );
            });
        });
    }
}
