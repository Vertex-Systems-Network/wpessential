<?php
/**
 * Dashboard preset fingerprint freshness lookup.
 *
 * @package WPEssential\Modules\DashboardWidgets
 */

declare(strict_types=1);

namespace WPEssential\Modules\DashboardWidgets;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use InvalidArgumentException;
use Throwable;

/** Compare a preset fingerprint without revealing its canonical snapshot. */
final readonly class DashboardWidgetPresetPortabilityFreshnessService {

	/**
	 * Construct the freshness service.
	 *
	 * @param DashboardWidgetPresetPortabilityReadService $snapshots Canonical snapshot provider.
	 */
	public function __construct( private DashboardWidgetPresetPortabilityReadService $snapshots ) {}

	/**
	 * Compare a non-authenticating fingerprint with the current Published
	 * canonical preset snapshot. Never returns the snapshot or current hash.
	 *
	 * @param string $presetId Canonical preset UUID.
	 * @param string $sha256 Canonical lowercase SHA-256 fingerprint.
	 * @return array{status:string,current:bool}
	 * @throws InvalidArgumentException If the fingerprint input is malformed.
	 */
	public function check(string $presetId, string $sha256): array
	{
		if (
			preg_match('/^[0-9a-f]{8}-[0-9a-f]{4}-[1-5][0-9a-f]{3}-[89ab][0-9a-f]{3}-[0-9a-f]{12}$/', $presetId) !== 1
			|| preg_match( '/^[0-9a-f]{64}$/', $sha256 ) !== 1
		) {
			throw new InvalidArgumentException(
				'Preset fingerprint freshness requires a canonical UUID and lowercase SHA-256.'
			);
		}

		try {
			$snapshot = $this->snapshots->snapshot($presetId);
		} catch ( Throwable ) {
			// Malformed current Published definitions must never be interpreted
			// as an accepted/stale snapshot or reveal their untrusted content.
			return array(
				'status'  => 'invalid_catalog',
				'current' => false,
			);
		}
		if ( null === $snapshot ) {
			return array(
				'status'  => 'unavailable',
				'current' => false,
			);
		}

		$matches = hash_equals( $snapshot['sha256'], $sha256 );
		return array(
			'status'  => $matches ? 'match' : 'stale',
			'current' => $matches,
		);
	}
}
