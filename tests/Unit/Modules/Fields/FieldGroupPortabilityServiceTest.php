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

final class FieldGroupPortabilityServiceTest extends TestCase
{
    public function testExportIsDeterministicAndCarriesVerifiedPortableProvenance(): void
    {
        $repository = new InMemoryDefinitionRepository();
        $repository->save($this->definition(
            '22222222-2222-4222-8222-222222222222',
            'field-group-zulu',
            'zulu_group',
            'bbbbbbbb-bbbb-4bbb-8bbb-bbbbbbbbbbbb',
            revision: 9,
        ));
        $repository->save($this->definition(
            '11111111-1111-4111-8111-111111111111',
            'field-group-alpha',
            'alpha_group',
            'aaaaaaaa-aaaa-4aaa-8aaa-aaaaaaaaaaaa',
            revision: 4,
        ));

        $export = $this->service($repository)->export();

        self::assertSame(FieldGroupPortabilityService::FORMAT, $export['format']);
        self::assertSame(1, $export['version']);
        self::assertSame([
            '11111111-1111-4111-8111-111111111111',
            '22222222-2222-4222-8222-222222222222',
        ], array_column($export['definitions'], 'id'));
        self::assertSame([4, 9], array_column($export['definitions'], 'source_revision'));
        self::assertSame([[], []], array_column($export['definitions'], 'dependencies'));
        self::assertMatchesRegularExpression('/^[0-9a-f]{64}$/', $export['definitions'][0]['checksum']);
    }

    public function testRoundTripPreservesIdentityAndResetsOnlyLocalRevision(): void
    {
        $source = new InMemoryDefinitionRepository();
        $definition = $this->definition(
            '11111111-1111-4111-8111-111111111111',
            'field-group-events',
            'events',
            'aaaaaaaa-aaaa-4aaa-8aaa-aaaaaaaaaaaa',
            DefinitionStatus::Published,
            12,
        );
        $source->save($definition);
        $envelope = $this->service($source)->export();

        $target = new InMemoryDefinitionRepository();
        $result = $this->service($target)->import($envelope);

        self::assertSame([$definition->id], $result['created']);
        self::assertSame([], $result['unchanged']);
        $imported = $target->get($definition->id);
        self::assertInstanceOf(Definition::class, $imported);
        self::assertSame(1, $imported->revision);
        self::assertSame(DefinitionStatus::Published, $imported->status);
        self::assertSame($definition->payload, $imported->payload);
        self::assertSame($definition->payload['fields'][0]['uuid'], $imported->payload['fields'][0]['uuid']);
        self::assertSame($definition->computedChecksum(), $imported->checksum);

        $second = $this->service($target)->import($envelope);
        self::assertSame([], $second['created']);
        self::assertSame([$definition->id], $second['unchanged']);
        self::assertSame(1, $target->get($definition->id)?->revision);
    }

    public function testChecksumTamperFailsBeforeRepositoryMutation(): void
    {
        $source = new InMemoryDefinitionRepository();
        $source->save($this->definition(
            '11111111-1111-4111-8111-111111111111',
            'field-group-events',
            'events',
            'aaaaaaaa-aaaa-4aaa-8aaa-aaaaaaaaaaaa',
        ));
        $envelope = $this->service($source)->export();
        $envelope['definitions'][0]['payload']['title'] = 'Tampered title';

        $target = new InMemoryDefinitionRepository();
        try {
            $this->service($target)->import($envelope);
            self::fail('Tampered portable payload must fail checksum verification.');
        } catch (InvalidArgumentException $exception) {
            self::assertStringContainsString('checksum verification failed', $exception->getMessage());
        }

        self::assertSame([], $target->byType(FieldGroupDefinitionNormalizer::DEFINITION_TYPE));
    }

    public function testDivergentSameIdCollisionIsCreateOnlyAndDoesNotOverwrite(): void
    {
        $source = new InMemoryDefinitionRepository();
        $sourceDefinition = $this->definition(
            '11111111-1111-4111-8111-111111111111',
            'field-group-events',
            'events',
            'aaaaaaaa-aaaa-4aaa-8aaa-aaaaaaaaaaaa',
        );
        $source->save($sourceDefinition);
        $envelope = $this->service($source)->export();

        $target = new InMemoryDefinitionRepository();
        $existing = $this->definition(
            $sourceDefinition->id,
            'field-group-local-events',
            'local_events',
            'bbbbbbbb-bbbb-4bbb-8bbb-bbbbbbbbbbbb',
        );
        $target->save($existing);

        try {
            $this->service($target)->import($envelope);
            self::fail('Non-identical same-id import must fail closed.');
        } catch (InvalidArgumentException $exception) {
            self::assertStringContainsString('create-only import will not overwrite', $exception->getMessage());
        }

        self::assertSame($existing->payload, $target->get($existing->id)?->payload);
        self::assertSame($existing->slug, $target->get($existing->id)?->slug);
    }

