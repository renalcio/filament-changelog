<?php

namespace Filament\Changelog\Resources\ChangelogEntryResource\Pages;

use Filament\Changelog\Resources\ChangelogEntryResource;
use Filament\Resources\Pages\CreateRecord;

class CreateChangelogEntry extends CreateRecord
{
    protected static string $resource = ChangelogEntryResource::class;
}
