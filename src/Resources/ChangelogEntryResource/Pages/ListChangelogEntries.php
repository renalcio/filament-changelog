<?php

namespace Filament\Changelog\Resources\ChangelogEntryResource\Pages;

use Filament\Actions\Action;
use Filament\Actions\CreateAction;
use Filament\Changelog\Models\ChangelogEntry;
use Filament\Changelog\Pages\ChangelogPage;
use Filament\Changelog\Resources\ChangelogEntryResource;
use Filament\Changelog\Support\ChangelogWriter;
use Filament\Changelog\Support\KeepAChangelogParser;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ListRecords;
use Filament\Support\Icons\Heroicon;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ListChangelogEntries extends ListRecords
{
    protected static string $resource = ChangelogEntryResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('view_changelog')
                ->label(__('changelog::changelog.navigation.page_label'))
                ->icon(Heroicon::OutlinedDocumentText)
                ->color('gray')
                ->url(fn (): string => ChangelogPage::getUrl()),
            $this->importAction(),
            $this->exportAction(),
            CreateAction::make(),
        ];
    }

    protected function importAction(): Action
    {
        return Action::make('import')
            ->label(__('changelog::changelog.actions.import'))
            ->icon(Heroicon::OutlinedArrowUpTray)
            ->color('gray')
            ->schema([
                FileUpload::make('file')
                    ->label(__('changelog::changelog.import.file_label'))
                    ->acceptedFileTypes(['text/markdown', 'text/plain', 'text/x-markdown'])
                    ->storeFiles(false)
                    ->helperText(__('changelog::changelog.import.file_helper')),
                Textarea::make('markdown')
                    ->label(__('changelog::changelog.import.paste_label'))
                    ->rows(10),
                TextInput::make('project')
                    ->label(__('changelog::changelog.fields.project'))
                    ->helperText(__('changelog::changelog.import.project_helper'))
                    ->visible(fn (): bool => (bool) config('changelog.multi_project', false)),
                Toggle::make('replace')
                    ->label(__('changelog::changelog.import.replace_label'))
                    ->default(false),
            ])
            ->action(function (array $data): void {
                $markdown = $this->resolveImportMarkdown($data);

                if (blank($markdown)) {
                    Notification::make()
                        ->title(__('changelog::changelog.notifications.nothing_to_import_title'))
                        ->body(__('changelog::changelog.notifications.nothing_to_import_body'))
                        ->warning()
                        ->send();

                    return;
                }

                $entries = (new KeepAChangelogParser)->parse($markdown);

                if (empty($entries)) {
                    Notification::make()
                        ->title(__('changelog::changelog.notifications.no_entries_title'))
                        ->body(__('changelog::changelog.notifications.no_entries_body'))
                        ->warning()
                        ->send();

                    return;
                }

                $project = $data['project'] ?? null;

                if (! empty($data['replace'])) {
                    ChangelogEntry::query()
                        ->when($project, fn ($q) => $q->where('project', $project))
                        ->delete();
                }

                foreach ($entries as $entry) {
                    ChangelogEntry::create(array_merge($entry, [
                        'project' => $project ?: ($entry['project'] ?? null),
                    ]));
                }

                Notification::make()
                    ->title(__('changelog::changelog.notifications.imported_title', ['count' => count($entries)]))
                    ->success()
                    ->send();
            });
    }

    protected function exportAction(): Action
    {
        return Action::make('export')
            ->label(__('changelog::changelog.actions.export'))
            ->icon(Heroicon::OutlinedArrowDownTray)
            ->color('gray')
            ->action(function (): StreamedResponse {
                $entries = ChangelogEntry::query()
                    ->orderBy('sort')
                    ->get()
                    ->map->toChangelogArray();

                $markdown = (new ChangelogWriter)->render($entries);

                return response()->streamDownload(
                    fn () => print ($markdown),
                    'CHANGELOG.md',
                    ['Content-Type' => 'text/markdown'],
                );
            });
    }

    /**
     * @param  array<string, mixed>  $data
     */
    protected function resolveImportMarkdown(array $data): ?string
    {
        if (! empty($data['markdown'])) {
            return (string) $data['markdown'];
        }

        $file = $data['file'] ?? null;

        if ($file instanceof UploadedFile) {
            return $file->get();
        }

        // storeFiles(false) may still hand back a temporary path string.
        if (is_string($file) && Storage::disk('local')->exists($file)) {
            return Storage::disk('local')->get($file);
        }

        return null;
    }
}
