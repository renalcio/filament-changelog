<?php

namespace Filament\Changelog\Support;

use Illuminate\Support\Str;

/**
 * Vocabulário dos títulos de um CHANGELOG.md.
 *
 * O formato Keep a Changelog está escrito em inglês, mas os ficheiros são
 * escritos por pessoas, na língua delas. Um changelog em português com
 * "### Adicionado" era lido como se dissesse "Changed", e "## [Não lançado]"
 * entrava como uma versão chamada assim, já lançada.
 *
 * O leitor aceita o inglês canónico e também o rótulo que o próprio pacote
 * mostra em cada língua que traduz — acrescentar uma tradução ao pacote passa,
 * por si só, a ensinar o leitor a ler ficheiros nessa língua.
 */
class Headings
{
    /**
     * Grafias de "por lançar" que ninguém traduz mas toda a gente escreve.
     * O rótulo do pacote em português é "Por lançar"; quem escreve o ficheiro
     * à mão escreve quase sempre "Não lançado".
     *
     * @var array<int, string>
     */
    public const OUTRAS_POR_LANCAR = [
        'unreleased',
        'nao lancado',
        'nao publicado',
        'sem lancamento',
    ];

    /**
     * Compara títulos pelo que dizem, não por como estão escritos: sem acentos,
     * em minúsculas e sem espaços a mais.
     */
    public static function normalizar(string $titulo): string
    {
        return Str::of($titulo)->ascii()->lower()->squish()->toString();
    }

    /**
     * A secção que ainda não foi lançada — "Unreleased", "Não lançado", …
     */
    public static function ePorLancar(string $versao): bool
    {
        $normalizada = self::normalizar($versao);

        if ($normalizada === '') {
            return false;
        }

        if (in_array($normalizada, self::OUTRAS_POR_LANCAR, true)) {
            return true;
        }

        return in_array($normalizada, self::traduzidos('reader.unreleased'), true);
    }

    /**
     * O mesmo texto de tradução, em todas as línguas que o pacote conhece,
     * já normalizado para comparação.
     *
     * @return array<int, string>
     */
    public static function traduzidos(string $chave): array
    {
        $rotulos = [];

        foreach (self::linguas() as $lingua) {
            $rotulo = trans('changelog::changelog.'.$chave, [], $lingua);

            // Uma chave sem tradução volta como a própria chave.
            if (is_string($rotulo) && ! str_contains($rotulo, 'changelog::')) {
                $rotulos[] = self::normalizar($rotulo);
            }
        }

        return array_values(array_unique(array_filter($rotulos)));
    }

    /**
     * As línguas que o pacote traz, mais a da aplicação — que pode ter
     * publicado uma tradução própria, ou uma língua que o pacote não traz.
     *
     * @return array<int, string>
     */
    public static function linguas(): array
    {
        static $doPacote = null;

        if ($doPacote === null) {
            $pastas = glob(__DIR__.'/../../resources/lang/*', GLOB_ONLYDIR) ?: [];
            $doPacote = array_map('basename', $pastas);
        }

        return array_values(array_unique(array_filter([
            ...$doPacote,
            app()->getLocale(),
            (string) config('app.fallback_locale'),
        ])));
    }
}
