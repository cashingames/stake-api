<?php
namespace Database\Seeders;

use Illuminate\Database\Seeder;

use App\Models\User;
use App\Models\Wallet;

class WalletSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        //
        Wallet::factory()->times(5)->create();
    }
}
