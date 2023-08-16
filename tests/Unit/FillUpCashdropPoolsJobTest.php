<?php

namespace Tests\Unit;

use App\Jobs\FillUpCashdropPools;
use App\Models\Cashdrop;
use App\Models\CashdropRound;
use App\Models\User;
use App\Repositories\Cashingames\CashdropRepository;
use Database\Seeders\CashDropSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Mockery\MockInterface;
use Tests\TestCase;

class FillUpCashdropPoolsJobTest extends TestCase
{
    use RefreshDatabase;
    public $user, $cashdrop, $cashdropRound ;
    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
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
        $job = new FillUpCashdropPools(200, $this->user);
        $cashdropRepository = new CashdropRepository();

        $job->handle($cashdropRepository);

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
}
