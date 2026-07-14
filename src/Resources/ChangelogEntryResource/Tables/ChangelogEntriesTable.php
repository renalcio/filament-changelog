<?php

namespace Filament\Changelog\Resources\ChangelogEntryResource\Tables;

use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Changelog\Enums\ChangeType;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;

class ChangelogEntriesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultGroup('version')
            ->defaultSort('sort')
            ->columns([
                TextColumn::make('project')
                    ->label(__('changelog::changelog.fields.project'))
                    ->searchable()
                    ->toggleable()
                    ->visible(fn (): bool => (bool) config('changelog.multi_project', false)),

                TextColumn::make('version')
                    ->label(__('changelog::changelog.fields.version'))
                    ->badge()
                    ->color('gray')
                    ->sortable()
                    ->searchable(),

                TextColumn::make('type')
                    ->label(__('changelog::changelog.fields.type'))
                    ->badge()
                    ->sortable(),

                TextColumn::make('description')
                    ->label(__('changelog::changelog.fields.description'))
                    ->wrap()
                    ->searchable()
                    ->limit(120),

                IconColumn::make('is_released')
                    ->label(__('changelog::changelog.fields.released'))
                    ->boolean()
                    ->sortable(),

                TextColumn::make('released_at')
                    ->label(__('changelog::changelog.fields.release_date'))
                    ->date()
                    ->sortable()
                    ->toggleable(),
            ])
            ->filters([
                SelectFilter::make('type')
                    ->label(__('changelog::changelog.fields.type'))
                    ->options(ChangeType::class),

                TernaryFilter::make('is_released')
                    ->label(__('changelog::changelog.fields.released')),
            ])
            ->recordActions([
                ViewAction::make(),
                EditAction::make(),
            ])
            ->toolbarActions([
                DeleteBulkAction::make(),
            ]);
    }
}
