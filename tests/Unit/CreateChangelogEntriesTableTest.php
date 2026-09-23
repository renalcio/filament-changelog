<?php

use Illuminate\Support\Facades\Schema;

it('is safe to run twice against the same connection', function () {
    // TestCase::defineDatabaseMigrations() already ran this migration once;
    // running up() again must be a no-op instead of failing on "table already exists".
    $migration = require __DIR__.'/../../database/migrations/create_changelog_entries_table.php';

    $migration->up();

    expect(Schema::hasTable(config('changelog.table', 'changelog_entries')))->toBeTrue();
});
