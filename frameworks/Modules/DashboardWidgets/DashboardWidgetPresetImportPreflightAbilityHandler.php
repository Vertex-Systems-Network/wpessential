<?php
/**
 * Dashboard preset import preflight Ability handler.
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

/** Validate incoming dashboard widget snapshots before import. */
final readonly class DashboardWidgetPresetImportPreflightAbilityHandler implements AbilityHandlerInterface {

	/**
	 * Construct the preflight handler.
	 *
	 * @param DashboardWidgetPresetImportPreflightService $preflight Canonical validation service.
	 */
	public function __construct( private DashboardWidgetPresetImportPreflightService $preflight ) {}

	/**
	 * Validate the snapshot input and perform preflight checks.
	 *
	 * @param array            $input Canonical snapshot-only input.
	 * @param ExecutionContext $context Authorized execution context.
	 * @return mixed
	 * @throws InvalidArgumentException When input is invalid.
	 */
	public function handle( array $input, ExecutionContext $context ): mixed {
		if ( array_keys( $input ) !== array( 'snapshot' ) || ! is_array( $input['snapshot'] ) ) {
			throw new InvalidArgumentException(
				'Dashboard preset import preflight requires exactly one snapshot object.'
			);
		}

		return $this->preflight->preflight( $input['snapshot'] );
	}
}
