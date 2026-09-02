<?php

declare(strict_types=1);

namespace WPEssential\Modules\Relations;

if (!defined('ABSPATH')) {
    exit;
}

use InvalidArgumentException;
use RuntimeException;
use WPEssential\Contracts\DefinitionRepositoryInterface;
use WPEssential\Contracts\RelationQueryConsumerInterface;
use WPEssential\Platform\Auth\ExecutionContext;
use WPEssential\Platform\Definitions\Definition;
use WPEssential\Platform\Definitions\DefinitionStatus;

final readonly class RelationQueryConsumer implements RelationQueryConsumerInterface
{
    public function __construct(
        private DefinitionRepositoryInterface $definitions,
        private RelationDefinitionNormalizer $normalizer,
        private RelationQueryReadGateway $reads,
        private RelationEdgeScope $scope,
    ) {}

    public function describe(string $relationDefinitionId, ExecutionContext $context): array
    {
        $payload = $this->publishedPayload($relationDefinitionId, $context);
        $definition = $this->definition($relationDefinitionId);

        return [
            'contract_version' => self::CONTRACT_VERSION,
            'relation_definition_id' => $definition->id,
            'relation_key' => $payload['relation_key'],
            'definition_revision' => $definition->revision,
            'mutation_revision' => $this->reads->revision($definition->id),
            'cardinality' => $payload['cardinality'],
            'direction' => [
                'reciprocal' => $payload['direction']['reciprocal'],
                'bidirectional_traversal' => $payload['direction']['bidirectional_traversal'],
            ],
            'from' => $this->endpointDescriptor($payload['from']),
            'to' => $this->endpointDescriptor($payload['to']),
            'capabilities' => [
                'exists' => true,
                'related_ids' => true,
                'count_distinct' => true,
                'batch_exists' => true,
                'max_batch_size' => self::MAX_BATCH_SIZE,
                'max_result_limit' => self::MAX_RESULT_LIMIT,
                'max_traversal_depth' => 1,
            ],
        ];
    }

    public function relatedObjectIds(
        string $relationDefinitionId,
        string $direction,
        int $anchorObjectId,
        int $limit,
        ExecutionContext $context,
    ): array {
        $payload = $this->publishedPayload($relationDefinitionId, $context);
        $this->assertTraversalAllowed($payload, $direction);

        return $this->reads->relatedObjectIds(
            $relationDefinitionId,
            $direction,
            $anchorObjectId,
            $limit,
        );
    }

    public function matchingAnchorObjectIds(
        string $relationDefinitionId,
        string $direction,
        array $anchorObjectIds,
        ?array $relatedObjectIds,
        int $limit,
        ExecutionContext $context,
    ): array {
        $payload = $this->publishedPayload($relationDefinitionId, $context);
        $this->assertTraversalAllowed($payload, $direction);

        return $this->reads->matchingAnchorObjectIds(
            $relationDefinitionId,
            $direction,
            $anchorObjectIds,
            $relatedObjectIds,
            $limit,
        );
    }

    public function countRelatedObjects(
        string $relationDefinitionId,
        string $direction,
        int $anchorObjectId,
        ExecutionContext $context,
    ): int {
        $payload = $this->publishedPayload($relationDefinitionId, $context);
        $this->assertTraversalAllowed($payload, $direction);

        return $this->reads->countRelatedObjects(
            $relationDefinitionId,
            $direction,
            $anchorObjectId,
        );
    }

    /** @return array<string,mixed> */
    private function publishedPayload(string $relationDefinitionId, ExecutionContext $context): array
    {
        $this->assertScope($context);
        $definition = $this->definition($relationDefinitionId);
        if ($definition->status !== DefinitionStatus::Published) {
            throw new RuntimeException('Relation Query consumer requires a published Relation definition.');
        }
        return $this->normalizer->normalize($definition->payload, true);
    }

    private function definition(string $relationDefinitionId): Definition
    {
        $definition = $this->definitions->get($relationDefinitionId);
        if (!$definition instanceof Definition
            || $definition->type !== RelationDefinitionNormalizer::DEFINITION_TYPE
            || $definition->ownerSurfaceId !== RelationDefinitionNormalizer::OWNER_SURFACE_ID
        ) {
            throw new RuntimeException('Relation Query consumer definition was not found in canonical Surface 4.');
        }
        return $definition;
    }

    /** @param array<string,mixed> $payload */
    private function assertTraversalAllowed(array $payload, string $direction): void
    {
        if (!in_array($direction, [self::DIRECTION_FROM, self::DIRECTION_TO], true)) {
            throw new InvalidArgumentException('Relation Query traversal direction is not supported.');
        }
        if ($direction === self::DIRECTION_TO
            && ($payload['direction']['bidirectional_traversal'] ?? false) !== true
        ) {
            throw new RuntimeException('Relation Query reverse traversal is disabled by the Relation definition.');
        }
    }

    private function assertScope(ExecutionContext $context): void
    {
        if ($this->scope->siteId > 0 && $context->siteId !== $this->scope->siteId) {
            throw new RuntimeException('Relation Query consumer execution context does not match the registered site scope.');
        }
        if ($context->networkId !== null && $context->networkId !== $this->scope->networkId) {
            throw new RuntimeException('Relation Query consumer execution context does not match the registered network scope.');
        }
    }

    /**
     * @param array{object_type:string,object_subtype:?string,label:string} $endpoint
     * @return array{object_type:string,object_subtype:?string}
     */
    private function endpointDescriptor(array $endpoint): array
    {
        return [
            'object_type' => $endpoint['object_type'],
            'object_subtype' => $endpoint['object_subtype'],
        ];
    }
}
