<?php

namespace App\Providers;

use App\Services\Payments\PaystackWithdrawalService;
use Illuminate\Support\Facades\DB;
use Opcodes\LogViewer\Facades\LogViewer;

use App\Services\SMS\TermiiService;
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
        $this->app->bind(SMSProviderInterface::class, function () {
            return new TermiiService(config('services.termii.api_key'));
        });
    }
}
