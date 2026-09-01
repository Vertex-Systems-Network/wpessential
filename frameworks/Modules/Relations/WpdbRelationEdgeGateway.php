<?php

declare(strict_types=1);

namespace WPEssential\Modules\Relations;

if (!defined('ABSPATH')) {
    exit;
}

use Closure;
use LogicException;
use RuntimeException;
use Throwable;
use WPEssential\Platform\Database\DatabaseAdapterInterface;

final class WpdbRelationEdgeGateway
{
    private readonly RelationEdgeTableNames $tables;
    private readonly Closure $clock;
    private bool $mutationOpen = false;
    private ?string $lockedRelationDefinitionId = null;
    private ?int $lockedRevision = null;

    /** @param null|Closure():string $clock */
    public function __construct(
        private readonly DatabaseAdapterInterface $database,
        private readonly RelationEdgeScope $scope,
        ?Closure $clock = null,
    ) {
        $this->tables = new RelationEdgeTableNames($database);
        $this->clock = $clock ?? static fn (): string => gmdate('Y-m-d H:i:s') . '.000000';
    }

    public function beginRelationMutation(string $relationDefinitionId): int
    {
        $this->assertUuid($relationDefinitionId, 'Relation definition id');
        if ($this->mutationOpen) {
            throw new LogicException('A Relation edge mutation transaction is already open.');
        }

        $this->database->beginTransaction();
        $this->mutationOpen = true;

        try {
            $now = $this->now();
            $insertState = $this->database->prepare(
                "INSERT INTO `{$this->tables->state}`
                    (network_id, site_id, relation_definition_id, mutation_revision, updated_at)
                 VALUES (%d, %d, %s, 0, %s)
                 ON DUPLICATE KEY UPDATE relation_definition_id = VALUES(relation_definition_id)",
                $this->scope->networkId,
                $this->scope->siteId,
                $relationDefinitionId,
                $now,
            );
            if ($this->database->query($insertState) === false) {
                throw new RuntimeException('Failed to initialize Relation edge mutation state: ' . $this->database->lastError());
            }

            $selectState = $this->database->prepare(
                "SELECT mutation_revision
                 FROM `{$this->tables->state}`
                 WHERE network_id = %d AND site_id = %d AND relation_definition_id = %s
                 FOR UPDATE",
                $this->scope->networkId,
                $this->scope->siteId,
                $relationDefinitionId,
            );
            $revision = $this->revisionFromRow($this->database->getRow($selectState));

            $this->lockedRelationDefinitionId = $relationDefinitionId;
            $this->lockedRevision = $revision;
            return $revision;
        } catch (Throwable $error) {
            $this->failMutation($error);
        }
    }

    public function insertEdge(RelationEdge $edge): void
    {
        $this->assertMutationOwns($edge->relationDefinitionId);

        try {
            if (!$this->database->insert(
                $this->tables->edges,
                [
                    'network_id' => $this->scope->networkId,
                    'site_id' => $this->scope->siteId,
                    'edge_id' => $edge->edgeId,
                    'relation_definition_id' => $edge->relationDefinitionId,
                    'from_object_id' => $edge->fromObjectId,
                    'to_object_id' => $edge->toObjectId,
                    'created_at' => $edge->createdAt,
                    'updated_at' => $edge->updatedAt,
                ],
                ['%d', '%d', '%s', '%s', '%d', '%d', '%s', '%s'],
            )) {
                throw new RuntimeException('Failed to insert Relation edge: ' . $this->database->lastError());
            }
        } catch (Throwable $error) {
            $this->failMutation($error);
        }
    }

    public function deleteEdge(string $relationDefinitionId, string $edgeId): bool
    {
        $this->assertMutationOwns($relationDefinitionId);
        $this->assertUuid($edgeId, 'Relation edge id');

        try {
            $sql = $this->database->prepare(
                "DELETE FROM `{$this->tables->edges}`
                 WHERE network_id = %d AND site_id = %d
                   AND relation_definition_id = %s AND edge_id = %s",
                $this->scope->networkId,
                $this->scope->siteId,
                $relationDefinitionId,
                $edgeId,
            );
            $result = $this->database->query($sql);
            if ($result === false) {
                throw new RuntimeException('Failed to delete Relation edge: ' . $this->database->lastError());
            }

            return $result > 0;
        } catch (Throwable $error) {
            $this->failMutation($error);
        }
    }

