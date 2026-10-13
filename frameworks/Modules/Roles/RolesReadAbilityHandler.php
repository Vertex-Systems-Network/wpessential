<?php
/**
 * Authorized Roles read Ability handler.
 *
 * @package WPEssential\Modules\Roles
 */

declare(strict_types=1);

namespace WPEssential\Modules\Roles;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use InvalidArgumentException;
use RuntimeException;
use WPEssential\Contracts\AbilityHandlerInterface;
use WPEssential\Platform\Auth\ExecutionContext;

/** Read authorized Roles definitions using a bounded action dispatch. */
final readonly class RolesReadAbilityHandler implements AbilityHandlerInterface {

	public const CATALOG = 'catalog';
	public const IMPACT  = 'impact';

	/**
	 * Bind a canonical service to an allowlisted read action.
	 *
	 * @param RolesReadService $service Canonical read service.
	 * @param string           $action Allowlisted action name.
	 * @throws InvalidArgumentException On an unsupported action.
	 */
	public function __construct(
		private RolesReadService $service,
		private string $action,
	) {
		if ( ! in_array( $this->action, array( self::CATALOG, self::IMPACT ), true ) ) {
			throw new InvalidArgumentException( 'Unsupported Roles read ability action.' );
		}
	}

	/**
	 * Dispatch the requested read operation with a fail-closed default.
	 *
	 * @param array            $input Requested input.
	 * @param ExecutionContext $context Caller context.
	 * @return mixed
	 * @throws RuntimeException On unsupported action.
	 */
	public function handle( array $input, ExecutionContext $context ): mixed {
		switch ( $this->action ) {
			case self::CATALOG:
				return $this->service->roleCatalog( $context );
			case self::IMPACT:
				return $this->impact( $input, $context );
			default:
				throw new RuntimeException( 'Unsupported Roles read ability action.' );
		}
	}

	/**
	 * Read capability impact for the caller.
	 *
	 * @param array<string,mixed> $input Capability lookup input.
	 * @param ExecutionContext    $context Current execution context.
	 * @return array
	 * @throws InvalidArgumentException If capability is missing.
	 */
	private function impact( array $input, ExecutionContext $context ): array {
		$capability = $input['capability'] ?? null;
		if ( ! is_string( $capability ) ) {
			throw new InvalidArgumentException( 'Roles capability-impact requires a capability string.' );
		}

		return $this->service->capabilityImpact( $capability, $context );
	}
}
