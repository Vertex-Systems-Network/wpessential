<?php

declare(strict_types=1);

namespace WPEssential\Tests\Unit\Modules\Fields;

use InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use RuntimeException;
use WPEssential\Modules\Fields\FieldGroupDefinitionNormalizer;
use WPEssential\Modules\Fields\FieldGroupPortabilityService;
use WPEssential\Modules\Fields\FieldGroupValidationService;
use WPEssential\Platform\Definitions\Definition;
use WPEssential\Platform\Definitions\DefinitionStatus;
use WPEssential\Platform\Definitions\InMemoryDefinitionRepository;

final class FieldGroupPortabilityCompatibilityTest extends TestCase
{
    public function testUnknownEnvelopeOptionFailsBeforeMutation(): void
    {
        $source = new InMemoryDefinitionRepository();
        $source->save($this->definition(schemaVersion: 1));
        $envelope = $this->service($source)->export();
        $envelope['future_semantics'] = ['mode' => 'merge'];

        $target = new InMemoryDefinitionRepository();
        try {
            $this->service($target)->import($envelope);
            self::fail('Unknown envelope options must require an explicit compatibility adapter.');
        } catch (InvalidArgumentException $exception) {
            self::assertStringContainsString('Unsupported portability envelope option', $exception->getMessage());
        }

        self::assertSame([], $target->byType(FieldGroupDefinitionNormalizer::DEFINITION_TYPE));
    }

    public function testUnknownDefinitionOptionFailsBeforeMutation(): void
    {
        $source = new InMemoryDefinitionRepository();
        $source->save($this->definition(schemaVersion: 1));
        $envelope = $this->service($source)->export();
        $envelope['definitions'][0]['future_semantics'] = ['merge' => true];

        $target = new InMemoryDefinitionRepository();
        try {
            $this->service($target)->import($envelope);
            self::fail('Unknown definition options must require an explicit compatibility adapter.');
        } catch (InvalidArgumentException $exception) {
            self::assertStringContainsString('Unsupported portable Field Group definition option', $exception->getMessage());
        }

        self::assertSame([], $target->byType(FieldGroupDefinitionNormalizer::DEFINITION_TYPE));
    }

    public function testExporterRejectsUnsupportedDefinitionSchemaGeneration(): void
    {
        $repository = new InMemoryDefinitionRepository();
        $repository->save($this->definition(schemaVersion: 2));

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('explicit compatibility adapter');
        $this->service($repository)->export();
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

    private function definition(int $schemaVersion): Definition
    {
        $normalizer = new FieldGroupDefinitionNormalizer();
        $payload = $normalizer->normalize([
            'group_key' => 'events',
            'title' => 'Events',
            'fields' => [[
                'uuid' => 'aaaaaaaa-aaaa-4aaa-8aaa-aaaaaaaaaaaa',
                'key' => 'event_title',
                'label' => 'Title',
                'type' => 'text',
            ]],
        ], false);
        $definition = new Definition(
            id: '11111111-1111-4111-8111-111111111111',
            slug: 'field-group-events',
            type: FieldGroupDefinitionNormalizer::DEFINITION_TYPE,
            schemaVersion: $schemaVersion,
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
