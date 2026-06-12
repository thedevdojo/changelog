<?php

namespace Devdojo\Changelog;

use Illuminate\Support\ServiceProvider;

class ChangelogServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__.'/../config/devdojo/changelog/settings.php', 'devdojo.changelog.settings');
    }

    public function boot(): void
    {
        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__.'/../config/' => config_path('/'),
            ], 'changelog:config');

            $this->publishes([
                __DIR__.'/../database/migrations' => database_path('migrations'),
            ], 'changelog:migrations');
        }

        // Feature toggle (default-on when no foundation config is present).
        if (! config('foundation.features.changelog', true)) {
            return;
        }

        $this->loadRoutesFrom(__DIR__.'/../routes/web.php');
    }
}
