<?php

namespace Filament\Changelog\Support;

use Filament\Changelog\ChangelogPlugin;
use Filament\Changelog\Models\ChangelogEntry;
use Illuminate\Support\Collection;
use Throwable;

/**
 * Resolves where changelog entries come from — the database, or a live parse of
 * the project's CHANGELOG.md file — and where that file lives on disk.
 */
class ChangelogSource
{
    /**
     * Entries for the reader page, honouring the configured source.
     *
     * @return Collection<int, ChangelogEntry>
     */
    public static function entries(): Collection
    {
        if (static::isFile()) {
            return static::fromFile();
        }

        return ChangelogEntry::query()->orderBy('sort')->get();
    }

    public static function isFile(): bool
    {
        $source = static::pluginValue('getSource') ?? config('changelog.source', 'database');

        return $source === 'file';
    }

    /**
     * Absolute path to the configured CHANGELOG.md. Fluent plugin config wins,
     * then the config/env value; a relative value resolves from the app base
     * path, an absolute one is used verbatim.
     */
    public static function path(?string $override = null): string
    {
        $file = $override
            ?: static::pluginValue('getFile')
            ?: config('changelog.file', 'CHANGELOG.md');

        return str_starts_with($file, '/') ? $file : base_path($file);
    }

    /**
     * Read a value from the registered plugin, safely (e.g. in console there
     * may be no current panel). Returns null when unavailable.
     */
    protected static function pluginValue(string $getter): ?string
    {
        try {
            return ChangelogPlugin::get()->{$getter}();
        } catch (Throwable) {
            return null;
        }
    }

    /**
     * Parse the CHANGELOG.md file into (unsaved) entries. Returns an empty
     * collection when the file is missing.
     *
     * @return Collection<int, ChangelogEntry>
     */
    public static function fromFile(): Collection
    {
        $path = static::path();

        if (! is_file($path)) {
            return collect();
        }

        $parsed = (new KeepAChangelogParser)->parse((string) file_get_contents($path));

        return collect($parsed)->map(fn (array $attributes): ChangelogEntry => new ChangelogEntry($attributes));
    }
}
