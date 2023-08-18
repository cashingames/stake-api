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
                $cashdropRoundData = [
                    'pooled_amount' => $round->pooled_amount + $amount * $round->percentage_stake,
                ];
                $this->cashdropRepository->updateCashdropRound($round->id, $cashdropRoundData);

                $cashdropUsersconditions = [
                    'cashdrop_round_id' => $round->id,
                    'user_id' => $user->id
                ];
                $cashdropUsersData = [
                    'amount' => DB::raw('amount + ' . $amount * $round->percentage_stake),
                    'winner' => false
                ];
                $this->cashdropRepository->updateCashdropUser($cashdropUsersconditions, $cashdropUsersData);
            });
        });
    }
}
