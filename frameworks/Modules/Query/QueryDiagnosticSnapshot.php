<?php

declare(strict_types=1);

namespace WPEssential\Modules\Query;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Safe Query diagnostic projection.
 *
 * The V1 diagnostic contract intentionally excludes SQL, provider exceptions,
 * credentials, raw parameters, metadata payloads, cache values and wall-clock
 * timing. It is a pure value projection and is not exposed by any runtime API.
 */
final readonly class QueryDiagnosticSnapshot
{
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

    /** @return array<string,int|string|bool> */
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
