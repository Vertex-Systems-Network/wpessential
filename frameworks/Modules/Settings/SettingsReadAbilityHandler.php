<?php
/**
 * Authorized Settings read Ability handler.
 *
 * @package WPEssential\Modules\Settings
 */

declare(strict_types=1);

namespace WPEssential\Modules\Settings;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use InvalidArgumentException;
use RuntimeException;
use WPEssential\Contracts\AbilityHandlerInterface;
use WPEssential\Platform\Auth\ExecutionContext;

/** Read authorized Settings definitions using a bounded action dispatch. */
final readonly class SettingsReadAbilityHandler implements AbilityHandlerInterface {

	public const GET     = 'get';
	public const CATALOG = 'catalog';

	/**
	 * Bind a canonical service to an allowlisted read action.
	 *
	 * @param SettingsReadService $service Canonical read service.
	 * @param string              $action Allowlisted action name.
	 * @throws InvalidArgumentException On an unsupported action.
	 */
	public function __construct( private SettingsReadService $service, private string $action ) {
		if ( ! in_array( $this->action, array( self::GET, self::CATALOG ), true ) ) {
			throw new InvalidArgumentException( 'Unsupported Settings read ability action.' );
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
				throw new RuntimeException( 'Unsupported Settings read ability action.' );
		}
	}

	/**
	 * Read a Settings definition by ID.
	 *
	 * @param array<string,mixed> $input Definition lookup input.
	 * @return array|null
	 * @throws InvalidArgumentException On an invalid definition ID.
	 */
	private function get( array $input ): ?array {
		$id = $input['id'] ?? null;
		if ( ! is_string( $id ) || '' === trim( $id ) ) {
			throw new InvalidArgumentException( 'Settings get requires a non-empty definition id.' );
		}
		return $this->service->get( trim( $id ) );
	}

	/**
	 * List authorized Settings definitions.
	 *
	 * @param array<string,mixed> $input Must be empty.
	 * @return array
	 * @throws InvalidArgumentException If unexpected input is supplied.
	 */
	private function catalog( array $input ): array {
		if ( array() !== $input ) {
			throw new InvalidArgumentException( 'Settings catalog does not accept input.' );
		}
		return $this->service->catalog();
	}
}
