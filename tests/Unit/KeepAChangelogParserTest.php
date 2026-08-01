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

it('parses parenthesised dates and v-prefixed versions (GitLab style)', function () {
    $md = "## v19.1.1 (2026-06-25)\n### Bug fixes\n- Fixed the runner";

    $entries = (new KeepAChangelogParser)->parse($md);

    expect($entries[0])->toMatchArray([
        'version' => 'v19.1.1',
        'released_at' => '2026-06-25',
        'is_released' => true,
    ]);
});

it('parses linked version headings with a trailing date (conventional-changelog)', function () {
    $md = "## [v12.63.0](https://github.com/acme/app/compare/v12.62.0...v12.63.0) - 2026-07-07\n### Added\n- Thing";

    $entries = (new KeepAChangelogParser)->parse($md);

    expect($entries[0])->toMatchArray([
        'version' => 'v12.63.0',
        'released_at' => '2026-07-07',
    ]);
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

it('falls back to Changed for unrecognised headings (GitHub release notes)', function () {
    $md = "## 8.3.0 - 2026-07-03\n### What's Changed\n* Fixed a thing by @user\n* Added another by @user";

    $entries = (new KeepAChangelogParser)->parse($md);

    expect($entries)->toHaveCount(2);
    expect($entries[0])->toMatchArray([
        'version' => '8.3.0',
        'type' => ChangeType::Changed,
        'description' => 'Fixed a thing by @user',
    ]);
});

it('returns an empty array for content that is not keep-a-changelog', function () {
    expect((new KeepAChangelogParser)->parse('just some prose'))->toBe([]);
});

it('reads a changelog written in portuguese', function () {
    $md = <<<'MD'
    # Changelog

    ## [Não lançado]
    ### Adicionado
    - Ligar um assistente à loja
    ### Alterado
    - Produtos a repor passam a contar por armazém

    ## [1.3.0] — 2026-07-28
    ### Adicionado
    - Recibo de quitação em PDF
    ### Corrigido
    - Primeiro checkout do app nunca chegava a pagar
    ### Segurança
    - Endereço da ligação deixa de ser guardado em texto simples
    MD;

    $entries = (new KeepAChangelogParser)->parse($md);

    expect($entries)->toHaveCount(5);

    // A secção por lançar não é uma versão: não tem data e não está lançada.
    expect($entries[0])->toMatchArray([
        'version' => 'Não lançado',
        'is_released' => false,
        'released_at' => null,
        'type' => ChangeType::Added,
    ]);

    expect($entries[1]['type'])->toBe(ChangeType::Changed)
        ->and($entries[2]['type'])->toBe(ChangeType::Added)
        ->and($entries[3]['type'])->toBe(ChangeType::Fixed)
        ->and($entries[4]['type'])->toBe(ChangeType::Security);

    expect($entries[2])->toMatchArray([
        'version' => '1.3.0',
        'is_released' => true,
        'released_at' => '2026-07-28',
    ]);
});

it('recognises the unreleased section however it is spelled', function () {
    foreach (['Unreleased', 'UNRELEASED', 'Não lançado', 'Nao lancado', 'Por lançar'] as $titulo) {
        $entries = (new KeepAChangelogParser)->parse("## [{$titulo}]\n### Added\n- Uma coisa");

        expect($entries[0]['is_released'])->toBeFalse("'{$titulo}' devia contar como por lançar")
            ->and($entries[0]['released_at'])->toBeNull();
    }
});

it('a version heading that merely contains a word is still a version', function () {
    // "1.4.0 — Lançamento de Agosto" é uma versão lançada, não uma secção
    // por lançar, e "Adicionado suporte a X" não é um cabeçalho de tipo.
    $entries = (new KeepAChangelogParser)->parse(
        "## [1.4.0] - 2026-08-01\n### Adicionado\n- Uma coisa"
    );

    expect($entries[0]['version'])->toBe('1.4.0')
        ->and($entries[0]['is_released'])->toBeTrue()
        ->and($entries[0]['released_at'])->toBe('2026-08-01');
});
