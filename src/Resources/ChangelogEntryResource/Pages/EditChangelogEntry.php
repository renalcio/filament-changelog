<?php

namespace Filament\Changelog\Resources\ChangelogEntryResource\Pages;

use Filament\Actions\DeleteAction;
use Filament\Actions\ViewAction;
use Filament\Changelog\Resources\ChangelogEntryResource;
use Filament\Resources\Pages\EditRecord;

class EditChangelogEntry extends EditRecord
{
    protected static string $resource = ChangelogEntryResource::class;

    protected function getHeaderActions(): array
    {
        return [
            ViewAction::make(),
            DeleteAction::make(),
        ];
    }
}
