<?php

use Filament\Changelog\Enums\ChangeType;
use Filament\Changelog\Models\ChangelogEntry;
use Filament\Changelog\Support\VersionGrouper;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Support\Collection as SupportCollection;

function makeEntry(array $attributes): ChangelogEntry
{
    return new ChangelogEntry(array_merge([
        'is_released' => true,
        'type' => ChangeType::Added,
        'description' => 'x',
        'sort' => 0,
    ], $attributes));
}

it('groups by version with Unreleased first, then newest release date', function () {
    $entries = collect([
        makeEntry(['version' => '1.0.0', 'released_at' => '2024-01-15']),
        makeEntry(['version' => '1.1.0', 'released_at' => '2024-03-10']),
        makeEntry(['version' => 'Unreleased', 'released_at' => null, 'is_released' => false]),
    ]);

    $order = (new VersionGrouper)->group($entries)->keys()->all();

    expect($order)->toBe(['Unreleased', '1.1.0', '1.0.0']);
});

it('splits a version into buckets ordered by configured type order', function () {
    $group = collect([
        makeEntry(['type' => ChangeType::Security, 'sort' => 0]),
        makeEntry(['type' => ChangeType::Added, 'sort' => 1]),
        makeEntry(['type' => ChangeType::Fixed, 'sort' => 2]),
    ]);

    $types = (new VersionGrouper)->byType($group)
        ->map(fn (array $bucket) => $bucket['type'])
        ->all();

    expect($types)->toBe([ChangeType::Added, ChangeType::Fixed, ChangeType::Security]);
});

it('omits type buckets with no entries', function () {
    $group = collect([makeEntry(['type' => ChangeType::Added])]);

    expect((new VersionGrouper)->byType($group))->toHaveCount(1);
});

it('returns a plain support collection so key methods work on eloquent input', function () {
    // Grouping an Eloquent collection must not leak an Eloquent collection,
    // whose only()/except() expect model keys and call getKey() on the groups.
    $entries = new EloquentCollection([
        makeEntry(['version' => '1.0.0', 'released_at' => '2024-01-15']),
        makeEntry(['version' => '1.1.0', 'released_at' => '2024-03-10']),
    ]);

    $groups = (new VersionGrouper)->group($entries);

    expect($groups)->toBeInstanceOf(SupportCollection::class)
        ->and($groups)->not->toBeInstanceOf(EloquentCollection::class);

    // The reader filters to a single version with only() — this used to throw.
    $only = $groups->only(['1.1.0']);

    expect($only->keys()->all())->toBe(['1.1.0'])
        ->and($only->get('1.1.0'))->toHaveCount(1);
});
