<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\UserNotification;

class UserNotificationSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        UserNotification::factory()
        ->count(5)
        ->create();
    }
}
