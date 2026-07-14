<?php

namespace Filament\Changelog;

use BackedEnum;
use Closure;
use Filament\Changelog\Models\ChangelogEntry;
use Filament\Changelog\Pages\ChangelogPage;
use Filament\Changelog\Resources\ChangelogEntryResource;
use Filament\Contracts\Plugin;
use Filament\Panel;
use Illuminate\Support\Facades\Gate;
use UnitEnum;

class ChangelogPlugin implements Plugin
{
    protected bool $enabled = true;

    protected string|Closure|null $navigationLabel = null;

    protected string|BackedEnum|Closure|null $navigationIcon = null;

    protected string|BackedEnum|Closure|null $activeNavigationIcon = null;

    protected string|UnitEnum|Closure|null $navigationGroup = null;

    protected int|Closure|null $navigationSort = null;

    protected bool|Closure $shouldRegisterNavigation = true;

    protected string|Closure|null $modelLabel = null;

    protected string|Closure|null $pluralModelLabel = null;

    protected bool|Closure|null $canManage = null;

    protected string|Closure|null $source = null;

    protected string|Closure|null $file = null;

    protected int|Closure|null $perPage = null;

    protected bool|Closure|null $searchable = null;

    protected bool|Closure|null $filterableByVersion = null;

    protected string|Closure|null $dateFormat = null;

    /** @var array<int, string>|Closure|null */
    protected array|Closure|null $changeTypes = null;

    protected string|Closure|null $pageSlug = null;

    protected string|Closure|null $resourceSlug = null;

    protected int|Closure|null $remoteCacheTtl = null;

    protected string|Closure|null $policy = null;

    protected bool|Closure|null $multiProject = null;

    public static function make(): static
    {
        return app(static::class);
    }

    public static function get(): static
    {
        /** @var static $plugin */
        $plugin = filament(app(static::class)->getId());

        return $plugin;
    }

    public function getId(): string
    {
        return 'changelog';
    }

    public function register(Panel $panel): void
    {
        if (! $this->enabled) {
            return;
        }

        // Any option set fluently overrides config, so every consumer can keep
        // reading config('changelog.*') without knowing about the plugin.
        $this->applyConfiguration();

        $panel
            ->resources([
                ChangelogEntryResource::class,
            ])
            ->pages([
                ChangelogPage::class,
            ]);
    }

    protected function applyConfiguration(): void
    {
        $overrides = array_filter([
            'changelog.reader.per_page' => $this->perPage === null ? null : (int) value($this->perPage),
            'changelog.reader.searchable' => $this->searchable === null ? null : (bool) value($this->searchable),
            'changelog.reader.filterable_by_version' => $this->filterableByVersion === null ? null : (bool) value($this->filterableByVersion),
            'changelog.date_format' => value($this->dateFormat),
            'changelog.types' => value($this->changeTypes),
            'changelog.navigation.page.slug' => value($this->pageSlug),
            'changelog.navigation.resource.slug' => value($this->resourceSlug),
            'changelog.remote_cache_ttl' => $this->remoteCacheTtl === null ? null : (int) value($this->remoteCacheTtl),
            'changelog.multi_project' => $this->multiProject === null ? null : (bool) value($this->multiProject),
            'changelog.policy' => value($this->policy),
        ], fn ($value): bool => $value !== null);

        if ($overrides !== []) {
            config($overrides);
        }

        // The ServiceProvider binds the policy at boot from config; rebind here
        // when a policy is set fluently (register runs after that boot).
        $policy = value($this->policy);

        if (is_string($policy) && class_exists($policy)) {
            Gate::policy(ChangelogEntry::class, $policy);
        }
    }

    public function boot(Panel $panel): void
    {
        //
    }

    public function enabled(bool $enabled = true): static
    {
        $this->enabled = $enabled;

        return $this;
    }

    public function isEnabled(): bool
    {
        return $this->enabled;
    }

    /*
    |--------------------------------------------------------------------------
    | Fluent customization (mirrors the Filament Shield plugin API)
    |--------------------------------------------------------------------------
    */

    public function navigationLabel(string|Closure|null $label): static
    {
        $this->navigationLabel = $label;

        return $this;
    }

    public function navigationIcon(string|BackedEnum|Closure|null $icon): static
    {
        $this->navigationIcon = $icon;

        return $this;
    }

    public function activeNavigationIcon(string|BackedEnum|Closure|null $icon): static
    {
        $this->activeNavigationIcon = $icon;

        return $this;
    }

    public function navigationGroup(string|UnitEnum|Closure|null $group): static
    {
        $this->navigationGroup = $group;

        return $this;
    }

    public function navigationSort(int|Closure|null $sort): static
    {
        $this->navigationSort = $sort;

        return $this;
    }

    public function registerNavigation(bool|Closure $condition = true): static
    {
        $this->shouldRegisterNavigation = $condition;

        return $this;
    }

    public function modelLabel(string|Closure|null $label): static
    {
        $this->modelLabel = $label;

        return $this;
    }

    public function pluralModelLabel(string|Closure|null $label): static
    {
        $this->pluralModelLabel = $label;

        return $this;
    }

    public function getNavigationLabel(): ?string
    {
        return value($this->navigationLabel);
    }

    public function getNavigationIcon(): string|BackedEnum|null
    {
        return value($this->navigationIcon);
    }

    public function getActiveNavigationIcon(): string|BackedEnum|null
    {
        return value($this->activeNavigationIcon);
    }

