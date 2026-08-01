# Changelog

All notable changes to `filament-changelog` are documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/),
and this project adheres to [Semantic Versioning](https://semver.org/).

## [1.2.0] - 2026-08-01

### Fixed
- **Changelogs written in a language other than English are now read
  correctly.** `### Adicionado`, `### Corrigido` and every other translated
  heading fell through the `?? ChangeType::Changed` fallback, so *every entry
  of every version* was imported — and displayed — as "Changed". Headings are
  now matched against the label the package itself renders in each bundled
  language, ignoring accents, case and extra whitespace. Adding a translation
  to the package now teaches the reader to parse files written in it.
- **The unreleased section is recognised however it is spelled.** The check was
  `strtolower($version) !== 'unreleased'`, so `## [Não lançado]` was imported as
  a *released version literally named that*, with no date. It is matched against
  the translated `reader.unreleased` label plus the spellings people actually
  write by hand.
- **The unreleased section sorts first again.** `VersionGrouper` also compared
  the version string to `'unreleased'`, so a translated section sorted as plain
  text and sank to the bottom of the page. It now reads `is_released` — the
  data, not the label.

### Added
- `Support\Headings`, the shared vocabulary behind the above: `normalizar()`,
  `ePorLancar()`, `traduzidos()` and `linguas()`. Public so an application can
  reuse the same matching when it extends the parser.

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
