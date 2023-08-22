<?php

namespace Tests\Unit;

use App\Actions\ActionHelpers\CashdropFirestoreHelper;
use App\Actions\Cashdrop\CreateNewCashdropRoundAction;
use App\Actions\Cashdrop\DropCashdropAction;
use App\Actions\Cashdrop\FillCashdropRoundsAction;
use App\Jobs\FillCashdropRounds;
use App\Jobs\SendCashdropDroppedNotification;
use App\Models\Cashdrop;
use App\Models\CashdropRound;
use App\Models\User;
use App\Models\Wallet;
use App\Repositories\Cashingames\CashdropRepository;
use App\Repositories\Cashingames\WalletRepository;
use Database\Seeders\CashDropSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;

class FillCashdropRoundsJobTest extends TestCase
{
    use RefreshDatabase;
    public $user, $cashdrop, $cashdropRound;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->has(Wallet::factory()->count(1))->create();
        $this->seed(CashDropSeeder::class);
        $this->cashdrop = Cashdrop::first();
        $this->cashdropRound = CashdropRound::create([
            'cashdrop_id' => $this->cashdrop->id,
            'pooled_amount' => 100,
            'dropped_at' => null,
            'percentage_stake' => $this->cashdrop->percentage_stake,
            'created_at' => now(),
            'updated_at' => now()
        ]);
        DB::table('cashdrop_users')->insert([
            'user_id' => $this->user->id,
            'cashdrop_round_id' => $this->cashdropRound->id,
            'amount' => 10,
            'winner' => false,
            'created_at' => now(),
            'updated_at' => now()
        ]);
    }

    public function test_fill_up_cashdrop_pool(): void
    {
        $mockedFirestoreHelper = $this->mockFirestoreHelper();
        $mockedFirestoreHelper
            ->expects($this->once())
            ->method('updateCashdropFirestore');

        $job = new FillCashdropRounds(200, $this->user,'testing');
        $cashdropRepository = new CashdropRepository;
        $cashdropAction = new FillCashdropRoundsAction(
            $cashdropRepository,
            $this->mockdropCashdropAction(),
            $mockedFirestoreHelper,
           
          );


        $job->handle($cashdropAction);

        $this->assertDatabaseHas('cashdrop_users', [
            'user_id' => $this->user->id,
            'cashdrop_round_id' => $this->cashdropRound->id,
            'amount' => ($this->cashdropRound->percentage_stake * 200) + 10,
        ]);

        $this->assertDatabaseHas('cashdrop_rounds', [
            'cashdrop_id' => $this->cashdrop->id,
            'pooled_amount' => ($this->cashdrop->percentage_stake * 200) + 100,
        ]);
    }

    public function test_drop_cashdrop_action_is_triggered(): void
    {   
        Queue::fake();
        $this->cashdropRound->update(['pooled_amount' => $this->cashdrop->lower_pool_limit]);

        $job = new FillCashdropRounds(200, $this->user,'testing');
        $cashdropRepository = new CashdropRepository;
        $walletRepository = new WalletRepository;
        $createCashdropAction = $this->mockCreateNewCashdropRoundAction();
        $dropAction = new DropCashdropAction(
            $cashdropRepository,
            $walletRepository,
            $createCashdropAction
        );

        $cashdropAction = new FillCashdropRoundsAction(
            $cashdropRepository,
            $dropAction ,
            $this->mockFirestoreHelper()
        );

        $job->handle($cashdropAction);

        Queue::assertPushed(SendCashdropDroppedNotification::class);
        
        $this->assertDatabaseHas('wallets', [
            'withdrawable' => $this->cashdropRound->pooled_amount
        ]);
    }
    private function mockdropCashdropAction()
    {
        return $this->createMock(DropCashdropAction::class);
    }
    private function mockCreateNewCashdropRoundAction()
    {
        return $this->createMock(CreateNewCashdropRoundAction::class);
    }
    private function mockFirestoreHelper()
    {
        return $this->createMock(CashdropFirestoreHelper::class);
    }
}
