<?php

declare(strict_types=1);

namespace WPEssential\Tests\Unit\Modules\Relations;

use PHPUnit\Framework\TestCase;
use WPEssential\Modules\Relations\RelationDefinitionNormalizer;
use WPEssential\Modules\Relations\RelationDefinitionValidationService;
use WPEssential\Modules\Relations\RelationDiagnosticsAbilityHandler;
use WPEssential\Modules\Relations\RelationEndpointSupport;
use WPEssential\Platform\Auth\ExecutionContext;
use WPEssential\Platform\Auth\Principal;
use WPEssential\Platform\Definitions\Definition;
use WPEssential\Platform\Definitions\DefinitionStatus;
use WPEssential\Platform\Definitions\InMemoryDefinitionRepository;

final class RelationDiagnosticsAbilityHandlerTest extends TestCase
{
    private const RELATION_ID = '11111111-1111-4111-8111-111111111111';

    public function testDiagnosticsReportsHealthyCanonicalDefinitionWithoutPersistence(): void
    {
        $definitions = new InMemoryDefinitionRepository();
        $definitions->save($this->definition());
        $handler = $this->handler($definitions);

        $result = $handler->handle([], $this->context());

        self::assertSame(1, $result['summary']['relations']);
        self::assertSame(0, $result['summary']['unhealthy']);
        self::assertFalse($result['summary']['persistence_available']);
        self::assertSame('book_authors', $result['relations'][0]['relation_key']);
        self::assertSame('valid', $result['relations'][0]['checksum_status']);
        self::assertTrue($result['relations'][0]['endpoints']['from']['supported']);
        self::assertTrue($result['relations'][0]['endpoints']['to']['supported']);
        self::assertSame([], $result['relations'][0]['issues']);
    }

    public function testDiagnosticsSurfacesChecksumMismatchAsUnhealthy(): void
    {
        $definitions = new InMemoryDefinitionRepository();
        $valid = $this->definition();
        $definitions->save(new Definition(
            id: $valid->id,
            slug: $valid->slug,
            type: $valid->type,
            schemaVersion: $valid->schemaVersion,
            ownerSurfaceId: $valid->ownerSurfaceId,
            status: $valid->status,
            payload: $valid->payload,
            revision: $valid->revision,
            dependencies: [],
            checksum: str_repeat('0', 64),
        ));

        $result = $this->handler($definitions)->handle(
            ['definition_id' => self::RELATION_ID],
            $this->context(),
        );

        self::assertSame(1, $result['summary']['unhealthy']);
        self::assertSame('mismatch', $result['relations'][0]['checksum_status']);
        self::assertSame(
            'relation.diagnostics.checksum-mismatch',
            $result['relations'][0]['issues'][0]['id'],
        );
    }

    private function handler(InMemoryDefinitionRepository $definitions): RelationDiagnosticsAbilityHandler
    {
        $normalizer = new RelationDefinitionNormalizer();
        $endpoints = new RelationEndpointSupport(
            static fn (string $postType): bool => $postType === 'book',
            static fn (string $taxonomy): bool => true,
        );
        return new RelationDiagnosticsAbilityHandler(
            $definitions,
            $normalizer,
            new RelationDefinitionValidationService($normalizer, $endpoints),
            $endpoints,
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
            'unique_edge' => true,
        ], true);
        $definition = new Definition(
            id: self::RELATION_ID,
            slug: 'relation-book-authors',
            type: RelationDefinitionNormalizer::DEFINITION_TYPE,
            schemaVersion: 1,
            ownerSurfaceId: RelationDefinitionNormalizer::OWNER_SURFACE_ID,
            status: DefinitionStatus::Published,
            payload: $payload,
            revision: 3,
            dependencies: [],
        );
        return new Definition(
            id: $definition->id,
            slug: $definition->slug,
            type: $definition->type,
            schemaVersion: $definition->schemaVersion,
            ownerSurfaceId: $definition->ownerSurfaceId,
            status: $definition->status,
            payload: $definition->payload,
            revision: $definition->revision,
            dependencies: [],
            checksum: $definition->computedChecksum(),
        );
    }

    private function context(): ExecutionContext
    {
        return new ExecutionContext(new Principal(7), 17, networkId: 9);
    }
}
