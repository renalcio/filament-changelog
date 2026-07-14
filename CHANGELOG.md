# Changelog

All notable changes to `filament-changelog` are documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/),
and this project adheres to [Semantic Versioning](https://semver.org/).

## [Unreleased]

### Added
- Read the changelog from a **remote URL** (e.g. a GitHub raw `CHANGELOG.md`)
  via `->fromUrl()` / `->file('https://…')`, with a configurable fetch cache.
- Auto-convert GitHub `blob`/`raw` web URLs to `raw.githubusercontent.com`, so a
  normal repo file link works directly.

## [1.0.0] - 2026-07-14

### Added
- Read-only **Changelog** reader page, grouped by version with date badges and
  typed sections — built entirely with the Filament schema API (no custom Blade).
- **Management resource** to create, view, edit and delete entries, grouped by
  version with colored type badges and filters.
- **Import** a `CHANGELOG.md` (upload, paste, or `changelog:import`) and
  **export** the database back to a file (download, or `changelog:export`).
- **Hybrid source**: read from the database or parse the project's
  `CHANGELOG.md` live on every visit (`CHANGELOG_SOURCE`, `CHANGELOG_FILE`).
- Keep a Changelog **parser** and **writer** with a clean round-trip.
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
