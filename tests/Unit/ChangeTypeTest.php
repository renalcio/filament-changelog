<?php

use Filament\Changelog\Enums\ChangeType;

it('resolves a type from a keep-a-changelog heading case-insensitively', function () {
    expect(ChangeType::fromHeading('Added'))->toBe(ChangeType::Added);
    expect(ChangeType::fromHeading('  SECURITY '))->toBe(ChangeType::Security);
    expect(ChangeType::fromHeading('nonsense'))->toBeNull();
});

it('exposes a fixed canonical English label for the file', function () {
    app()->setLocale('pt_PT');

    expect(ChangeType::Added->getCanonicalLabel())->toBe('Added');
    expect(ChangeType::Fixed->getCanonicalLabel())->toBe('Fixed');
});

it('translates the UI label with the app locale', function () {
    app()->setLocale('en');
    expect(ChangeType::Added->getLabel())->toBe('Added');

    app()->setLocale('pt_PT');
    expect(ChangeType::Added->getLabel())->toBe('Adicionado');

    app()->setLocale('pt_BR');
    expect(ChangeType::Added->getLabel())->toBe('Adicionado');
});
