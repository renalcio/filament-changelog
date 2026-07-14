<?php

namespace Filament\Changelog\Commands;

use Filament\Changelog\Models\ChangelogEntry;
use Filament\Changelog\Support\ChangelogSource;
use Filament\Changelog\Support\KeepAChangelogParser;
use Illuminate\Console\Command;

class ImportChangelogCommand extends Command
{
    protected $signature = 'changelog:import
        {file? : Path to the CHANGELOG.md file (defaults to config value)}
        {--project= : Tag imported entries with this project}
        {--fresh : Delete existing entries (scoped to project when given) before importing}';

    protected $description = 'Import a CHANGELOG.md file into the changelog database.';

    public function handle(): int
    {
        $path = ChangelogSource::path($this->argument('file'));

        if (! is_file($path)) {
            $this->error("File not found: {$path}");

            return self::FAILURE;
        }

        $entries = (new KeepAChangelogParser)->parse((string) file_get_contents($path));

        if (empty($entries)) {
            $this->warn('No entries found — is the file in Keep a Changelog format?');

            return self::SUCCESS;
        }

        $project = $this->option('project');

        if ($this->option('fresh')) {
            ChangelogEntry::query()
                ->when($project, fn ($q) => $q->where('project', $project))
                ->delete();
        }

        foreach ($entries as $entry) {
            ChangelogEntry::create(array_merge($entry, ['project' => $project ?: null]));
        }

        $this->info('Imported '.count($entries).' changelog entries.');

        return self::SUCCESS;
    }
}
