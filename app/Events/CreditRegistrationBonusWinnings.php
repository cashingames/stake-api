<?php

namespace App\Events;

use App\Models\User;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class CreditRegistrationBonusWinnings
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $user , $amount , $bonus;

    /**
     * Create a new event instance.
     */
    public function __construct(User $user, $amount)
    {   
        $this->user = $user;
        $this->amount = $amount;
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return array<int, \Illuminate\Broadcasting\Channel>
     */
    public function broadcastOn(): array
    {
        return [
            new PrivateChannel('registrationBonusWinnings-credit'),
        ];
    }
}
