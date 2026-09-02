<?php

declare(strict_types=1);

namespace WPEssential\Tests\Unit\Modules\Relations;

use PHPUnit\Framework\TestCase;
use RuntimeException;
use WPEssential\Modules\Relations\RelationEdgeScope;
use WPEssential\Modules\Relations\WpdbRelationEdgeGateway;

final class WpdbRelationEdgeGatewayScaleTest extends TestCase
{
    private const RELATION_ID = '11111111-1111-4111-8111-111111111111';
    private const EDGE_ID = '22222222-2222-4222-8222-222222222222';
    private const NOW = '2026-09-02 11:00:00.123456';

    public function testTupleLookupIsBoundedAndDeterministic(): void
    {
        $database = new RelationEdgeDatabaseAdapter();
        $database->rowQueue = [[
            'edge_id' => self::EDGE_ID,
            'relation_definition_id' => self::RELATION_ID,
            'from_object_id' => '31',
            'to_object_id' => '41',
            'created_at' => self::NOW,
            'updated_at' => self::NOW,
        ]];
        $gateway = $this->gateway($database);

        $edge = $gateway->findTuple(self::RELATION_ID, 31, 41);

        self::assertSame(self::EDGE_ID, $edge?->edgeId);
        self::assertStringContainsString('from_object_id = %d AND to_object_id = %d', $database->prepared[0]['query']);
        self::assertStringContainsString('ORDER BY created_at ASC, edge_id ASC', $database->prepared[0]['query']);
        self::assertStringContainsString('LIMIT 1', $database->prepared[0]['query']);
    }

    public function testSourceAndTargetCountsUseAggregateQueries(): void
    {
        $database = new RelationEdgeDatabaseAdapter();
        $database->varQueue = ['125000', 70000];
        $gateway = $this->gateway($database);

        self::assertSame(125000, $gateway->countBySource(self::RELATION_ID, 31));
        self::assertSame(70000, $gateway->countByTarget(self::RELATION_ID, 41));
        self::assertStringContainsString('SELECT COUNT(*)', $database->prepared[0]['query']);
        self::assertStringContainsString('from_object_id = %d', $database->prepared[0]['query']);
        self::assertStringContainsString('SELECT COUNT(*)', $database->prepared[1]['query']);
        self::assertStringContainsString('to_object_id = %d', $database->prepared[1]['query']);
    }

    public function testMalformedAggregateCountFailsClosed(): void
    {
        $database = new RelationEdgeDatabaseAdapter();
        $database->varQueue = [null];
        $gateway = $this->gateway($database);

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('count query returned malformed data');
        $gateway->countBySource(self::RELATION_ID, 31);
    }

    private function gateway(RelationEdgeDatabaseAdapter $database): WpdbRelationEdgeGateway
    {
        return new WpdbRelationEdgeGateway(
            $database,
            RelationEdgeScope::site(9, 17),
            static fn (): string => self::NOW,
        );
    }
}
