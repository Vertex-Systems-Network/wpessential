<?php
/**
 * Query cache eligibility decision and reason taxonomy.
 *
 * @package WPEssential\Modules\Query
 */

declare(strict_types=1);

namespace WPEssential\Modules\Query;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use InvalidArgumentException;
use WPEssential\Platform\Cache\CacheDependencies;
use WPEssential\Platform\Cache\CacheKey;
use WPEssential\Platform\Cache\CachePolicy;

/** Immutable cache decision for a compiled Query. */
final readonly class QueryCacheDecision {

	private const REASONS = array(
		'eligible',
		'source_mismatch',
		'source_not_cacheable',
		'missing_generation_keys',
		'principal_unavailable',
		'network_scope_unavailable',
		'invalid_ttl',
		'definition_not_serializable',
	);

	/**
	 * Construct a validated cache decision.
	 *
	 * @param bool              $eligible Whether caching is enabled.
	 * @param string            $reason Cache eligibility reason.
	 * @param CacheKey|null     $key Cache key when eligible.
	 * @param CachePolicy       $policy Effective cache policy.
	 * @param CacheDependencies $dependencies Cache dependency generations.
	 * @throws InvalidArgumentException If the decision violates its reason or eligibility rules.
	 */
	private function __construct(
		public bool $eligible,
		public string $reason,
		public ?CacheKey $key,
		public CachePolicy $policy,
		public CacheDependencies $dependencies,
	) {
		if ( ! in_array( $this->reason, self::REASONS, true ) ) {
			throw new InvalidArgumentException( 'Query cache decision reason is not part of the V1 taxonomy.' );
		}
		if ( ( 'eligible' === $this->reason ) !== $this->eligible ) {
			throw new InvalidArgumentException( 'Query cache decision eligibility must match its reason.' );
		}
		if ( $this->eligible && ( null === $this->key || ! $this->policy->enabled ) ) {
			throw new InvalidArgumentException( 'Eligible Query cache decisions require a key and enabled policy.' );
		}
		if ( ! $this->eligible && ( null !== $this->key || $this->policy->enabled ) ) {
			throw new InvalidArgumentException(
				'Disabled Query cache decisions cannot carry an active key or policy.'
			);
		}
	}

	/**
	 * Create a non-cacheable decision.
	 *
	 * @param string $reason Ineligibility reason.
	 * @return self
	 */
	public static function disabled( string $reason ): self {
		return new self(
			eligible: false,
			reason: $reason,
			key: null,
			policy: CachePolicy::disabled(),
			dependencies: new CacheDependencies(),
		);
	}

	/**
	 * Create an eligible cache decision.
	 *
	 * @param CacheKey          $key Canonical cache key.
	 * @param CachePolicy       $policy Enabled cache policy.
	 * @param CacheDependencies $dependencies Cache dependencies.
	 * @return self
	 */
	public static function eligible(
		CacheKey $key,
		CachePolicy $policy,
		CacheDependencies $dependencies,
	): self {
		return new self(
			eligible: true,
			reason: 'eligible',
			key: $key,
			policy: $policy,
			dependencies: $dependencies,
		);
	}
}
