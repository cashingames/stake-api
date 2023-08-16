<?php

namespace App\Jobs;

use App\Models\User;
use App\Repositories\Cashingames\CashdropRepository;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class FillUpCashdropPools implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     */
    public function __construct(
        private readonly float $amount,
        private readonly User $user,
    ) {
        //
    }

    /**
     * Execute the job.
     */
    public function handle(CashdropRepository $cashdropRepository): void
    {
        $cashdropRepository->fillUpCashdropPools($this->user, $this->amount);
    }
}
