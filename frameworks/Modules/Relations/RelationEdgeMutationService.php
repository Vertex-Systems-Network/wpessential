<?php

declare(strict_types=1);

namespace WPEssential\Modules\Relations;

if (!defined('ABSPATH')) {
    exit;
}

use Closure;
use InvalidArgumentException;
use RuntimeException;
use Throwable;
use WPEssential\Contracts\DefinitionRepositoryInterface;
use WPEssential\Platform\Auth\ExecutionContext;
use WPEssential\Platform\Definitions\Definition;
use WPEssential\Platform\Definitions\DefinitionStatus;

final readonly class RelationEdgeMutationService
{
    private Closure $uuid;
    private Closure $clock;

    /**
     * @param null|Closure():string $uuid
     * @param null|Closure():string $clock
     */
    public function __construct(
        private DefinitionRepositoryInterface $definitions,
        private RelationDefinitionNormalizer $normalizer,
        private WpdbRelationEdgeGateway $gateway,
        private RelationEndpointObjectAuthorizer $objects,
        ?Closure $uuid = null,
        ?Closure $clock = null,
        private bool $supportsNonUniqueTuples = false,
    ) {
        $this->uuid = $uuid ?? $this->generateUuid(...);
        $this->clock = $clock ?? static fn (): string => gmdate('Y-m-d H:i:s') . '.000000';
    }

    /** @return array{changed:bool,relation_definition_id:string,edge_id:?string,from_object_id:int,to_object_id:int,revision:int} */
    public function connect(
        string $relationDefinitionId,
        int $fromObjectId,
        int $toObjectId,
        ExecutionContext $context,
    ): array {
        $payload = $this->mutationPayload($relationDefinitionId);
        $this->assertObjectAccess($payload, $fromObjectId, $toObjectId, $context);
        $uniqueEdge = $payload['unique_edge'] ?? null;
        if (!is_bool($uniqueEdge)) {
            throw new RuntimeException('Relation unique edge policy is malformed.');
        }
        $this->assertStorageSupportsUniquenessPolicy($uniqueEdge);

        $revision = $this->gateway->beginRelationMutation($relationDefinitionId);
        try {
            if ($uniqueEdge) {
                $existing = $this->gateway->findTuple($relationDefinitionId, $fromObjectId, $toObjectId);
                if ($existing instanceof RelationEdge) {
                    $this->gateway->rollbackRelationMutation();
                    return $this->result(
                        false,
                        $relationDefinitionId,
                        $existing->edgeId,
                        $fromObjectId,
                        $toObjectId,
                        $revision,
                    );
                }
            }

            $this->assertMaximum(
                $relationDefinitionId,
                $fromObjectId,
                $payload['bounds']['from_max'],
                'source',
            );
            $this->assertMaximum(
                $relationDefinitionId,
                $toObjectId,
                $payload['bounds']['to_max'],
                'target',
            );

            $edge = new RelationEdge(
                $this->edgeUuid(),
                $relationDefinitionId,
                $fromObjectId,
                $toObjectId,
                $this->now(),
                $this->now(),
            );
            $this->gateway->insertEdge($edge);
            $nextRevision = $this->gateway->completeRelationMutation($relationDefinitionId, $revision);

            return $this->result(true, $relationDefinitionId, $edge->edgeId, $fromObjectId, $toObjectId, $nextRevision);
        } catch (Throwable $error) {
            $this->gateway->rollbackRelationMutation();
            throw $error;
        }
    }

    /** @return array{changed:bool,relation_definition_id:string,edge_id:?string,from_object_id:int,to_object_id:int,revision:int} */
    public function disconnect(
        string $relationDefinitionId,
        int $fromObjectId,
        int $toObjectId,
        ExecutionContext $context,
    ): array {
        $payload = $this->mutationPayload($relationDefinitionId);
        $this->assertObjectAccess($payload, $fromObjectId, $toObjectId, $context);
        $uniqueEdge = $payload['unique_edge'] ?? null;
        if (!is_bool($uniqueEdge)) {
            throw new RuntimeException('Relation unique edge policy is malformed.');
        }
        $this->assertStorageSupportsUniquenessPolicy($uniqueEdge);

        $revision = $this->gateway->beginRelationMutation($relationDefinitionId);
        try {
            $edge = $this->gateway->findTuple($relationDefinitionId, $fromObjectId, $toObjectId);
            if (!$edge instanceof RelationEdge) {
                $this->gateway->rollbackRelationMutation();
                return $this->result(false, $relationDefinitionId, null, $fromObjectId, $toObjectId, $revision);
            }

            $this->assertMinimumAfterRemoval(
                $relationDefinitionId,
                $fromObjectId,
                $payload['bounds']['from_min'],
                'source',
            );
            $this->assertMinimumAfterRemoval(
                $relationDefinitionId,
                $toObjectId,
                $payload['bounds']['to_min'],
                'target',
            );

            if (!$this->gateway->deleteEdge($relationDefinitionId, $edge->edgeId)) {
                throw new RuntimeException('Relation edge disappeared during the locked mutation.');
            }
            $nextRevision = $this->gateway->completeRelationMutation($relationDefinitionId, $revision);

            return $this->result(
                true,
                $relationDefinitionId,
                $edge->edgeId,
                $fromObjectId,
                $toObjectId,
                $nextRevision,
            );
        } catch (Throwable $error) {
            $this->gateway->rollbackRelationMutation();
            throw $error;
        }
    }

    /**
     * @return array{
     *   relation_key:string,
     *   title:string,
     *   description:string,
     *   cardinality:string,
     *   direction:array{reciprocal:bool,bidirectional_traversal:bool},
     *   from:array{object_type:string,object_subtype:?string,label:string},
     *   to:array{object_type:string,object_subtype:?string,label:string},
     *   bounds:array{from_min:int,from_max:?int,to_min:int,to_max:?int},
     *   unique_edge:bool
     * }
     */
    private function mutationPayload(string $relationDefinitionId): array
    {
        if (!$this->isUuid($relationDefinitionId)) {
            throw new InvalidArgumentException('Relation definition id must be a lowercase RFC 4122 UUID.');
        }
        $definition = $this->definitions->get($relationDefinitionId);
        if (!$definition instanceof Definition
            || $definition->type !== RelationDefinitionNormalizer::DEFINITION_TYPE
            || $definition->ownerSurfaceId !== RelationDefinitionNormalizer::OWNER_SURFACE_ID
        ) {
            throw new RuntimeException('Relation definition was not found in canonical Surface 4.');
        }
        if ($definition->status !== DefinitionStatus::Published) {
            throw new RuntimeException('Relation edge mutation requires a published Relation definition.');
        }

        $payload = $this->normalizer->normalize($definition->payload, true);
        /** @var array{
         *   relation_key:string,title:string,description:string,cardinality:string,
         *   direction:array{reciprocal:bool,bidirectional_traversal:bool},
         *   from:array{object_type:string,object_subtype:?string,label:string},
         *   to:array{object_type:string,object_subtype:?string,label:string},
         *   bounds:array{from_min:int,from_max:?int,to_min:int,to_max:?int},
         *   unique_edge:bool
         * } $payload
         */
        return $payload;
    }

    /**
     * @param array{
     *   from:array{object_type:string,object_subtype:?string,label:string},
     *   to:array{object_type:string,object_subtype:?string,label:string}
     * } $payload
     */
    private function assertObjectAccess(
        array $payload,
        int $fromObjectId,
        int $toObjectId,
        ExecutionContext $context,
    ): void {
        $from = $payload['from'];
        $to = $payload['to'];
        $this->assertCertifiedEndpoint($from, 'source');
        $this->assertCertifiedEndpoint($to, 'target');
        $this->objects->assertCanMutate($from, $fromObjectId, $context, 'source');
        $this->objects->assertCanMutate($to, $toObjectId, $context, 'target');
    }

    /** @param array<string,mixed> $endpoint */
    private function assertCertifiedEndpoint(array $endpoint, string $side): void
    {
        $type = $endpoint['object_type'] ?? null;
        if (!is_string($type) || !in_array($type, ['post', 'term', 'user', 'comment', 'media'], true)) {
            throw new RuntimeException(sprintf(
                'Relation %s endpoint type is not certified for edge mutation.',
                $side,
            ));
        }
    }

    private function assertStorageSupportsUniquenessPolicy(bool $uniqueEdge): void
    {
        if (!$uniqueEdge && !$this->supportsNonUniqueTuples) {
            throw new RuntimeException(
                'Relation edge mutation currently requires unique_edge=true; non-unique tuples need a later storage contract.',
            );
        }
    }

    private function assertMaximum(
        string $relationDefinitionId,
        int $objectId,
        mixed $maximum,
        string $side,
    ): void {
        if ($maximum === null) {
            return;
        }
        if (!is_int($maximum) || $maximum < 1) {
            throw new RuntimeException(sprintf('Relation %s maximum is malformed.', $side));
        }
        $count = $side === 'source'
            ? $this->gateway->countBySource($relationDefinitionId, $objectId)
            : $this->gateway->countByTarget($relationDefinitionId, $objectId);
        if ($count >= $maximum) {
            throw new RuntimeException(sprintf(
                'Relation %s cardinality limit of %d would be exceeded.',
                $side,
                $maximum,
            ));
        }
    }

    private function assertMinimumAfterRemoval(
        string $relationDefinitionId,
        int $objectId,
        mixed $minimum,
        string $side,
    ): void {
        if (!is_int($minimum) || $minimum < 0) {
            throw new RuntimeException(sprintf('Relation %s minimum is malformed.', $side));
        }
        if ($minimum === 0) {
            return;
        }
        $count = $side === 'source'
            ? $this->gateway->countBySource($relationDefinitionId, $objectId)
            : $this->gateway->countByTarget($relationDefinitionId, $objectId);
        if (($count - 1) < $minimum) {
            throw new RuntimeException(sprintf(
                'Relation %s minimum bound of %d would be violated by disconnect.',
                $side,
                $minimum,
            ));
        }
    }

    /** @return array{changed:bool,relation_definition_id:string,edge_id:?string,from_object_id:int,to_object_id:int,revision:int} */
    private function result(
        bool $changed,
        string $relationDefinitionId,
        ?string $edgeId,
        int $fromObjectId,
        int $toObjectId,
        int $revision,
    ): array {
        return [
            'changed' => $changed,
            'relation_definition_id' => $relationDefinitionId,
            'edge_id' => $edgeId,
            'from_object_id' => $fromObjectId,
            'to_object_id' => $toObjectId,
            'revision' => $revision,
        ];
    }

    private function edgeUuid(): string
    {
        $uuid = strtolower((string) ($this->uuid)());
        if (!$this->isUuid($uuid)) {
            throw new RuntimeException('Relation edge UUID provider returned an invalid UUID.');
        }
        return $uuid;
    }

    private function now(): string
    {
        $timestamp = (string) ($this->clock)();
        if (preg_match('/^\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2}\.\d{6}$/', $timestamp) !== 1) {
            throw new RuntimeException('Relation edge clock must return UTC database datetime format with microseconds.');
        }
        return $timestamp;
    }

    private function isUuid(string $value): bool
    {
        return preg_match('/^[0-9a-f]{8}-[0-9a-f]{4}-[1-5][0-9a-f]{3}-[89ab][0-9a-f]{3}-[0-9a-f]{12}$/', $value) === 1;
    }

    private function generateUuid(): string
    {
        if (function_exists('wp_generate_uuid4')) {
            $uuid = strtolower((string) wp_generate_uuid4());
            if ($this->isUuid($uuid)) {
                return $uuid;
            }
        }

        $bytes = random_bytes(16);
        $bytes[6] = chr((ord($bytes[6]) & 0x0f) | 0x40);
        $bytes[8] = chr((ord($bytes[8]) & 0x3f) | 0x80);
        $hex = bin2hex($bytes);
        return sprintf(
            '%s-%s-%s-%s-%s',
            substr($hex, 0, 8),
            substr($hex, 8, 4),
            substr($hex, 12, 4),
            substr($hex, 16, 4),
            substr($hex, 20, 12),
        );
    }
}
