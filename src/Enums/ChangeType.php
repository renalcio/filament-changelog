<?php

namespace Filament\Changelog\Enums;

use Filament\Changelog\Support\Headings;
use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasIcon;
use Filament\Support\Contracts\HasLabel;
use Filament\Support\Icons\Heroicon;

enum ChangeType: string implements HasColor, HasIcon, HasLabel
{
    case Added = 'added';
    case Changed = 'changed';
    case Deprecated = 'deprecated';
    case Removed = 'removed';
    case Fixed = 'fixed';
    case Security = 'security';

    /**
     * Translated label for the UI (badges, select options, reader page).
     */
    public function getLabel(): string
    {
        return __('changelog::changelog.types.'.$this->value);
    }

    /**
     * Canonical English heading used in the CHANGELOG.md file. MUST stay in
     * English so the Keep a Changelog format and the import round-trip work
     * regardless of the app locale.
     */
    public function getCanonicalLabel(): string
    {
        return match ($this) {
            self::Added => 'Added',
            self::Changed => 'Changed',
            self::Deprecated => 'Deprecated',
            self::Removed => 'Removed',
            self::Fixed => 'Fixed',
            self::Security => 'Security',
        };
    }

    public function getColor(): string
    {
        return match ($this) {
            self::Added => 'success',
            self::Changed => 'info',
            self::Deprecated => 'warning',
            self::Removed => 'danger',
            self::Fixed => 'primary',
            self::Security => 'danger',
        };
    }

    public function getIcon(): Heroicon
    {
        return match ($this) {
            self::Added => Heroicon::OutlinedPlusCircle,
            self::Changed => Heroicon::OutlinedArrowPath,
            self::Deprecated => Heroicon::OutlinedExclamationTriangle,
            self::Removed => Heroicon::OutlinedMinusCircle,
            self::Fixed => Heroicon::OutlinedWrench,
            self::Security => Heroicon::OutlinedShieldExclamation,
        };
    }

    /**
     * Resolve a type from a Keep a Changelog "### Heading".
     *
     * Aceita o inglês canónico ("Added") e o rótulo em qualquer língua que o
     * pacote traduza ("Adicionado"), sem se importar com acentos, maiúsculas
     * ou espaços a mais. Um changelog escrito em português deixou de ser lido
     * como se estivesse todo em "Changed".
     */
    public static function fromHeading(string $heading): ?self
    {
        $normalizado = Headings::normalizar($heading);

        if ($normalizado === '') {
            return null;
        }

        foreach (self::cases() as $tipo) {
            if ($normalizado === $tipo->value) {
                return $tipo;
            }

            if (in_array($normalizado, Headings::traduzidos('types.'.$tipo->value), true)) {
                return $tipo;
            }
        }

        return null;
    }
}
