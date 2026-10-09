<?php

declare(strict_types=1);

namespace WPEssential\Modules\DashboardWidgets;

if (!defined('ABSPATH')) {
    exit;
}

use InvalidArgumentException;
use Throwable;

final readonly class DashboardWidgetPresetPortabilityFreshnessService
{
    public function __construct(private DashboardWidgetPresetPortabilityReadService $snapshots) {}

    /**
     * Compare a non-authenticating fingerprint with the current Published
     * canonical preset snapshot. Never returns the snapshot or current hash.
     *
     * @return array{status:string,current:bool}
     */
    public function check(string $presetId, string $sha256): array
    {
        if (
            preg_match('/^[0-9a-f]{8}-[0-9a-f]{4}-[1-5][0-9a-f]{3}-[89ab][0-9a-f]{3}-[0-9a-f]{12}$/', $presetId) !== 1
            || preg_match('/^[0-9a-f]{64}$/', $sha256) !== 1
        ) {
            throw new InvalidArgumentException('Preset fingerprint freshness requires a canonical UUID and lowercase SHA-256.');
        }

        try {
            $snapshot = $this->snapshots->snapshot($presetId);
        } catch (Throwable) {
            // Malformed current Published definitions must never be interpreted
            // as an accepted/stale snapshot or reveal their untrusted content.
            return ['status' => 'invalid_catalog', 'current' => false];
        }
        if ($snapshot === null) {
            return ['status' => 'unavailable', 'current' => false];
        }

        $matches = hash_equals($snapshot['sha256'], $sha256);
        return ['status' => $matches ? 'match' : 'stale', 'current' => $matches];
    }
}
