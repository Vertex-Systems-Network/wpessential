<?php

declare(strict_types=1);

namespace WPEssential\Tests\Unit\Modules\Taxonomies;

use PHPUnit\Framework\TestCase;
use WPEssential\Modules\Taxonomies\TaxonomyDefinitionReadModel;
use WPEssential\Modules\Taxonomies\TaxonomyDefinitionProjector;
use WPEssential\Platform\Definitions\Definition;
use WPEssential\Platform\Definitions\DefinitionStatus;
use WPEssential\Platform\Definitions\InMemoryDefinitionRepository;

final class TaxonomyDefinitionReadModelTest extends TestCase
{
    public function testDependencyUsageDeduplicatesCanonicalReferencesAndIncludesReverseUsage(): void
    {
        $repository = new InMemoryDefinitionRepository();
        $postTypeId = '11111111-1111-4111-8111-111111111111';
        $taxonomyId = '22222222-2222-4222-8222-222222222222';
        $dependentId = '33333333-3333-4333-8333-333333333333';

        $repository->save(new Definition(
            id: $postTypeId,
            slug: 'library-book',
            type: 'post_type',
            schemaVersion: 1,
            ownerSurfaceId: 1,
            status: DefinitionStatus::Published,
            payload: [
                'post_type_key' => 'library_book',
                'name' => 'Books',
                'singular_name' => 'Book',
            ],
        ));
        $taxonomy = new Definition(
            id: $taxonomyId,
            slug: 'taxonomy-library-genre',
            type: TaxonomyDefinitionProjector::DEFINITION_TYPE,
            schemaVersion: 1,
            ownerSurfaceId: TaxonomyDefinitionProjector::OWNER_SURFACE_ID,
            status: DefinitionStatus::Draft,
            payload: [
                'taxonomy_key' => 'library_genre',
                'name' => 'Genres',
                'singular_name' => 'Genre',
                'object_types' => ['library_book', 'external_book'],
            ],
            dependencies: [$postTypeId],
        );
        $repository->save($taxonomy);
        $repository->save(new Definition(
            id: $dependentId,
            slug: 'genre-query',
            type: 'query',
            schemaVersion: 1,
            ownerSurfaceId: 6,
            status: DefinitionStatus::Published,
            payload: ['name' => 'Genre query'],
            dependencies: [$taxonomyId],
        ));

        $summary = (new TaxonomyDefinitionReadModel($repository))->summary($taxonomy);
        $usage = $summary['dependency_usage'];

        self::assertSame(3, $usage['count']);
        self::assertSame($postTypeId, $usage['declared'][0]['id']);
        self::assertTrue($usage['declared'][0]['resolved']);
        self::assertSame($dependentId, $usage['dependents'][0]['id']);
        self::assertSame('query', $usage['dependents'][0]['type']);
        self::assertSame($postTypeId, $usage['object_types'][0]['canonical_definition_id']);
        self::assertTrue($usage['object_types'][0]['canonical']);
        self::assertSame('external_book', $usage['object_types'][1]['key']);
        self::assertNull($usage['object_types'][1]['canonical_definition_id']);
        self::assertFalse($usage['object_types'][1]['canonical']);
        self::assertSame('inactive', $summary['runtime_health']['state']);
        self::assertSame('draft', $summary['runtime_health']['definition_status']);
    }

    public function testUnresolvedDeclaredDependencyRemainsVisibleInsteadOfBeingDropped(): void
    {
        $repository = new InMemoryDefinitionRepository();
        $missingId = '44444444-4444-4444-8444-444444444444';
        $taxonomy = new Definition(
            id: '55555555-5555-4555-8555-555555555555',
            slug: 'taxonomy-library-topic',
            type: TaxonomyDefinitionProjector::DEFINITION_TYPE,
            schemaVersion: 1,
            ownerSurfaceId: TaxonomyDefinitionProjector::OWNER_SURFACE_ID,
            status: DefinitionStatus::Disabled,
            payload: [
                'taxonomy_key' => 'library_topic',
                'name' => 'Topics',
                'singular_name' => 'Topic',
                'object_types' => [],
            ],
            dependencies: [$missingId],
        );
        $repository->save($taxonomy);

        $usage = (new TaxonomyDefinitionReadModel($repository))->dependencyUsage($taxonomy);

        self::assertSame(1, $usage['count']);
        self::assertSame($missingId, $usage['declared'][0]['id']);
        self::assertFalse($usage['declared'][0]['resolved']);
        self::assertNull($usage['declared'][0]['slug']);
    }
}
