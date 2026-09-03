<?php

declare(strict_types=1);

namespace WPEssential\Modules\Query;

if (!defined('ABSPATH')) {
    exit;
}

use JsonException;
use WPEssential\Platform\Auth\ExecutionContext;
use WPEssential\Platform\Cache\CacheDependencies;
use WPEssential\Platform\Cache\CacheKey;
use WPEssential\Platform\Cache\CachePolicy;
use WPEssential\Platform\DataSources\DataSourceDescriptor;

/**
 * Query-specific cache eligibility/key contract.
 *
 * This class deliberately does not read or write a cache. Runtime wiring is a
 * later serialized tranche after invalidation and source generation semantics
 * are certified.
 */
final readonly class QueryCacheContract
{
    public function decide(
        QueryDefinition $definition,
        DataSourceDescriptor $descriptor,
        ExecutionContext $context,
        int $ttlSeconds,
    ): QueryCacheDecision {
        if (
            $descriptor->id !== $definition->source->sourceRef
            || $descriptor->sourceType !== $definition->source->sourceType
            || $descriptor->capabilityVersion !== $definition->source->capabilityVersion
        ) {
            return QueryCacheDecision::disabled('source_mismatch');
        }

        if (!$descriptor->cacheable) {
            return QueryCacheDecision::disabled('source_not_cacheable');
        }
        if ($descriptor->cacheGenerationKeys === []) {
            return QueryCacheDecision::disabled('missing_generation_keys');
        }
        if (!$context->principal->isAuthenticated()) {
            return QueryCacheDecision::disabled('principal_unavailable');
        }
        if ($context->networkId === null) {
            return QueryCacheDecision::disabled('network_scope_unavailable');
        }
        if ($ttlSeconds < 1) {
            return QueryCacheDecision::disabled('invalid_ttl');
        }

        try {
            $serialized = json_encode(
                $this->canonicalDefinition($definition),
                JSON_THROW_ON_ERROR | JSON_UNESCAPED_SLASHES,
            );
        } catch (JsonException) {
            return QueryCacheDecision::disabled('definition_not_serializable');
        }

        return QueryCacheDecision::eligible(
            new CacheKey(
                namespace: 'query.result',
                key: hash('sha256', $serialized),
                networkId: $context->networkId,
                siteId: $context->siteId,
                principalId: $context->principal->userId,
                revision: sprintf(
                    'q%d.c%d',
                    $definition->identity['revision'],
                    $definition->source->capabilityVersion,
                ),
            ),
            CachePolicy::ttl($ttlSeconds),
            new CacheDependencies($descriptor->cacheGenerationKeys),
        );
    }

    /** @return array<string,mixed> */
    private function canonicalDefinition(QueryDefinition $definition): array
    {
        return [
            'identity' => [
                'uuid' => $definition->identity['uuid'],
                'key' => $definition->identity['key'],
                'revision' => $definition->identity['revision'],
                'lifecycle' => $definition->identity['lifecycle'],
            ],
            'ast_version' => $definition->astVersion,
            'source' => [
                'source_ref' => $definition->source->sourceRef,
                'source_type' => $definition->source->sourceType,
                'capability_version' => $definition->source->capabilityVersion,
            ],
            'operation' => $definition->operation,
            'projection' => $definition->projection,
            'parameters' => $definition->parameters,
            'filter' => $this->predicate($definition->filter),
            'order_by' => array_map(
                static fn (QueryOrderClause $clause): array => [
                    'field_ref' => $clause->fieldRef,
                    'direction' => $clause->direction,
                ],
                $definition->orderBy,
            ),
            'pagination' => [
                'mode' => $definition->pagination->mode,
                'page_size' => $definition->pagination->pageSize,
                'offset' => $definition->pagination->offset,
                'cursor' => $definition->pagination->cursor,
            ],
            'distinct' => $definition->distinct,
            'execution_policy' => $definition->executionPolicy,
            'cache_policy' => $definition->cachePolicy,
        ];
    }

    /** @return array<string,mixed>|null */
    private function predicate(?QueryPredicate $predicate): ?array
    {
        if ($predicate === null) {
            return null;
        }

        return [
            'type' => $predicate->type->value,
            'payload' => $predicate->payload,
            'children' => array_map(
                fn (QueryPredicate $child): array => $this->predicate($child) ?? [],
                $predicate->children,
            ),
        ];
    }
}
