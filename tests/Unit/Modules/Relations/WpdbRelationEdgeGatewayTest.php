<?php

declare(strict_types=1);

namespace WPEssential\Tests\Unit\Modules\Relations;

use LogicException;
use PHPUnit\Framework\TestCase;
use RuntimeException;
use WPEssential\Modules\Relations\RelationEdge;
use WPEssential\Modules\Relations\RelationEdgeScope;
use WPEssential\Modules\Relations\WpdbRelationEdgeGateway;

final class WpdbRelationEdgeGatewayTest extends TestCase
{
    private const RELATION_ID = '11111111-1111-4111-8111-111111111111';
    private const EDGE_ID = '22222222-2222-4222-8222-222222222222';
    private const NOW = '2026-09-02 00:00:00.123456';

    public function testMutationIsScopedTransactionalAndAdvancesRevision(): void
    {
        $database = new RelationEdgeDatabaseAdapter();
        $database->rowQueue = [['mutation_revision' => '4']];
        $gateway = $this->gateway($database);

        $revision = $gateway->beginRelationMutation(self::RELATION_ID);
        self::assertSame(4, $revision);

        $gateway->insertEdge($this->edge());
        $next = $gateway->completeRelationMutation(self::RELATION_ID, 4);

        self::assertSame(5, $next);
        self::assertSame(1, $database->begins);
        self::assertSame(1, $database->commits);
        self::assertSame(0, $database->rollbacks);
        self::assertCount(1, $database->inserts);
        self::assertSame('wp_wpe_relation_edges', $database->inserts[0]['table']);
        self::assertSame(9, $database->inserts[0]['data']['network_id']);
        self::assertSame(17, $database->inserts[0]['data']['site_id']);
        self::assertSame(self::RELATION_ID, $database->inserts[0]['data']['relation_definition_id']);

        self::assertSame([9, 17, self::RELATION_ID, self::NOW], $database->prepared[0]['args']);
        self::assertStringContainsString('FOR UPDATE', $database->prepared[1]['query']);
        self::assertSame([9, 17, self::RELATION_ID], $database->prepared[1]['args']);
        self::assertSame([5, self::NOW, 9, 17, self::RELATION_ID, 4], $database->prepared[2]['args']);
    }

    public function testWritesRequireMatchingOpenMutation(): void
    {
        $database = new RelationEdgeDatabaseAdapter();
        $gateway = $this->gateway($database);

        $this->expectException(LogicException::class);
        $this->expectExceptionMessage('matching open mutation transaction');
        $gateway->insertEdge($this->edge());
    }

    public function testInsertFailureAutomaticallyRollsBackAndReleasesLocalLock(): void
    {
        $database = new RelationEdgeDatabaseAdapter();
        $database->rowQueue = [['mutation_revision' => '0']];
        $database->insertResult = false;
        $database->error = 'duplicate edge';
        $gateway = $this->gateway($database);
        $gateway->beginRelationMutation(self::RELATION_ID);

        try {
            $gateway->insertEdge($this->edge());
            self::fail('Expected Relation edge insert failure.');
        } catch (RuntimeException $error) {
            self::assertStringContainsString('duplicate edge', $error->getMessage());
        }

        self::assertSame(1, $database->rollbacks);

        $database->insertResult = true;
        $database->rowQueue = [['mutation_revision' => '0']];
        self::assertSame(0, $gateway->beginRelationMutation(self::RELATION_ID));
        self::assertSame(2, $database->begins);
        $gateway->rollbackRelationMutation();
    }

    public function testStaleCompletionRollsBackWithoutRevisionAdvance(): void
    {
        $database = new RelationEdgeDatabaseAdapter();
        $database->rowQueue = [['mutation_revision' => '7']];
        $gateway = $this->gateway($database);
        self::assertSame(7, $gateway->beginRelationMutation(self::RELATION_ID));

        try {
            $gateway->completeRelationMutation(self::RELATION_ID, 6);
            self::fail('Expected stale revision conflict.');
        } catch (RuntimeException $error) {
            self::assertStringContainsString('no longer matches', $error->getMessage());
        }

        self::assertSame(1, $database->rollbacks);
        self::assertSame(0, $database->commits);
        self::assertCount(2, $database->prepared);
    }

    public function testScopedReadsHydrateDeterministically(): void
    {
        $database = new RelationEdgeDatabaseAdapter();
        $database->resultsQueue = [[
            [
                'edge_id' => self::EDGE_ID,
                'relation_definition_id' => self::RELATION_ID,
                'from_object_id' => '31',
                'to_object_id' => '41',
                'created_at' => self::NOW,
                'updated_at' => self::NOW,
            ],
        ]];
        $gateway = $this->gateway($database);

        $edges = $gateway->bySource(self::RELATION_ID, 31);

        self::assertCount(1, $edges);
        self::assertSame(self::EDGE_ID, $edges[0]->edgeId);
        self::assertSame(31, $edges[0]->fromObjectId);
        self::assertSame(41, $edges[0]->toObjectId);
        self::assertSame([9, 17, self::RELATION_ID, 31], $database->prepared[0]['args']);
        self::assertStringContainsString('ORDER BY created_at ASC, edge_id ASC', $database->prepared[0]['query']);
    }

    public function testMalformedPersistedRowFailsClosed(): void
    {
        $database = new RelationEdgeDatabaseAdapter();
        $database->rowQueue = [[
            'edge_id' => self::EDGE_ID,
            'relation_definition_id' => self::RELATION_ID,
            'from_object_id' => '0',
            'to_object_id' => '41',
            'created_at' => self::NOW,
            'updated_at' => self::NOW,
        ]];
        $gateway = $this->gateway($database);

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('from_object_id must be positive');
        $gateway->findById(self::EDGE_ID);
    }

    private function gateway(RelationEdgeDatabaseAdapter $database): WpdbRelationEdgeGateway
    {
        return new WpdbRelationEdgeGateway(
            $database,
            RelationEdgeScope::site(9, 17),
            static fn (): string => self::NOW,
        );
    }

    private function edge(): RelationEdge
    {
        return new RelationEdge(
            self::EDGE_ID,
            self::RELATION_ID,
            31,
            41,
            self::NOW,
            self::NOW,
        );
    }
}
