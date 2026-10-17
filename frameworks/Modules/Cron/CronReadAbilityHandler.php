<?php
/**
 * Read-only Cron Ability handler.
 *
 * @package WPEssential\Modules\Cron
 */

declare(strict_types=1);

namespace WPEssential\Modules\Cron;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use InvalidArgumentException;
use RuntimeException;
use WPEssential\Contracts\AbilityHandlerInterface;
use WPEssential\Platform\Auth\ExecutionContext;

/** Delegate allowlisted Cron read actions to their canonical service. */
final readonly class CronReadAbilityHandler implements AbilityHandlerInterface {

	public const GET     = 'get';
	public const CATALOG = 'catalog';

	/**
	 * Bind the canonical service to a supported action.
	 *
	 * @param CronReadService $service Canonical read service.
	 * @param string          $action Allowlisted action identifier.
	 * @throws InvalidArgumentException If the requested action is unsupported.
	 */
	public function __construct( private CronReadService $service, private string $action ) {
		if ( ! in_array( $this->action, array( self::GET, self::CATALOG ), true ) ) {
			throw new InvalidArgumentException( 'Unsupported Cron read ability action.' );
		}
	}

	/**
	 * Dispatch the authorized read action without broadening its action list.
	 *
	 * @param array            $input Read action input.
	 * @param ExecutionContext $context Authorized context.
	 * @return mixed
	 * @throws RuntimeException If the action is not supported.
	 */
	public function handle( array $input, ExecutionContext $context ): mixed {
		switch ( $this->action ) {
			case self::GET:
				return $this->get( $input );
			case self::CATALOG:
				return $this->catalog( $input );
			default:
				throw new RuntimeException( 'Unsupported Cron read ability action.' );
		}
	}

	/**
	 * Read a single definition by its non-empty identifier.
	 *
	 * @param array $input Definition lookup input.
	 * @return array|null
	 * @throws InvalidArgumentException If the identifier is missing or invalid.
	 */
	private function get( array $input ): ?array {
		$id = $input['id'] ?? null;
		if ( ! is_string( $id ) || '' === trim( $id ) ) {
			throw new InvalidArgumentException( 'Cron get requires a non-empty definition id.' );
		}
		return $this->service->get( trim( $id ) );
	}

	/**
	 * List definitions without accepting caller-controlled filters.
	 *
	 * @param array $input Catalog input, which must be empty.
	 * @return array
	 * @throws InvalidArgumentException If any unsupported input is supplied.
	 */
	private function catalog( array $input ): array {
		if ( array() !== $input ) {
			throw new InvalidArgumentException( 'Cron catalog does not accept input.' );
		}
		return $this->service->catalog();
	}
}
