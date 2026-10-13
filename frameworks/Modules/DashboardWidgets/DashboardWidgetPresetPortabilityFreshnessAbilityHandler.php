<?php
/**
 * Dashboard preset portability freshness validation.
 *
 * @package WPEssential\Modules\DashboardWidgets
 */

declare(strict_types=1);

namespace WPEssential\Modules\DashboardWidgets;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use InvalidArgumentException;
use WPEssential\Contracts\AbilityHandlerInterface;
use WPEssential\Platform\Auth\ExecutionContext;

/** Read-only validation adapter for Dashboard preset portability. */
final readonly class DashboardWidgetPresetPortabilityFreshnessAbilityHandler implements AbilityHandlerInterface {

	/**
	 * Construct the handler with the canonical service.
	 *
	 * @param DashboardWidgetPresetPortabilityFreshnessService $freshness Portability validation service.
	 */
	public function __construct( private DashboardWidgetPresetPortabilityFreshnessService $freshness ) {}

	/**
	 * Return a verified preset snapshot freshness decision.
	 *
	 * @param array            $input Canonical preset portability input.
	 * @param ExecutionContext $context Authorized invocation context.
	 * @return mixed
	 * @throws InvalidArgumentException On invalid canonical input.
	 */
	public function handle( array $input, ExecutionContext $context ): mixed {
		$keys = array_keys( $input );
		sort( $keys, SORT_STRING );
		if ( array( 'id', 'sha256' ) !== $keys ) {
			throw new InvalidArgumentException( 'Preset freshness requires exactly id and sha256 input.' );
		}
		$id     = $input['id'];
		$sha256 = $input['sha256'];
		if (
			! is_string( $id )
			|| ! is_string( $sha256 )
			|| preg_match( '/^[0-9a-f]{8}-[0-9a-f]{4}-[1-5][0-9a-f]{3}-[89ab][0-9a-f]{3}-[0-9a-f]{12}$/', $id ) !== 1
			|| preg_match( '/^[0-9a-f]{64}$/', $sha256 ) !== 1
		) {
			throw new InvalidArgumentException( 'Preset freshness requires canonical lowercase UUID and SHA-256.' );
		}

		return $this->freshness->check( $id, $sha256 );
	}
}
