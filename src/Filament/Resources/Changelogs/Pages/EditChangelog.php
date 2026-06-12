<?php

namespace Devdojo\Changelog\Filament\Resources\Changelogs\Pages;

use Devdojo\Changelog\Filament\Resources\Changelogs\ChangelogResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditChangelog extends EditRecord
{
    protected static string $resource = ChangelogResource::class;

    protected function getHeaderActions(): array
    {
        return [DeleteAction::make()];
    }
}
