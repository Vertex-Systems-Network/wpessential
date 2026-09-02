<?php

declare(strict_types=1);

namespace WPEssential\Tests\Unit\Modules\Relations;

use InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use RuntimeException;
use WPEssential\Modules\Relations\RelationDefinitionAbilityHandler;
use WPEssential\Modules\Relations\RelationDefinitionNormalizer;
use WPEssential\Modules\Relations\RelationDefinitionValidationService;
use WPEssential\Modules\Relations\RelationEndpointSupport;
use WPEssential\Platform\Auth\ExecutionContext;
use WPEssential\Platform\Auth\Principal;
use WPEssential\Platform\Definitions\Definition;
use WPEssential\Platform\Definitions\DefinitionStatus;
use WPEssential\Platform\Definitions\InMemoryDefinitionRepository;

final class RelationDefinitionAbilityHandlerTest extends TestCase
{
    public function testCreateAndUpdateUseCanonicalDefinitionOwnershipRevisionAndChecksum(): void
    {
        $repository = new InMemoryDefinitionRepository();
        $created = $this->handler($repository, RelationDefinitionAbilityHandler::SAVE)->handle([
            'payload' => $this->payload(),
        ], $this->context())['definition'];

        self::assertSame('relation', $created['type']);
        self::assertSame(4, $created['owner_surface_id']);
        self::assertSame('relation-book-authors', $created['slug']);
        self::assertSame('draft', $created['status']);
        self::assertSame(1, $created['revision']);
        self::assertIsString($created['checksum']);
        self::assertMatchesRegularExpression('/^[0-9a-f]{64}$/', $created['checksum']);

        $payload = $this->payload();
        $payload['title'] = 'Book Contributors';
        $updated = $this->handler($repository, RelationDefinitionAbilityHandler::SAVE)->handle([
            'id' => $created['id'],
            'expected_revision' => 1,
            'payload' => $payload,
        ], $this->context())['definition'];

        self::assertSame($created['id'], $updated['id']);
        self::assertSame('book_authors', $updated['payload']['relation_key']);
        self::assertSame('Book Contributors', $updated['payload']['title']);
        self::assertSame(2, $updated['revision']);
    }

    public function testPartialBasicUpdatePreservesExplicitAdvancedPolicy(): void
    {
        $repository = new InMemoryDefinitionRepository();
        $advanced = $this->payload();
        $advanced['direction'] = [
            'reciprocal' => false,
            'bidirectional_traversal' => true,
            'parent_relation' => 'aaaaaaaa-aaaa-4aaa-8aaa-aaaaaaaaaaaa',
        ];
        $advanced['storage_mode'] = 'provider';
        $advanced['pivot_enabled'] = true;
        $advanced['multisite_scope'] = 'network';
        $advanced['portability'] = ['definition' => true, 'edges' => true, 'pivot' => false];

        $created = $this->handler($repository, RelationDefinitionAbilityHandler::SAVE)->handle([
            'payload' => $advanced,
            'status' => 'draft',
        ], $this->context())['definition'];

        $basic = $this->payload();
        $basic['title'] = 'Updated without advanced controls';
        $updated = $this->handler($repository, RelationDefinitionAbilityHandler::SAVE)->handle([
            'id' => $created['id'],
            'expected_revision' => 1,
            'payload' => $basic,
            'status' => 'draft',
        ], $this->context())['definition'];

        self::assertSame('provider', $updated['payload']['storage_mode']);
        self::assertTrue($updated['payload']['pivot_enabled']);
        self::assertSame('network', $updated['payload']['multisite_scope']);
        self::assertTrue($updated['payload']['portability']['edges']);
        self::assertSame(
            'aaaaaaaa-aaaa-4aaa-8aaa-aaaaaaaaaaaa',
            $updated['payload']['direction']['parent_relation'],
        );
    }

    public function testRelationKeyIsImmutableAfterCreation(): void
    {
        $repository = new InMemoryDefinitionRepository();
        $created = $this->handler($repository, RelationDefinitionAbilityHandler::SAVE)->handle([
            'payload' => $this->payload(),
        ], $this->context())['definition'];

        $payload = $this->payload();
        $payload['relation_key'] = 'renamed_relation';

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Relation key is immutable after creation');
        $this->handler($repository, RelationDefinitionAbilityHandler::SAVE)->handle([
            'id' => $created['id'],
            'expected_revision' => 1,
            'payload' => $payload,
        ], $this->context());
    }

    public function testStaleExpectedRevisionFailsClosed(): void
    {
        $repository = new InMemoryDefinitionRepository();
        $created = $this->handler($repository, RelationDefinitionAbilityHandler::SAVE)->handle([
            'payload' => $this->payload(),
        ], $this->context())['definition'];

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Relation write conflict');
        $this->handler($repository, RelationDefinitionAbilityHandler::SAVE)->handle([
            'id' => $created['id'],
            'expected_revision' => 2,
            'payload' => $this->payload(),
        ], $this->context());
    }

