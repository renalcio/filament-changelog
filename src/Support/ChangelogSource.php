<?php

namespace Filament\Changelog\Support;

use Filament\Changelog\ChangelogPlugin;
use Filament\Changelog\Models\ChangelogEntry;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Throwable;

/**
 * Resolves where changelog entries come from — the database, or a live parse of
 * the project's CHANGELOG.md (a local file or a remote URL, e.g. a GitHub raw
 * link) — and where that source lives.
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
     * The configured CHANGELOG.md location. Fluent plugin config wins, then the
     * config/env value. A remote URL is returned as-is; a relative local path
     * resolves from the app base path; an absolute one is used verbatim.
     */
    public static function path(?string $override = null): string
    {
        $file = $override
            ?: static::pluginValue('getFile')
            ?: config('changelog.file', 'CHANGELOG.md');

        if (static::isRemote($file)) {
            return $file;
        }

        return str_starts_with($file, '/') ? $file : base_path($file);
    }

    public static function isRemote(string $path): bool
    {
        return str_starts_with($path, 'http://') || str_starts_with($path, 'https://');
    }

    /**
     * Turn a GitHub "blob"/"raw" web URL into its raw.githubusercontent.com
     * equivalent, so a normal repo link fetches the actual markdown instead of
     * the rendered HTML page. Other URLs are returned unchanged.
     */
    public static function normalizeUrl(string $url): string
    {
        return preg_replace(
            '#^https://github\.com/([^/]+)/([^/]+)/(?:blob|raw)/(.+)$#',
            'https://raw.githubusercontent.com/$1/$2/$3',
            $url,
        ) ?? $url;
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
     * Parse the CHANGELOG.md source (local file or remote URL) into (unsaved)
     * entries. Returns an empty collection when it cannot be read.
     *
     * @return Collection<int, ChangelogEntry>
     */
    public static function fromFile(): Collection
    {
        $content = static::read(static::path());

        if ($content === null) {
            return collect();
        }

        $parsed = (new KeepAChangelogParser)->parse($content);

        return collect($parsed)->map(fn (array $attributes): ChangelogEntry => new ChangelogEntry($attributes));
    }

    /**
     * Read the raw contents of a changelog location — a remote URL (cached) or
     * a local file. Returns null when unreadable.
     */
    public static function read(string $path): ?string
    {
        if (static::isRemote($path)) {
            return static::fetch($path);
        }

        return is_file($path) ? (string) file_get_contents($path) : null;
    }

    /**
     * Fetch a remote changelog, cached for `changelog.remote_cache_ttl` seconds
     * (0 disables caching) so the reader doesn't hit the network on every visit.
     */
    protected static function fetch(string $url): ?string
    {
        $url = static::normalizeUrl($url);

        $get = function () use ($url): ?string {
            try {
                $response = Http::timeout((int) config('changelog.remote_timeout', 5))->get($url);

                return $response->successful() ? $response->body() : null;
            } catch (Throwable) {
                return null;
            }
        };

        $ttl = (int) config('changelog.remote_cache_ttl', 300);

        return $ttl > 0
            ? Cache::remember('changelog:remote:'.md5($url), $ttl, $get)
            : $get();
    }
}
