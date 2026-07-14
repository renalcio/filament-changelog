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
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Infolists\Components\TextEntry;
use Filament\Pages\Page;
use Filament\Panel;
use Filament\Schemas\Components\Component;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Text;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;
use Filament\Support\Enums\FontWeight;
use Filament\Support\Icons\Heroicon;
use Illuminate\Support\Collection;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Throwable;
use UnitEnum;

class ChangelogPage extends Page
{
    /**
     * Number of version cards revealed per infinite-scroll step.
     */
    protected const PER_PAGE = 8;

    /**
     * Toolbar state (search term + selected version), bound via `statePath`.
     *
     * @var array<string, mixed>
     */
    public ?array $data = [];

    /**
     * How many version cards are currently visible (grows on scroll).
     */
    public int $limit = self::PER_PAGE;

    public function mount(): void
    {
        $this->data = [
            'search' => '',
            'version' => null,
        ];

        $this->limit = $this->perPage();
    }

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

    /**
     * Reveal the next batch of version cards (called by the scroll sentinel).
     */
    public function loadMore(): void
    {
        $this->limit += $this->perPage();
    }

    /**
     * Reset paging whenever a filter changes so results start from the top.
     */
    public function resetLimit(): void
    {
        $this->limit = $this->perPage();
    }

    protected function perPage(): int
    {
        return max(1, (int) config('changelog.reader.per_page', static::PER_PAGE));
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
                Section::make()->schema([
                    Text::make(__('changelog::changelog.reader.empty'))->color('gray'),
                ]),
            ]);
        }

        $components = [];

        if ($toolbar = $this->toolbar($entries)) {
            $components[] = $toolbar;
        }

        $components[] = Grid::make(1)
            ->key('changelog-list')
            ->schema(fn (Get $get): array => $this->list(
                (string) ($get('search') ?? ''),
                $get('version'),
            ));

        return $schema
            ->statePath('data')
            ->components($components);
    }

    /**
     * Search box + version filter, both live so the list reacts instantly.
     * Either can be turned off; returns null when both are disabled.
     */
    protected function toolbar(Collection $entries): ?Grid
    {
        $searchable = (bool) config('changelog.reader.searchable', true);
        $filterable = (bool) config('changelog.reader.filterable_by_version', true);

        if (! $searchable && ! $filterable) {
            return null;
        }

        $both = $searchable && $filterable;
        $fields = [];

        if ($searchable) {
            $fields[] = TextInput::make('search')
                ->hiddenLabel()
                ->placeholder(__('changelog::changelog.reader.search_placeholder'))
                ->prefixIcon(Heroicon::MagnifyingGlass)
                ->live(debounce: 350)
                ->afterStateUpdated(fn () => $this->resetLimit())
                ->columnSpan($both ? ['default' => 1, 'sm' => 2] : 1);
        }

        if ($filterable) {
            $fields[] = Select::make('version')
                ->hiddenLabel()
                ->placeholder(__('changelog::changelog.reader.all_versions'))
                ->options($this->versionOptions($entries))
                ->native(false)
                ->searchable()
                ->live()
                ->afterStateUpdated(fn () => $this->resetLimit())
                ->columnSpan(1);
        }

        return Grid::make(['default' => 1, 'sm' => $both ? 3 : 1])
            ->schema($fields);
    }

    /**
     * @return array<string, string>
     */
    protected function versionOptions(Collection $entries): array
    {
        return (new VersionGrouper)->group($entries)
            ->keys()
            ->mapWithKeys(fn (string $version): array => [$version => $this->versionHeading($version)])
            ->all();
    }

    /**
     * Build the (filtered, paginated) list of version cards, appending an
     * intersection sentinel when more cards remain to be revealed.
     *
     * @return array<int, Component>
     */
    protected function list(string $search, ?string $version): array
    {
        $grouper = new VersionGrouper;

        $entries = $this->filter(ChangelogSource::entries(), $search);

        $groups = $grouper->group($entries);

        if ($version !== null && $version !== '') {
            $groups = $groups->only([$version]);
        }

        if ($groups->isEmpty()) {
            return [
                Section::make()->schema([
                    Text::make(__('changelog::changelog.reader.no_results'))->color('gray'),
                ]),
            ];
        }

        $total = $groups->count();

        $sections = $groups
            ->take($this->limit)
            ->map(fn (Collection $group, string $v): Section => $this->versionSection($v, $group, $grouper, $search))
            ->values()
            ->all();

        if ($total > $this->limit) {
            $sections[] = $this->loadMoreSentinel();
        }

        return $sections;
    }

    /**
     * Keep only entries whose description (or version) matches the search term.
     */
    protected function filter(Collection $entries, string $search): Collection
    {
        $search = trim($search);

        if ($search === '') {
            return $entries;
        }

        $needle = mb_strtolower($search);

        return $entries->filter(function (ChangelogEntry $entry) use ($needle): bool {
            return str_contains(mb_strtolower((string) $entry->description), $needle)
                || str_contains(mb_strtolower((string) $entry->version), $needle);
        })->values();
    }

    protected function loadMoreSentinel(): Grid
    {
        return Grid::make(1)
            ->key('changelog-load-more-'.$this->limit)
            ->extraAttributes([
                'x-intersect.margin.600px.once' => '$wire.loadMore()',
                'class' => 'flex justify-center py-2',
            ])
            ->schema([
                Text::make(__('changelog::changelog.reader.loading'))
                    ->color('gray')
                    ->size('sm'),
            ]);
    }

    protected function versionSection(string $version, Collection $group, VersionGrouper $grouper, string $search = ''): Section
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
                TextEntry::make('body_'.md5($version.'|'.$search))
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
