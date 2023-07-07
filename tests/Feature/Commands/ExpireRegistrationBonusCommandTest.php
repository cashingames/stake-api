<?php
namespace Tests\Feature\Commands;

use App\Enums\BonusType;
use App\Models\Bonus;
use App\Models\User;
use App\Models\UserBonus;
use Database\Seeders\BonusSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ExpireRegistrationBonusCommandTest extends TestCase
{

    use RefreshDatabase;
    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(\Database\Seeders\UserSeeder::class);
    }

    public function test_command_should_expire_registration_bonus()
    {

        $this->seed(BonusSeeder::class);
        $user = User::first();

        $user->wallet->update(['bonus' => 600, 'withdrawable' => 1000]);

        UserBonus::create([
            'user_id' => $user->id,
            'bonus_id' => Bonus::where('name', BonusType::RegistrationBonus->value)->first()->id,
            'is_on' => true,
            'amount_credited' => 1500,
            'amount_remaining_after_staking' => 500,
            'total_amount_won' => 1000,
            'amount_remaining_after_withdrawal' => 1000
        ]);

        $bonus = UserBonus::first();
        $bonus->created_at = now()->subDays(10);
        $bonus->save();

        $this->artisan('bonus:registration:expire')->assertExitCode(0);

        $this->assertDatabaseHas('user_bonuses', [
            'user_id' => $user->id,
            'total_amount_won' => 1000,
            'is_on' => false,
            'amount_remaining_after_staking' => 500,
            'amount_remaining_after_withdrawal' => 1000,
        ]);

        $this->assertDatabaseHas('wallets', [
            'user_id' => $user->id,
            'bonus' => 100,
            'withdrawable' => 1000
        ]);
    }
}