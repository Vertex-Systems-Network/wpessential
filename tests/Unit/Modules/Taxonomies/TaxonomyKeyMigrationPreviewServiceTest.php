<?php

declare(strict_types=1);

namespace WPEssential\Tests\Unit\Modules\Taxonomies;

use PHPUnit\Framework\TestCase;
use WPEssential\Modules\Taxonomies\TaxonomyDefinitionProjector;
use WPEssential\Modules\Taxonomies\TaxonomyKeyMigrationPreviewService;
use WPEssential\Platform\Definitions\Definition;
use WPEssential\Platform\Definitions\DefinitionStatus;
use WPEssential\Platform\Definitions\InMemoryDefinitionRepository;

final class TaxonomyKeyMigrationPreviewServiceTest extends TestCase
{
    public function testBuildsDeterministicImpactAndRecoveryPlanWithoutMutatingDefinition(): void
    {
        $repository = new InMemoryDefinitionRepository();
        $source = $this->taxonomyDefinition(
            id: '11111111-1111-4111-8111-111111111111',
            key: 'library_genre',
            revision: 3,
        );
        $repository->save($source);
        $repository->save(new Definition(
            id: '22222222-2222-4222-8222-222222222222',
            slug: 'dependent-definition',
            type: 'post_type',
            schemaVersion: 1,
            ownerSurfaceId: 1,
            status: DefinitionStatus::Published,
            payload: ['post_type_key' => 'library_book'],
            revision: 1,
            dependencies: [$source->id],
        ));
        $before = $repository->get($source->id);

        $preview = (new TaxonomyKeyMigrationPreviewService(
            $repository,
            new TaxonomyDefinitionProjector(),
            static fn (string $key): bool => false,
        ))->preview($source->id, 'library_topic');

        self::assertTrue($preview['preview_only']);
        self::assertFalse($preview['execution_authorized']);
        self::assertFalse($preview['blocked']);
        self::assertTrue($preview['change_required']);
        self::assertSame('library_genre', $preview['source_key']);
        self::assertSame('library_topic', $preview['target_key']);
        self::assertSame('/library_genre/{term-slug}/', $preview['impacts']['term_urls']['source_pattern']);
        self::assertSame('/library_topic/{term-slug}/', $preview['impacts']['term_urls']['target_pattern']);
        self::assertTrue($preview['impacts']['term_urls']['changes']);
        self::assertSame(['post'], $preview['impacts']['object_associations']['source_object_types']);
        self::assertSame(['post'], $preview['impacts']['object_associations']['target_object_types']);
        self::assertFalse($preview['impacts']['object_associations']['changes']);
        self::assertSame('/wp/v2/library_genre', $preview['impacts']['rest_route']['source']);
        self::assertSame('/wp/v2/library_topic', $preview['impacts']['rest_route']['target']);
        self::assertTrue($preview['impacts']['rest_route']['changes']);
        self::assertSame('library_genre', $preview['impacts']['query_var']['source']);
        self::assertSame('library_topic', $preview['impacts']['query_var']['target']);
        self::assertTrue($preview['impacts']['query_var']['changes']);
        self::assertSame(1, $preview['impacts']['dependent_definitions']['count']);
        self::assertSame('22222222-2222-4222-8222-222222222222', $preview['impacts']['dependent_definitions']['items'][0]['id']);
        self::assertContains('dependent_definitions_present', $this->messageIds($preview['warnings']));
        self::assertContains('term_urls_change', $this->messageIds($preview['warnings']));
        self::assertContains('rest_route_changes', $this->messageIds($preview['warnings']));
        self::assertContains('query_var_changes', $this->messageIds($preview['warnings']));
        self::assertFalse($preview['recovery_plan']['execution_available']);
        self::assertNotEmpty($preview['recovery_plan']['required_before_execution']);
        self::assertNotEmpty($preview['recovery_plan']['rollback_outline']);

        $after = $repository->get($source->id);
        self::assertSame($before?->payload, $after?->payload);
        self::assertSame($before?->revision, $after?->revision);
        self::assertSame($before?->status, $after?->status);
        self::assertSame($before?->checksum, $after?->checksum);
    }

