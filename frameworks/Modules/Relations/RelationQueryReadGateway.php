<?php

declare(strict_types=1);

namespace WPEssential\Modules\Relations;

if (!defined('ABSPATH')) {
    exit;
}

use InvalidArgumentException;
use RuntimeException;
use WPEssential\Contracts\RelationQueryConsumerInterface;
use WPEssential\Platform\Database\DatabaseAdapterInterface;

/** @internal Storage adapter behind the public RelationQueryConsumerInterface. */
final readonly class RelationQueryReadGateway
{
    private RelationEdgeTableNames $tables;

    public function __construct(
        private DatabaseAdapterInterface $database,
        private RelationEdgeScope $scope,
    ) {
        $this->tables = new RelationEdgeTableNames($database);
    }

    /** @return list<int> */
    public function relatedObjectIds(
        string $relationDefinitionId,
        string $direction,
        int $anchorObjectId,
        int $limit,
    ): array {
        $this->assertUuid($relationDefinitionId);
        $this->assertDirection($direction);
        $this->assertObjectId($anchorObjectId, 'Relation Query anchor object id');
        $this->assertLimit($limit);

        [$anchorColumn, $relatedColumn] = $this->columns($direction);
        $sql = $this->database->prepare(
            "SELECT DISTINCT {$relatedColumn} AS related_object_id
             FROM `{$this->tables->edges}`
             WHERE network_id = %d AND site_id = %d
               AND relation_definition_id = %s AND {$anchorColumn} = %d
             ORDER BY related_object_id ASC
             LIMIT %d",
            $this->scope->networkId,
            $this->scope->siteId,
            $relationDefinitionId,
            $anchorObjectId,
            $limit,
        );

        return $this->positiveIds($this->database->getResults($sql), 'related_object_id');
    }

    /**
     * @param list<int> $anchorObjectIds
     * @param null|list<int> $relatedObjectIds
     * @return list<int>
     */
    public function matchingAnchorObjectIds(
        string $relationDefinitionId,
        string $direction,
        array $anchorObjectIds,
        ?array $relatedObjectIds,
        int $limit,
    ): array {
        $this->assertUuid($relationDefinitionId);
        $this->assertDirection($direction);
        $anchorObjectIds = $this->validatedIdList($anchorObjectIds, 'Relation Query anchor ids');
        if ($anchorObjectIds === []) {
            return [];
        }
        if (count($anchorObjectIds) > RelationQueryConsumerInterface::MAX_BATCH_SIZE) {
            throw new InvalidArgumentException('Relation Query anchor batch exceeds the public contract limit.');
        }
        if ($relatedObjectIds !== null) {
            $relatedObjectIds = $this->validatedIdList($relatedObjectIds, 'Relation Query related ids');
            if ($relatedObjectIds === []) {
                return [];
            }
            if (count($relatedObjectIds) > RelationQueryConsumerInterface::MAX_BATCH_SIZE) {
                throw new InvalidArgumentException('Relation Query related-id batch exceeds the public contract limit.');
            }
        }
        $this->assertLimit($limit);

        [$anchorColumn, $relatedColumn] = $this->columns($direction);
        $args = [$this->scope->networkId, $this->scope->siteId, $relationDefinitionId];
        $anchorPlaceholders = implode(', ', array_fill(0, count($anchorObjectIds), '%d'));
        array_push($args, ...$anchorObjectIds);

        $relatedClause = '';
        if ($relatedObjectIds !== null) {
            $relatedPlaceholders = implode(', ', array_fill(0, count($relatedObjectIds), '%d'));
            $relatedClause = " AND {$relatedColumn} IN ({$relatedPlaceholders})";
            array_push($args, ...$relatedObjectIds);
        }
        $args[] = $limit;

        $sql = $this->database->prepare(
            "SELECT DISTINCT {$anchorColumn} AS anchor_object_id
             FROM `{$this->tables->edges}`
             WHERE network_id = %d AND site_id = %d
               AND relation_definition_id = %s
               AND {$anchorColumn} IN ({$anchorPlaceholders}){$relatedClause}
             ORDER BY anchor_object_id ASC
             LIMIT %d",
            ...$args,
        );

        return $this->positiveIds($this->database->getResults($sql), 'anchor_object_id');
    }

    public function countRelatedObjects(
        string $relationDefinitionId,
        string $direction,
        int $anchorObjectId,
    ): int {
        $this->assertUuid($relationDefinitionId);
        $this->assertDirection($direction);
        $this->assertObjectId($anchorObjectId, 'Relation Query anchor object id');
        [$anchorColumn, $relatedColumn] = $this->columns($direction);

        $sql = $this->database->prepare(
            "SELECT COUNT(DISTINCT {$relatedColumn})
             FROM `{$this->tables->edges}`
             WHERE network_id = %d AND site_id = %d
               AND relation_definition_id = %s AND {$anchorColumn} = %d",
            $this->scope->networkId,
            $this->scope->siteId,
            $relationDefinitionId,
            $anchorObjectId,
        );
        $value = $this->database->getVar($sql);
        if (is_int($value)) {
            $count = $value;
        } elseif (is_string($value) && ctype_digit($value)) {
            $count = (int) $value;
        } else {
            throw new RuntimeException('Relation Query count returned malformed data.');
        }
        if ($count < 0) {
            throw new RuntimeException('Relation Query count cannot be negative.');
        }
        return $count;
    }

    public function revision(string $relationDefinitionId): int
    {
        $this->assertUuid($relationDefinitionId);
        $sql = $this->database->prepare(
            "SELECT mutation_revision
             FROM `{$this->tables->state}`
             WHERE network_id = %d AND site_id = %d AND relation_definition_id = %s",
            $this->scope->networkId,
            $this->scope->siteId,
            $relationDefinitionId,
        );
        $row = $this->database->getRow($sql);
        if ($row === null) {
            return 0;
        }
        $value = $row['mutation_revision'] ?? null;
        if (is_int($value)) {
            $revision = $value;
        } elseif (is_string($value) && ctype_digit($value)) {
            $revision = (int) $value;
        } else {
            throw new RuntimeException('Relation Query mutation revision is malformed.');
        }
        if ($revision < 0) {
            throw new RuntimeException('Relation Query mutation revision cannot be negative.');
        }
        return $revision;
    }

    /** @return array{0:string,1:string} */
    private function columns(string $direction): array
    {
        return $direction === RelationQueryConsumerInterface::DIRECTION_FROM
            ? ['from_object_id', 'to_object_id']
            : ['to_object_id', 'from_object_id'];
    }

    private function assertDirection(string $direction): void
    {
        if (!in_array($direction, [
            RelationQueryConsumerInterface::DIRECTION_FROM,
            RelationQueryConsumerInterface::DIRECTION_TO,
        ], true)) {
            throw new InvalidArgumentException('Relation Query traversal direction is not supported.');
        }
    }

    private function assertLimit(int $limit): void
    {
        if ($limit < 1 || $limit > RelationQueryConsumerInterface::MAX_RESULT_LIMIT) {
            throw new InvalidArgumentException('Relation Query result limit is outside the public contract bounds.');
        }
    }

    /** @param list<int> $ids @return list<int> */
    private function validatedIdList(array $ids, string $label): array
    {
        $unique = [];
        foreach ($ids as $id) {
            if (!is_int($id) || $id < 1) {
                throw new InvalidArgumentException($label . ' must contain positive integers only.');
            }
            $unique[$id] = $id;
        }
        $values = array_values($unique);
        sort($values, SORT_NUMERIC);
        return $values;
    }

    /** @param list<array<string,mixed>> $rows @return list<int> */
    private function positiveIds(array $rows, string $field): array
    {
        $ids = [];
        foreach ($rows as $row) {
            $value = $row[$field] ?? null;
            if (is_int($value)) {
                $id = $value;
            } elseif (is_string($value) && ctype_digit($value)) {
                $id = (int) $value;
            } else {
                throw new RuntimeException('Relation Query persistence row is malformed.');
            }
            if ($id < 1) {
                throw new RuntimeException('Relation Query persistence returned a non-positive object id.');
            }
            $ids[$id] = $id;
        }
        return array_values($ids);
    }

    private function assertObjectId(int $objectId, string $label): void
    {
        if ($objectId < 1) {
            throw new InvalidArgumentException($label . ' must be positive.');
        }
    }

    private function assertUuid(string $value): void
    {
        if (preg_match('/^[0-9a-f]{8}-[0-9a-f]{4}-[1-5][0-9a-f]{3}-[89ab][0-9a-f]{3}-[0-9a-f]{12}$/', $value) !== 1) {
            throw new InvalidArgumentException('Relation Query definition id must be a lowercase RFC 4122 UUID.');
        }
    }
}
