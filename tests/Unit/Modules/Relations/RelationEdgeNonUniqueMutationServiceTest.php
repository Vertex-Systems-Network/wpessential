<?php

declare(strict_types=1);

namespace WPEssential\Tests\Unit\Modules\Relations;

use PHPUnit\Framework\TestCase;
use WPEssential\Modules\Relations\RelationDefinitionNormalizer;
use WPEssential\Modules\Relations\RelationEdgeMutationService;
use WPEssential\Modules\Relations\RelationEdgeScope;
use WPEssential\Modules\Relations\RelationEndpointObjectAuthorizer;
use WPEssential\Modules\Relations\WpdbRelationEdgeGateway;
use WPEssential\Platform\Auth\ExecutionContext;
use WPEssential\Platform\Auth\Principal;
use WPEssential\Platform\Definitions\Definition;
use WPEssential\Platform\Definitions\DefinitionStatus;
use WPEssential\Platform\Definitions\InMemoryDefinitionRepository;

final class RelationEdgeNonUniqueMutationServiceTest extends TestCase
{
    private const RELATION_ID = '11111111-1111-4111-8111-111111111111';
    private const NEW_EDGE_ID = '22222222-2222-4222-8222-222222222222';
    private const FIRST_EDGE_ID = '33333333-3333-4333-8333-333333333333';
    private const NOW = '2026-09-02 10:00:00.123456';

    public function testNonUniqueConnectCreatesAnotherTupleAndAdvancesRevisionWithoutTupleScan(): void
    {
        $database = new RelationEdgeDatabaseAdapter();
        $database->rowQueue = [['mutation_revision' => '3']];

        $result = $this->service($database)->connect(self::RELATION_ID, 31, 41, $this->context());

        self::assertTrue($result['changed']);
        self::assertSame(self::NEW_EDGE_ID, $result['edge_id']);
        self::assertSame(4, $result['revision']);
        self::assertCount(1, $database->inserts);
        self::assertSame(1, $database->commits);
        self::assertSame(0, $database->rollbacks);
        self::assertSame([], $database->resultsQueue);
    }

    public function testNonUniqueDisconnectRemovesOneDeterministicEdgeOnly(): void
    {
        $database = new RelationEdgeDatabaseAdapter();
        $database->rowQueue = [
            ['mutation_revision' => '4'],
            $this->edgeRow(self::FIRST_EDGE_ID),
        ];

        $result = $this->service($database)->disconnect(self::RELATION_ID, 31, 41, $this->context());

        self::assertTrue($result['changed']);
        self::assertSame(self::FIRST_EDGE_ID, $result['edge_id']);
        self::assertSame(5, $result['revision']);
        self::assertStringContainsString('LIMIT 1', $database->prepared[2]['query']);
        self::assertStringContainsString('edge_id = %s', $database->prepared[3]['query']);
        self::assertSame(self::FIRST_EDGE_ID, $database->prepared[3]['args'][3]);
        self::assertSame(1, $database->commits);
    }

    private function service(RelationEdgeDatabaseAdapter $database): RelationEdgeMutationService
    {
        $repository = new InMemoryDefinitionRepository();
        $repository->save($this->definition());
        $objects = new RelationEndpointObjectAuthorizer(
            static fn (array $endpoint, int $objectId): bool => true,
            static fn (array $endpoint, int $objectId, int $actorId): bool => true,
        );

        return new RelationEdgeMutationService(
            $repository,
            new RelationDefinitionNormalizer(),
            new WpdbRelationEdgeGateway(
                $database,
                RelationEdgeScope::site(9, 17),
                static fn (): string => self::NOW,
            ),
            $objects,
            static fn (): string => self::NEW_EDGE_ID,
            static fn (): string => self::NOW,
            true,
        );
    }

    private function definition(): Definition
    {
        $payload = (new RelationDefinitionNormalizer())->normalize([
            'relation_key' => 'book_authors',
            'title' => 'Book Authors',
            'description' => '',
            'cardinality' => 'many_to_many',
            'direction' => [
                'reciprocal' => false,
                'bidirectional_traversal' => true,
            ],
            'from' => [
                'object_type' => 'post',
                'object_subtype' => 'book',
                'label' => 'Books',
            ],
            'to' => [
                'object_type' => 'user',
                'object_subtype' => null,
                'label' => 'Authors',
            ],
            'unique_edge' => false,
        ], true);

        return new Definition(
            id: self::RELATION_ID,
            slug: 'relation-book-authors',
            type: RelationDefinitionNormalizer::DEFINITION_TYPE,
            schemaVersion: 1,
            ownerSurfaceId: RelationDefinitionNormalizer::OWNER_SURFACE_ID,
            status: DefinitionStatus::Published,
            payload: $payload,
        );
    }

    /** @return array<string,mixed> */
    private function edgeRow(string $edgeId): array
    {
        return [
            'edge_id' => $edgeId,
            'relation_definition_id' => self::RELATION_ID,
            'from_object_id' => '31',
            'to_object_id' => '41',
            'created_at' => self::NOW,
            'updated_at' => self::NOW,
        ];
    }

    private function context(): ExecutionContext
    {
        return new ExecutionContext(new Principal(7), 17, networkId: 9);
    }
}
