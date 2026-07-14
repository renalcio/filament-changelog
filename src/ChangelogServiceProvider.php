<?php

namespace Filament\Changelog;

use Filament\Changelog\Commands\ExportChangelogCommand;
use Filament\Changelog\Commands\ImportChangelogCommand;
use Filament\Changelog\Models\ChangelogEntry;
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

        $this->bindPolicy();
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
            Gate::policy(ChangelogEntry::class, $policy);
        }
    }
}
