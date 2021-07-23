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
            "transaction_type" => $this->faker->randomElement(['Fund Recieved', 'Fund Withdrawal']),
            "wallet_kind" => $this->faker->randomElement(['CREDITS', 'WINNINGS']),
            "amount" => $this->faker->randomElement([150.00, 800.00,450.00,2000.00,2500.00]),
            "description" => $this->faker->sentence(),
            "reference" => Str::random(10)

        ];
    }
}
