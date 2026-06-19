<?php

namespace Devdojo\Changelog\Filament\Resources\Changelogs\Pages;

use Devdojo\Changelog\Filament\Resources\Changelogs\ChangelogResource;
use Filament\Resources\Pages\CreateRecord;

class CreateChangelog extends CreateRecord
{
    protected static string $resource = ChangelogResource::class;
}
