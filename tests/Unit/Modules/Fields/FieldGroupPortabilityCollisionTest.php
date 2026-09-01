<?php

declare(strict_types=1);

namespace WPEssential\Tests\Unit\Modules\Fields;

use InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use WPEssential\Modules\Fields\FieldGroupDefinitionNormalizer;
use WPEssential\Modules\Fields\FieldGroupPortabilityService;
use WPEssential\Modules\Fields\FieldGroupValidationService;
use WPEssential\Platform\Definitions\Definition;
use WPEssential\Platform\Definitions\DefinitionStatus;
use WPEssential\Platform\Definitions\InMemoryDefinitionRepository;

final class FieldGroupPortabilityCollisionTest extends TestCase
{
    public function testDuplicateSlugInBundleFailsBeforeAnyCreate(): void
    {
        $source = new InMemoryDefinitionRepository();
        $source->save($this->definition(
            '11111111-1111-4111-8111-111111111111',
            'field-group-alpha',
            'alpha',
            'aaaaaaaa-aaaa-4aaa-8aaa-aaaaaaaaaaaa',
        ));
        $source->save($this->definition(
            '22222222-2222-4222-8222-222222222222',
            'field-group-beta',
            'beta',
            'bbbbbbbb-bbbb-4bbb-8bbb-bbbbbbbbbbbb',
        ));
        $envelope = $this->service($source)->export();
        $envelope['definitions'][1]['slug'] = $envelope['definitions'][0]['slug'];

        $target = new InMemoryDefinitionRepository();
        try {
            $this->service($target)->import($envelope);
            self::fail('Duplicate portable slugs must fail before any definition is created.');
        } catch (InvalidArgumentException $exception) {
            self::assertStringContainsString('slug "field-group-alpha" is duplicated', $exception->getMessage());
        }

        self::assertSame([], $target->byType(FieldGroupDefinitionNormalizer::DEFINITION_TYPE));
    }

    public function testDestinationSlugCollisionFailsBeforeCreateAndDoesNotRemap(): void
    {
        $source = new InMemoryDefinitionRepository();
        $source->save($this->definition(
            '11111111-1111-4111-8111-111111111111',
            'field-group-events',
            'events',
            'aaaaaaaa-aaaa-4aaa-8aaa-aaaaaaaaaaaa',
        ));
        $envelope = $this->service($source)->export();

        $target = new InMemoryDefinitionRepository();
        $existing = $this->definition(
            '22222222-2222-4222-8222-222222222222',
            'field-group-events',
            'local-events',
            'bbbbbbbb-bbbb-4bbb-8bbb-bbbbbbbbbbbb',
        );
        $target->save($existing);

        try {
            $this->service($target)->import($envelope);
            self::fail('Destination slug ownership must fail closed instead of being remapped.');
        } catch (InvalidArgumentException $exception) {
            self::assertStringContainsString('already owned by definition', $exception->getMessage());
            self::assertStringContainsString('create-only import will not remap it', $exception->getMessage());
        }

        self::assertNull($target->get('11111111-1111-4111-8111-111111111111'));
        self::assertSame($existing->payload, $target->get($existing->id)?->payload);
    }

    private function service(InMemoryDefinitionRepository $repository): FieldGroupPortabilityService
    {
        $normalizer = new FieldGroupDefinitionNormalizer();
        return new FieldGroupPortabilityService(
            $repository,
            $normalizer,
            new FieldGroupValidationService($repository, $normalizer),
        );
    }

    private function definition(
        string $id,
        string $slug,
        string $groupKey,
        string $fieldUuid,
    ): Definition {
        $normalizer = new FieldGroupDefinitionNormalizer();
        $payload = $normalizer->normalize([
            'group_key' => $groupKey,
            'title' => ucfirst(str_replace('-', ' ', $groupKey)),
            'fields' => [[
                'uuid' => $fieldUuid,
                'key' => str_replace('-', '_', $groupKey) . '_title',
                'label' => 'Title',
                'type' => 'text',
            ]],
        ], false);
        $definition = new Definition(
            id: $id,
            slug: $slug,
            type: FieldGroupDefinitionNormalizer::DEFINITION_TYPE,
            schemaVersion: 1,
            ownerSurfaceId: FieldGroupDefinitionNormalizer::OWNER_SURFACE_ID,
            status: DefinitionStatus::Draft,
            payload: $payload,
            revision: 1,
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
            revision: 1,
            dependencies: [],
            checksum: $definition->computedChecksum(),
        );
    }
}
