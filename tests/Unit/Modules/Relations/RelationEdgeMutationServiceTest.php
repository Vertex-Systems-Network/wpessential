<?php

declare(strict_types=1);

namespace WPEssential\Tests\Unit\Modules\Relations;

use PHPUnit\Framework\TestCase;
use RuntimeException;
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

final class RelationEdgeMutationServiceTest extends TestCase
{
    private const RELATION_ID = '11111111-1111-4111-8111-111111111111';
    private const EDGE_ID = '22222222-2222-4222-8222-222222222222';
    private const EXISTING_EDGE_ID = '33333333-3333-4333-8333-333333333333';
    private const NOW = '2026-09-02 08:00:00.123456';

    public function testManyToManyConnectIsTransactionalAndAdvancesRevision(): void
    {
        $database = new RelationEdgeDatabaseAdapter();
        $database->rowQueue = [['mutation_revision' => '2'], null];
        $service = $this->service($database, $this->definition('many_to_many'));

        $result = $service->connect(self::RELATION_ID, 31, 41, $this->context());

        self::assertTrue($result['changed']);
        self::assertSame(self::EDGE_ID, $result['edge_id']);
        self::assertSame(3, $result['revision']);
        self::assertSame(1, $database->begins);
        self::assertStringContainsString('FOR UPDATE', $database->prepared[1]['query']);
        self::assertStringContainsString('LIMIT 1', $database->prepared[2]['query']);
        self::assertSame(1, $database->commits);
        self::assertSame(0, $database->rollbacks);
        self::assertCount(1, $database->inserts);
    }

    public function testUniqueTupleConnectIsIdempotentWithoutRevisionAdvance(): void
    {
        $database = new RelationEdgeDatabaseAdapter();
        $database->rowQueue = [
            ['mutation_revision' => '7'],
            $this->edgeRow(self::EXISTING_EDGE_ID, 31, 41),
        ];
        $service = $this->service($database, $this->definition('many_to_many'));

        $result = $service->connect(self::RELATION_ID, 31, 41, $this->context());

        self::assertFalse($result['changed']);
        self::assertSame(self::EXISTING_EDGE_ID, $result['edge_id']);
        self::assertSame(7, $result['revision']);
        self::assertSame(1, $database->rollbacks);
        self::assertSame(0, $database->commits);
        self::assertCount(0, $database->inserts);
    }

    public function testOneToOneBlocksSecondSourceEdgeUnderRelationLock(): void
    {
        $database = new RelationEdgeDatabaseAdapter();
        $database->rowQueue = [['mutation_revision' => '0'], null];
        $database->varQueue = [1];
        $service = $this->service($database, $this->definition('one_to_one'));

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('source cardinality limit of 1');
        try {
            $service->connect(self::RELATION_ID, 31, 41, $this->context());
        } finally {
            self::assertSame(1, $database->rollbacks);
            self::assertCount(0, $database->inserts);
        }
    }

    public function testOneToManyBlocksSecondSourceForSameTarget(): void
    {
        $database = new RelationEdgeDatabaseAdapter();
        $database->rowQueue = [['mutation_revision' => '0'], null];
        $database->varQueue = [1];
        $service = $this->service($database, $this->definition('one_to_many'));

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('target cardinality limit of 1');
        try {
            $service->connect(self::RELATION_ID, 31, 41, $this->context());
        } finally {
            self::assertSame(1, $database->rollbacks);
        }
    }

    public function testManyToOneBlocksSecondTargetForSameSource(): void
    {
        $database = new RelationEdgeDatabaseAdapter();
        $database->rowQueue = [['mutation_revision' => '0'], null];
        $database->varQueue = [1];
        $service = $this->service($database, $this->definition('many_to_one'));

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('source cardinality limit of 1');
        $service->connect(self::RELATION_ID, 31, 41, $this->context());
    }

    public function testCustomMaximumUsesCountQueryWithoutMaterializingEdges(): void
    {
        $definition = $this->definition('many_to_many', [
            'from_min' => 0,
            'from_max' => 2,
            'to_min' => 0,
            'to_max' => null,
        ]);
        $database = new RelationEdgeDatabaseAdapter();
        $database->rowQueue = [['mutation_revision' => '0'], null];
        $database->varQueue = [2];
        $service = $this->service($database, $definition);

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('source cardinality limit of 2');
        try {
            $service->connect(self::RELATION_ID, 31, 41, $this->context());
        } finally {
            self::assertStringContainsString('SELECT COUNT(*)', $database->prepared[3]['query']);
            self::assertSame([], $database->resultsQueue);
        }
    }

