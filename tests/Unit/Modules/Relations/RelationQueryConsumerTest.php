<?php

declare(strict_types=1);

namespace WPEssential\Tests\Unit\Modules\Relations;

use PHPUnit\Framework\TestCase;
use RuntimeException;
use WPEssential\Contracts\RelationQueryConsumerInterface;
use WPEssential\Modules\Relations\RelationDefinitionNormalizer;
use WPEssential\Modules\Relations\RelationEdgeScope;
use WPEssential\Modules\Relations\RelationQueryConsumer;
use WPEssential\Modules\Relations\RelationQueryReadGateway;
use WPEssential\Platform\Auth\ExecutionContext;
use WPEssential\Platform\Auth\Principal;
use WPEssential\Platform\Definitions\Definition;
use WPEssential\Platform\Definitions\DefinitionStatus;
use WPEssential\Platform\Definitions\InMemoryDefinitionRepository;

final class RelationQueryConsumerTest extends TestCase
{
    private const RELATION_ID = '11111111-1111-4111-8111-111111111111';

    public function testDescribeExposesStableContractWithoutStorageDetails(): void
    {
        $database = new RelationEdgeDatabaseAdapter();
        $database->rowQueue = [['mutation_revision' => '3']];
        $consumer = $this->consumer($database, $this->definition());

        $descriptor = $consumer->describe(self::RELATION_ID, $this->context());

        self::assertSame(RelationQueryConsumerInterface::CONTRACT_VERSION, $descriptor['contract_version']);
        self::assertSame('book_authors', $descriptor['relation_key']);
        self::assertSame(3, $descriptor['mutation_revision']);
        self::assertSame(1, $descriptor['capabilities']['max_traversal_depth']);
        self::assertSame(RelationQueryConsumerInterface::MAX_BATCH_SIZE, $descriptor['capabilities']['max_batch_size']);
        self::assertArrayNotHasKey('table', $descriptor);
        self::assertArrayNotHasKey('gateway', $descriptor);
        self::assertArrayNotHasKey('sql', $descriptor);
    }

    public function testRelatedIdsAreDistinctBoundedAndStorageOpaque(): void
    {
        $database = new RelationEdgeDatabaseAdapter();
        $database->resultsQueue = [[
            ['related_object_id' => '41'],
            ['related_object_id' => '42'],
        ]];
        $consumer = $this->consumer($database, $this->definition());

        $ids = $consumer->relatedObjectIds(
            self::RELATION_ID,
            RelationQueryConsumerInterface::DIRECTION_FROM,
            31,
            25,
            $this->context(),
        );

        self::assertSame([41, 42], $ids);
        self::assertStringContainsString('SELECT DISTINCT to_object_id AS related_object_id', $database->prepared[0]['query']);
        self::assertStringContainsString('LIMIT %d', $database->prepared[0]['query']);
        self::assertSame(25, $database->prepared[0]['args'][4]);
    }

    public function testBatchExistsUsesOneBoundedQuery(): void
    {
        $database = new RelationEdgeDatabaseAdapter();
        $database->resultsQueue = [[
            ['anchor_object_id' => '31'],
            ['anchor_object_id' => '33'],
        ]];
        $consumer = $this->consumer($database, $this->definition());

        $ids = $consumer->matchingAnchorObjectIds(
            self::RELATION_ID,
            RelationQueryConsumerInterface::DIRECTION_FROM,
            [33, 31, 32],
            [42, 41],
            3,
            $this->context(),
        );

        self::assertSame([31, 33], $ids);
        self::assertCount(1, $database->prepared);
        self::assertStringContainsString('from_object_id IN (%d, %d, %d)', $database->prepared[0]['query']);
        self::assertStringContainsString('to_object_id IN (%d, %d)', $database->prepared[0]['query']);
        self::assertSame(3, $database->prepared[0]['args'][8]);
    }

    public function testDistinctCountDoesNotExposeDuplicateEdgeMultiplicity(): void
    {
        $database = new RelationEdgeDatabaseAdapter();
        $database->varQueue = ['2'];
        $consumer = $this->consumer($database, $this->definition());

        $count = $consumer->countRelatedObjects(
            self::RELATION_ID,
            RelationQueryConsumerInterface::DIRECTION_FROM,
            31,
            $this->context(),
        );

        self::assertSame(2, $count);
        self::assertStringContainsString('COUNT(DISTINCT to_object_id)', $database->prepared[0]['query']);
    }

    public function testReverseTraversalFailsClosedWhenDefinitionDisablesIt(): void
    {
        $database = new RelationEdgeDatabaseAdapter();
        $consumer = $this->consumer($database, $this->definition(false));

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('reverse traversal is disabled');
        $consumer->relatedObjectIds(
            self::RELATION_ID,
            RelationQueryConsumerInterface::DIRECTION_TO,
            41,
            10,
            $this->context(),
        );
    }

    public function testWrongSiteFailsBeforeStorageRead(): void
    {
        $database = new RelationEdgeDatabaseAdapter();
        $consumer = $this->consumer($database, $this->definition());

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('registered site scope');
        try {
            $consumer->relatedObjectIds(
                self::RELATION_ID,
                RelationQueryConsumerInterface::DIRECTION_FROM,
                31,
                10,
                new ExecutionContext(new Principal(7), 18, networkId: 9),
            );
        } finally {
            self::assertSame([], $database->prepared);
        }
    }

    public function testDraftDefinitionFailsClosed(): void
    {
        $database = new RelationEdgeDatabaseAdapter();
        $consumer = $this->consumer($database, $this->definition(status: DefinitionStatus::Draft));

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('published Relation definition');
        $consumer->relatedObjectIds(
            self::RELATION_ID,
            RelationQueryConsumerInterface::DIRECTION_FROM,
            31,
            10,
            $this->context(),
        );
    }

    private function consumer(RelationEdgeDatabaseAdapter $database, Definition $definition): RelationQueryConsumer
    {
        $definitions = new InMemoryDefinitionRepository();
        $definitions->save($definition);
        $scope = RelationEdgeScope::site(9, 17);

        return new RelationQueryConsumer(
            $definitions,
            new RelationDefinitionNormalizer(),
            new RelationQueryReadGateway($database, $scope),
            $scope,
        );
    }

    private function definition(
        bool $bidirectionalTraversal = true,
        DefinitionStatus $status = DefinitionStatus::Published,
    ): Definition {
        $payload = (new RelationDefinitionNormalizer())->normalize([
            'relation_key' => 'book_authors',
            'title' => 'Book Authors',
            'description' => '',
            'cardinality' => 'many_to_many',
            'direction' => [
                'reciprocal' => false,
                'bidirectional_traversal' => $bidirectionalTraversal,
            ],
            'from' => [
                'object_type' => 'post',
                'object_subtype' => 'book',
                'label' => 'Books',
            ],
            'to' => [
                'object_type' => 'user',
                'label' => 'Authors',
            ],
            'bounds' => [
                'from_min' => 0,
                'from_max' => null,
                'to_min' => 0,
                'to_max' => null,
            ],
            'unique_edge' => true,
        ], $status === DefinitionStatus::Published);

        return new Definition(
            id: self::RELATION_ID,
            slug: 'relation-book-authors',
            type: RelationDefinitionNormalizer::DEFINITION_TYPE,
            schemaVersion: 1,
            ownerSurfaceId: RelationDefinitionNormalizer::OWNER_SURFACE_ID,
            status: $status,
            payload: $payload,
            revision: 4,
        );
    }

    private function context(): ExecutionContext
    {
        return new ExecutionContext(new Principal(7), 17, networkId: 9);
    }
}
