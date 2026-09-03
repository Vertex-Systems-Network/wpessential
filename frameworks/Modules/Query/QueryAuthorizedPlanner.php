<?php

declare(strict_types=1);

namespace WPEssential\Modules\Query;

if (!defined('ABSPATH')) {
    exit;
}

use RuntimeException;
use Throwable;
use WPEssential\Contracts\DataSourceRegistryInterface;
use WPEssential\Platform\Auth\AuthorizationRequest;
use WPEssential\Platform\Auth\ExecutionContext;
use WPEssential\Platform\Auth\PolicyEngine;

final readonly class QueryAuthorizedPlanner
{
    public function __construct(
        private DataSourceRegistryInterface $dataSources,
        private PolicyEngine $policy,
        private QueryProviderCompilerInterface $compiler,
        private ?QueryRelationPredicateResolver $relationResolver = null,
        private ?QueryFieldPredicateResolver $fieldResolver = null,
    ) {
    }

    public function plan(QueryDefinition $definition, ExecutionContext $context): AuthorizedQueryPlan
    {
        $descriptor = $this->dataSources->find($definition->source->sourceRef);
        if ($descriptor === null) {
            throw new QueryPlanningException(
                'wpe_query_unknown_source',
                '$.source.source_ref',
                'Data Source is not registered in the canonical registry.',
            );
        }

        if (
            $descriptor->sourceType !== $definition->source->sourceType
            || $descriptor->capabilityVersion !== $definition->source->capabilityVersion
        ) {
            throw new QueryPlanningException(
                'wpe_query_dependency_unavailable',
                '$.source',
                'Data Source type or capability version no longer matches the validated Query definition.',
            );
        }

        if (!$descriptor->isAvailable()) {
            throw new QueryPlanningException(
                'wpe_query_dependency_unavailable',
                '$.source',
                'Data Source is degraded and unavailable for authorized planning.',
            );
        }

        try {
            $authorization = $descriptor->requireAuthorizationMapping();
        } catch (RuntimeException) {
            throw new QueryPlanningException(
                'wpe_query_dependency_unavailable',
                '$.source',
                'Data Source has no canonical Policy authorization mapping.',
            );
        }

        $request = new AuthorizationRequest(
            context: $context,
            ability: $authorization->ability,
            capability: $authorization->capability,
            resourceType: $authorization->resourceType,
        );

        try {
            $decision = $this->policy->authorize($request);
        } catch (Throwable) {
            throw new QueryPlanningException(
                'wpe_query_policy_denied',
                '$.source',
                'Canonical Policy authorization could not be established.',
            );
        }

        if (!$decision->allowed) {
            throw new QueryPlanningException(
                'wpe_query_policy_denied',
                '$.source',
                'Canonical Policy denied Data Source access.',
            );
        }

        $effectiveDefinition = $definition;
        $shortCircuitEmpty = false;
        if ($this->relationResolver !== null) {
            $relationResolution = $this->relationResolver->resolve($effectiveDefinition, $context);
            $effectiveDefinition = $relationResolution->definition;
            $shortCircuitEmpty = $relationResolution->shortCircuitEmpty;
        }
        if ($this->fieldResolver !== null) {
            $fieldResolution = $this->fieldResolver->resolve(
                $effectiveDefinition,
                $context,
                $shortCircuitEmpty,
            );
            $effectiveDefinition = $fieldResolution->definition;
            $shortCircuitEmpty = $shortCircuitEmpty || $fieldResolution->shortCircuitEmpty;
        }

        if (!$this->compiler->supports($effectiveDefinition)) {
            throw new QueryPlanningException(
                'wpe_query_dependency_unavailable',
                '$.source',
                'No certified provider compiler supports this Query definition.',
            );
        }

        try {
            $providerPlan = $this->compiler->compile($effectiveDefinition);
        } catch (QueryProviderCompilationException $exception) {
            throw $exception;
        } catch (Throwable) {
            throw new QueryPlanningException(
                'wpe_query_provider_failed',
                '$.source',
                'Provider compilation failed before execution.',
            );
        }

        if ($providerPlan->sourceRef !== $definition->source->sourceRef) {
            throw new QueryPlanningException(
                'wpe_query_provider_failed',
                '$.source.source_ref',
                'Provider compiler returned a plan for a different Data Source.',
            );
        }

        $shortCircuitResult = $shortCircuitEmpty
            ? new QueryExecutionResult(
                provider: $providerPlan->provider,
                sourceRef: $providerPlan->sourceRef,
                projection: $providerPlan->projection,
                rows: [],
                returned: 0,
            )
            : null;

        return new AuthorizedQueryPlan(
            providerPlan: $providerPlan,
            ability: $authorization->ability,
            capability: $authorization->capability,
            resourceType: $authorization->resourceType,
            policyReason: $decision->reason,
            shortCircuitResult: $shortCircuitResult,
        );
    }
}
