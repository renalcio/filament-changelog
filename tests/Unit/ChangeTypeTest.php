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

it('resolves types from the headings of every language the package speaks', function () {
    // Um CHANGELOG.md é escrito por pessoas, na língua delas. O leitor tem de
    // reconhecer o rótulo que o próprio pacote mostra em cada língua.
    expect(ChangeType::fromHeading('Adicionado'))->toBe(ChangeType::Added)
        ->and(ChangeType::fromHeading('Alterado'))->toBe(ChangeType::Changed)
        ->and(ChangeType::fromHeading('Corrigido'))->toBe(ChangeType::Fixed)
        ->and(ChangeType::fromHeading('Removido'))->toBe(ChangeType::Removed)
        ->and(ChangeType::fromHeading('Descontinuado'))->toBe(ChangeType::Deprecated)
        ->and(ChangeType::fromHeading('Segurança'))->toBe(ChangeType::Security);
});

it('reads a heading regardless of the accents, the case or the app language', function () {
    app()->setLocale('en');

    // Ficheiro em português numa aplicação em inglês: continua a ser lido.
    expect(ChangeType::fromHeading('SEGURANCA'))->toBe(ChangeType::Security)
        ->and(ChangeType::fromHeading('  segurança  '))->toBe(ChangeType::Security)
        ->and(ChangeType::fromHeading('Adicionado'))->toBe(ChangeType::Added);

    app()->setLocale('pt_PT');

    // E o inverso: ficheiro em inglês numa aplicação em português.
    expect(ChangeType::fromHeading('Added'))->toBe(ChangeType::Added)
        ->and(ChangeType::fromHeading('Fixed'))->toBe(ChangeType::Fixed);
});

it('não inventa um tipo para um título que não conhece', function () {
    // O leitor devolve null; é o parser que decide o que fazer com isso.
    expect(ChangeType::fromHeading("What's Changed"))->toBeNull()
        ->and(ChangeType::fromHeading('Adicionados novos ecrãs'))->toBeNull()
        ->and(ChangeType::fromHeading(''))->toBeNull();
});
