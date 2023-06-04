<?php

namespace App\Console\Commands;

use App\Enums\BonusType;
use App\Enums\FeatureFlags;
use App\Models\Bonus;
use App\Models\UserBonus;
use App\Services\FeatureFlag;
use Illuminate\Console\Command;

class ExpireRegistrationBonus extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:expire-registration-bonus';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Deactivates all registratio bonuses';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $freePlan = Plan::where('is_free', true)->first();

        UserPlan::where('plan_id', $freePlan->id)
            ->whereNotNull('expire_at')
            ->update(['is_active' => false]);

        if (FeatureFlag::isEnabled((FeatureFlags::REGISTRATION_BONUS))) {
            UserBonus::tobeExpired()
                ->where('bonus_id', Bonus::where('name', BonusType::RegistrationBonus->value)->first()->id)
                ->chunkById(10, function ($registrationBonuses) {
                    foreach ($registrationBonuses as $bonus) {
                        if ($bonus->user()->wallet->bonus >= $bonus->amount_remaining_after_staking) {
                            $newBonus = $bonus->user()->wallet->bonus - $bonus->amount_remaining_after_staking;
                            $bonus->user()->wallet->bonus = $newBonus;
                            $bonus->user()->wallet->save();
                        }
                        if ($bonus->amount_remaining_after_withdrawal > 0) {
                            $bonus->user()->wallet->withdrawable = $bonus->user()->wallet->withdrawable - $bonus->amount_remaining_after_withdrawal;
                            $bonus->user()->wallet->save();
                        }
                    }
                    $registrationBonuses->each->update(['is_on' => false]);
                }, $column = 'id');
        }
    }
}
