<?php

use Filament\Changelog\Models\ChangelogEntry;

afterEach(function () {
    config()->set('changelog.connection', null);
});

it('uses the default connection by default', function () {
    expect((new ChangelogEntry)->getConnectionName())->toBeNull();
});

it('resolves a custom connection from config', function () {
    config()->set('changelog.connection', 'other');

    expect((new ChangelogEntry)->getConnectionName())->toBe('other');
});