    public function testDisconnectRemovesTupleAndAdvancesRevision(): void
    {
        $database = new RelationEdgeDatabaseAdapter();
        $database->rowQueue = [
            ['mutation_revision' => '4'],
            $this->edgeRow(self::EXISTING_EDGE_ID, 31, 41),
        ];
        $service = $this->service($database, $this->definition('many_to_many'));

        $result = $service->disconnect(self::RELATION_ID, 31, 41, $this->context());

        self::assertTrue($result['changed']);
        self::assertSame(self::EXISTING_EDGE_ID, $result['edge_id']);
        self::assertSame(5, $result['revision']);
        self::assertSame(1, $database->commits);
        self::assertStringContainsString('DELETE FROM', $database->prepared[3]['query']);
        self::assertStringContainsString('edge_id = %s', $database->prepared[3]['query']);
    }

    public function testDisconnectRejectsSourceMinimumViolation(): void
    {
        $definition = $this->definition('many_to_many', [
            'from_min' => 1,
            'from_max' => null,
            'to_min' => 0,
            'to_max' => null,
        ]);
        $database = new RelationEdgeDatabaseAdapter();
        $database->rowQueue = [
            ['mutation_revision' => '1'],
            $this->edgeRow(self::EXISTING_EDGE_ID, 31, 41),
        ];
        $database->varQueue = [1];
        $service = $this->service($database, $definition);

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('source minimum bound of 1');
        try {
            $service->disconnect(self::RELATION_ID, 31, 41, $this->context());
        } finally {
            self::assertSame(1, $database->rollbacks);
            self::assertSame(0, $database->commits);
        }
    }

    public function testMissingTupleDisconnectIsIdempotent(): void
    {
        $database = new RelationEdgeDatabaseAdapter();
        $database->rowQueue = [['mutation_revision' => '9'], null];
        $service = $this->service($database, $this->definition('many_to_many'));

        $result = $service->disconnect(self::RELATION_ID, 31, 41, $this->context());

        self::assertFalse($result['changed']);
        self::assertNull($result['edge_id']);
        self::assertSame(9, $result['revision']);
        self::assertSame(1, $database->rollbacks);
    }

    public function testUnpublishedDefinitionFailsBeforeOpeningTransaction(): void
    {
        $database = new RelationEdgeDatabaseAdapter();
        $service = $this->service(
            $database,
            $this->definition('many_to_many', status: DefinitionStatus::Draft),
        );

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('requires a published');
        try {
            $service->connect(self::RELATION_ID, 31, 41, $this->context());
        } finally {
            self::assertSame(0, $database->begins);
        }
    }

    public function testObjectAuthorizationFailureFailsBeforeOpeningTransaction(): void
    {
        $database = new RelationEdgeDatabaseAdapter();
        $service = $this->service(
            $database,
            $this->definition('many_to_many'),
            new RelationEndpointObjectAuthorizer(
                static fn (array $endpoint, int $objectId): bool => true,
                static fn (array $endpoint, int $objectId, int $actorId): bool => $objectId !== 41,
            ),
        );

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('target object 41 is not authorized');
        try {
            $service->connect(self::RELATION_ID, 31, 41, $this->context());
        } finally {
            self::assertSame(0, $database->begins);
        }
    }

    public function testObjectEndpointMismatchFailsClosed(): void
    {
        $database = new RelationEdgeDatabaseAdapter();
        $service = $this->service(
            $database,
            $this->definition('many_to_many'),
            new RelationEndpointObjectAuthorizer(
                static fn (array $endpoint, int $objectId): bool => $objectId !== 31,
                static fn (array $endpoint, int $objectId, int $actorId): bool => true,
            ),
        );

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('source object 31 does not exist or does not match');
        $service->connect(self::RELATION_ID, 31, 41, $this->context());
    }

    public function testNonUniqueTupleDefinitionFailsClosedAgainstCurrentStorageContract(): void
    {
        $database = new RelationEdgeDatabaseAdapter();
        $definition = $this->definition('many_to_many');
        $payload = $definition->payload;
        $payload['unique_edge'] = false;
        $definition = new Definition(
            id: $definition->id,
            slug: $definition->slug,
            type: $definition->type,
            schemaVersion: $definition->schemaVersion,
            ownerSurfaceId: $definition->ownerSurfaceId,
            status: $definition->status,
            payload: $payload,
        );
        $service = $this->service($database, $definition);

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('requires unique_edge=true');
        try {
            $service->connect(self::RELATION_ID, 31, 41, $this->context());
        } finally {
            self::assertSame(0, $database->begins);
        }
    }

