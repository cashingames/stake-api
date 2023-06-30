<?php

namespace Tests\Unit\ActionHelpers;

use App\Actions\TriviaChallenge\MatchEndWalletAction;
use App\Actions\Wallet\CreditWalletAction;
use App\Models\ChallengeRequest;
use App\Models\User;
use App\Models\Wallet;
use App\Repositories\Cashingames\TriviaChallengeStakingRepository;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class CreditWalletActionTest extends TestCase
{
    use RefreshDatabase;



}