    public function testBlocksCanonicalTargetOwnershipConflict(): void
    {
        $repository = new InMemoryDefinitionRepository();
        $source = $this->taxonomyDefinition('11111111-1111-4111-8111-111111111111', 'library_genre');
        $target = $this->taxonomyDefinition('33333333-3333-4333-8333-333333333333', 'library_topic');
        $repository->save($source);
        $repository->save($target);

        $preview = (new TaxonomyKeyMigrationPreviewService(
            $repository,
            new TaxonomyDefinitionProjector(),
            static fn (string $key): bool => false,
        ))->preview($source->id, 'library_topic');

        self::assertTrue($preview['blocked']);
        self::assertContains('canonical_target_owned', $this->messageIds($preview['blockers']));
    }

    public function testBlocksRuntimeTargetOwnershipConflict(): void
    {
        $repository = new InMemoryDefinitionRepository();
        $source = $this->taxonomyDefinition('11111111-1111-4111-8111-111111111111', 'library_genre');
        $repository->save($source);

        $preview = (new TaxonomyKeyMigrationPreviewService(
            $repository,
            new TaxonomyDefinitionProjector(),
            static fn (string $key): bool => $key === 'external_topic',
        ))->preview($source->id, 'external_topic');

        self::assertTrue($preview['blocked']);
        self::assertContains('runtime_target_registered', $this->messageIds($preview['blockers']));
    }

    public function testBlocksReservedTargetThroughCanonicalProjector(): void
    {
        $repository = new InMemoryDefinitionRepository();
        $source = $this->taxonomyDefinition('11111111-1111-4111-8111-111111111111', 'library_genre');
        $repository->save($source);

        $preview = (new TaxonomyKeyMigrationPreviewService(
            $repository,
            new TaxonomyDefinitionProjector(),
            static fn (string $key): bool => false,
        ))->preview($source->id, 'category');

        self::assertTrue($preview['blocked']);
        self::assertContains('target_schema_invalid', $this->messageIds($preview['blockers']));
        self::assertNull($preview['impacts']['term_urls']['target_pattern']);
    }

    public function testSameKeyPreviewIsNoChangeAndDoesNotTreatCurrentRuntimeAsCollision(): void
    {
        $repository = new InMemoryDefinitionRepository();
        $source = $this->taxonomyDefinition('11111111-1111-4111-8111-111111111111', 'library_genre');
        $repository->save($source);

        $preview = (new TaxonomyKeyMigrationPreviewService(
            $repository,
            new TaxonomyDefinitionProjector(),
            static fn (string $key): bool => true,
        ))->preview($source->id, 'library_genre');

        self::assertFalse($preview['blocked']);
        self::assertFalse($preview['change_required']);
        self::assertSame([], $preview['blockers']);
        self::assertFalse($preview['impacts']['term_urls']['changes']);
        self::assertFalse($preview['impacts']['rest_route']['changes']);
        self::assertFalse($preview['impacts']['query_var']['changes']);
    }

    private function taxonomyDefinition(string $id, string $key, int $revision = 1): Definition
    {
        $definition = new Definition(
            id: $id,
            slug: 'taxonomy-' . str_replace('_', '-', $key),
            type: TaxonomyDefinitionProjector::DEFINITION_TYPE,
            schemaVersion: 1,
            ownerSurfaceId: TaxonomyDefinitionProjector::OWNER_SURFACE_ID,
            status: DefinitionStatus::Published,
            payload: [
                'taxonomy_key' => $key,
                'object_types' => ['post'],
                'name' => 'Library Genres',
                'singular_name' => 'Library Genre',
                'public' => true,
                'show_in_rest' => true,
            ],
            revision: $revision,
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
            dependencies: $definition->dependencies,
            checksum: $definition->computedChecksum(),
        );
    }

    /**
     * @param list<array{id:string,field:string,message:string}> $messages
     * @return list<string>
     */
    private function messageIds(array $messages): array
    {
        return array_values(array_map(
            static fn (array $message): string => $message['id'],
            $messages,
        ));
    }
}
