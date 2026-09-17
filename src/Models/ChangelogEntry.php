<?php

namespace Filament\Changelog\Models;

use Filament\Changelog\ChangelogPlugin;
use Filament\Changelog\Enums\ChangeType;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

/**
 * @property string|null $project
 * @property string $version
 * @property Carbon|null $released_at
 * @property bool $is_released
 * @property ChangeType $type
 * @property string $description
 * @property int $sort
 */
class ChangelogEntry extends Model
{
    protected $guarded = [];

    protected $casts = [
        'released_at' => 'date',
        'is_released' => 'boolean',
        'type' => ChangeType::class,
        'sort' => 'integer',
    ];

    public function getTable(): string
    {
        return config('changelog.table', 'changelog_entries');
    }

    public function getConnectionName(): ?string
    {
        return ChangelogPlugin::current()->getConnection() ?? config('changelog.connection') ?? parent::getConnectionName();
    }

    /**
     * Shape expected by the ChangelogWriter / parser round-trip.
     *
     * @return array{version: string, released_at: ?string, is_released: bool, type: ChangeType, description: string, sort: int}
     */
    public function toChangelogArray(): array
    {
        return [
            'version' => $this->version,
            'released_at' => $this->released_at?->format('Y-m-d'),
            'is_released' => $this->is_released,
            'type' => $this->type,
            'description' => $this->description,
            'sort' => $this->sort,
        ];
    }
}
