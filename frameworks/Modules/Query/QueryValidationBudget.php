<?php
/**
 * Query AST validation limits.
 *
 * @package WPEssential\Modules\Query
 */

declare(strict_types=1);

namespace WPEssential\Modules\Query;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use InvalidArgumentException;

/** Immutable validated upper limits for query AST processing. */
final readonly class QueryValidationBudget {

	/**
	 * Validate the positive integer budget limits.
	 *
	 * @param int $maxAstBytes Maximum AST byte length.
	 * @param int $maxGroupDepth Maximum nested group depth.
	 * @param int $maxPredicateCount Maximum predicate count.
	 * @param int $maxInListSize Maximum IN-list size.
	 * @param int $maxPageSize Maximum pagination size.
	 * @param int $maxRelationDepth Maximum relation traversal depth.
	 * @throws InvalidArgumentException If any budget limit is not positive.
	 */
	public function __construct(
		public int $maxAstBytes,
		public int $maxGroupDepth,
		public int $maxPredicateCount,
		public int $maxInListSize,
		public int $maxPageSize,
		public int $maxRelationDepth,
	) {
		foreach ( get_object_vars( $this ) as $name => $value ) {
			if ( ! is_int( $value ) || $value < 1 ) {
				throw new InvalidArgumentException(sprintf('Query validation budget %s must be a positive integer.', $name));
			}
		}
	}
}
