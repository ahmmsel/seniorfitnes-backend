<?php

namespace App\Providers;

use App\Services\DailyTargetService;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Gate;
use App\Models\Plan;
use App\Policies\PlanPolicy;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void {}

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Register Plan policy
        Gate::policy(Plan::class, PlanPolicy::class);
    }
}
