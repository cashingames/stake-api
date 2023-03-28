<?php

namespace App\Providers;

use App\Enums\ClientPlatform;

use App\Services\SMS\TermiiService;
use App\Traits\Utils\EnvironmentUtils;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\ServiceProvider;
use App\Services\SMS\SMSProviderInterface;

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
        $this->app->singleton(
            SMSProviderInterface::class,
            fn() => new TermiiService(config('services.termii.api_key'))
        );
        $this->app->scoped(
            ClientPlatform::class,
            fn() => ClientPlatform::detect($this->app->request->header('x-brand-id')));
        EnvironmentUtils::setGoogleCredentials();
    }
}
