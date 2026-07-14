# Changelog

All notable changes to `filament-changelog` are documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/),
and this project adheres to [Semantic Versioning](https://semver.org/).

## [1.1.0] - 2026-07-14

### Added
- Complete the fluent plugin API so every option is configurable on
  `ChangelogPlugin::make()` (and documented in one place): `perPage()`,
  `searchable()`, `filterableByVersion()`, `dateFormat()`, `changeTypes()`,
  `slug()`, `resourceSlug()`, `remoteCacheTtl()`, `policy()` and
  `multiProject()`. Anything left unset still falls back to `config/changelog.php`.

## [1.0.0] - 2026-07-14

First public release.

### Added
- Read-only **Changelog** reader page, grouped by version with date badges and
  typed sections — built entirely with the Filament schema API (no custom Blade).
- Reader **search, version filter and infinite scroll** — find entries as you
  type, jump to a single version, and load version cards in batches as you scroll.
- **Management resource** to create, view, edit and delete entries, grouped by
  version with colored type badges and filters.
- **Import** a `CHANGELOG.md` (upload, paste, or `changelog:import`) and
  **export** the database back to a file (download, or `changelog:export`).
- **Hybrid source**: read from the database or parse the project's
  `CHANGELOG.md` live on every visit (`CHANGELOG_SOURCE`, `CHANGELOG_FILE`).
- Read the changelog from a **remote URL** (e.g. a GitHub raw `CHANGELOG.md`)
  via `->fromUrl()` / `->file('https://…')`, with a configurable fetch cache;
  normal GitHub `blob`/`raw` links are auto-converted to `raw.githubusercontent.com`.
- Keep a Changelog **parser** and **writer** with a clean round-trip; the parser
  is tolerant of real-world files (unknown `###` headings fall back to *Changed*,
  and dates are detected in GitHub, GitLab and conventional-changelog styles).
- **Translations** for English, European Portuguese and Brazilian Portuguese;
  the generated file always uses canonical English section headings.
- **Fluent plugin API**: `navigationLabel`, `navigationIcon`,
  `activeNavigationIcon`, `navigationGroup`, `navigationSort`,
  `registerNavigation`, `modelLabel`, `pluralModelLabel`.
- **Authorization** via `canManage()` (bool/closure), deferring to the model
  policy when unset.
- **Filament Shield integration** with no hard dependency — resource and page
  are auto-discovered, the policy is bound automatically, and everything stays
  open when Shield is absent.
- Pest test suite (parser, writer, round-trip, enum, grouping, source, commands
  and plugin API).