    public function deleteTuple(string $relationDefinitionId, int $fromObjectId, int $toObjectId): bool
    {
        $this->assertMutationOwns($relationDefinitionId);
        $this->assertObjectId($fromObjectId, 'Relation source object id');
        $this->assertObjectId($toObjectId, 'Relation target object id');

        try {
            $sql = $this->database->prepare(
                "DELETE FROM `{$this->tables->edges}`
                 WHERE network_id = %d AND site_id = %d
                   AND relation_definition_id = %s
                   AND from_object_id = %d AND to_object_id = %d",
                $this->scope->networkId,
                $this->scope->siteId,
                $relationDefinitionId,
                $fromObjectId,
                $toObjectId,
            );
            $result = $this->database->query($sql);
            if ($result === false) {
                throw new RuntimeException('Failed to delete Relation edge tuple: ' . $this->database->lastError());
            }

            return $result > 0;
        } catch (Throwable $error) {
            $this->failMutation($error);
        }
    }

    public function completeRelationMutation(string $relationDefinitionId, int $expectedRevision): int
    {
        $this->assertMutationOwns($relationDefinitionId);
        if ($expectedRevision < 0 || $this->lockedRevision !== $expectedRevision) {
            $this->failMutation(new RuntimeException(
                'Relation edge mutation revision no longer matches the locked revision.',
            ));
        }

        try {
            $nextRevision = $expectedRevision + 1;
            $sql = $this->database->prepare(
                "UPDATE `{$this->tables->state}`
                 SET mutation_revision = %d, updated_at = %s
                 WHERE network_id = %d AND site_id = %d
                   AND relation_definition_id = %s AND mutation_revision = %d",
                $nextRevision,
                $this->now(),
                $this->scope->networkId,
                $this->scope->siteId,
                $relationDefinitionId,
                $expectedRevision,
            );
            $updated = $this->database->query($sql);
            if ($updated !== 1) {
                $error = $updated === false ? ': ' . $this->database->lastError() : '';
                throw new RuntimeException('Failed to advance Relation edge mutation revision' . $error . '.');
            }

            $this->database->commit();
            $this->resetMutationState();
            return $nextRevision;
        } catch (Throwable $error) {
            $this->failMutation($error);
        }
    }

    public function rollbackRelationMutation(): void
    {
        if (!$this->mutationOpen) {
            return;
        }

        try {
            $this->database->rollBack();
        } finally {
            $this->resetMutationState();
        }
    }

    public function findById(string $edgeId): ?RelationEdge
    {
        $this->assertUuid($edgeId, 'Relation edge id');
        $sql = $this->database->prepare(
            "SELECT edge_id, relation_definition_id, from_object_id, to_object_id, created_at, updated_at
             FROM `{$this->tables->edges}`
             WHERE network_id = %d AND site_id = %d AND edge_id = %s",
            $this->scope->networkId,
            $this->scope->siteId,
            $edgeId,
        );
        $row = $this->database->getRow($sql);
        return $row === null ? null : $this->hydrate($row);
    }

    /** @return list<RelationEdge> */
    public function bySource(string $relationDefinitionId, int $fromObjectId): array
    {
        $this->assertUuid($relationDefinitionId, 'Relation definition id');
        $this->assertObjectId($fromObjectId, 'Relation source object id');
        $sql = $this->database->prepare(
            "SELECT edge_id, relation_definition_id, from_object_id, to_object_id, created_at, updated_at
             FROM `{$this->tables->edges}`
             WHERE network_id = %d AND site_id = %d
               AND relation_definition_id = %s AND from_object_id = %d
             ORDER BY created_at ASC, edge_id ASC",
            $this->scope->networkId,
            $this->scope->siteId,
            $relationDefinitionId,
            $fromObjectId,
        );
        return array_map($this->hydrate(...), $this->database->getResults($sql));
    }

    /** @return list<RelationEdge> */
    public function byTarget(string $relationDefinitionId, int $toObjectId): array
    {
        $this->assertUuid($relationDefinitionId, 'Relation definition id');
        $this->assertObjectId($toObjectId, 'Relation target object id');
        $sql = $this->database->prepare(
            "SELECT edge_id, relation_definition_id, from_object_id, to_object_id, created_at, updated_at
             FROM `{$this->tables->edges}`
             WHERE network_id = %d AND site_id = %d
               AND relation_definition_id = %s AND to_object_id = %d
             ORDER BY created_at ASC, edge_id ASC",
            $this->scope->networkId,
            $this->scope->siteId,
            $relationDefinitionId,
            $toObjectId,
        );
        return array_map($this->hydrate(...), $this->database->getResults($sql));
    }

