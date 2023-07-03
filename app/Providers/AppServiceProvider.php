<?php

namespace App\Providers;

use App\Enums\ClientPlatform;
use App\Notifications\CustomNotificationChannels\FcmNotificationChannel;
use App\Services\Firebase\FirestoreService;
use App\Services\SMS\TermiiService;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\ServiceProvider;
use App\Services\SMS\SMSProviderInterface;
use Illuminate\Support\Facades\Notification;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        Schema::defaultStringLength(191);
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {

        if ($this->app->request->header('x-request-env')) {
            putenv('GOOGLE_CREDENTIALS_ENV=' . $this->app->request->header('x-request-env'));
        }

        $this->app->singleton(
            SMSProviderInterface::class,
            fn() => new TermiiService(config('services.termii.api_key'))
        );
        $this->app->scoped(
            ClientPlatform::class,
            fn() => ClientPlatform::detect($this->app->request->header('x-brand-id')));

        $this->app->bind(
            FirestoreService::class,
            fn() => new FirestoreService()
        );

        Notification::extend('fcm', function () {
            return new FcmNotificationChannel();
        });
    }
}
