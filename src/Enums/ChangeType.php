<?php

namespace Filament\Changelog\Enums;

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
     * Resolve a type from a Keep a Changelog "### Heading", case-insensitively.
     */
    public static function fromHeading(string $heading): ?self
    {
        return self::tryFrom(strtolower(trim($heading)));
    }
}
