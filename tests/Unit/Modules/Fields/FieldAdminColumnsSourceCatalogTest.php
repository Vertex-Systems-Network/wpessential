<?php

declare(strict_types=1);

namespace WPEssential\Tests\Unit\Modules\Fields;

use PHPUnit\Framework\TestCase;
use WPEssential\Modules\Fields\FieldAdminColumnsSourceCatalog;
use WPEssential\Modules\Fields\FieldGroupDefinitionNormalizer;
use WPEssential\Modules\Fields\FieldGroupPostTypeTargetCompiler;
use WPEssential\Modules\Fields\FieldGroupRuntimeStorageProjection;
use WPEssential\Modules\Fields\PostMetaRegistrationCompiler;
use WPEssential\Platform\Definitions\Definition;
use WPEssential\Platform\Definitions\DefinitionStatus;
use WPEssential\Platform\Definitions\InMemoryDefinitionRepository;

final class FieldAdminColumnsSourceCatalogTest extends TestCase
{
    public function testDiscoversOnlyPublishedCertifiedSingleScalarNativePostMetaFields(): void
    {
        $repository = new InMemoryDefinitionRepository();
        $repository->save($this->definition(
            id: '11111111-1111-4111-8111-111111111111',
            status: DefinitionStatus::Published,
            revision: 7,
            fields: [
                [
                    'uuid' => '22222222-2222-4222-8222-222222222222',
                    'key' => 'headline',
                    'label' => 'Headline',
                    'type' => 'text',
                ],
                [
                    'uuid' => '33333333-3333-4333-8333-333333333333',
                    'key' => 'rating',
                    'label' => 'Rating',
                    'type' => 'number',
                ],
                [
                    'uuid' => '33333333-3333-4333-8333-333333333334',
                    'key' => 'published_at',
                    'label' => 'Published at',
                    'type' => 'datetime',
                ],
                [
                    'uuid' => '44444444-4444-4444-8444-444444444444',
                    'key' => 'gallery',
                    'label' => 'Gallery',
                    'type' => 'gallery',
                ],
                [
                    'uuid' => '55555555-5555-4555-8555-555555555555',
                    'key' => 'aliases',
                    'label' => 'Aliases',
                    'type' => 'text',
                    'cloneable' => true,
                ],
            ],
        ));
        $repository->save($this->definition(
            id: '66666666-6666-4666-8666-666666666666',
            status: DefinitionStatus::Draft,
            revision: 1,
            fields: [[
                'uuid' => '77777777-7777-4777-8777-777777777777',
                'key' => 'draft_only',
                'label' => 'Draft only',
                'type' => 'text',
            ]],
        ));

        $sources = $this->catalog($repository)->adminColumnSources();

        self::assertSame([
            'fields.11111111-1111-4111-8111-111111111111.22222222-2222-4222-8222-222222222222',
            'fields.11111111-1111-4111-8111-111111111111.33333333-3333-4333-8333-333333333333',
            'fields.11111111-1111-4111-8111-111111111111.33333333-3333-4333-8333-333333333334',
        ], array_column($sources, 'reference'));
        self::assertSame(['text'], $sources[0]['formats']);
        self::assertSame(['number', 'text'], $sources[1]['formats']);
        self::assertSame(['date', 'text'], $sources[2]['formats']);
        self::assertSame([
            'groupRevision' => 7,
            'fieldUuid' => '22222222-2222-4222-8222-222222222222',
            'logicalType' => 'string',
            'storageOwner' => 'native_post_meta',
            'postTypes' => ['page', 'post'],
        ], $sources[0]['ownerMetadata']);
        self::assertSame([
            'sort' => false,
            'filter' => false,
            'edit' => false,
            'export' => false,
        ], $sources[0]['capabilities']);
    }

    public function testMalformedOrUnboundedPublishedGroupIsSkippedWithoutBreakingCatalog(): void
    {
        $repository = new InMemoryDefinitionRepository();
        $definition = $this->definition(
            id: '88888888-8888-4888-8888-888888888888',
            status: DefinitionStatus::Published,
            revision: 2,
            fields: [[
                'uuid' => '99999999-9999-4999-8999-999999999999',
                'key' => 'headline',
                'label' => 'Headline',
                'type' => 'text',
            ]],
        );
        $payload = $definition->payload;
        $payload['locations'] = [[
            ['source' => 'post_status', 'operator' => 'equals', 'value' => 'publish'],
        ]];
        $repository->save(new Definition(
            id: $definition->id,
            slug: $definition->slug,
            type: $definition->type,
            schemaVersion: $definition->schemaVersion,
            ownerSurfaceId: $definition->ownerSurfaceId,
            status: $definition->status,
            payload: $payload,
            revision: $definition->revision,
        ));

        self::assertSame([], $this->catalog($repository)->adminColumnSources());
    }

    private function catalog(InMemoryDefinitionRepository $repository): FieldAdminColumnsSourceCatalog
    {
        return new FieldAdminColumnsSourceCatalog(
            $repository,
            new FieldGroupDefinitionNormalizer(),
            new FieldGroupRuntimeStorageProjection(),
            new FieldGroupPostTypeTargetCompiler(),
            new PostMetaRegistrationCompiler(),
        );
    }

    /** @param list<array<string,mixed>> $fields */
    private function definition(
        string $id,
        DefinitionStatus $status,
        int $revision,
        array $fields,
    ): Definition {
        return new Definition(
            id: $id,
            slug: 'catalog-' . substr($id, 0, 8),
            type: FieldGroupDefinitionNormalizer::DEFINITION_TYPE,
            schemaVersion: 1,
            ownerSurfaceId: FieldGroupDefinitionNormalizer::OWNER_SURFACE_ID,
            status: $status,
            payload: [
                'group_key' => 'catalog_' . substr($id, 0, 8),
                'title' => 'Catalog ' . substr($id, 0, 8),
                'fields' => $fields,
                'locations' => [[
                    ['source' => 'post_type', 'operator' => 'in', 'value' => ['post', 'page']],
                ]],
                'storage' => ['mode' => 'native_post_meta'],
                'show_in_rest' => false,
                'revision_policy' => 'disabled',
            ],
            revision: $revision,
        );
    }
}
