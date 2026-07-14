<?php

namespace Filament\Changelog\Support;

use Filament\Changelog\Enums\ChangeType;
use Filament\Changelog\Models\ChangelogEntry;
use Illuminate\Support\Collection;

/**
 * Groups changelog entries by version in display order (Unreleased first, then
 * most recent release date descending) and, within each version, by the
 * configured change-type order.
 */
class VersionGrouper
{
    /**
     * @param  Collection<int, ChangelogEntry>  $entries
     * @return Collection<string, Collection<int, ChangelogEntry>>
     */
    public function group(Collection $entries): Collection
    {
        return $entries
            ->groupBy('version')
            ->sortBy(function (Collection $group, string $version): string {
                if (strtolower($version) === 'unreleased') {
                    return '9999-99-99'; // reversed below → always first
                }

                $date = optional($group->first())->released_at;

                return $date ? $date->format('Y-m-d') : '0000-00-00';
            })
            ->reverse();
    }

    /**
     * Entries of a single version, split and ordered by change type.
     *
     * @param  Collection<int, ChangelogEntry>  $group
     * @return Collection<int, array{type: ChangeType, entries: Collection<int, ChangelogEntry>}>
     */
    public function byType(Collection $group): Collection
    {
        return $this->typeOrder()
            ->map(fn (ChangeType $type): array => [
                'type' => $type,
                'entries' => $group
                    ->filter(fn (ChangelogEntry $e): bool => $e->type === $type)
                    ->sortBy('sort')
                    ->values(),
            ])
            ->filter(fn (array $bucket): bool => $bucket['entries']->isNotEmpty())
            ->values();
    }

    /**
     * @return Collection<int, ChangeType>
     */
    protected function typeOrder(): Collection
    {
        $configured = collect(config('changelog.types', []))
            ->map(fn (string $t): ?ChangeType => ChangeType::tryFrom($t))
            ->filter()
            ->values();

        return $configured->isNotEmpty()
            ? $configured
            : collect(ChangeType::cases());
    }
}
