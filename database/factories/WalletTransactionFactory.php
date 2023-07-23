<?php

namespace Database\Factories;

use App\Enums\WalletBalanceType;
use App\Enums\WalletTransactionAction;
use App\Enums\WalletTransactionType;
use App\Models\Model;
use App\Models\WalletTransaction;
use App\Models\Wallet;
use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\Factories\Factory;

class WalletTransactionFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = WalletTransaction::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            //
            'wallet_id' => Wallet::factory(),
            "transaction_type" => $this->faker->randomElement(WalletTransactionType::cases()),
            "transaction_action" => $this->faker->randomElement(WalletTransactionAction::cases()),
            "balance_type" => $this->faker->randomElement(WalletBalanceType::cases()),
            "reference" => Str::random(10),
            "amount" => $this->faker->randomFloat(2, 0, 100000),
            "description" => $this->faker->sentence,
        ];
    }
}
