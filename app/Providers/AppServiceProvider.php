<?php

namespace App\Providers;

use App\Enums\ClientPlatform;

use App\Services\SMS\TermiiService;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\ServiceProvider;
use App\Services\SMS\SMSProviderInterface;
use Opcodes\LogViewer\Facades\LogViewer;

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
            fn() => ClientPlatform::detect($this->app->request->header('x-brand-id'))
        );

        // LogViewer::auth(function ($request) {
        //     return $request->user()
        //         && in_array($request->user()->email, [
        //             'oyekunmi@gmail.com',
        //             'zubbybrightson@gmail.com'
        //         ]);
        // });
    }
}
