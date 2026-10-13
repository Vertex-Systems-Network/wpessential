<?php
/**
 * Authorized Dashboard widget preset read Ability adapter.
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

/** Validate preset read requests and dispatch canonical service operations. */
final readonly class DashboardWidgetPresetReadAbilityHandler implements AbilityHandlerInterface {

	public const GET       = 'get';
	public const CATALOG   = 'catalog';
	public const EFFECTIVE = 'effective';

	/**
	 * Select an authorized preset read action.
	 *
	 * @param DashboardWidgetPresetReadService $service Canonical preset reader.
	 * @param string                           $action Selected read action.
	 * @throws InvalidArgumentException If the action is unsupported.
	 */
	public function __construct(
		private DashboardWidgetPresetReadService $service,
		private string $action,
	) {
		if ( ! in_array( $this->action, array( self::GET, self::CATALOG, self::EFFECTIVE ), true ) ) {
			throw new InvalidArgumentException( 'Unsupported Dashboard Widget preset read action.' );
		}
	}

	/**
	 * Dispatch an authorized preset read to the canonical service.
	 *
	 * @param array            $input Canonical read input.
	 * @param ExecutionContext $context Authorized execution context.
	 * @return mixed
	 * @throws RuntimeException When the action is not supported.
	 */
	public function handle( array $input, ExecutionContext $context ): mixed {
		return match ( $this->action ) {
			self::GET => $this->get( $input ),
			self::CATALOG => $this->catalog( $input ),
			self::EFFECTIVE => $this->effective( $input, $context ),
			default => throw new RuntimeException( 'Unsupported Dashboard Widget preset read action.' ),
		};
	}

	/**
	 * Read one preset by its canonical UUID.
	 *
	 * @param array<string,mixed> $input Request containing only the preset ID.
	 * @return array|null
	 * @throws InvalidArgumentException If the ID or keys are invalid.
	 */
	private function get( array $input ): ?array {
		if ( array( 'id' ) !== array_keys( $input ) ) {
			throw new InvalidArgumentException( 'Dashboard Widget preset get requires exactly one id field.' );
		}

		$id = $input['id'];
		if (
			! is_string( $id )
			|| preg_match( '/^[0-9a-f]{8}-[0-9a-f]{4}-[1-5][0-9a-f]{3}-[89ab][0-9a-f]{3}-[0-9a-f]{12}$/', $id ) !== 1
		) {
			throw new InvalidArgumentException( 'Dashboard Widget preset get requires a lowercase RFC 4122 UUID.' );
		}

		return $this->service->get( $id );
	}

	/**
	 * Return the canonical preset catalog.
	 *
	 * @param array<string,mixed> $input Must contain no fields.
	 * @return array
	 * @throws InvalidArgumentException If extra input is supplied.
	 */
	private function catalog( array $input ): array {
		if ( array() !== $input ) {
			throw new InvalidArgumentException( 'Dashboard Widget preset catalog does not accept input.' );
		}

		return $this->service->catalog();
	}

	/**
	 * Resolve effective presets for the execution context.
	 *
	 * @param array            $input Must contain no fields.
	 * @param ExecutionContext $context Authorized execution context.
	 * @return array
	 * @throws InvalidArgumentException If extra input is supplied.
	 */
	private function effective( array $input, ExecutionContext $context ): array {
		if ( array() !== $input ) {
			throw new InvalidArgumentException( 'Dashboard Widget effective preset does not accept input.' );
		}

		return $this->service->effective( $context );
	}
}
