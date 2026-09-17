<?php

namespace Filament\Changelog;

use Filament\Changelog\Commands\ExportChangelogCommand;
use Filament\Changelog\Commands\ImportChangelogCommand;
use Filament\Changelog\Resources\ChangelogEntryResource;
use Illuminate\Support\Facades\Gate;
use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;

class ChangelogServiceProvider extends PackageServiceProvider
{
    public static string $name = 'changelog';

    public static string $viewNamespace = 'changelog';

    public function configurePackage(Package $package): void
    {
        $package
            ->name(static::$name)
            ->hasConfigFile()
            ->hasTranslations()
            ->hasMigration('create_changelog_entries_table')
            ->hasCommands([
                ImportChangelogCommand::class,
                ExportChangelogCommand::class,
            ]);
    }

    public function packageBooted(): void
    {
        parent::packageBooted();

        $this->ensureSqliteDatabaseExists();
        $this->bindPolicy();
    }

    /**
     * Create the SQLite database file for the changelog connection when it
     * doesn't exist yet. Laravel doesn't do this automatically, so migrating a
     * dedicated SQLite connection (set via `changelog.connection`) fails on a
     * fresh environment with "unable to open database file" unless the file
     * (and its directory) is created first.
     */
    protected function ensureSqliteDatabaseExists(): void
    {
        $connection = config('changelog.connection') ?: config('database.default');

        if (config("database.connections.{$connection}.driver") !== 'sqlite') {
            return;
        }

        $database = config("database.connections.{$connection}.database");

        if (blank($database) || $database === ':memory:' || file_exists($database)) {
            return;
        }

        $directory = dirname($database);

        if (! is_dir($directory)) {
            mkdir($directory, recursive: true);
        }

        touch($database);
    }

    /**
     * Bind the changelog policy to the model. Filament Shield generates the
     * policy under App\Policies, but because the model lives in this package's
     * namespace, Laravel's policy auto-discovery would miss it — so we register
     * the mapping explicitly. No-op when no policy exists (Shield not used).
     */
    protected function bindPolicy(): void
    {
        $policy = config('changelog.policy') ?: 'App\\Policies\\ChangelogEntryPolicy';

        if (class_exists($policy)) {
            Gate::policy(ChangelogEntryResource::getModel(), $policy);
        }
    }
}