    public function testDuplicateRelationKeyIsRejectedBeforeRepositoryOverwrite(): void
    {
        $repository = new InMemoryDefinitionRepository();
        $this->handler($repository, RelationDefinitionAbilityHandler::SAVE)->handle([
            'payload' => $this->payload(),
        ], $this->context());

        $duplicate = $this->payload();
        $duplicate['title'] = 'Duplicate';

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('already owned by another definition');
        $this->handler($repository, RelationDefinitionAbilityHandler::SAVE)->handle([
            'payload' => $duplicate,
        ], $this->context());
    }

    public function testStatusTransitionRevalidatesPublishableEndpoints(): void
    {
        $repository = new InMemoryDefinitionRepository();
        $created = $this->handler($repository, RelationDefinitionAbilityHandler::SAVE)->handle([
            'payload' => $this->payload(),
        ], $this->context())['definition'];

        $published = $this->handler($repository, RelationDefinitionAbilityHandler::STATUS)->handle([
            'id' => $created['id'],
            'expected_revision' => 1,
            'status' => 'published',
        ], $this->context())['definition'];

        self::assertSame('published', $published['status']);
        self::assertSame(2, $published['revision']);
    }

    public function testStatusTransitionRejectsUncertifiedAdvancedRuntimePolicy(): void
    {
        $repository = new InMemoryDefinitionRepository();
        $payload = $this->payload();
        $payload['storage_mode'] = 'provider';
        $created = $this->handler($repository, RelationDefinitionAbilityHandler::SAVE)->handle([
            'payload' => $payload,
            'status' => 'draft',
        ], $this->context())['definition'];

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('does not have a certified runtime adapter');
        $this->handler($repository, RelationDefinitionAbilityHandler::STATUS)->handle([
            'id' => $created['id'],
            'expected_revision' => 1,
            'status' => 'published',
        ], $this->context());
    }

    public function testOwnerBoundaryRejectsForeignRelationDefinition(): void
    {
        $repository = new InMemoryDefinitionRepository();
        $foreign = new Definition(
            id: '11111111-1111-4111-8111-111111111111',
            slug: 'foreign-relation',
            type: RelationDefinitionNormalizer::DEFINITION_TYPE,
            schemaVersion: 1,
            ownerSurfaceId: 3,
            status: DefinitionStatus::Draft,
            payload: $this->payload(),
        );
        $repository->save($foreign);

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('canonical Surface 4');
        $this->handler($repository, RelationDefinitionAbilityHandler::GET)->handle([
            'id' => $foreign->id,
        ], $this->context());
    }

    public function testListFiltersForeignOwnerEvenWhenDefinitionTypeMatches(): void
    {
        $repository = new InMemoryDefinitionRepository();
        $this->handler($repository, RelationDefinitionAbilityHandler::SAVE)->handle([
            'payload' => $this->payload(),
        ], $this->context());
        $repository->save(new Definition(
            id: '22222222-2222-4222-8222-222222222222',
            slug: 'foreign-relation',
            type: RelationDefinitionNormalizer::DEFINITION_TYPE,
            schemaVersion: 1,
            ownerSurfaceId: 3,
            status: DefinitionStatus::Draft,
            payload: $this->payload(),
        ));

        $listed = $this->handler($repository, RelationDefinitionAbilityHandler::LIST)->handle([], $this->context());

        self::assertCount(1, $listed['definitions']);
        self::assertSame(4, $listed['definitions'][0]['owner_surface_id']);
    }

    private function handler(
        InMemoryDefinitionRepository $repository,
        string $action,
    ): RelationDefinitionAbilityHandler {
        $normalizer = new RelationDefinitionNormalizer();
        $validation = new RelationDefinitionValidationService(
            $normalizer,
            new RelationEndpointSupport(
                static fn (string $postType): bool => $postType === 'book',
                static fn (string $taxonomy): bool => $taxonomy === 'genre',
            ),
        );

        return new RelationDefinitionAbilityHandler($repository, $normalizer, $validation, $action);
    }

    private function context(): ExecutionContext
    {
        return new ExecutionContext(new Principal(1), 1);
    }

    /** @return array<string,mixed> */
    private function payload(): array
    {
        return [
            'relation_key' => 'book_authors',
            'title' => 'Book Authors',
            'cardinality' => 'one_to_many',
            'from' => [
                'object_type' => 'post',
                'object_subtype' => 'book',
                'label' => 'Books',
            ],
            'to' => [
                'object_type' => 'user',
                'label' => 'Authors',
            ],
        ];
    }
}
