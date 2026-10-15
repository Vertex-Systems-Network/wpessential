<?php
/**
 * Validate canonical scalar or nested-array Field persistence values.
 *
 * @package WPEssential\Modules\Fields
 */

declare(strict_types=1);

namespace WPEssential\Modules\Fields;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use InvalidArgumentException;

/** Enforce persistence value types without changing the original value. */
final class FieldValuePersistenceGuard {

	/**
	 * Reject unsupported persisted Field values, including non-finite floats.
	 *
	 * @param mixed $value Value to recursively validate.
	 * @return mixed The unchanged, validated value.
	 * @throws InvalidArgumentException If value is not persistence-safe.
	 */
	public function assertSafe(mixed $value): mixed
	{
		if ( null === $value || is_string( $value ) || is_int( $value ) || is_bool( $value ) ) {
			return $value;
		}

		if ( is_float( $value ) ) {
			if ( ! is_finite( $value ) ) {
				throw new InvalidArgumentException( 'Persisted numeric Field values must be finite.' );
			}
			return $value;
		}

		if ( is_array( $value ) ) {
			foreach ( $value as $item ) {
				$this->assertSafe( $item );
			}
			return $value;
		}

		throw new InvalidArgumentException( 'Persisted Field values must contain only canonical scalar/array data.' );
	}
}
