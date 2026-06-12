<?php

namespace Devdojo\Changelog\Filament;

use Devdojo\Changelog\Filament\Resources\Changelogs\ChangelogResource;
use Filament\Contracts\Plugin;
use Filament\Panel;

/**
 * Registers the changelog admin resource into a host Filament panel:
 *
 *     ->plugin(\Devdojo\Changelog\Filament\ChangelogPlugin::make())
 */
class ChangelogPlugin implements Plugin
{
    public static function make(): static
    {
        return app(static::class);
    }

    public function getId(): string
    {
        return 'devdojo-changelog';
    }

    public function register(Panel $panel): void
    {
        if (! config('foundation.features.changelog', true)) {
            return;
        }

        $panel->resources([
            ChangelogResource::class,
        ]);
    }

    public function boot(Panel $panel): void
    {
        //
    }
}
