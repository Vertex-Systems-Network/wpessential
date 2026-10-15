<?php
/**
 * Query diagnostic redacted snapshot.
 *
 * @package WPEssential\Modules\Query
 */

declare(strict_types=1);

namespace WPEssential\Modules\Query;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Safe Query diagnostic projection.
 *
 * The V1 diagnostic contract intentionally excludes SQL, provider exceptions,
 * credentials, raw parameters, metadata payloads, cache values and wall-clock
 * timing. It is a pure value projection and is not exposed by any runtime API.
 */
final readonly class QueryDiagnosticSnapshot {

	/**
	 * Store a sanitized, bounded Query diagnostics snapshot.
	 *
	 * @param string $queryKey Query identifier.
	 * @param int    $queryRevision Query revision.
	 * @param string $sourceRef Source reference.
	 * @param int    $sourceCapabilityVersion Source capability version.
	 * @param int    $pageSize Pagination size.
	 * @param int    $offset Pagination offset.
	 * @param bool   $principalScoped Whether the cache key is principal-scoped.
	 * @param bool   $cacheEligible Whether this Query can be cached.
	 * @param string $cacheReason Cache eligibility reason.
	 */
	public function __construct(
		public string $queryKey,
		public int $queryRevision,
		public string $sourceRef,
		public int $sourceCapabilityVersion,
		public int $pageSize,
		public int $offset,
		public bool $principalScoped,
		public bool $cacheEligible,
		public string $cacheReason,
	) {
	}

	/**
	 * Project a Query decision to a redacted diagnostic snapshot.
	 *
	 * @param QueryDefinition    $definition Source Query definition.
	 * @param QueryCacheDecision $decision Resulting Query cache decision.
	 * @return self
	 */
	public static function fromDecision(
		QueryDefinition $definition,
		QueryCacheDecision $decision,
	): self {
		return new self(
			queryKey: $definition->identity['key'],
			queryRevision: $definition->identity['revision'],
			sourceRef: $definition->source->sourceRef,
			sourceCapabilityVersion: $definition->source->capabilityVersion,
			pageSize: $definition->pagination->pageSize,
			offset: $definition->pagination->offset,
			principalScoped: $decision->key?->principalId !== null,
			cacheEligible: $decision->eligible,
			cacheReason: $decision->reason,
		);
	}

	/**
	 * Return the redacted diagnostic snapshot as plain values.
	 *
	 * @return array<string,int|string|bool>
	 */
	public function toArray(): array
	{
		return [
			'query_key' => $this->queryKey,
			'query_revision' => $this->queryRevision,
			'source_ref' => $this->sourceRef,
			'source_capability_version' => $this->sourceCapabilityVersion,
			'page_size' => $this->pageSize,
			'offset' => $this->offset,
			'principal_scoped' => $this->principalScoped,
			'cache_eligible' => $this->cacheEligible,
			'cache_reason' => $this->cacheReason,
		];
	}
}
