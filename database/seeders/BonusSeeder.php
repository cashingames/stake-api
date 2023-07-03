<?php

namespace Database\Seeders;

use App\Enums\BonusDurations;
use App\Enums\BonusTriggers;
use App\Enums\BonusType;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class BonusSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('bonuses')->insert(
            [ 
                'name' => BonusType::RegistrationBonus->value,
                'trigger' => BonusTriggers::FirstTimeFunding->value,
                'duration_count' => 7,
                'duration_measurement' => BonusDurations::Days->value,
                'created_at' => now(),
                'updated_at' => now()
            ],
            
        );

        DB::table('bonuses')->insert(
            [ 
                'name' => BonusType::StakingLossCashback->value,
                'trigger' => BonusTriggers::LossOnStaking->value,
                'duration_count' => 3,
                'duration_measurement' => BonusDurations::Days->value,
                'created_at' => now(),
                'updated_at' => now()
            ],
            
        );

    }
}
