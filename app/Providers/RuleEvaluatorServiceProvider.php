<?php

namespace App\Providers;

use Illuminate\Contracts\Foundation\Application;
use Illuminate\Support\ServiceProvider;
use App\Helpers\RuleEvaluator;

class RuleEvaluatorServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        $this->app->singleton(RuleEvaluator::class, function (Application $app) {
            return new RuleEvaluator();
        });
    }
}
