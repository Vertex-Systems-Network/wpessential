<?php
/**
 * Read-only Dashboard preset portability Ability handler.
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

/** Serve a validated, redacted preset portability snapshot. */
final readonly class DashboardWidgetPresetPortabilityAbilityHandler implements AbilityHandlerInterface {

	/**
	 * Construct the portability snapshot handler.
	 *
	 * @param DashboardWidgetPresetPortabilityReadService $service Read-only snapshot service.
	 */
	public function __construct( private DashboardWidgetPresetPortabilityReadService $service ) {}

	/**
	 * Validate a preset ID and return its portable snapshot.
	 *
	 * @param array            $input Canonical input containing only the preset ID.
	 * @param ExecutionContext $context Authorized execution context.
	 * @return mixed
	 * @throws InvalidArgumentException If the identifier is invalid.
	 */
	public function handle( array $input, ExecutionContext $context ): mixed {
		if ( array_keys( $input ) !== array( 'id' ) ) {
			throw new InvalidArgumentException( 'Dashboard preset portability snapshot requires exactly one id.' );
		}

		$id = $input['id'];
		if (
			! is_string( $id )
			|| preg_match( '/^[0-9a-f]{8}-[0-9a-f]{4}-[1-5][0-9a-f]{3}-[89ab][0-9a-f]{3}-[0-9a-f]{12}$/', $id ) !== 1
		) {
			throw new InvalidArgumentException( 'Dashboard preset portability requires a lowercase RFC4122 UUID.' );
		}
		return $this->service->snapshot( $id );
	}
}
