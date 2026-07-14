# Filament Changelog

Manage, read and generate [Keep a Changelog](https://keepachangelog.com/) files inside Filament.

It is **hybrid**: changelog entries live in your database (fully editable via a
Filament resource, with grouping, filters and badges) **and** can be imported
from / exported to a `CHANGELOG.md` file at any time — via the UI or artisan.

## Features

- 📖 **Reader page** — a polished, read-only changelog grouped by version with
  date badges and typed sections (built entirely with the Filament schema API,
  no custom Blade)
- 📝 Management resource to create/read/edit entries, grouped by version
- 🏷️ Typed changes (Added, Changed, Deprecated, Removed, Fixed, Security) with colored badges
- ⬆️ **Import** an existing `CHANGELOG.md` (upload, paste, or `changelog:import`)
- ⬇️ **Export** the database back to a `CHANGELOG.md` (download, or `changelog:export`)
- 🗂️ Optional multi-project mode (one app, many changelogs)

## Installation

```bash
composer require filamentphp/changelog
php artisan vendor:publish --tag="changelog-migrations"
php artisan migrate
php artisan vendor:publish --tag="changelog-config"
```

Register the plugin on your panel:

```php
use Filament\Changelog\ChangelogPlugin;

public function panel(Panel $panel): Panel
{
    return $panel->plugin(ChangelogPlugin::make());
}
```

### Customizing the plugin

Fluent methods (each accepts a value or a `Closure`), mirroring other Filament
plugins — everything falls back to the translations/config when not set:

```php
ChangelogPlugin::make()
    ->navigationLabel('What\'s new')            // string|Closure|null
    ->navigationIcon('heroicon-o-sparkles')     // string|BackedEnum|Closure|null
    ->activeNavigationIcon('heroicon-s-sparkles')
    ->navigationGroup('Docs')                   // string|UnitEnum|Closure|null
    ->navigationSort(1)                         // int|Closure|null
    ->registerNavigation(true)                  // bool|Closure
    ->modelLabel('release note')                // string|Closure|null
    ->pluralModelLabel('release notes');        // string|Closure|null
```

### Authorization (who can manage)

The read-only **Changelog** page is always visible; the **Manage** area
(create / edit / delete) is guarded. Control it in two ways:

```php
// 1) A simple gate — block everyone, or decide per user:
ChangelogPlugin::make()
    ->canManage(fn ($user) => $user?->hasRole('admin'));

// 2) Do nothing and it defers to the model's policy / Gate —
//    so Filament Shield permissions work out of the box:
//    php artisan shield:generate  → ViewAny/Create/Update/Delete are enforced.
```

When management is denied, the routes return `403` and the **Manage** button on
the reader page is hidden automatically.

### Filament Shield

The plugin is **Shield-ready with no hard dependency** — it works with or
without Shield installed:

- **Detected automatically.** The resource and the reader page are registered on
  the panel, so `php artisan shield:generate` discovers them and creates their
  permissions (nothing to configure).
- **Enforced automatically.** Shield generates the policy under `App\Policies`;
  because the model lives in the package namespace, the plugin binds that policy
  to the model for you (override with `config('changelog.policy')`). The reader
  page also honours its generated Shield permission at runtime.
- **Without Shield**, everything stays open/functional and no Shield classes are
  referenced.

Prefer explicit wiring? Point `canManage()` at a Shield permission:

```php
ChangelogPlugin::make()
    ->canManage(fn ($user) => $user?->can('Update:ChangelogEntry'));
```

## Usage

### In the panel

A single **Changelog** item appears in the navigation (the read-only reader
page). From there, the **Manage** button opens the management table to
create/edit entries and **Import** / **Export** a `CHANGELOG.md` file; a
**Changelog** button takes you back.

### From the CLI

```bash
# Import CHANGELOG.md into the database
php artisan changelog:import

# Import a specific file, wiping existing entries first
php artisan changelog:import path/to/CHANGELOG.md --fresh

# Generate CHANGELOG.md from the database
php artisan changelog:export

# Print instead of writing
php artisan changelog:export --print
```

## Translations

Ships with **English (`en`)**, **European Portuguese (`pt_PT`)** and **Brazilian
Portuguese (`pt_BR`)**. The UI follows the app locale (`config('app.locale')`).
Add more languages by publishing the translations:

```bash
php artisan vendor:publish --tag="changelog-translations"
```

> The generated `CHANGELOG.md` always uses the canonical English section
> headings (`### Added`, `### Fixed`, …) regardless of locale, so the file stays
> valid Keep a Changelog and round-trips cleanly.

## Reader source & file path

The reader page can read from two sources, set in `config/changelog.php` (or via
env), so you point it at **your own project** and it never shows a stale copy:

```env
# 'database' (default) — entries edited in the panel
# 'file' — parses your CHANGELOG.md live on every visit (never "dead")
CHANGELOG_SOURCE=file

# Your project's changelog. Relative → from base_path(); absolute → used as-is.
CHANGELOG_FILE=CHANGELOG.md
# CHANGELOG_FILE=/srv/my-other-project/CHANGELOG.md
```

Or configure it fluently on the plugin (this wins over config/env):

```php
ChangelogPlugin::make()
    ->fromFile(base_path('CHANGELOG.md'));   // = ->source('file')->file(...)
// or, granularly:
ChangelogPlugin::make()
    ->source('file')                          // 'database' | 'file'
    ->file('/srv/my-project/CHANGELOG.md');
```

The file can also be a **remote URL** — e.g. read your `CHANGELOG.md` straight
from GitHub. Remote content is cached (`CHANGELOG_REMOTE_CACHE_TTL`, default
300s; `0` disables it):

```php
ChangelogPlugin::make()
    ->fromUrl('https://raw.githubusercontent.com/acme/app/main/CHANGELOG.md');
```

In `file` mode the panel's edit/export buttons are hidden — the file is the
single source of truth, and every page load re-parses it, so what you see always
matches the file on disk.

## Configuration

See `config/changelog.php` — reader source, file path, table name, change-type
order, date format, multi-project toggle and navigation placement.

## License

MIT © Anselmo Kossa
