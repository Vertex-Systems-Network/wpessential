<?php

declare(strict_types=1);

namespace WPEssential\Modules\Relations\Migrations;

if (!defined('ABSPATH')) {
    exit;
}

use RuntimeException;
use WPEssential\Contracts\MigrationInterface;
use WPEssential\Modules\Relations\RelationEdgeTableNames;
use WPEssential\Platform\Database\DatabaseAdapterInterface;

final class AllowNonUniqueRelationEdgeTuplesMigration implements MigrationInterface
{
    private readonly RelationEdgeTableNames $tables;

    public function __construct(private readonly DatabaseAdapterInterface $database)
    {
        $this->tables = new RelationEdgeTableNames($database);
    }

    public function id(): string
    {
        return '020.allow-non-unique-relation-edge-tuples';
    }

    public function sequence(): int
    {
        return 110;
    }

    public function isDestructive(): bool
    {
        return false;
    }

    public function recoveryPlan(): ?string
    {
        return null;
    }

    public function apply(): void
    {
        $index = $this->database->getRow(
            "SHOW INDEX FROM `{$this->tables->edges}` WHERE Key_name = 'scope_relation_edge'",
        );
        if ($index === null) {
            return;
        }

        $result = $this->database->query(
            "ALTER TABLE `{$this->tables->edges}` DROP INDEX `scope_relation_edge`",
        );
        if ($result === false) {
            throw new RuntimeException(
                'Failed to allow non-unique Relation edge tuples: ' . $this->database->lastError(),
            );
        }
    }
}
