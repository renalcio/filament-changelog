<?php

use Filament\Changelog\Enums\ChangeType;
use Filament\Changelog\Models\ChangelogEntry;
use Filament\Changelog\Support\ChangelogSource;

it('reads from the database by default', function () {
    config()->set('changelog.source', 'database');

    ChangelogEntry::create([
        'version' => '1.0.0',
        'released_at' => '2024-01-15',
        'is_released' => true,
        'type' => ChangeType::Added,
        'description' => 'From DB',
        'sort' => 0,
    ]);

    $entries = ChangelogSource::entries();

    expect($entries)->toHaveCount(1);
    expect($entries->first()->description)->toBe('From DB');
});

it('reads live from the CHANGELOG.md file in file mode', function () {
    $path = sys_get_temp_dir() . '/changelog-test-' . uniqid() . '.md';
    file_put_contents($path, "## [2.0.0] - 2024-05-25\n### Added\n- Live from file");

    config()->set('changelog.source', 'file');
    config()->set('changelog.file', $path);

    $entries = ChangelogSource::entries();

    expect(ChangelogSource::isFile())->toBeTrue();
    expect($entries)->toHaveCount(1);
    expect($entries->first())
        ->version->toBe('2.0.0')
        ->description->toBe('Live from file');

    unlink($path);
});

it('resolves relative paths from the base path and keeps absolute paths as-is', function () {
    expect(ChangelogSource::path('CHANGELOG.md'))->toBe(base_path('CHANGELOG.md'));
    expect(ChangelogSource::path('/tmp/foo.md'))->toBe('/tmp/foo.md');
});

it('returns an empty collection when the file is missing', function () {
    config()->set('changelog.source', 'file');
    config()->set('changelog.file', '/does/not/exist.md');

    expect(ChangelogSource::entries())->toBeEmpty();
});
