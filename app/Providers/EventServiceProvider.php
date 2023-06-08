<?php

namespace App\Providers;

use Illuminate\Auth\Events\Registered;
use Illuminate\Auth\Listeners\SendEmailVerificationNotification;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Event;
use App\Events\AchievementBadgeEvent;
use App\Events\CreditRegistrationBonusWinnings;
use App\Listeners\AchievementBadgeEventListener;
use App\Listeners\RegistrationBonusWinningsCredited;

class EventServiceProvider extends ServiceProvider
{
    /**
     * The event listener mappings for the application.
     *
     * @var array
     */
    protected $listen = [
        Registered::class => [
            SendEmailVerificationNotification::class,
        ],
        CreditRegistrationBonusWinnings::class => [
            RegistrationBonusWinningsCredited::class,
        ],
    ];

    /**
     * Register any events for your application.
     *
     * @return void
     */
    public function boot()
    {
        //
        Event::listen(
            AchievementBadgeEvent::class,
            [AchievementBadgeEventListener::class, 'handle']
        );
    }
}
