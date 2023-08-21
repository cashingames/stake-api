<?php

namespace App\Jobs;

use App\Actions\Cashdrop\FillCashdropRoundsAction;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use App\Traits\Utils\ResolveGoogleCredentials;

class FillCashdropRounds implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels, ResolveGoogleCredentials;

    /**
     * Create a new job instance.
     */
    public function __construct(
        private readonly float $amount,
        private readonly User $user,
        private $env
    ) {
        //
    }

    /**
     * Execute the job.
     */
    public function handle(FillCashdropRoundsAction $fillCashdropRoundsAction): void
    {
        $fillCashdropRoundsAction->execute($this->user, $this->amount, $this->env);
    }
}
