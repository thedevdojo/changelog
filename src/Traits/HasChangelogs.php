<?php

namespace Devdojo\Changelog\Traits;

use Devdojo\Changelog\Models\Changelog;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

trait HasChangelogs
{
    public function changelogs(): BelongsToMany
    {
        return $this->belongsToMany(Changelog::class);
    }

    public function hasChangelogNotifications()
    {
        // Get the latest Changelog
        $latest_changelog = Changelog::orderByDesc('created_at')->first();

        if (! $latest_changelog) {
            return false;
        }

        return ! $this->changelogs->contains($latest_changelog->id);
    }
}
