<?php

namespace Filament\Changelog\Commands;

use Filament\Changelog\Models\ChangelogEntry;
use Filament\Changelog\Support\ChangelogSource;
use Filament\Changelog\Support\ChangelogWriter;
use Illuminate\Console\Command;

class ExportChangelogCommand extends Command
{
    protected $signature = 'changelog:export
        {file? : Destination path (defaults to config value)}
        {--project= : Only export entries for this project}
        {--print : Print to stdout instead of writing a file}';

    protected $description = 'Generate a CHANGELOG.md file from the changelog database.';

    public function handle(): int
    {
        $project = $this->option('project');

        $entries = ChangelogEntry::query()
            ->when($project, fn ($q) => $q->where('project', $project))
            ->orderBy('sort')
            ->get()
            ->map->toChangelogArray();

        if ($entries->isEmpty()) {
            $this->warn('No entries to export.');

            return self::SUCCESS;
        }

        $markdown = (new ChangelogWriter)->render($entries);

        if ($this->option('print')) {
            $this->line($markdown);

            return self::SUCCESS;
        }

        $path = ChangelogSource::path($this->argument('file'));

        if (ChangelogSource::isRemote($path)) {
            $this->error("Cannot export to a URL: {$path}");

            return self::FAILURE;
        }

        file_put_contents($path, $markdown);

        $this->info("Wrote {$entries->count()} entries to {$path}");

        return self::SUCCESS;
    }
}
