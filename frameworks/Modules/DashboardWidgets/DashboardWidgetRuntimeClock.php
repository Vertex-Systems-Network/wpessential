<?php
/**
 * Dashboard widget runtime clock with injectable Unix timestamp source.
 *
 * @package WPEssential\Modules\DashboardWidgets
 */

declare(strict_types=1);

namespace WPEssential\Modules\DashboardWidgets;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use Closure;
use RuntimeException;

/** Provide a validated, injectable wall-clock source. */
final readonly class DashboardWidgetRuntimeClock {

	/**
	 * Unix timestamp callback.
	 *
	 * @var Closure(): int
	 */
	private Closure $source;

	/**
	 * Construct the clock using an optional injected source.
	 *
	 * @param callable|null $source Custom Unix timestamp callback.
	 */
	public function __construct( ?callable $source = null ) {
		$this->source = null !== $source
			? Closure::fromCallable( $source )
			: static fn (): int => time();
	}

	/**
	 * Read a validated non-negative Unix timestamp.
	 *
	 * @return int
	 * @throws RuntimeException When the clock returns an invalid timestamp.
	 */
	public function now(): int {
		$value = ( $this->source )();
		if ( ! is_int( $value ) || $value < 0 ) {
			throw new RuntimeException( 'Dashboard Widget runtime clock returned an invalid Unix timestamp.' );
		}

		return $value;
	}
}
