<?php

use Filament\Changelog\Support\ChangelogWriter;
use Filament\Changelog\Support\KeepAChangelogParser;

it('round-trips a document: parse then render preserves the structure', function () {
    $source = <<<'MD'
    ## [Unreleased]
    ### Added
    - Dark mode

    ## [1.1.0] - 2024-03-10
    ### Added
    - PDF export
    - Global search
    ### Changed
    - Faster listing
    ### Security
    - Patched dependency

    ## [1.0.0] - 2024-01-15
    ### Added
    - First public release
    MD;

    $entries = (new KeepAChangelogParser)->parse($source);
    $rendered = (new ChangelogWriter)->render($entries);

    // Re-parsing the rendered output yields the same logical entries.
    $reparsed = (new KeepAChangelogParser)->parse($rendered);

    expect($reparsed)->toHaveCount(count($entries));

    $normalise = fn (array $rows) => collect($rows)
        ->map(fn ($e) => [
            'version' => $e['version'],
            'released_at' => $e['released_at'],
            'type' => $e['type']->value,
            'description' => $e['description'],
        ])
        ->all();

    expect($normalise($reparsed))->toEqual($normalise($entries));
});
