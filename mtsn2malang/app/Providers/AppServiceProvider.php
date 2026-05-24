<?php

namespace App\Providers;

use App\Services\AiService;
use Illuminate\Support\ServiceProvider;
use Illuminate\Pagination\Paginator;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(AiService::class, function ($app) {
            return new AiService();
        });
    }

    public function boot(): void
    {
        Paginator::useBootstrapFive();

        // Check maintenance mode from settings
        if (!$this->app->runningInConsole()) {
            $maintenanceMode = \App\Models\Setting::get('maintenance_mode', false);
            if ($maintenanceMode && !$this->app->isDownForMaintenance()) {
                // Custom maintenance check
            }
        }
    }
}
