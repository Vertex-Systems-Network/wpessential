<?php

declare(strict_types=1);

namespace WPEssential\Tests\Unit\Modules\Taxonomies;

use InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use RuntimeException;
use WPEssential\Modules\Taxonomies\TaxonomyAbilityHandler;
use WPEssential\Modules\Taxonomies\TaxonomyDefinitionProjector;
use WPEssential\Modules\Taxonomies\TaxonomyValidationService;
use WPEssential\Platform\Auth\ExecutionContext;
use WPEssential\Platform\Auth\Principal;
use WPEssential\Platform\Definitions\Definition;
use WPEssential\Platform\Definitions\DefinitionStatus;
use WPEssential\Platform\Definitions\InMemoryDefinitionRepository;

final class TaxonomyDefinitionPortabilityTest extends TestCase
{
    private const ID = '11111111-1111-4111-8111-111111111111';
    private const OTHER_ID = '22222222-2222-4222-8222-222222222222';
    private const DEPENDENCY_ID = '33333333-3333-4333-8333-333333333333';

    public function testCreatePreservesPortableIdentityAndIdenticalImportIsNoChange(): void
    {
        $repository = new InMemoryDefinitionRepository();
        $handler = $this->handler($repository);
        $record = $this->record(
            id: self::ID,
            slug: 'taxonomy-library-genre',
            sourceRevision: 7,
            dependencies: [self::DEPENDENCY_ID],
        );

        $created = $handler->handle(
            ['definition' => $record],
            $this->context(),
        );

        self::assertSame('created', $created['action']);
        self::assertSame(self::ID, $created['definition']['id']);
        self::assertSame('taxonomy-library-genre', $created['definition']['slug']);
        self::assertSame(1, $created['definition']['revision']);
        self::assertSame([self::DEPENDENCY_ID], $created['definition']['dependencies']);
        self::assertSame($record['checksum'], $created['definition']['checksum']);

        $sameRecord = $record;
        $sameRecord['revision'] = 99;
        $unchanged = $handler->handle(
            ['definition' => $sameRecord, 'strategy' => 'update_existing', 'expected_revision' => 1],
            $this->context(),
        );

        self::assertSame('no_change', $unchanged['action']);
        self::assertSame(1, $unchanged['definition']['revision']);
        $persisted = $repository->get(self::ID);
        self::assertInstanceOf(Definition::class, $persisted);
        self::assertSame(1, $persisted->revision);
    }

    public function testExplicitUpdateUsesTargetCasAndRejectsStaleRevisionWithoutMutation(): void
    {
        $repository = new InMemoryDefinitionRepository();
        $handler = $this->handler($repository);
        $handler->handle(['definition' => $this->record()], $this->context());

        $updatedRecord = $this->record(name: 'Imported Genres', sourceRevision: 44);
        $updated = $handler->handle([
            'definition' => $updatedRecord,
            'strategy' => 'update_existing',
            'expected_revision' => 1,
        ], $this->context());

        self::assertSame('updated', $updated['action']);
        self::assertSame(2, $updated['definition']['revision']);
        self::assertSame('Imported Genres', $updated['definition']['payload']['name']);

        $staleRecord = $this->record(name: 'Stale Genres', sourceRevision: 45);
        try {
            $handler->handle([
                'definition' => $staleRecord,
                'strategy' => 'update_existing',
                'expected_revision' => 1,
            ], $this->context());
            self::fail('Expected stale portability revision to fail closed.');
        } catch (RuntimeException $exception) {
            self::assertStringContainsString('Taxonomy write conflict', $exception->getMessage());
        }

        $persisted = $repository->get(self::ID);
        self::assertInstanceOf(Definition::class, $persisted);
        self::assertSame(2, $persisted->revision);
        self::assertSame('Imported Genres', $persisted->payload['name']);
    }