    public function getNavigationGroup(): string|UnitEnum|null
    {
        return value($this->navigationGroup);
    }

    public function getNavigationSort(): ?int
    {
        return value($this->navigationSort);
    }

    public function shouldRegisterNavigation(): bool
    {
        return (bool) value($this->shouldRegisterNavigation);
    }

    /**
     * Gate access to the whole management area (list / create / edit / delete)
     * and the "Manage" button on the reader page.
     *
     * Pass a bool or a Closure receiving the authenticated user. When left
     * unset (null), authorization falls back to the model's policy / Gate —
     * so Filament Shield permissions are respected out of the box.
     */
    public function canManage(bool|Closure|null $callback = true): static
    {
        $this->canManage = $callback;

        return $this;
    }

    /**
     * @return bool|null null → defer to the resource's default policy check
     */
    public function isManageAuthorized(): ?bool
    {
        if ($this->canManage === null) {
            return null;
        }

        return (bool) value($this->canManage, auth()->user());
    }

    /**
     * Where the reader page reads from: 'database' or 'file'.
     */
    public function source(string|Closure|null $source): static
    {
        $this->source = $source;

        return $this;
    }

    /**
     * Path to the project's CHANGELOG.md (relative to base_path, or absolute).
     */
    public function file(string|Closure|null $path): static
    {
        $this->file = $path;

        return $this;
    }

    /**
     * Convenience: read live from the given CHANGELOG.md — a local path or a
     * remote http(s) URL (e.g. a GitHub raw link).
     */
    public function fromFile(string|Closure|null $path = 'CHANGELOG.md'): static
    {
        return $this->source('file')->file($path);
    }

    /**
     * Convenience alias for reading live from a remote CHANGELOG.md URL.
     */
    public function fromUrl(string|Closure $url): static
    {
        return $this->fromFile($url);
    }

    public function getSource(): ?string
    {
        return value($this->source);
    }

    public function getFile(): ?string
    {
        return value($this->file);
    }

    public function getModelLabel(): ?string
    {
        return value($this->modelLabel);
    }

    public function getPluralModelLabel(): ?string
    {
        return value($this->pluralModelLabel);
    }

    /*
    |--------------------------------------------------------------------------
    | Reader page
    |--------------------------------------------------------------------------
    */

    /**
     * Version cards revealed per infinite-scroll step.
     */
    public function perPage(int|Closure|null $count): static
    {
        $this->perPage = $count;

        return $this;
    }

    /**
     * Toggle the reader's live search box.
     */
    public function searchable(bool|Closure|null $condition = true): static
    {
        $this->searchable = $condition;

        return $this;
    }

    /**
     * Toggle the reader's version filter select.
     */
    public function filterableByVersion(bool|Closure|null $condition = true): static
    {
        $this->filterableByVersion = $condition;

        return $this;
    }

    public function getPerPage(): ?int
    {
        return $this->perPage === null ? null : (int) value($this->perPage);
    }

    public function isSearchable(): ?bool
    {
        return $this->searchable === null ? null : (bool) value($this->searchable);
    }

    public function isFilterableByVersion(): ?bool
    {
        return $this->filterableByVersion === null ? null : (bool) value($this->filterableByVersion);
    }

    /*
    |--------------------------------------------------------------------------
    | Formatting, slugs and advanced options
    |--------------------------------------------------------------------------
    */

    /**
     * PHP date() format for the release-date badge.
     */
    public function dateFormat(string|Closure|null $format): static
    {
        $this->dateFormat = $format;

        return $this;
    }

    /**
     * Change-type keys in render order (added, changed, deprecated, …).
     *
     * @param  array<int, string>|Closure|null  $types
     */
    public function changeTypes(array|Closure|null $types): static
    {
        $this->changeTypes = $types;

        return $this;
    }

    /**
     * Route slug of the reader page.
     */
    public function slug(string|Closure|null $slug): static
    {
        $this->pageSlug = $slug;

        return $this;
    }

    /**
     * Route slug of the management resource.
     */
    public function resourceSlug(string|Closure|null $slug): static
    {
        $this->resourceSlug = $slug;

        return $this;
    }

    /**
     * Cache TTL (seconds) for a remote CHANGELOG.md URL; 0 disables caching.
     */
    public function remoteCacheTtl(int|Closure|null $seconds): static
    {
        $this->remoteCacheTtl = $seconds;

        return $this;
    }

    /**
     * Policy class bound to the ChangelogEntry model.
     */
    public function policy(string|Closure|null $policy): static
    {
        $this->policy = $policy;

        return $this;
    }

    /**
     * Scope entries by a "project" column so one app tracks many changelogs.
     */
    public function multiProject(bool|Closure|null $condition = true): static
    {
        $this->multiProject = $condition;

        return $this;
    }

    public function getDateFormat(): ?string
    {
        return value($this->dateFormat);
    }

    /**
     * @return array<int, string>|null
     */
    public function getChangeTypes(): ?array
    {
        return value($this->changeTypes);
    }

    public function getSlug(): ?string
    {
        return value($this->pageSlug);
    }

    public function getResourceSlug(): ?string
    {
        return value($this->resourceSlug);
    }

    public function getRemoteCacheTtl(): ?int
    {
        return $this->remoteCacheTtl === null ? null : (int) value($this->remoteCacheTtl);
    }

    public function getPolicy(): ?string
    {
        return value($this->policy);
    }

    public function isMultiProject(): ?bool
    {
        return $this->multiProject === null ? null : (bool) value($this->multiProject);
    }
}
