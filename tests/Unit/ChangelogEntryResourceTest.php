<?php

use Filament\Changelog\Models\ChangelogEntry;
use Filament\Changelog\Resources\ChangelogEntryResource;

afterEach(function () {
    config()->set('changelog.model', ChangelogEntry::class);
    config()->set('changelog.cluster', null);
});

it('uses the default model by default', function () {
    expect(ChangelogEntryResource::getModel())->toBe(ChangelogEntry::class);
});

it('resolves a custom model from config', function () {
    config()->set('changelog.model', CustomChangelogEntryForResourceTest::class);

    expect(ChangelogEntryResource::getModel())->toBe(CustomChangelogEntryForResourceTest::class);
});

it('has no cluster by default', function () {
    expect(ChangelogEntryResource::getCluster())->toBeNull();
});

it('resolves a custom cluster from config', function () {
    config()->set('changelog.cluster', CustomClusterForResourceTest::class);

    expect(ChangelogEntryResource::getCluster())->toBe(CustomClusterForResourceTest::class);
});

it('hides itself from navigation when it has no cluster', function () {
    expect(ChangelogEntryResource::shouldRegisterNavigation())->toBeFalse();
});

it('registers navigation when nested inside a cluster', function () {
    config()->set('changelog.cluster', CustomClusterForResourceTest::class);

    expect(ChangelogEntryResource::shouldRegisterNavigation())->toBeTrue();
});

class CustomChangelogEntryForResourceTest extends ChangelogEntry {}

class CustomClusterForResourceTest {}
