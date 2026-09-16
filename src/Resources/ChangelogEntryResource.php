<?php

namespace Filament\Changelog\Resources;

use BackedEnum;
use Filament\Changelog\ChangelogPlugin;
use Filament\Changelog\Models\ChangelogEntry;
use Filament\Changelog\Resources\ChangelogEntryResource\Pages\CreateChangelogEntry;
use Filament\Changelog\Resources\ChangelogEntryResource\Pages\EditChangelogEntry;
use Filament\Changelog\Resources\ChangelogEntryResource\Pages\ListChangelogEntries;
use Filament\Changelog\Resources\ChangelogEntryResource\Schemas\ChangelogEntryForm;
use Filament\Changelog\Resources\ChangelogEntryResource\Tables\ChangelogEntriesTable;
use Filament\Panel;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;
use Throwable;
use UnitEnum;

/**
 * @extends \Filament\Resources\Resource<ChangelogEntry>
 */
class ChangelogEntryResource extends Resource
{
    protected static ?string $model = ChangelogEntry::class;

    protected static ?string $recordTitleAttribute = 'description';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedPencilSquare;

    protected static function plugin(): ChangelogPlugin
    {
        try {
            return ChangelogPlugin::get();
        } catch (Throwable) {
            return ChangelogPlugin::make();
        }
    }

    public static function getCluster(): ?string
    {
        return static::plugin()->getCluster() ?? config('changelog.cluster') ?? parent::getCluster();
    }

    public static function getModel(): string
    {
        return config('changelog.model') ?? parent::getModel();
    }

    public static function getModelLabel(): string
    {
        return static::plugin()->getModelLabel()
            ?? __('changelog::changelog.model_label');
    }

    public static function getPluralModelLabel(): string
    {
        return static::plugin()->getPluralModelLabel()
            ?? __('changelog::changelog.plural_model_label');
    }

    public static function getNavigationLabel(): string
    {
        return __('changelog::changelog.navigation.resource_label');
    }

    public static function shouldRegisterNavigation(): bool
    {
        // Only the reader page appears in the sidebar. This management area is
        // reached via the "Manage" button on the reader page.
        return false;
    }

    /**
     * @return bool|null null → let the parent (policy / Gate) decide
     */
    protected static function authorizeManage(): ?bool
    {
        return static::plugin()->isManageAuthorized();
    }

    public static function canViewAny(): bool
    {
        return static::authorizeManage() ?? parent::canViewAny();
    }

    public static function canCreate(): bool
    {
        return static::authorizeManage() ?? parent::canCreate();
    }

    public static function canView(Model $record): bool
    {
        return static::authorizeManage() ?? parent::canView($record);
    }

    public static function canEdit(Model $record): bool
    {
        return static::authorizeManage() ?? parent::canEdit($record);
    }

    public static function canDelete(Model $record): bool
    {
        return static::authorizeManage() ?? parent::canDelete($record);
    }

    public static function canDeleteAny(): bool
    {
        return static::authorizeManage() ?? parent::canDeleteAny();
    }

    public static function form(Schema $schema): Schema
    {
        return ChangelogEntryForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return ChangelogEntriesTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListChangelogEntries::route('/'),
            'create' => CreateChangelogEntry::route('/create'),
            'edit' => EditChangelogEntry::route('/{record}/edit'),
        ];
    }

    public static function getNavigationGroup(): string|UnitEnum|null
    {
        return static::plugin()->getNavigationGroup()
            ?? config('changelog.navigation.group', 'Documentation');
    }

    public static function getNavigationSort(): ?int
    {
        return config('changelog.navigation.resource.sort', 91);
    }

    public static function getSlug(?Panel $panel = null): string
    {
        return config('changelog.navigation.resource.slug', 'changelog/manage');
    }
}
