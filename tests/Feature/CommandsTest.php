<?php

use Filament\Changelog\Enums\ChangeType;
use Filament\Changelog\Models\ChangelogEntry;

it('imports a CHANGELOG.md file into the database', function () {
    $path = sys_get_temp_dir() . '/changelog-import-' . uniqid() . '.md';
    file_put_contents($path, "## [1.0.0] - 2024-01-15\n### Added\n- One\n- Two");

    $this->artisan('changelog:import', ['file' => $path])
        ->assertSuccessful();

    expect(ChangelogEntry::count())->toBe(2);
    expect(ChangelogEntry::where('description', 'One')->first()->type)->toBe(ChangeType::Added);

    unlink($path);
});

it('replaces existing entries when importing with --fresh', function () {
    ChangelogEntry::create([
        'version' => '0.9.0', 'is_released' => true, 'type' => ChangeType::Added,
        'description' => 'old', 'sort' => 0,
    ]);

    $path = sys_get_temp_dir() . '/changelog-fresh-' . uniqid() . '.md';
    file_put_contents($path, "## [1.0.0] - 2024-01-15\n### Added\n- new");

    $this->artisan('changelog:import', ['file' => $path, '--fresh' => true])
        ->assertSuccessful();

    expect(ChangelogEntry::count())->toBe(1);
    expect(ChangelogEntry::first()->description)->toBe('new');

    unlink($path);
});

it('fails gracefully when the import file is missing', function () {
    $this->artisan('changelog:import', ['file' => '/no/such/file.md'])
        ->assertFailed();
});

it('exports the database to a CHANGELOG.md file', function () {
    ChangelogEntry::create([
        'version' => '1.0.0', 'released_at' => '2024-01-15', 'is_released' => true,
        'type' => ChangeType::Added, 'description' => 'Exported', 'sort' => 0,
    ]);

    $path = sys_get_temp_dir() . '/changelog-export-' . uniqid() . '.md';

    $this->artisan('changelog:export', ['file' => $path])
        ->assertSuccessful();

    $contents = file_get_contents($path);
    expect($contents)
        ->toContain('## [1.0.0] - 2024-01-15')
        ->toContain('### Added')
        ->toContain('- Exported');

    unlink($path);
});
