<?php

declare(strict_types=1);

namespace WPEssential\Tests\Unit\Modules\Taxonomies;

use PHPUnit\Framework\TestCase;
use RuntimeException;
use WPEssential\Modules\Taxonomies\TaxonomyDefinitionProjector;
use WPEssential\Modules\Taxonomies\TaxonomyImportAbilityHandler;
use WPEssential\Modules\Taxonomies\TaxonomyValidationService;
use WPEssential\Platform\Auth\ExecutionContext;
use WPEssential\Platform\Auth\Principal;
use WPEssential\Platform\Definitions\Definition;
use WPEssential\Platform\Definitions\DefinitionStatus;
use WPEssential\Platform\Definitions\InMemoryDefinitionRepository;

final class TaxonomyImportAbilityHandlerTest extends TestCase
{
    public function testCreatePreservesPortableUuidAndExternalObjectTypeAssociation(): void
    {
        $repository = new InMemoryDefinitionRepository();
        $record = $this->record();

        $result = $this->handler($repository)->handle([
            'definition' => $record,
            'strategy' => 'create_only',
        ], $this->context());

        self::assertSame('created', $result['action']);
        self::assertSame($record['id'], $result['definition']['id']);
        self::assertSame(['post', 'external_book'], $result['definition']['payload']['object_types']);
        self::assertSame(1, $result['definition']['revision']);
        self::assertNotNull($repository->get($record['id']));
    }

    public function testDifferentUuidCannotTakeExistingRuntimeKey(): void
    {
        $repository = new InMemoryDefinitionRepository();
        $this->handler($repository)->handle([
            'definition' => $this->record(),
            'strategy' => 'create_only',
        ], $this->context());

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Taxonomy import key collision');
        $this->handler($repository)->handle([
            'definition' => $this->record([], '44444444-4444-4444-8444-444444444444'),
            'strategy' => 'create_only',
        ], $this->context());
    }

    private function handler(InMemoryDefinitionRepository $repository): TaxonomyImportAbilityHandler
    {
        $projector = new TaxonomyDefinitionProjector();
        return new TaxonomyImportAbilityHandler(
            $repository,
            $projector,
            new TaxonomyValidationService($repository, $projector),
        );
    }

    private function context(): ExecutionContext
    {
        return new ExecutionContext(new Principal(1), 1);
    }

    /** @param array<string,mixed> $overrides @return array<string,mixed> */
    private function record(
        array $overrides = [],
        string $id = '33333333-3333-4333-8333-333333333333',
    ): array {
        $payload = array_merge([
            'taxonomy_key' => 'portable_genre',
            'object_types' => ['post', 'external_book'],
            'name' => 'Portable Genres',
            'singular_name' => 'Portable Genre',
            'public' => true,
            'show_in_rest' => true,
        ], $overrides);
        $definition = new Definition(
            id: $id,
            slug: 'taxonomy-portable-genre',
            type: TaxonomyDefinitionProjector::DEFINITION_TYPE,
            schemaVersion: 1,
            ownerSurfaceId: TaxonomyDefinitionProjector::OWNER_SURFACE_ID,
            status: DefinitionStatus::Draft,
            payload: $payload,
            revision: 5,
        );

        return [
            'id' => $definition->id,
            'slug' => $definition->slug,
            'type' => $definition->type,
            'schema_version' => $definition->schemaVersion,
            'owner_surface_id' => $definition->ownerSurfaceId,
            'status' => $definition->status->value,
            'payload' => $definition->payload,
            'source_revision' => $definition->revision,
            'dependencies' => $definition->dependencies,
            'checksum' => $definition->computedChecksum(),
        ];
    }
}
