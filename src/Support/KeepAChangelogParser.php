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

            // Type header:  ### Added
            if (preg_match('/^###\s+(.+)$/', $trimmed, $m)) {
                $currentType = ChangeType::fromHeading($m[1]);

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

        // Strip surrounding brackets/links: [1.2.0] or [1.2.0](url)
        if (preg_match('/^\[([^\]]+)\](?:\([^)]*\))?\s*(?:-\s*(.+))?$/', $heading, $m)) {
            $version = trim($m[1]);
            $date = isset($m[2]) ? trim($m[2]) : null;
        } elseif (preg_match('/^([^\s-]+)\s*(?:-\s*(.+))?$/', $heading, $m)) {
            $version = trim($m[1]);
            $date = isset($m[2]) ? trim($m[2]) : null;
        } else {
            $version = $heading;
            $date = null;
        }

        $isReleased = strtolower($version) !== 'unreleased';
        $releasedAt = null;

        if ($isReleased && $date) {
            $ts = strtotime($date);
            if ($ts !== false) {
                $releasedAt = date('Y-m-d', $ts);
            }
        }

        return [$version, $releasedAt, $isReleased];
    }
}
