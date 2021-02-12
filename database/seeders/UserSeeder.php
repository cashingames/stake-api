<?php
namespace Database\Seeders;

use Illuminate\Database\Seeder;

use App\Models\User;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        //
        // User::factory()->times(5)->create();

        User::factory()
            ->count(10)
            ->hasWallet(1)
            ->hasProfile(1)
            ->create();
    }
}
