<?php

declare(strict_types=1);

namespace WPEssential\Tests\Unit\Modules\Fields;

use PHPUnit\Framework\TestCase;
use WPEssential\Modules\Fields\FieldGroupDefinitionNormalizer;
use WPEssential\Modules\Fields\FieldGroupValidationService;
use WPEssential\Platform\Definitions\Definition;
use WPEssential\Platform\Definitions\DefinitionStatus;
use WPEssential\Platform\Definitions\InMemoryDefinitionRepository;

final class FieldGroupValidationServiceTest extends TestCase
{
    public function testRejectsDuplicateGroupKeyAcrossSurfaceThreeDefinitions(): void
    {
        $repository = new InMemoryDefinitionRepository();
        $repository->save($this->definition(
            '11111111-1111-4111-8111-111111111111',
            'profile',
            ['group_key' => 'profile', 'title' => 'Profile', 'fields' => []],
        ));
        $service = new FieldGroupValidationService($repository, new FieldGroupDefinitionNormalizer());

        $report = $service->validate([
            'payload' => ['group_key' => 'profile', 'title' => 'Another Profile', 'fields' => []],
        ]);

        self::assertFalse($report['valid']);
        self::assertSame('duplicate_group_key', $report['issues'][0]['id']);
    }

    public function testRejectsChangingExistingGroupKey(): void
    {
        $repository = new InMemoryDefinitionRepository();
        $definition = $this->definition(
            '22222222-2222-4222-8222-222222222222',
            'contact',
            ['group_key' => 'contact', 'title' => 'Contact', 'fields' => []],
        );
        $repository->save($definition);
        $service = new FieldGroupValidationService($repository, new FieldGroupDefinitionNormalizer());

        $report = $service->validate([
            'id' => $definition->id,
            'payload' => ['group_key' => 'contact_v2', 'title' => 'Contact', 'fields' => []],
        ]);

        self::assertFalse($report['valid']);
        self::assertSame('group_key_immutable', $report['issues'][0]['id']);
    }

    public function testPublishValidationRequiresAtLeastOneField(): void
    {
        $service = new FieldGroupValidationService(new InMemoryDefinitionRepository(), new FieldGroupDefinitionNormalizer());
        $report = $service->validate([
            'status' => 'published',
            'payload' => ['group_key' => 'empty', 'title' => 'Empty', 'fields' => []],
        ]);

        self::assertFalse($report['valid']);
        self::assertSame('schema_invalid', $report['issues'][0]['id']);
    }

    public function testReportsNormalizedFieldCountForValidPublishCandidate(): void
    {
        $service = new FieldGroupValidationService(new InMemoryDefinitionRepository(), new FieldGroupDefinitionNormalizer());
        $report = $service->validate([
            'status' => 'published',
            'payload' => [
                'group_key' => 'article_meta',
                'title' => 'Article Meta',
                'fields' => [
                    ['key' => 'subtitle', 'label' => 'Subtitle', 'type' => 'text'],
                    ['key' => 'topics', 'label' => 'Topics', 'type' => 'multi_select'],
                ],
                'locations' => [[['source' => 'post_type', 'operator' => 'equals', 'value' => 'post']]],
                'storage' => ['mode' => 'native_post_meta'],
            ],
        ]);

        self::assertTrue($report['valid']);
        self::assertSame(2, $report['candidate']['field_count']);
    }

    public function testPublishIsBlockedWhenRuntimeCannotPlaceTheGroup(): void
    {
        $service = new FieldGroupValidationService(new InMemoryDefinitionRepository(), new FieldGroupDefinitionNormalizer());
        $fields = [['key' => 'subtitle', 'label' => 'Subtitle', 'type' => 'text']];

        $unplaced = $service->validate(['status' => 'published', 'payload' => ['group_key' => 'a', 'title' => 'A', 'fields' => $fields]]);
        self::assertFalse($unplaced['valid']);
        self::assertContains('runtime_binding_unsupported', array_column($unplaced['issues'], 'id'));

        $draft = $service->validate(['status' => 'draft', 'payload' => ['group_key' => 'a', 'title' => 'A', 'fields' => $fields]]);
        self::assertTrue($draft['valid']);
    }

    /** @param array<string,mixed> $payload */
    private function definition(string $id, string $slug, array $payload): Definition
    {
        return new Definition(
            id: $id,
            slug: 'field-group-' . $slug,
            type: FieldGroupDefinitionNormalizer::DEFINITION_TYPE,
            schemaVersion: 1,
            ownerSurfaceId: FieldGroupDefinitionNormalizer::OWNER_SURFACE_ID,
            status: DefinitionStatus::Draft,
            payload: $payload,
        );
    }
}