    public function revision(string $relationDefinitionId): int
    {
        $this->assertUuid($relationDefinitionId, 'Relation definition id');
        $sql = $this->database->prepare(
            "SELECT mutation_revision
             FROM `{$this->tables->state}`
             WHERE network_id = %d AND site_id = %d AND relation_definition_id = %s",
            $this->scope->networkId,
            $this->scope->siteId,
            $relationDefinitionId,
        );
        $row = $this->database->getRow($sql);
        return $row === null ? 0 : $this->revisionFromRow($row);
    }

    private function assertMutationOwns(string $relationDefinitionId): void
    {
        $this->assertUuid($relationDefinitionId, 'Relation definition id');
        if (!$this->mutationOpen || $this->lockedRelationDefinitionId !== $relationDefinitionId) {
            throw new LogicException('Relation edge writes require the matching open mutation transaction.');
        }
    }

    /** @param array<string,mixed>|null $row */
    private function revisionFromRow(?array $row): int
    {
        $value = $row['mutation_revision'] ?? null;
        if (!is_int($value) && !(is_string($value) && ctype_digit($value))) {
            throw new RuntimeException('Relation edge mutation state row is malformed.');
        }
        $revision = (int) $value;
        if ($revision < 0) {
            throw new RuntimeException('Relation edge mutation revision cannot be negative.');
        }
        return $revision;
    }

    /** @param array<string,mixed> $row */
    private function hydrate(array $row): RelationEdge
    {
        $edgeId = $row['edge_id'] ?? null;
        $relationDefinitionId = $row['relation_definition_id'] ?? null;
        $fromObjectId = $this->positiveIntFromRow($row['from_object_id'] ?? null, 'from_object_id');
        $toObjectId = $this->positiveIntFromRow($row['to_object_id'] ?? null, 'to_object_id');
        $createdAt = $row['created_at'] ?? null;
        $updatedAt = $row['updated_at'] ?? null;

        if (!is_string($edgeId) || !is_string($relationDefinitionId)
            || !is_string($createdAt) || !is_string($updatedAt)
        ) {
            throw new RuntimeException('Relation edge persistence row is malformed.');
        }

        try {
            return new RelationEdge(
                $edgeId,
                $relationDefinitionId,
                $fromObjectId,
                $toObjectId,
                $createdAt,
                $updatedAt,
            );
        } catch (Throwable $error) {
            throw new RuntimeException('Relation edge persistence row failed canonical hydration.', 0, $error);
        }
    }

    private function positiveIntFromRow(mixed $value, string $field): int
    {
        if (is_int($value)) {
            $integer = $value;
        } elseif (is_string($value) && ctype_digit($value)) {
            $integer = (int) $value;
        } else {
            throw new RuntimeException(sprintf('Relation edge persistence %s is malformed.', $field));
        }
        if ($integer < 1) {
            throw new RuntimeException(sprintf('Relation edge persistence %s must be positive.', $field));
        }
        return $integer;
    }

    private function assertObjectId(int $value, string $label): void
    {
        if ($value < 1) {
            throw new LogicException($label . ' must be positive.');
        }
    }

    private function assertUuid(string $value, string $label): void
    {
        if (preg_match('/^[0-9a-f]{8}-[0-9a-f]{4}-[1-5][0-9a-f]{3}-[89ab][0-9a-f]{3}-[0-9a-f]{12}$/', $value) !== 1) {
            throw new LogicException($label . ' must be a lowercase RFC 4122 UUID.');
        }
    }

    private function now(): string
    {
        $value = ($this->clock)();
        if (preg_match('/^\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2}\.\d{6}$/', $value) !== 1) {
            throw new RuntimeException('Relation edge clock must return database datetime format with microseconds.');
        }
        return $value;
    }

    private function failMutation(Throwable $cause): never
    {
        try {
            if ($this->mutationOpen) {
                $this->database->rollBack();
            }
        } catch (Throwable $rollbackError) {
            $this->resetMutationState();
            throw new RuntimeException(
                'Relation edge mutation failed and rollback could not be confirmed: ' . $cause->getMessage(),
                0,
                $rollbackError,
            );
        }

        $this->resetMutationState();
        throw $cause;
    }

    private function resetMutationState(): void
    {
        $this->mutationOpen = false;
        $this->lockedRelationDefinitionId = null;
        $this->lockedRevision = null;
    }
}
