<?php

namespace Iqbalatma\LaravelAudit\Providers;

use Illuminate\Support\ServiceProvider;

class LaravelAuditServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->mergeConfigFrom(
            __DIR__.'/../config/laravel_audit.php', 'laravel_audit'
        );
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        $this->loadMigrationsFrom(__DIR__ . '/../database/migrations');

        $this->publishes([
            __DIR__.'/../config/laravel_audit.php' => config_path('laravel_audit.php'),
        ]);

        $this->publishes([
            __DIR__.'/../migrations/' => database_path('migrations')
        ], 'audit-migration');
    }
}
