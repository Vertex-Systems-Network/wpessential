<?php

declare(strict_types=1);

namespace WPEssential\Tests\Unit\Modules\CustomPostTypes;

use PHPUnit\Framework\TestCase;
use RuntimeException;
use WPEssential\Modules\CustomPostTypes\CustomPostTypeDefinitionProjector;
use WPEssential\Modules\CustomPostTypes\CustomPostTypeImportAbilityHandler;
use WPEssential\Modules\CustomPostTypes\CustomPostTypeValidationService;
use WPEssential\Platform\Auth\ExecutionContext;
use WPEssential\Platform\Auth\Principal;
use WPEssential\Platform\Definitions\Definition;
use WPEssential\Platform\Definitions\DefinitionStatus;
use WPEssential\Platform\Definitions\InMemoryDefinitionRepository;

final class CustomPostTypeImportAbilityHandlerTest extends TestCase
{
    public function testCreatePreservesPortableUuidAndCanonicalizesRevision(): void
    {
        $repository = new InMemoryDefinitionRepository();
        $record = $this->record();

        $result = $this->handler($repository)->handle([
            'definition' => $record,
            'strategy' => 'create_only',
        ], $this->context());

        self::assertSame('created', $result['action']);
        self::assertSame($record['id'], $result['definition']['id']);
        self::assertSame(1, $result['definition']['revision']);
        self::assertSame($record['checksum'], $result['definition']['checksum']);
        self::assertNotNull($repository->get($record['id']));
    }

    public function testUpdateRequiresExactTargetRevision(): void
    {
        $repository = new InMemoryDefinitionRepository();
        $created = $this->handler($repository)->handle([
            'definition' => $this->record(),
            'strategy' => 'create_only',
        ], $this->context())['definition'];
        $updatedRecord = $this->record(['description' => 'Portable catalogue']);

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('CPT import write conflict');
        $this->handler($repository)->handle([
            'definition' => $updatedRecord,
            'strategy' => 'update_existing',
            'expected_revision' => $created['revision'] + 1,
        ], $this->context());
    }

    private function handler(InMemoryDefinitionRepository $repository): CustomPostTypeImportAbilityHandler
    {
        $projector = new CustomPostTypeDefinitionProjector();
        return new CustomPostTypeImportAbilityHandler(
            $repository,
            $projector,
            new CustomPostTypeValidationService($repository, $projector),
        );
    }

    private function context(): ExecutionContext
    {
        return new ExecutionContext(new Principal(1), 1);
    }

    /** @param array<string,mixed> $overrides @return array<string,mixed> */
    private function record(array $overrides = []): array
    {
        $payload = array_merge([
            'post_type_key' => 'portable_book',
            'name' => 'Portable Books',
            'singular_name' => 'Portable Book',
            'public' => true,
            'show_in_rest' => true,
            'supports' => ['title', 'editor'],
        ], $overrides);
        $definition = new Definition(
            id: '22222222-2222-4222-8222-222222222222',
            slug: 'cpt-portable-book',
            type: CustomPostTypeDefinitionProjector::DEFINITION_TYPE,
            schemaVersion: 1,
            ownerSurfaceId: CustomPostTypeDefinitionProjector::OWNER_SURFACE_ID,
            status: DefinitionStatus::Draft,
            payload: $payload,
            revision: 7,
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
