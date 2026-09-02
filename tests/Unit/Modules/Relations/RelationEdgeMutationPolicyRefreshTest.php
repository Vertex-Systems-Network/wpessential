<?php

declare(strict_types=1);

namespace WPEssential\Tests\Unit\Modules\Relations;

use PHPUnit\Framework\TestCase;
use WPEssential\Contracts\DefinitionRepositoryInterface;
use WPEssential\Modules\Relations\RelationDefinitionNormalizer;
use WPEssential\Modules\Relations\RelationEdgeMutationService;
use WPEssential\Modules\Relations\RelationEdgeScope;
use WPEssential\Modules\Relations\RelationEndpointObjectAuthorizer;
use WPEssential\Modules\Relations\WpdbRelationEdgeGateway;
use WPEssential\Platform\Auth\ExecutionContext;
use WPEssential\Platform\Auth\Principal;
use WPEssential\Platform\Definitions\Definition;
use WPEssential\Platform\Definitions\DefinitionStatus;

final class RelationEdgeMutationPolicyRefreshTest extends TestCase
{
    private const RELATION_ID = '11111111-1111-4111-8111-111111111111';
    private const EXISTING_EDGE_ID = '33333333-3333-4333-8333-333333333333';
    private const NEW_EDGE_ID = '22222222-2222-4222-8222-222222222222';
    private const NOW = '2026-09-02 08:00:00.123456';

    public function testConnectRefreshesUniquenessPolicyAfterAcquiringRelationLock(): void
    {
        $repository = new SequencedRelationDefinitionRepositoryForPolicyRefresh(
            $this->definition(false),
            $this->definition(true),
        );
        $database = new RelationEdgeDatabaseAdapter();
        $database->rowQueue = [
            ['mutation_revision' => '4'],
            [
                'edge_id' => self::EXISTING_EDGE_ID,
                'relation_definition_id' => self::RELATION_ID,
                'from_object_id' => '31',
                'to_object_id' => '41',
                'created_at' => self::NOW,
                'updated_at' => self::NOW,
            ],
        ];

        $service = new RelationEdgeMutationService(
            $repository,
            new RelationDefinitionNormalizer(),
            new WpdbRelationEdgeGateway(
                $database,
                RelationEdgeScope::site(9, 17),
                static fn (): string => self::NOW,
            ),
            new RelationEndpointObjectAuthorizer(
                static fn (array $endpoint, int $objectId): bool => true,
                static fn (array $endpoint, int $objectId, int $actorId): bool => true,
            ),
            static fn (): string => self::NEW_EDGE_ID,
            static fn (): string => self::NOW,
            supportsNonUniqueTuples: true,
        );

        $result = $service->connect(
            self::RELATION_ID,
            31,
            41,
            new ExecutionContext(new Principal(7), 17, networkId: 9),
        );

        self::assertFalse($result['changed']);
        self::assertSame(self::EXISTING_EDGE_ID, $result['edge_id']);
        self::assertSame(4, $result['revision']);
        self::assertGreaterThanOrEqual(2, $repository->reads);
        self::assertSame(1, $database->begins);
        self::assertSame(1, $database->rollbacks);
        self::assertSame(0, $database->commits);
        self::assertSame([], $database->inserts);
    }

    private function definition(bool $uniqueEdge): Definition
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
                'label' => 'Authors',
            ],
            'bounds' => [
                'from_min' => 0,
                'from_max' => null,
                'to_min' => 0,
                'to_max' => null,
            ],
            'unique_edge' => $uniqueEdge,
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
}

final class SequencedRelationDefinitionRepositoryForPolicyRefresh implements DefinitionRepositoryInterface
{
    public int $reads = 0;

    public function __construct(
        private Definition $beforeLock,
        private Definition $afterLock,
    ) {}

    public function save(Definition $definition): void
    {
        $this->afterLock = $definition;
    }

    public function get(string $id): ?Definition
    {
        ++$this->reads;
        return $this->reads === 1 ? $this->beforeLock : $this->afterLock;
    }

    public function byType(string $type): array
    {
        return [];
    }

    public function dependentsOf(string $id): array
    {
        return [];
    }
}
