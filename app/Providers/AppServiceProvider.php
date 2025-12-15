<?php

namespace App\Providers;

use App\Services\DailyTargetService;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Gate;
use App\Models\Plan;
use App\Policies\PlanPolicy;
use Filament\Forms\Components\SpatieMediaLibraryFileUpload;

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

        // Configure Filament file uploads to convert images to WebP globally
        SpatieMediaLibraryFileUpload::configureUsing(function (SpatieMediaLibraryFileUpload $component) {
            $component
                ->conversion('webp')
                ->acceptedFileTypes(['image/jpeg', 'image/png', 'image/jpg', 'image/gif', 'image/webp'])
                ->maxSize(10240); // 10MB
        });
    }
}
