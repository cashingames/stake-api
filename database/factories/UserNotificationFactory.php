<?php

namespace Database\Factories;

use App\Models\UserNotification;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;

class UserNotificationFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = UserNotification::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'id' => Str::uuid()->toString(),
            'type' => 'App\Notifications\ChallengeReceivedNotification',
            'notifiable_type' => 'App\Models\User',
            'notifiable_id' => 1,
            'data' => "{'title':'You have received a challenge invitation from Seyijay',
                'action_type':'CHALLENGE','action_id':1}",
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
        ];
    }
}