    public function testCompatibilityAndOwnershipDriftFailClosed(): void
    {
        $source = new InMemoryDefinitionRepository();
        $source->save($this->definition(
            '11111111-1111-4111-8111-111111111111',
            'field-group-events',
            'events',
            'aaaaaaaa-aaaa-4aaa-8aaa-aaaaaaaaaaaa',
        ));
        $baseline = $this->service($source)->export();

        $mutations = [
            static function (array &$envelope): void {
                $envelope['version'] = 2;
            },
            static function (array &$envelope): void {
                $envelope['definitions'][0]['schema_version'] = 2;
            },
            static function (array &$envelope): void {
                $envelope['definitions'][0]['owner_surface_id'] = 4;
            },
            static function (array &$envelope): void {
                $envelope['definitions'][0]['type'] = 'relation';
            },
            static function (array &$envelope): void {
                $envelope['definitions'][0]['dependencies'] = ['22222222-2222-4222-8222-222222222222'];
            },
        ];

        foreach ($mutations as $mutation) {
            $envelope = $baseline;
            $mutation($envelope);
            $target = new InMemoryDefinitionRepository();
            try {
                $this->service($target)->import($envelope);
                self::fail('Unsupported portability compatibility/ownership drift must fail closed.');
            } catch (InvalidArgumentException) {
                self::assertSame([], $target->byType(FieldGroupDefinitionNormalizer::DEFINITION_TYPE));
            }
        }
    }

    public function testCrossGroupFieldUuidCollisionFailsBeforeCreate(): void
    {
        $sharedFieldUuid = 'aaaaaaaa-aaaa-4aaa-8aaa-aaaaaaaaaaaa';
        $source = new InMemoryDefinitionRepository();
        $source->save($this->definition(
            '11111111-1111-4111-8111-111111111111',
            'field-group-events',
            'events',
            $sharedFieldUuid,
        ));
        $envelope = $this->service($source)->export();

        $target = new InMemoryDefinitionRepository();
        $targetDefinition = $this->definition(
            '22222222-2222-4222-8222-222222222222',
            'field-group-local',
            'local',
            $sharedFieldUuid,
        );
        $target->save($targetDefinition);

        try {
            $this->service($target)->import($envelope);
            self::fail('Cross-group stable Field UUID collision must fail closed.');
        } catch (InvalidArgumentException $exception) {
            self::assertStringContainsString('already owned by another canonical Field Group', $exception->getMessage());
        }

        self::assertNull($target->get('11111111-1111-4111-8111-111111111111'));
        self::assertSame($targetDefinition->payload, $target->get($targetDefinition->id)?->payload);
    }

    public function testBundleDuplicateFieldIdentityFailsBeforeAnyCreate(): void
    {
        $source = new InMemoryDefinitionRepository();
        $sharedFieldUuid = 'aaaaaaaa-aaaa-4aaa-8aaa-aaaaaaaaaaaa';
        $source->save($this->definition(
            '11111111-1111-4111-8111-111111111111',
            'field-group-alpha',
            'alpha',
            $sharedFieldUuid,
        ));
        $source->save($this->definition(
            '22222222-2222-4222-8222-222222222222',
            'field-group-beta',
            'beta',
            'bbbbbbbb-bbbb-4bbb-8bbb-bbbbbbbbbbbb',
        ));
        $envelope = $this->service($source)->export();
        $envelope['definitions'][1]['payload']['fields'][0]['uuid'] = $sharedFieldUuid;
        $second = $this->candidateFromRecord($envelope['definitions'][1]);
        $envelope['definitions'][1]['checksum'] = $second->computedChecksum();

        $target = new InMemoryDefinitionRepository();
        try {
            $this->service($target)->import($envelope);
            self::fail('Duplicate Field UUID across one import bundle must fail before create.');
        } catch (InvalidArgumentException $exception) {
            self::assertStringContainsString('duplicated across the import bundle', $exception->getMessage());
        }

        self::assertSame([], $target->byType(FieldGroupDefinitionNormalizer::DEFINITION_TYPE));
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
        DefinitionStatus $status = DefinitionStatus::Draft,
        int $revision = 1,
    ): Definition {
        $normalizer = new FieldGroupDefinitionNormalizer();
        $payload = $normalizer->normalize([
            'group_key' => $groupKey,
            'title' => ucfirst(str_replace('_', ' ', $groupKey)),
            'fields' => [[
                'uuid' => $fieldUuid,
                'key' => $groupKey . '_title',
                'label' => 'Title',
                'type' => 'text',
            ]],
        ], $status === DefinitionStatus::Published);
        $definition = new Definition(
            id: $id,
            slug: $slug,
            type: FieldGroupDefinitionNormalizer::DEFINITION_TYPE,
            schemaVersion: 1,
            ownerSurfaceId: FieldGroupDefinitionNormalizer::OWNER_SURFACE_ID,
            status: $status,
            payload: $payload,
            revision: $revision,
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

    /** @param array<string,mixed> $record */
    private function candidateFromRecord(array $record): Definition
    {
        $status = DefinitionStatus::from((string) $record['status']);
        $payload = (new FieldGroupDefinitionNormalizer())->normalize(
            $record['payload'],
            $status === DefinitionStatus::Published,
        );
        return new Definition(
            id: (string) $record['id'],
            slug: (string) $record['slug'],
            type: (string) $record['type'],
            schemaVersion: (int) $record['schema_version'],
            ownerSurfaceId: (int) $record['owner_surface_id'],
            status: $status,
            payload: $payload,
            revision: 1,
            dependencies: [],
        );
    }
}