    public function testIdentityConflictsAndKeyMigrationFailClosed(): void
    {
        $repository = new InMemoryDefinitionRepository();
        $handler = $this->handler($repository);
        $handler->handle(['definition' => $this->record()], $this->context());

        try {
            $handler->handle([
                'definition' => $this->record(
                    id: self::OTHER_ID,
                    slug: 'taxonomy-other-genre',
                ),
            ], $this->context());
            self::fail('Expected taxonomy-key collision to fail closed.');
        } catch (RuntimeException $exception) {
            self::assertStringContainsString('key collision', $exception->getMessage());
        }

        try {
            $handler->handle([
                'definition' => $this->record(
                    id: self::OTHER_ID,
                    slug: 'taxonomy-library-genre',
                    taxonomyKey: 'library_topic',
                ),
            ], $this->context());
            self::fail('Expected definition-slug collision to fail closed.');
        } catch (RuntimeException $exception) {
            self::assertStringContainsString('slug collision', $exception->getMessage());
        }

        try {
            $handler->handle([
                'definition' => $this->record(taxonomyKey: 'renamed_genre'),
                'strategy' => 'update_existing',
                'expected_revision' => 1,
            ], $this->context());
            self::fail('Expected taxonomy-key migration to remain blocked.');
        } catch (InvalidArgumentException $exception) {
            self::assertStringContainsString('key cannot be changed', $exception->getMessage());
        }

        self::assertCount(1, $repository->byType(TaxonomyDefinitionProjector::DEFINITION_TYPE));
    }

    public function testTamperedChecksumAndUnsupportedMetadataFailBeforeMutation(): void
    {
        $repository = new InMemoryDefinitionRepository();
        $handler = $this->handler($repository);

        $tampered = $this->record();
        $tampered['payload']['name'] = 'Tampered';
        try {
            $handler->handle(['definition' => $tampered], $this->context());
            self::fail('Expected checksum tampering to fail closed.');
        } catch (InvalidArgumentException $exception) {
            self::assertStringContainsString('checksum does not match', $exception->getMessage());
        }

        $wrongOwner = $this->record();
        $wrongOwner['owner_surface_id'] = 1;
        try {
            $handler->handle(['definition' => $wrongOwner], $this->context());
            self::fail('Expected unsupported owner metadata to fail closed.');
        } catch (InvalidArgumentException $exception) {
            self::assertStringContainsString('metadata is invalid or unsupported', $exception->getMessage());
        }

        $invalidDependency = $this->record();
        $invalidDependency['dependencies'] = ['not-a-uuid'];
        try {
            $handler->handle(['definition' => $invalidDependency], $this->context());
            self::fail('Expected invalid dependency identity to fail closed.');
        } catch (InvalidArgumentException $exception) {
            self::assertStringContainsString('dependencies must be lowercase RFC 4122 UUIDs', $exception->getMessage());
        }

        self::assertSame([], $repository->byType(TaxonomyDefinitionProjector::DEFINITION_TYPE));
    }

    private function handler(InMemoryDefinitionRepository $repository): TaxonomyAbilityHandler
    {
        $projector = new TaxonomyDefinitionProjector();
        return new TaxonomyAbilityHandler(
            $repository,
            $projector,
            new TaxonomyValidationService($repository, $projector),
            TaxonomyAbilityHandler::IMPORT,
        );
    }

    private function context(): ExecutionContext
    {
        return new ExecutionContext(new Principal(1), 1);
    }

    /**
     * @param list<string> $dependencies
     * @return array<string,mixed>
     */
    private function record(
        string $id = self::ID,
        string $slug = 'taxonomy-library-genre',
        string $taxonomyKey = 'library_genre',
        string $name = 'Genres',
        int $sourceRevision = 1,
        array $dependencies = [],
    ): array {
        $definition = new Definition(
            id: $id,
            slug: $slug,
            type: TaxonomyDefinitionProjector::DEFINITION_TYPE,
            schemaVersion: 1,
            ownerSurfaceId: TaxonomyDefinitionProjector::OWNER_SURFACE_ID,
            status: DefinitionStatus::Draft,
            payload: [
                'taxonomy_key' => $taxonomyKey,
                'object_types' => ['post'],
                'name' => $name,
                'singular_name' => 'Genre',
                'public' => true,
                'show_in_rest' => true,
            ],
            revision: $sourceRevision,
            dependencies: $dependencies,
        );

        return [
            'id' => $definition->id,
            'slug' => $definition->slug,
            'type' => $definition->type,
            'schema_version' => $definition->schemaVersion,
            'owner_surface_id' => $definition->ownerSurfaceId,
            'status' => $definition->status->value,
            'payload' => $definition->payload,
            'revision' => $definition->revision,
            'dependencies' => $definition->dependencies,
            'checksum' => $definition->computedChecksum(),
        ];
    }
}
