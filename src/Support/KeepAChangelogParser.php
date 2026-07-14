<?php

namespace Filament\Changelog\Support;

use Filament\Changelog\Enums\ChangeType;

/**
 * Parses a "Keep a Changelog" formatted markdown string into a flat list of
 * entries. Each entry is an associative array ready to be persisted:
 *
 *   ['version' => '1.2.0', 'released_at' => '2024-01-15', 'is_released' => true,
 *    'type' => ChangeType, 'description' => 'Added dark mode', 'sort' => 0]
 *
 * @see https://keepachangelog.com/
 */
class KeepAChangelogParser
{
    /**
     * @return array<int, array<string, mixed>>
     */
    public function parse(string $markdown): array
    {
        $entries = [];
        $sort = 0;

        $currentVersion = null;
        $currentReleasedAt = null;
        $currentIsReleased = false;
        $currentType = null;

        $lines = preg_split('/\r\n|\r|\n/', $markdown) ?: [];

        foreach ($lines as $line) {
            $trimmed = rtrim($line);

            // Version header:  ## [1.2.0] - 2024-01-15   |   ## [Unreleased]
            if (preg_match('/^##\s+(.+)$/', $trimmed, $m)) {
                [$currentVersion, $currentReleasedAt, $currentIsReleased] = $this->parseVersionHeading($m[1]);
                $currentType = null;

                continue;
            }

            // Type header:  ### Added   (an unrecognised heading such as the
            // GitHub-release "### What's Changed" falls back to "Changed", so
            // release-notes style changelogs still parse).
            if (preg_match('/^###\s+(.+)$/', $trimmed, $m)) {
                $currentType = ChangeType::fromHeading($m[1]) ?? ChangeType::Changed;

                continue;
            }

            // Bullet line:  - Something changed   |   * Something changed
            if ($currentVersion !== null && $currentType !== null
                && preg_match('/^\s*[-*]\s+(.+)$/', $trimmed, $m)) {
                $description = trim($m[1]);

                if ($description === '') {
                    continue;
                }

                $entries[] = [
                    'version' => $currentVersion,
                    'released_at' => $currentReleasedAt,
                    'is_released' => $currentIsReleased,
                    'type' => $currentType,
                    'description' => $description,
                    'sort' => $sort++,
                ];
            }
        }

        return $entries;
    }

    /**
     * @return array{0: string, 1: ?string, 2: bool}
     */
    protected function parseVersionHeading(string $heading): array
    {
        $heading = trim($heading);

        // Version token: bracketed [1.2.0] (optionally linked), else the first
        // whitespace/paren-delimited token (1.2.0, v19.1.1, …).
        if (preg_match('/^\[([^\]]+)\]/', $heading, $m)) {
            $version = trim($m[1]);
        } elseif (preg_match('/^([^\s(]+)/', $heading, $m)) {
            $version = trim($m[1]);
        } else {
            $version = $heading;
        }

        $isReleased = strtolower($version) !== 'unreleased';
        $releasedAt = $isReleased ? $this->extractDate($heading, $version) : null;

        return [$version, $releasedAt, $isReleased];
    }

    /**
     * Find a release date in a version heading, supporting several styles:
     * "- 2024-03-10", "(2026-06-25)", "- May 25, 2024", …
     */
    protected function extractDate(string $heading, string $version): ?string
    {
        // Prefer an explicit ISO date anywhere after the version token.
        $afterVersion = trim((string) preg_replace('/^\S+/', '', $heading));

        if (preg_match('/(\d{4}-\d{2}-\d{2})/', $afterVersion, $m)) {
            return $m[1];
        }

        // Fall back to a trailing "- <date>" or "(<date>)" segment.
        if (preg_match('/[-(]\s*([^)]+?)\s*\)?$/', $afterVersion, $m)) {
            $ts = strtotime(trim($m[1]));

            if ($ts !== false) {
                return date('Y-m-d', $ts);
            }
        }

        return null;
    }
}
