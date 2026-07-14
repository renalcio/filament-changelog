<?php

use Filament\Changelog\Enums\ChangeType;
use Filament\Changelog\Support\KeepAChangelogParser;

it('parses versions, dates, types and descriptions', function () {
    $md = <<<'MD'
    # Changelog

    ## [Unreleased]
    ### Added
    - Dark mode

    ## [1.1.0] - 2024-03-10
    ### Added
    - PDF export
    - Global search
    ### Fixed
    - Startup crash
    MD;

    $entries = (new KeepAChangelogParser)->parse($md);

    expect($entries)->toHaveCount(4);

    expect($entries[0])->toMatchArray([
        'version' => 'Unreleased',
        'is_released' => false,
        'released_at' => null,
        'type' => ChangeType::Added,
        'description' => 'Dark mode',
    ]);

    expect($entries[1])->toMatchArray([
        'version' => '1.1.0',
        'is_released' => true,
        'released_at' => '2024-03-10',
        'type' => ChangeType::Added,
        'description' => 'PDF export',
    ]);

    expect($entries[3]['type'])->toBe(ChangeType::Fixed);
});

it('keeps a stable sort order across the whole document', function () {
    $md = "## [1.0.0] - 2024-01-01\n### Added\n- A\n- B\n- C";

    $entries = (new KeepAChangelogParser)->parse($md);

    expect(array_column($entries, 'sort'))->toBe([0, 1, 2]);
});

it('supports asterisk bullets and linked version headings', function () {
    $md = "## [1.0.0](https://example.com/releases/1.0.0) - 2024-01-01\n### Added\n* Something";

    $entries = (new KeepAChangelogParser)->parse($md);

    expect($entries)->toHaveCount(1);
    expect($entries[0]['version'])->toBe('1.0.0');
    expect($entries[0]['description'])->toBe('Something');
});

it('ignores bullets that appear before any version/type heading', function () {
    $md = "- orphan bullet\n\n## [1.0.0] - 2024-01-01\n### Added\n- real";

    $entries = (new KeepAChangelogParser)->parse($md);

    expect($entries)->toHaveCount(1);
    expect($entries[0]['description'])->toBe('real');
});

it('returns an empty array for content that is not keep-a-changelog', function () {
    expect((new KeepAChangelogParser)->parse('just some prose'))->toBe([]);
});
