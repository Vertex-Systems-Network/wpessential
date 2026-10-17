<?php
/**
 * Authorized Admin Menu read Ability handler.
 *
 * @package WPEssential\Modules\AdminMenu
 */

declare(strict_types=1);

namespace WPEssential\Modules\AdminMenu;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use InvalidArgumentException;
use RuntimeException;
use WPEssential\Contracts\AbilityHandlerInterface;
use WPEssential\Platform\Auth\ExecutionContext;

/** Read authorized Admin Menu definitions using a bounded action dispatch. */
final readonly class AdminMenuReadAbilityHandler implements AbilityHandlerInterface {

	public const GET     = 'get';
	public const CATALOG = 'catalog';

	/**
	 * Bind a canonical service to an allowlisted read action.
	 *
	 * @param AdminMenuReadService $service Canonical read service.
	 * @param string               $action Allowlisted action name.
	 * @throws InvalidArgumentException On an unsupported action.
	 */
	public function __construct(
		private AdminMenuReadService $service,
		private string $action,
	) {
		if ( ! in_array( $this->action, array( self::GET, self::CATALOG ), true ) ) {
			throw new InvalidArgumentException( 'Unsupported Admin Menu read ability action.' );
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
			case self::GET:
				return $this->get( $input );
			case self::CATALOG:
				return $this->catalog( $input );
			default:
				throw new RuntimeException( 'Unsupported Admin Menu read ability action.' );
		}
	}

	/**
	 * Read an Admin Menu definition by ID.
	 *
	 * @param array<string,mixed> $input Definition lookup input.
	 * @return array|null
	 * @throws InvalidArgumentException On an invalid definition ID.
	 */
	private function get( array $input ): ?array {
		$id = $input['id'] ?? null;
		if ( ! is_string( $id ) || '' === trim( $id ) ) {
			throw new InvalidArgumentException( 'Admin Menu get requires a non-empty definition id.' );
		}

		return $this->service->get( trim( $id ) );
	}

	/**
	 * List authorized Admin Menu definitions.
	 *
	 * @param array<string,mixed> $input Must be empty.
	 * @return array
	 * @throws InvalidArgumentException If unexpected input is supplied.
	 */
	private function catalog( array $input ): array {
		if ( array() !== $input ) {
			throw new InvalidArgumentException( 'Admin Menu catalog does not accept input.' );
		}

		return $this->service->catalog();
	}
}
