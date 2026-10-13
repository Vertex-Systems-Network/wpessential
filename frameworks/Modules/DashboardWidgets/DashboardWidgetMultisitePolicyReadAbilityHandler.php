<?php
/**
 * Authorized Dashboard multisite policy read Ability.
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

/** Read validated multisite widget policies in an authorized context. */
final readonly class DashboardWidgetMultisitePolicyReadAbilityHandler implements AbilityHandlerInterface {

	public const GET       = 'get';
	public const CATALOG   = 'catalog';
	public const EFFECTIVE = 'effective';

	/**
	 * Construct the read handler and verify its action.
	 *
	 * @param DashboardWidgetMultisitePolicyReadService $service Canonical read service.
	 * @param string                                    $action Read action.
	 * @throws InvalidArgumentException If action is unsupported.
	 */
	public function __construct(
		private DashboardWidgetMultisitePolicyReadService $service,
		private string $action,
	) {
		if ( ! in_array( $action, array( self::GET, self::CATALOG, self::EFFECTIVE ), true ) ) {
			throw new InvalidArgumentException( 'Unsupported Dashboard Multisite policy read action.' );
		}
	}

	/**
	 * Dispatch a validated multisite policy read.
	 *
	 * @param array            $input Canonical input.
	 * @param ExecutionContext $context Authorized context.
	 * @return mixed
	 */
	public function handle( array $input, ExecutionContext $context ): mixed {
		return match ( $this->action ) {
			self::GET => $this->get( $input ),
			self::CATALOG => $this->catalog( $input ),
			self::EFFECTIVE => $this->effective( $input, $context ),
		};
	}

	/**
	 * Read a multisite policy for its canonical UUID.
	 *
	 * @param array<string,mixed> $input Canonical policy identifier input.
	 * @return array|null
	 * @throws InvalidArgumentException On an invalid UUID or extra input.
	 */
	private function get( array $input ): ?array {
		if ( array( 'id' ) !== array_keys( $input )
			|| ! is_string( $input['id'] )
            || preg_match('/^[0-9a-f]{8}-[0-9a-f]{4}-[1-5][0-9a-f]{3}-[89ab][0-9a-f]{3}-[0-9a-f]{12}$/', $input['id']) !== 1
		) {
            throw new InvalidArgumentException('Dashboard Multisite policy get requires one lowercase RFC 4122 UUID.');
		}
		return $this->service->get( $input['id'] );
	}
	/**
	 * List all authorized multisite policies.
	 *
	 * @param array<string,mixed> $input Must be empty.
	 * @return array
	 * @throws InvalidArgumentException If extra input is supplied.
	 */
	private function catalog( array $input ): array {
		if ( array() !== $input ) {
			throw new InvalidArgumentException( 'Dashboard Multisite policy catalog accepts no input.' );
		}
		return $this->service->catalog();
	}
	/**
	 * Read the effective policy for the authorized caller.
	 *
	 * @param array            $input Must be empty.
	 * @param ExecutionContext $context Authorized context.
	 * @return array
	 * @throws InvalidArgumentException If extra input is supplied.
	 */
	private function effective( array $input, ExecutionContext $context ): array {
		if ( array() !== $input ) {
			throw new InvalidArgumentException( 'Dashboard Multisite effective policy accepts no input.' );
		}
		return $this->service->effective( $context );
	}
}
