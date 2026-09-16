<?php

use Filament\Changelog\ChangelogPlugin;
use Filament\Support\Icons\Heroicon;

it('has a stable id', function () {
    expect(ChangelogPlugin::make()->getId())->toBe('changelog');
});

it('exposes fluent navigation overrides', function () {
    $plugin = ChangelogPlugin::make()
        ->navigationLabel('What\'s new')
        ->navigationIcon(Heroicon::OutlinedSparkles)
        ->navigationGroup('Docs')
        ->navigationSort(3);

    expect($plugin->getNavigationLabel())->toBe('What\'s new');
    expect($plugin->getNavigationIcon())->toBe(Heroicon::OutlinedSparkles);
    expect($plugin->getNavigationGroup())->toBe('Docs');
    expect($plugin->getNavigationSort())->toBe(3);
});

it('evaluates closures for fluent values', function () {
    $plugin = ChangelogPlugin::make()->navigationLabel(fn () => 'Computed');

    expect($plugin->getNavigationLabel())->toBe('Computed');
});

it('defaults navigation registration to true and honours overrides', function () {
    expect(ChangelogPlugin::make()->shouldRegisterNavigation())->toBeTrue();
    expect(ChangelogPlugin::make()->registerNavigation(false)->shouldRegisterNavigation())->toBeFalse();
});

it('defers management authorization to policy by default', function () {
    expect(ChangelogPlugin::make()->isManageAuthorized())->toBeNull();
});

it('resolves an explicit management gate', function () {
    expect(ChangelogPlugin::make()->canManage(false)->isManageAuthorized())->toBeFalse();
    expect(ChangelogPlugin::make()->canManage(fn () => true)->isManageAuthorized())->toBeTrue();
});

it('configures the changelog source fluently', function () {
    $plugin = ChangelogPlugin::make()->source('file')->file('docs/CHANGELOG.md');

    expect($plugin->getSource())->toBe('file');
    expect($plugin->getFile())->toBe('docs/CHANGELOG.md');
});

it('fromFile() sets both source and path', function () {
    $plugin = ChangelogPlugin::make()->fromFile('/srv/app/CHANGELOG.md');

    expect($plugin->getSource())->toBe('file');
    expect($plugin->getFile())->toBe('/srv/app/CHANGELOG.md');
});

it('fromUrl() points the file source at a remote URL', function () {
    $url = 'https://raw.githubusercontent.com/acme/app/main/CHANGELOG.md';
    $plugin = ChangelogPlugin::make()->fromUrl($url);

    expect($plugin->getSource())->toBe('file');
    expect($plugin->getFile())->toBe($url);
});

it('leaves every optional setting unset (null) by default', function () {
    $plugin = ChangelogPlugin::make();

    expect($plugin->getPerPage())->toBeNull();
    expect($plugin->isSearchable())->toBeNull();
    expect($plugin->isFilterableByVersion())->toBeNull();
    expect($plugin->getDateFormat())->toBeNull();
    expect($plugin->getChangeTypes())->toBeNull();
    expect($plugin->getSlug())->toBeNull();
    expect($plugin->getResourceSlug())->toBeNull();
    expect($plugin->getRemoteCacheTtl())->toBeNull();
    expect($plugin->getPolicy())->toBeNull();
    expect($plugin->isMultiProject())->toBeNull();
    expect($plugin->getCluster())->toBeNull();
});

it('exposes fluent reader options', function () {
    $plugin = ChangelogPlugin::make()
        ->perPage(20)
        ->searchable(false)
        ->filterableByVersion(fn () => true);

    expect($plugin->getPerPage())->toBe(20);
    expect($plugin->isSearchable())->toBeFalse();
    expect($plugin->isFilterableByVersion())->toBeTrue();
});

it('exposes fluent formatting, slug and advanced options', function () {
    $plugin = ChangelogPlugin::make()
        ->dateFormat('Y-m-d')
        ->changeTypes(['added', 'fixed'])
        ->slug('releases')
        ->resourceSlug('releases/manage')
        ->remoteCacheTtl(0)
        ->policy('App\\Policies\\ChangelogEntryPolicy')
        ->multiProject();

    expect($plugin->getDateFormat())->toBe('Y-m-d');
    expect($plugin->getChangeTypes())->toBe(['added', 'fixed']);
    expect($plugin->getSlug())->toBe('releases');
    expect($plugin->getResourceSlug())->toBe('releases/manage');
    expect($plugin->getRemoteCacheTtl())->toBe(0);
    expect($plugin->getPolicy())->toBe('App\\Policies\\ChangelogEntryPolicy');
    expect($plugin->isMultiProject())->toBeTrue();
});

it('configures the resource cluster fluently', function () {
    $plugin = ChangelogPlugin::make()->cluster('App\\Filament\\Clusters\\Docs');

    expect($plugin->getCluster())->toBe('App\\Filament\\Clusters\\Docs');
});
