<?php

namespace App\Providers;

use App\Contracts\CaptchaSolverContract;
use App\Contracts\DOMCrawlerContract;
use App\Contracts\MailParserContract;
use App\Contracts\ModulesContract;
use App\Modules\BlackScaleModule;
use App\Services\DOMCrawlerService;
use App\Services\GuerrillaMailService;
use App\Services\NextCaptchaService;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->bind(MailParserContract::class, GuerrillaMailService::class);
        $this->app->bind(DOMCrawlerContract::class, DOMCrawlerService::class);
        $this->app->bind(CaptchaSolverContract::class, NextCaptchaService::class);
        $this->app->bind(ModulesContract::class, BlackScaleModule::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}
