<?php

namespace App\Actions\Cashdrop;

use App\Models\User;
use App\Repositories\Cashingames\CashdropRepository;
use Illuminate\Support\Facades\DB;

class FillCashdropRoundsAction
{
    public function __construct(
        private readonly CashdropRepository $cashdropRepository,
        private readonly DropCashdropAction $dropCashdropAction,
    ) {
    }

    public function execute(User $user, float $amount, $env)
    {
        $activeCashdrops = $this->cashdropRepository->getActiveCashdrops();
        DB::transaction(function () use ($user, $amount, $activeCashdrops, $env) {
            $activeCashdrops->map(function ($round) use ($user, $amount, $env) {
                $this->cashdropRepository->updateUserCashdropRound(
                    $user->id,
                    $amount,
                    $round
                );
                if ($round->pooled_amount >= $round->cashdrop->lower_pool_limit) {
                    $this->dropCashdropAction->execute($round, $env);
                }
            });
        });

    }

  
}
