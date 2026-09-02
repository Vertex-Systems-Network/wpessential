<?php

declare(strict_types=1);

namespace WPEssential\Tests\Unit\Modules\Relations;

use InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use WPEssential\Modules\Relations\RelationDefinitionNormalizer;
use WPEssential\Modules\Relations\RelationDefinitionValidationService;
use WPEssential\Modules\Relations\RelationEndpointSupport;
use WPEssential\Modules\Relations\RelationPortabilityService;
use WPEssential\Platform\Definitions\Definition;
use WPEssential\Platform\Definitions\DefinitionStatus;
use WPEssential\Platform\Definitions\InMemoryDefinitionRepository;

final class RelationPortabilityServiceTest extends TestCase
{
    private const RELATION_ID = '11111111-1111-4111-8111-111111111111';
    private const OTHER_ID = '22222222-2222-4222-8222-222222222222';

    public function testExportAndCreateOnlyImportRoundTrip(): void
    {
        $source = new InMemoryDefinitionRepository();
        $definition = $this->definition(self::RELATION_ID, 'book_authors', 7);
        $source->save($definition);
        $envelope = $this->service($source)->export([self::RELATION_ID]);

        self::assertSame(RelationPortabilityService::FORMAT, $envelope['format']);
        self::assertSame(RelationPortabilityService::FORMAT_VERSION, $envelope['version']);
        self::assertSame(7, $envelope['definitions'][0]['source_revision']);
        self::assertSame($definition->computedChecksum(), $envelope['definitions'][0]['checksum']);

        $target = new InMemoryDefinitionRepository();
        $service = $this->service($target);
        self::assertSame(
            ['created' => [self::RELATION_ID], 'unchanged' => []],
            $service->import($envelope),
        );

        $imported = $target->get(self::RELATION_ID);
        self::assertInstanceOf(Definition::class, $imported);
        self::assertSame('book_authors', $imported->payload['relation_key']);
        self::assertSame($definition->computedChecksum(), $imported->checksum);

        self::assertSame(
            ['created' => [], 'unchanged' => [self::RELATION_ID]],
            $service->import($envelope),
        );
    }

    public function testImportRejectsUnsupportedFormatVersionBeforePersistence(): void
    {
        $source = new InMemoryDefinitionRepository();
        $source->save($this->definition(self::RELATION_ID, 'book_authors'));
        $envelope = $this->service($source)->export();
        $envelope['version'] = 2;

        $target = new InMemoryDefinitionRepository();
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('compatibility adapter');
        try {
            $this->service($target)->import($envelope);
        } finally {
            self::assertSame([], $target->byType(RelationDefinitionNormalizer::DEFINITION_TYPE));
        }
    }

    public function testImportRejectsChecksumTampering(): void
    {
        $source = new InMemoryDefinitionRepository();
        $source->save($this->definition(self::RELATION_ID, 'book_authors'));
        $envelope = $this->service($source)->export();
        $envelope['definitions'][0]['payload']['title'] = 'Tampered title';

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('checksum verification failed');
        $this->service(new InMemoryDefinitionRepository())->import($envelope);
    }

    public function testImportRejectsExistingRelationKeyCollisionWithoutOverwrite(): void
    {
        $source = new InMemoryDefinitionRepository();
        $source->save($this->definition(self::RELATION_ID, 'book_authors'));
        $envelope = $this->service($source)->export();

        $target = new InMemoryDefinitionRepository();
        $existing = $this->definition(self::OTHER_ID, 'book_authors');
        $target->save($existing);

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Relation key "book_authors" is already owned');
        try {
            $this->service($target)->import($envelope);
        } finally {
            self::assertSame($existing->computedChecksum(), $target->get(self::OTHER_ID)?->computedChecksum());
            self::assertNull($target->get(self::RELATION_ID));
        }
    }

    private function service(InMemoryDefinitionRepository $definitions): RelationPortabilityService
    {
        $normalizer = new RelationDefinitionNormalizer();
        $validation = new RelationDefinitionValidationService(
            $normalizer,
            new RelationEndpointSupport(
                static fn (string $postType): bool => $postType === 'book',
                static fn (string $taxonomy): bool => true,
            ),
        );
        return new RelationPortabilityService($definitions, $normalizer, $validation);
    }

    private function definition(string $id, string $key, int $revision = 1): Definition
    {
        $normalizer = new RelationDefinitionNormalizer();
        $payload = $normalizer->normalize([
            'relation_key' => $key,
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
        $slug = 'relation-' . str_replace('_', '-', $key) . ($id === self::OTHER_ID ? '-other' : '');
        $definition = new Definition(
            id: $id,
            slug: $slug,
            type: RelationDefinitionNormalizer::DEFINITION_TYPE,
            schemaVersion: 1,
            ownerSurfaceId: RelationDefinitionNormalizer::OWNER_SURFACE_ID,
            status: DefinitionStatus::Published,
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
}
