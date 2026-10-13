<?php
/**
 * Authorized Dashboard Widget read Ability handler.
 *
 * @package WPEssential\Modules\DashboardWidgets
 */

declare(strict_types=1);

namespace WPEssential\Modules\DashboardWidgets;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use InvalidArgumentException;
use RuntimeException;
use WPEssential\Contracts\AbilityHandlerInterface;
use WPEssential\Platform\Auth\ExecutionContext;

/** Validate Dashboard Widget read requests and delegate authorized reads. */
final readonly class DashboardWidgetsReadAbilityHandler implements AbilityHandlerInterface {

	public const GET     = 'get';
	public const CATALOG = 'catalog';

	/**
	 * Create a read handler for one canonical action.
	 *
	 * @param DashboardWidgetsReadService $service Canonical read service.
	 * @param string                      $action Allowed read action.
	 * @throws InvalidArgumentException If the action is unsupported.
	 */
	public function __construct(
		private DashboardWidgetsReadService $service,
		private string $action,
	) {
		if ( ! in_array( $this->action, array( self::GET, self::CATALOG ), true ) ) {
			throw new InvalidArgumentException( 'Unsupported Dashboard Widgets read ability action.' );
		}
	}

	/**
	 * Dispatch an authorized Dashboard Widget read.
	 *
	 * @param array            $input Canonical read request.
	 * @param ExecutionContext $context Authorized execution context.
	 * @return mixed
	 * @throws RuntimeException If an unsupported action is invoked.
	 */
	public function handle( array $input, ExecutionContext $context ): mixed {
		return match ( $this->action ) {
			self::GET => $this->get( $input ),
			self::CATALOG => $this->catalog( $input ),
			default => throw new RuntimeException( 'Unsupported Dashboard Widgets read ability action.' ),
		};
	}

	/**
	 * Read a specific canonical Dashboard Widget definition.
	 *
	 * @param array<string,mixed> $input Request containing a definition ID.
	 * @return array|null
	 * @throws InvalidArgumentException If the ID is invalid.
	 */
	private function get( array $input ): ?array {
		$id = $input['id'] ?? null;
		if ( ! is_string( $id ) || '' === trim( $id ) ) {
			throw new InvalidArgumentException( 'Dashboard Widgets get requires a non-empty definition id.' );
		}

		return $this->service->get( trim( $id ) );
	}

	/**
	 * Read the canonical Dashboard Widget catalog.
	 *
	 * @param array<string,mixed> $input Must be empty.
	 * @return array
	 * @throws InvalidArgumentException If extra input is supplied.
	 */
	private function catalog( array $input ): array {
		if ( array() !== $input ) {
			throw new InvalidArgumentException( 'Dashboard Widgets catalog does not accept input.' );
		}

		return $this->service->catalog();
	}
}
