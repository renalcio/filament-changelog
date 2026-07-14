<?php

namespace Filament\Changelog\Pages;

use BackedEnum;
use BezhanSalleh\FilamentShield\Facades\FilamentShield;
use Filament\Actions\Action;
use Filament\Changelog\ChangelogPlugin;
use Filament\Changelog\Models\ChangelogEntry;
use Filament\Changelog\Resources\ChangelogEntryResource;
use Filament\Changelog\Support\ChangelogSource;
use Filament\Changelog\Support\ChangelogWriter;
use Filament\Changelog\Support\VersionGrouper;
use Filament\Facades\Filament;
use Filament\Infolists\Components\TextEntry;
use Filament\Pages\Page;
use Filament\Panel;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Text;
use Filament\Schemas\Schema;
use Filament\Support\Enums\FontWeight;
use Filament\Support\Icons\Heroicon;
use Illuminate\Support\Collection;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Throwable;
use UnitEnum;

class ChangelogPage extends Page
{
    protected static function plugin(): ChangelogPlugin
    {
        try {
            return ChangelogPlugin::get();
        } catch (Throwable) {
            return ChangelogPlugin::make();
        }
    }

    public static function getNavigationLabel(): string
    {
        return static::plugin()->getNavigationLabel()
            ?? __('changelog::changelog.navigation.page_label');
    }

    public function getTitle(): string
    {
        return static::getNavigationLabel();
    }

    public static function getNavigationIcon(): string|BackedEnum|null
    {
        return static::plugin()->getNavigationIcon() ?? Heroicon::OutlinedDocumentText;
    }

    public static function getActiveNavigationIcon(): string|BackedEnum|null
    {
        return static::plugin()->getActiveNavigationIcon() ?? static::getNavigationIcon();
    }

    public static function getNavigationGroup(): string|UnitEnum|null
    {
        return static::plugin()->getNavigationGroup()
            ?? config('changelog.navigation.group', 'Documentation');
    }

    public static function getNavigationSort(): ?int
    {
        return static::plugin()->getNavigationSort()
            ?? config('changelog.navigation.page.sort', 90);
    }

    public static function shouldRegisterNavigation(): bool
    {
        return static::plugin()->shouldRegisterNavigation() && static::canAccess();
    }

    public static function canAccess(): bool
    {
        $permission = static::shieldPagePermission();
        $user = Filament::auth()?->user();

        // Honour the Shield-generated page permission when present; otherwise
        // the reader page is public (Shield not installed / page not generated).
        return ($permission && $user)
            ? $user->can($permission)
            : true;
    }

    /**
     * The Shield permission for this page, resolved at runtime so there is no
     * hard dependency on Filament Shield. Returns null when Shield is absent or
     * has not generated a permission for this page.
     */
    protected static function shieldPagePermission(): ?string
    {
        if (! class_exists(FilamentShield::class)) {
            return null;
        }

        $page = FilamentShield::getPages()[static::class] ?? null;

        return $page ? array_key_first($page['permissions']) : null;
    }

    public static function getSlug(?Panel $panel = null): string
    {
        return config('changelog.navigation.page.slug', 'changelog');
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('manage')
                ->label(__('changelog::changelog.actions.manage'))
                ->icon(Heroicon::OutlinedPencilSquare)
                ->color('gray')
                ->visible(fn (): bool => ! ChangelogSource::isFile() && ChangelogEntryResource::canViewAny())
                ->url(fn (): string => ChangelogEntryResource::getUrl()),

            Action::make('export')
                ->label(__('changelog::changelog.actions.export_md'))
                ->icon(Heroicon::OutlinedArrowDownTray)
                ->color('gray')
                ->visible(fn (): bool => ! ChangelogSource::isFile())
                ->action(function (): StreamedResponse {
                    $markdown = (new ChangelogWriter)->render(
                        ChangelogEntry::query()->orderBy('sort')->get()
                            ->map->toChangelogArray()
                    );

                    return response()->streamDownload(
                        fn () => print ($markdown),
                        'CHANGELOG.md',
                        ['Content-Type' => 'text/markdown'],
                    );
                }),
        ];
    }

    public function content(Schema $schema): Schema
    {
        $entries = ChangelogSource::entries();

        if ($entries->isEmpty()) {
            return $schema->components([
                Section::make()
                    ->schema([
                        Text::make(__('changelog::changelog.reader.empty'))
                            ->color('gray'),
                    ]),
            ]);
        }

        $grouper = new VersionGrouper;

        $sections = $grouper->group($entries)
            ->map(fn (Collection $group, string $version): Section => $this->versionSection($version, $group, $grouper))
            ->values()
            ->all();

        return $schema->components($sections);
    }

    protected function versionSection(string $version, Collection $group, VersionGrouper $grouper): Section
    {
        $body = $grouper->byType($group)
            ->map(function (array $bucket): string {
                $lines = $bucket['entries']
                    ->map(fn (ChangelogEntry $e): string => '- '.trim($e->description))
                    ->implode("\n");

                return '**'.$bucket['type']->getLabel().'**'."\n\n".$lines;
            })
            ->implode("\n\n");

        return Section::make()
            ->heading($this->versionHeading($version))
            ->afterHeader($this->dateBadge($group))
            ->schema([
                TextEntry::make('body_'.md5($version))
                    ->hiddenLabel()
                    ->state($body)
                    ->markdown(),
            ]);
    }

    protected function versionHeading(string $version): string
    {
        if (strtolower($version) === 'unreleased') {
            return __('changelog::changelog.reader.unreleased');
        }

        return preg_match('/^\d/', $version) ? 'v'.$version : $version;
    }

    protected function dateBadge(Collection $group): ?Text
    {
        $date = optional($group->first())->released_at;

        if (! $date) {
            return null;
        }

        return Text::make($date->format(config('changelog.date_format', 'd M Y')))
            ->badge()
            ->color('warning')
            ->weight(FontWeight::Medium);
    }
}
