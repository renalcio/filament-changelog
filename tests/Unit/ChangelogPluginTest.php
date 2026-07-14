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
