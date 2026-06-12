<?php

namespace Devdojo\Changelog\Filament\Resources\Changelogs\Pages;

use Devdojo\Changelog\Filament\Resources\Changelogs\ChangelogResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListChangelogs extends ListRecords
{
    protected static string $resource = ChangelogResource::class;

    protected function getHeaderActions(): array
    {
        return [CreateAction::make()];
    }
}
