<?php

declare(strict_types=1);

namespace WPEssential\Modules\Relations;

if (!defined('ABSPATH')) {
    exit;
}

use InvalidArgumentException;
use Throwable;
use WPEssential\Contracts\DefinitionRepositoryInterface;
use WPEssential\Platform\Database\DatabaseAdapterInterface;
use WPEssential\Platform\Definitions\Definition;

/**
 * Guards Relation definition writes whose edge-uniqueness policy becomes stricter.
 *
 * Canonical edge writers serialize on the Relation state row. Holding the same lock
 * across duplicate inspection and definition persistence prevents a concurrent
 * non-unique writer from racing the false -> true policy transition.
 */
final readonly class RelationDefinitionMutationRepository implements DefinitionRepositoryInterface
{
    private RelationEdgeTableNames $tables;

    public function __construct(
        private DefinitionRepositoryInterface $definitions,
        private WpdbRelationEdgeGateway $edges,
        private DatabaseAdapterInterface $database,
        private RelationEdgeScope $scope,
    ) {
        $this->tables = new RelationEdgeTableNames($database);
    }

    public function save(Definition $definition): void
    {
        $existing = $this->definitions->get($definition->id);
        if (!$existing instanceof Definition || !$this->strengthensUniqueEdge($existing, $definition)) {
            $this->definitions->save($definition);
            return;
        }

        $revision = $this->edges->beginRelationMutation($definition->id);

        try {
            if ($this->hasDuplicateTuples($definition->id)) {
                throw new InvalidArgumentException(
                    'Relation unique_edge cannot be enabled while persisted duplicate source/target tuples exist.',
                );
            }

            $this->definitions->save($definition);

            // A uniqueness-policy transition changes edge mutation semantics. Advancing
            // the mutation revision invalidates stale edge observers/writers together
            // with releasing the per-Relation serialization lock.
            $this->edges->completeRelationMutation($definition->id, $revision);
        } catch (Throwable $error) {
            $this->edges->rollbackRelationMutation();
            throw $error;
        }
    }

    public function get(string $id): ?Definition
    {
        return $this->definitions->get($id);
    }

    public function byType(string $type): array
    {
        return $this->definitions->byType($type);
    }

    public function dependentsOf(string $id): array
    {
        return $this->definitions->dependentsOf($id);
    }

    private function strengthensUniqueEdge(Definition $existing, Definition $candidate): bool
    {
        if ($existing->type !== RelationDefinitionNormalizer::DEFINITION_TYPE
            || $candidate->type !== RelationDefinitionNormalizer::DEFINITION_TYPE
            || $existing->ownerSurfaceId !== RelationDefinitionNormalizer::OWNER_SURFACE_ID
            || $candidate->ownerSurfaceId !== RelationDefinitionNormalizer::OWNER_SURFACE_ID
        ) {
            return false;
        }

        return ($existing->payload['unique_edge'] ?? true) === false
            && ($candidate->payload['unique_edge'] ?? true) === true;
    }

    private function hasDuplicateTuples(string $relationDefinitionId): bool
    {
        $sql = $this->database->prepare(
            "SELECT from_object_id, to_object_id
             FROM `{$this->tables->edges}`
             WHERE network_id = %d AND site_id = %d AND relation_definition_id = %s
             GROUP BY from_object_id, to_object_id
             HAVING COUNT(*) > 1
             LIMIT 1",
            $this->scope->networkId,
            $this->scope->siteId,
            $relationDefinitionId,
        );

        return $this->database->getRow($sql) !== null;
    }
}
