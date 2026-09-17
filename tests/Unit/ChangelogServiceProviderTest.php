<?php

use Filament\Changelog\ChangelogServiceProvider;

afterEach(function () {
    config()->set('changelog.connection', null);
    config()->set('database.connections.changelog_sqlite_test', null);
});

it('creates the sqlite file and its directory when missing', function () {
    $directory = sys_get_temp_dir().'/changelog-sqlite-test-'.uniqid();
    $database = $directory.'/nested/changelog.sqlite';

    config()->set('changelog.connection', 'changelog_sqlite_test');
    config()->set('database.connections.changelog_sqlite_test', [
        'driver' => 'sqlite',
        'database' => $database,
    ]);

    expect($database)->not->toBeFile();

    $provider = new ChangelogServiceProvider(app());
    (new ReflectionMethod($provider, 'ensureSqliteDatabaseExists'))->invoke($provider);

    expect($database)->toBeFile();

    unlink($database);
    rmdir(dirname($database));
    rmdir($directory);
});

it('leaves an in-memory sqlite connection alone', function () {
    config()->set('changelog.connection', 'changelog_sqlite_test');
    config()->set('database.connections.changelog_sqlite_test', [
        'driver' => 'sqlite',
        'database' => ':memory:',
    ]);

    $provider = new ChangelogServiceProvider(app());
    (new ReflectionMethod($provider, 'ensureSqliteDatabaseExists'))->invoke($provider);
})->throwsNoExceptions();

it('ignores non-sqlite connections', function () {
    config()->set('changelog.connection', 'changelog_sqlite_test');
    config()->set('database.connections.changelog_sqlite_test', [
        'driver' => 'mysql',
        'database' => 'does-not-matter',
    ]);

    $provider = new ChangelogServiceProvider(app());
    (new ReflectionMethod($provider, 'ensureSqliteDatabaseExists'))->invoke($provider);
})->throwsNoExceptions();
