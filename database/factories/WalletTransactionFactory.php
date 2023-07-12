<?php

namespace Database\Factories;

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
            "transaction_type" => $this->faker->randomElement(['CREDIT', 'DEBIT']),
            "reference" => Str::random(10),
            "amount" => $this->faker->randomElement([150.00, 800.00, 450.00, 2000.00, 2500.00]),
            "description" => $this->faker->randomElement(['Wallet Top-up', 'Successful Withdrawal', 'Failed Withdrawal Reversed']),

        ];
    }
}
