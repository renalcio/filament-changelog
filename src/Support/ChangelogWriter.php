<?php

namespace Filament\Changelog\Support;

use Filament\Changelog\Enums\ChangeType;
use Illuminate\Support\Collection;

/**
 * Renders a collection of changelog entries back into a "Keep a Changelog"
 * formatted markdown document.
 */
class ChangelogWriter
{
    /**
     * @param  iterable<int, array<string, mixed>|object>  $entries
     */
    public function render(iterable $entries): string
    {
        $rows = collect($entries)->map(fn ($e) => (array) $e);

        $out = [];
        $out[] = '# Changelog';
        $out[] = '';
        $out[] = 'All notable changes to this project are documented in this file.';
        $out[] = '';
        $out[] = 'The format is based on [Keep a Changelog](https://keepachangelog.com/),';
        $out[] = 'and this project adheres to [Semantic Versioning](https://semver.org/).';
        $out[] = '';

        // Group by version, keeping unreleased first, then by date/version desc.
        $grouped = $rows->groupBy('version');

        $ordered = $grouped->sortBy(function (Collection $group, string $version) {
            if (strtolower($version) === 'unreleased') {
                return '~'; // sorts last ascending, so reverse below puts it first
            }

            $date = optional($group->first())['released_at'] ?? null;

            return ($date ? (is_string($date) ? $date : (string) $date) : '0000-00-00').'|'.$version;
        })->reverse();

        foreach ($ordered as $version => $group) {
            $out[] = $this->versionHeading($version, $group);
            $out[] = '';

            foreach ($this->typeOrder() as $type) {
                $forType = $group->filter(fn (array $e) => $this->typeValue($e['type']) === $type->value)
                    ->sortBy('sort');

                if ($forType->isEmpty()) {
                    continue;
                }

                $out[] = '### '.$type->getCanonicalLabel();
                foreach ($forType as $entry) {
                    $out[] = '- '.trim((string) $entry['description']);
                }
                $out[] = '';
            }
        }

        return rtrim(implode("\n", $out))."\n";
    }

    protected function versionHeading(string $version, Collection $group): string
    {
        if (strtolower($version) === 'unreleased') {
            return '## [Unreleased]';
        }

        $date = optional($group->first())['released_at'] ?? null;

        if ($date) {
            $date = is_string($date) ? $date : (string) $date;
            // Normalise possible datetime to Y-m-d
            $ts = strtotime($date);
            $date = $ts !== false ? date('Y-m-d', $ts) : $date;

            return "## [{$version}] - {$date}";
        }

        return "## [{$version}]";
    }

    /**
     * @return array<int, ChangeType>
     */
    protected function typeOrder(): array
    {
        $configured = config('changelog.types', []);

        if (empty($configured)) {
            return ChangeType::cases();
        }

        return collect($configured)
            ->map(fn ($t) => ChangeType::tryFrom($t))
            ->filter()
            ->values()
            ->all();
    }

    protected function typeValue(mixed $type): string
    {
        return $type instanceof ChangeType ? $type->value : (string) $type;
    }
}
