<?php

namespace App\Jobs;

use App\Enums\WalletBalanceType;
use App\Enums\WalletTransactionAction;
use App\Enums\WalletTransactionType;
use App\Models\ChallengeRequest;
use App\Repositories\Cashingames\TriviaChallengeStakingRepository;
use App\Repositories\Cashingames\WalletRepository;
use App\Repositories\Cashingames\WalletTransactionDto;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class VerifyChallengeWinner implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     */
    public function __construct(
        private readonly ChallengeRequest $request,
        private readonly ChallengeRequest $matchedRequest,
        private readonly WalletRepository $walletRepository,
        private readonly TriviaChallengeStakingRepository $triviaChallengeStakingRepository
    ) {
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {   
        if (
            $this->request->ended_at->diffInMinutes(now()) >= 1 &&
            $this->matchedRequest->ended_at == null
        ) {
            $this->creditWinner($this->request);
            $this->triviaChallengeStakingRepository->updateSystemCompletedRequest($this->request);
        }
       
        return;
    }

    private function creditWinner(ChallengeRequest $winner): void
    {
        $amountWon = $winner->amount * 2;
        $this->walletRepository->addTransaction(
            new WalletTransactionDto(
                $winner->user_id,
                $amountWon,
                'Challenge game Winnings credited',
                WalletBalanceType::WinningsBalance,
                WalletTransactionType::Credit,
                WalletTransactionAction::WinningsCredited,
            )
        );

        $winner->amount_won = $amountWon;
        $winner->save(); 
    }
}
