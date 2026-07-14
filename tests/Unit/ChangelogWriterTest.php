<?php

use Filament\Changelog\Enums\ChangeType;
use Filament\Changelog\Support\ChangelogWriter;

function entry(string $version, ChangeType $type, string $description, ?string $date, int $sort = 0): array
{
    return [
        'version' => $version,
        'released_at' => $date,
        'is_released' => $date !== null,
        'type' => $type,
        'description' => $description,
        'sort' => $sort,
    ];
}

it('renders Unreleased first, then releases newest-first', function () {
    $md = (new ChangelogWriter)->render([
        entry('1.0.0', ChangeType::Added, 'First', '2024-01-15'),
        entry('1.1.0', ChangeType::Added, 'Second', '2024-03-10'),
        entry('Unreleased', ChangeType::Added, 'WIP', null),
    ]);

    $unreleased = strpos($md, '[Unreleased]');
    $v11 = strpos($md, '[1.1.0]');
    $v10 = strpos($md, '[1.0.0]');

    expect($unreleased)->toBeLessThan($v11);
    expect($v11)->toBeLessThan($v10);
});

it('renders canonical English type headings regardless of app locale', function () {
    app()->setLocale('pt_PT');

    $md = (new ChangelogWriter)->render([
        entry('1.0.0', ChangeType::Added, 'x', '2024-01-15'),
        entry('1.0.0', ChangeType::Fixed, 'y', '2024-01-15', 1),
    ]);

    expect($md)->toContain('### Added')
        ->toContain('### Fixed')
        ->not->toContain('### Adicionado');
});

it('orders type groups by the configured type order', function () {
    $md = (new ChangelogWriter)->render([
        entry('1.0.0', ChangeType::Security, 's', '2024-01-15', 0),
        entry('1.0.0', ChangeType::Added, 'a', '2024-01-15', 1),
    ]);

    expect(strpos($md, '### Added'))->toBeLessThan(strpos($md, '### Security'));
});

it('includes the release date next to the version', function () {
    $md = (new ChangelogWriter)->render([
        entry('2.0.0', ChangeType::Added, 'x', '2024-05-25'),
    ]);

    expect($md)->toContain('## [2.0.0] - 2024-05-25');
});
