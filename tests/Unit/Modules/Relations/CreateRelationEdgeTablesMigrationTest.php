<?php

declare(strict_types=1);

namespace WPEssential\Tests\Unit\Modules\Relations;

use PHPUnit\Framework\TestCase;
use WPEssential\Modules\Relations\Migrations\CreateRelationEdgeTablesMigration;

final class CreateRelationEdgeTablesMigrationTest extends TestCase
{
    public function testMigrationUsesStableIdentitySequenceAndCreatesScopedTables(): void
    {
        $database = new RelationEdgeDatabaseAdapter('network_');
        $migration = new CreateRelationEdgeTablesMigration($database);

        self::assertSame('010.create-relation-edge-persistence', $migration->id());
        self::assertSame(100, $migration->sequence());
        self::assertFalse($migration->isDestructive());
        self::assertNull($migration->recoveryPlan());

        $migration->apply();

        self::assertCount(2, $database->queries);
        $edges = $database->queries[0];
        $state = $database->queries[1];

        self::assertStringContainsString('CREATE TABLE IF NOT EXISTS `network_wpe_relation_edges`', $edges);
        self::assertStringContainsString('network_id BIGINT UNSIGNED NOT NULL', $edges);
        self::assertStringContainsString('site_id BIGINT UNSIGNED NOT NULL DEFAULT 0', $edges);
        self::assertStringContainsString('edge_id CHAR(36) NOT NULL', $edges);
        self::assertStringContainsString('relation_definition_id CHAR(36) NOT NULL', $edges);
        self::assertStringContainsString('from_object_id BIGINT UNSIGNED NOT NULL', $edges);
        self::assertStringContainsString('to_object_id BIGINT UNSIGNED NOT NULL', $edges);
        self::assertStringContainsString('PRIMARY KEY (network_id, site_id, edge_id)', $edges);
        self::assertStringContainsString(
            'UNIQUE KEY scope_relation_edge (network_id, site_id, relation_definition_id, from_object_id, to_object_id)',
            $edges,
        );
        self::assertStringContainsString(
            'KEY relation_source_lookup (network_id, site_id, relation_definition_id, from_object_id, to_object_id)',
            $edges,
        );
        self::assertStringContainsString(
            'KEY relation_target_lookup (network_id, site_id, relation_definition_id, to_object_id, from_object_id)',
            $edges,
        );
        self::assertStringContainsString('ENGINE=InnoDB DEFAULT CHARACTER SET utf8mb4', $edges);

        self::assertStringContainsString('CREATE TABLE IF NOT EXISTS `network_wpe_relation_edge_state`', $state);
        self::assertStringContainsString('mutation_revision BIGINT UNSIGNED NOT NULL DEFAULT 0', $state);
        self::assertStringContainsString('PRIMARY KEY (network_id, site_id, relation_definition_id)', $state);
    }

    public function testMigrationFailsClosedWhenEitherTableCannotBeCreated(): void
    {
        $database = new RelationEdgeDatabaseAdapter();
        $database->queryResultQueue = [1, false];
        $database->error = 'synthetic DDL failure';

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('synthetic DDL failure');
        (new CreateRelationEdgeTablesMigration($database))->apply();
    }
}
