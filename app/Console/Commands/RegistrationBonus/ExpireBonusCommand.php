<?php

namespace App\Console\Commands\RegistrationBonus;

use App\Enums\BonusType;
use App\Enums\FeatureFlags;
use App\Models\Bonus;
use App\Models\UserBonus;
use App\Services\FeatureFlag;
use Illuminate\Console\Command;

class ExpireBonusCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'registration-bonus:expire';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Deactivates all registration bonuses';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $regBonus = Bonus::where('name', BonusType::RegistrationBonus->value)->first();
        if (FeatureFlag::isEnabled((FeatureFlags::REGISTRATION_BONUS))) {
            UserBonus::tobeExpired()
                ->where('bonus_id', $regBonus->id)
                ->chunkById(10, function ($registrationBonuses) {
                    foreach ($registrationBonuses as $bonus) {
                        $wallet = $bonus->user->wallet;
                        if (
                            $bonus->amount_remaining_after_staking > 0 and
                            $wallet->bonus >= $bonus->amount_remaining_after_staking
                        ) {
                            $newBonus = $wallet->bonus - $bonus->amount_remaining_after_staking;
                            $wallet->bonus = $newBonus;
                            $wallet->save();
                        }
                    }
                    $registrationBonuses->each->update(['is_on' => false]);
                }, $column = 'id');
        }
    }
}