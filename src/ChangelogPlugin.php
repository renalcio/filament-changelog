<?php

namespace Filament\Changelog;

use BackedEnum;
use Closure;
use Filament\Changelog\Pages\ChangelogPage;
use Filament\Changelog\Resources\ChangelogEntryResource;
use Filament\Contracts\Plugin;
use Filament\Panel;
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

        $panel
            ->resources([
                ChangelogEntryResource::class,
            ])
            ->pages([
                ChangelogPage::class,
            ]);
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
     * Convenience: read live from the given CHANGELOG.md file.
     */
    public function fromFile(string|Closure|null $path = 'CHANGELOG.md'): static
    {
        return $this->source('file')->file($path);
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
}
