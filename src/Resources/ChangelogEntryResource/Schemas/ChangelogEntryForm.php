<?php

namespace Filament\Changelog\Resources\ChangelogEntryResource\Schemas;

use Filament\Changelog\Enums\ChangeType;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;

class ChangelogEntryForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make()
                    ->schema([
                        TextInput::make('project')
                            ->label(__('changelog::changelog.fields.project'))
                            ->maxLength(255)
                            ->placeholder(__('changelog::changelog.fields.project_placeholder'))
                            ->helperText(__('changelog::changelog.fields.project_helper'))
                            ->columnSpanFull()
                            ->visible(fn (): bool => (bool) config('changelog.multi_project', false)),

                        TextInput::make('version')
                            ->label(__('changelog::changelog.fields.version'))
                            ->required()
                            ->maxLength(255)
                            ->placeholder(__('changelog::changelog.fields.version_placeholder'))
                            ->columnSpanFull(),

                        Grid::make()
                            ->schema([
                                Toggle::make('is_released')
                                    ->label(__('changelog::changelog.fields.released'))
                                    ->live()
                                    ->default(false),

                                DatePicker::make('released_at')
                                    ->label(__('changelog::changelog.fields.release_date'))
                                    ->native(false)
                                    ->visible(fn (Get $get): bool => (bool) $get('is_released')),
                            ]),

                        Select::make('type')
                            ->label(__('changelog::changelog.fields.type'))
                            ->options(ChangeType::class)
                            ->required()
                            ->searchable()
                            ->native(false)
                            ->default(ChangeType::Added)
                            ->columnSpanFull(),

                        Textarea::make('description')
                            ->label(__('changelog::changelog.fields.description'))
                            ->required()
                            ->rows(3)
                            ->maxLength(1000)
                            ->placeholder(__('changelog::changelog.fields.description_placeholder'))
                            ->columnSpanFull(),
                    ])
                    ->columns(1)
                    ->columnSpanFull(),
            ]);
    }
}
