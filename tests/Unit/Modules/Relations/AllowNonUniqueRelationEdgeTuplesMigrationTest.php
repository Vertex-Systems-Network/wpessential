<?php

declare(strict_types=1);

namespace WPEssential\Tests\Unit\Modules\Relations;

use PHPUnit\Framework\TestCase;
use RuntimeException;
use WPEssential\Modules\Relations\Migrations\AllowNonUniqueRelationEdgeTuplesMigration;

final class AllowNonUniqueRelationEdgeTuplesMigrationTest extends TestCase
{
    public function testMigrationDropsOnlyLegacyTupleUniquenessIndex(): void
    {
        $database = new RelationEdgeDatabaseAdapter('network_');
        $database->rowQueue = [['Key_name' => 'scope_relation_edge']];
        $migration = new AllowNonUniqueRelationEdgeTuplesMigration($database);

        self::assertSame('020.allow-non-unique-relation-edge-tuples', $migration->id());
        self::assertSame(110, $migration->sequence());
        self::assertFalse($migration->isDestructive());
        self::assertNull($migration->recoveryPlan());

        $migration->apply();

        self::assertCount(2, $database->queries);
        self::assertSame(
            "SHOW INDEX FROM `network_wpe_relation_edges` WHERE Key_name = 'scope_relation_edge'",
            $database->queries[0],
        );
        self::assertSame(
            'ALTER TABLE `network_wpe_relation_edges` DROP INDEX `scope_relation_edge`',
            $database->queries[1],
        );
    }

    public function testMigrationIsIdempotentWhenLegacyIndexIsAlreadyAbsent(): void
    {
        $database = new RelationEdgeDatabaseAdapter();
        $database->rowQueue = [null];

        (new AllowNonUniqueRelationEdgeTuplesMigration($database))->apply();

        self::assertCount(1, $database->queries);
        self::assertStringStartsWith('SHOW INDEX FROM `wp_wpe_relation_edges`', $database->queries[0]);
    }

    public function testMigrationFailsClosedWhenIndexCannotBeDropped(): void
    {
        $database = new RelationEdgeDatabaseAdapter();
        $database->rowQueue = [['Key_name' => 'scope_relation_edge']];
        $database->queryResultQueue = [false];
        $database->error = 'synthetic alter failure';

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('synthetic alter failure');
        (new AllowNonUniqueRelationEdgeTuplesMigration($database))->apply();
    }
}