    public function testWrongSurfaceDefinitionFailsBeforeOpeningTransaction(): void
    {
        $database = new RelationEdgeDatabaseAdapter();
        $definition = $this->definition('many_to_many');
        $definition = new Definition(
            id: $definition->id,
            slug: $definition->slug,
            type: $definition->type,
            schemaVersion: $definition->schemaVersion,
            ownerSurfaceId: 3,
            status: $definition->status,
            payload: $definition->payload,
        );
        $service = $this->service($database, $definition);

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('canonical Surface 4');
        try {
            $service->connect(self::RELATION_ID, 31, 41, $this->context());
        } finally {
            self::assertSame(0, $database->begins);
        }
    }

    public function testUnsupportedEndpointClassFailsClosedEvenWithPermissiveObjectAdapter(): void
    {
        $database = new RelationEdgeDatabaseAdapter();
        $definition = $this->definition('many_to_many');
        $payload = $definition->payload;
        $payload['from'] = [
            'object_type' => 'custom_table',
            'object_subtype' => 'orders',
            'label' => 'Orders',
        ];
        $definition = new Definition(
            id: $definition->id,
            slug: $definition->slug,
            type: $definition->type,
            schemaVersion: $definition->schemaVersion,
            ownerSurfaceId: $definition->ownerSurfaceId,
            status: $definition->status,
            payload: $payload,
        );
        $service = $this->service($database, $definition);

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('source endpoint type is not certified');
        $service->connect(self::RELATION_ID, 31, 41, $this->context());
    }

    public function testSelfRelationCanConnectCanonicalDirectedTuple(): void
    {
        $database = new RelationEdgeDatabaseAdapter();
        $definition = $this->definition('many_to_many');
        $payload = $definition->payload;
        $payload['to'] = $payload['from'];
        $definition = new Definition(
            id: $definition->id,
            slug: $definition->slug,
            type: $definition->type,
            schemaVersion: $definition->schemaVersion,
            ownerSurfaceId: $definition->ownerSurfaceId,
            status: $definition->status,
            payload: $payload,
        );
        $database->rowQueue = [['mutation_revision' => '0'], null];
        $service = $this->service($database, $definition);

        $result = $service->connect(self::RELATION_ID, 31, 31, $this->context());

        self::assertTrue($result['changed']);
        self::assertSame(31, $result['from_object_id']);
        self::assertSame(31, $result['to_object_id']);
    }

    public function testGatewayInsertFailureRollsBackExactlyOnce(): void
    {
        $database = new RelationEdgeDatabaseAdapter();
        $database->rowQueue = [['mutation_revision' => '0'], null];
        $database->insertResult = false;
        $database->error = 'insert failed';
        $service = $this->service($database, $this->definition('many_to_many'));

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('insert failed');
        try {
            $service->connect(self::RELATION_ID, 31, 41, $this->context());
        } finally {
            self::assertSame(1, $database->rollbacks);
            self::assertSame(0, $database->commits);
        }
    }

    private function service(
        RelationEdgeDatabaseAdapter $database,
        Definition $definition,
        ?RelationEndpointObjectAuthorizer $objects = null,
    ): RelationEdgeMutationService {
        $repository = new InMemoryDefinitionRepository();
        $repository->save($definition);
        $objects ??= new RelationEndpointObjectAuthorizer(
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
            static fn (): string => self::EDGE_ID,
            static fn (): string => self::NOW,
        );
    }

    /** @param null|array{from_min:int,from_max:?int,to_min:int,to_max:?int} $bounds */
    private function definition(
        string $cardinality,
        ?array $bounds = null,
        DefinitionStatus $status = DefinitionStatus::Published,
    ): Definition {
        $payload = [
            'relation_key' => 'book_authors',
            'title' => 'Book Authors',
            'description' => '',
            'cardinality' => $cardinality,
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
            'unique_edge' => true,
        ];
        if ($bounds !== null) {
            $payload['bounds'] = $bounds;
        }
        $payload = (new RelationDefinitionNormalizer())->normalize(
            $payload,
            $status === DefinitionStatus::Published,
        );

        return new Definition(
            id: self::RELATION_ID,
            slug: 'relation-book-authors',
            type: RelationDefinitionNormalizer::DEFINITION_TYPE,
            schemaVersion: 1,
            ownerSurfaceId: RelationDefinitionNormalizer::OWNER_SURFACE_ID,
            status: $status,
            payload: $payload,
        );
    }

    /** @return array<string,mixed> */
    private function edgeRow(string $edgeId, int $fromObjectId, int $toObjectId): array
    {
        return [
            'edge_id' => $edgeId,
            'relation_definition_id' => self::RELATION_ID,
            'from_object_id' => (string) $fromObjectId,
            'to_object_id' => (string) $toObjectId,
            'created_at' => self::NOW,
            'updated_at' => self::NOW,
        ];
    }

    private function context(): ExecutionContext
    {
        return new ExecutionContext(new Principal(7), 17, networkId: 9);
    }
}
