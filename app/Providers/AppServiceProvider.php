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
        // DB::listen(function ($query) {
        //     $query = $query->sql;
        //     // $query->time;
        // });

        // LogViewer::auth(function ($request) {
        //     return $request->user()
        //     && in_array($request->user()->email, [
        //         'john@example.com',
        //     ]);
        // });

        $this->app->bind(SMSProviderInterface::class, function($app){
            $api_key = config('services.termii.api_key');
            return new TermiiService($api_key);
        });

        $this->app->bind(PaystackWithdrawalService::class, function($app){
            return new PaystackWithdrawalService(config('trivia.payment_key'));
        });
    }
}
