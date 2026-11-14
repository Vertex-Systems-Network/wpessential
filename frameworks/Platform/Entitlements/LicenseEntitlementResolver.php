<?php
/**
 * Maps the stored WPEssential licence to a Pro entitlement snapshot.
 *
 * Only the Pro add-on consumes this. Free modules never depend on it, so a missing,
 * expired or invalid licence can never lock a Free feature.
 *
 * @package WPEssential
 */

declare(strict_types=1);

namespace WPEssential\Platform\Entitlements;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use WPEssential\Platform\Admin\Connect\LicenseService;

final class LicenseEntitlementResolver {

	/** A licence not confirmed by the server for this long becomes read-only. */
	public const STALE_AFTER = 30 * 86400;

	private const EXPIRED_ERRORS   = [ 'wpe_license_expired', 'wpe_license_major_ended', 'wpe_license_cancelled' ];
	private const SUSPENDED_ERRORS = [ 'wpe_license_revoked' ];

	/** Snapshot for the licence stored on this site. */
	public static function current( ?int $now = null ): ProductEntitlementSnapshot {
		$service = new LicenseService();
		$data    = $service->data();
		$data['pro'] = $data['pro'] || $service->config()['provider'] !== 'custom';
		return self::resolve( $data, $now ?? time() );
	}

	/**
	 * @param array<string,mixed> $license LicenseService::data() shape.
	 */
	public static function resolve( array $license, int $now ): ProductEntitlementSnapshot {
		$key     = is_string( $license['key'] ?? null ) ? $license['key'] : '';
		$status  = is_string( $license['status'] ?? null ) ? $license['status'] : 'inactive';
		$error   = is_string( $license['error'] ?? null ) ? $license['error'] : '';
		$plan    = is_string( $license['plan'] ?? null ) ? strtolower( $license['plan'] ) : '';
		$expires = self::timestamp( $license['expires'] ?? '' );

		if ( $key === '' ) {
			return self::snapshot( ProductEntitlementState::VerificationUnavailable, 'license_missing' );
		}

		if ( $status === 'active' ) {
			if ( empty( $license['pro'] ) ) {
				return self::snapshot( ProductEntitlementState::VerificationUnavailable, 'license_not_pro' );
			}
			$verified = (int) ( $license['verified'] ?? 0 );
			$verified = $verified > 0 ? $verified : (int) ( $license['checked'] ?? 0 );
			if ( $verified > 0 && $now - $verified > self::STALE_AFTER ) {
				return self::snapshot( ProductEntitlementState::VerificationStale, 'license_check_stale' );
			}
			if ( $expires !== null && $now > $expires ) {
				// The server still answers "active" during its grace days.
				return self::snapshot( ProductEntitlementState::Grace, 'license_in_grace' );
			}
			if ( str_contains( $plan, 'trial' ) ) {
				return self::snapshot( ProductEntitlementState::TrialActive );
			}
			return self::snapshot( ProductEntitlementState::ProActive );
		}

		if ( in_array( $error, self::SUSPENDED_ERRORS, true ) ) {
			return self::snapshot( ProductEntitlementState::Suspended, 'license_revoked' );
		}
		if ( in_array( $error, self::EXPIRED_ERRORS, true ) || ( $expires !== null && $now > $expires ) ) {
			return self::snapshot( ProductEntitlementState::Expired, 'license_expired' );
		}
		return self::snapshot( ProductEntitlementState::VerificationUnavailable, 'license_inactive' );
	}

	private static function snapshot( ProductEntitlementState $state, ?string $reason = null ): ProductEntitlementSnapshot {
		return new ProductEntitlementSnapshot( $state, $reason );
	}

	private static function timestamp( mixed $value ): ?int {
		if ( ! is_string( $value ) || trim( $value ) === '' ) {
			return null;
		}
		$time = strtotime( trim( $value ) . ( preg_match( '/^\d{4}-\d{2}-\d{2}$/', trim( $value ) ) ? ' 23:59:59 UTC' : '' ) );
		return $time === false ? null : $time;
	}
}
