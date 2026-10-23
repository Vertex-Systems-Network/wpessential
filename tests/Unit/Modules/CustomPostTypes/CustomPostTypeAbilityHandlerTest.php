<?php

declare(strict_types=1);

namespace WPEssential\Tests\Unit\Modules\CustomPostTypes;

use InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use RuntimeException;
use WPEssential\Modules\CustomPostTypes\CustomPostTypeAbilityHandler;
use WPEssential\Modules\CustomPostTypes\CustomPostTypeDefinitionProjector;
use WPEssential\Modules\CustomPostTypes\CustomPostTypeValidationService;
use WPEssential\Platform\Auth\ExecutionChannel;
use WPEssential\Platform\Auth\ExecutionContext;
use WPEssential\Platform\Auth\Principal;
use WPEssential\Platform\Definitions\InMemoryDefinitionRepository;

final class CustomPostTypeAbilityHandlerTest extends TestCase
{
    public function testCreatesValidatedDraftWithRevisionAndChecksum(): void
    {
        $repository = new InMemoryDefinitionRepository();
        $result = $this->handler($repository, CustomPostTypeAbilityHandler::SAVE)->handle([
            'payload' => $this->payload(),
        ], $this->context());

        $definition = $result['definition'];
        self::assertSame('cpt-library-book', $definition['slug']);
        self::assertSame('draft', $definition['status']);
        self::assertSame(1, $definition['revision']);
        self::assertSame('library_book', $definition['payload']['post_type_key']);
        self::assertMatchesRegularExpression('/^[0-9a-f]{64}$/', (string) $definition['checksum']);
        self::assertCount(1, $repository->byType(CustomPostTypeDefinitionProjector::DEFINITION_TYPE));
    }

    public function testTrashRestoreLockAndDeleteFollowWordPressRules(): void
    {
        $repository = new InMemoryDefinitionRepository();
        $run = fn (string $action, array $input) => $this->handler($repository, $action)->handle($input, $this->context());
        $created = $run(CustomPostTypeAbilityHandler::SAVE, ['payload' => $this->payload(), 'status' => 'published'])['definition'];

        // Locked: cannot be trashed.
        $locked = $run(CustomPostTypeAbilityHandler::LOCK, ['id' => $created['id'], 'expected_revision' => 1, 'locked' => true])['definition'];
        self::assertTrue($locked['payload']['locked']);
        try {
            $run(CustomPostTypeAbilityHandler::TRASH, ['id' => $created['id'], 'expected_revision' => 2]);
            self::fail('A locked entry must not be trashed.');
        } catch (InvalidArgumentException $exception) {
            self::assertStringContainsString('locked', $exception->getMessage());
        }
        $run(CustomPostTypeAbilityHandler::LOCK, ['id' => $created['id'], 'expected_revision' => 2, 'locked' => false]);

        // Delete needs the Trash first.
        try {
            $run(CustomPostTypeAbilityHandler::DELETE, ['id' => $created['id'], 'expected_revision' => 3]);
            self::fail('Only trashed entries can be deleted permanently.');
        } catch (InvalidArgumentException $exception) {
            self::assertStringContainsString('Trash', $exception->getMessage());
        }

        // Trashing a published entry disables it; restoring brings it back as a draft.
        $trashed = $run(CustomPostTypeAbilityHandler::TRASH, ['id' => $created['id'], 'expected_revision' => 3])['definition'];
        self::assertSame('disabled', $trashed['status']);
        self::assertTrue($trashed['payload']['trashed']);
        $restored = $run(CustomPostTypeAbilityHandler::RESTORE, ['id' => $created['id'], 'expected_revision' => 4])['definition'];
        self::assertSame('draft', $restored['status']);
        self::assertArrayNotHasKey('trashed', $restored['payload']);

        $run(CustomPostTypeAbilityHandler::TRASH, ['id' => $created['id'], 'expected_revision' => 5]);
        $result = $run(CustomPostTypeAbilityHandler::DELETE, ['id' => $created['id'], 'expected_revision' => 6]);
        self::assertSame($created['id'], $result['deleted']);
        self::assertNull($repository->get($created['id']));
    }

    public function testAiCannotSetOrClearTheEntryLock(): void
    {
        $repository = new InMemoryDefinitionRepository();
        $ai = new ExecutionContext(new Principal(1), 1, ExecutionChannel::Ai);
        $created = $this->handler($repository, CustomPostTypeAbilityHandler::SAVE)->handle([
            'payload' => array_merge($this->payload(), ['locked' => true]),
        ], $ai)['definition'];
        self::assertArrayNotHasKey('locked', $created['payload']);

        $locked = $this->handler($repository, CustomPostTypeAbilityHandler::SAVE)->handle([
            'id' => $created['id'], 'expected_revision' => 1, 'payload' => array_merge($this->payload(), ['locked' => true]),
        ], $this->context())['definition'];
        $edited = $this->handler($repository, CustomPostTypeAbilityHandler::SAVE)->handle([
            'id' => $created['id'], 'expected_revision' => 2, 'payload' => $this->payload(),
        ], $ai)['definition'];
        self::assertTrue($edited['payload']['locked'], 'AI cannot clear the lock.');
        unset($locked);
    }

        public function testAiSavesNewDefinitionsAsDraftsAndCannotChangeStatus(): void
    {
        $repository = new InMemoryDefinitionRepository();
        $ai = new ExecutionContext(new Principal(1), 1, ExecutionChannel::Ai);

        $created = $this->handler($repository, CustomPostTypeAbilityHandler::SAVE)->handle([
            'payload' => $this->payload(),
            'status' => 'published',
        ], $ai)['definition'];
        self::assertSame('draft', $created['status'], 'AI may not publish a new post type.');

        $published = $this->handler($repository, CustomPostTypeAbilityHandler::SAVE)->handle([
            'id' => $created['id'],
            'expected_revision' => 1,
            'payload' => $this->payload(),
            'status' => 'published',
        ], $this->context())['definition'];
        self::assertSame('published', $published['status']);

        $edited = $this->handler($repository, CustomPostTypeAbilityHandler::SAVE)->handle([
            'id' => $created['id'],
            'expected_revision' => 2,
            'payload' => array_merge($this->payload(), ['description' => 'Edited by AI']),
            'status' => 'archived',
        ], $ai)['definition'];
        self::assertSame('published', $edited['status'], 'AI keeps the existing status.');
        self::assertSame('Edited by AI', $edited['payload']['description']);
    }

        public function testUpdateRequiresExactExpectedRevision(): void
    {
        $repository = new InMemoryDefinitionRepository();
        $created = $this->handler($repository, CustomPostTypeAbilityHandler::SAVE)->handle([
            'payload' => $this->payload(),
        ], $this->context())['definition'];

        $updated = $this->handler($repository, CustomPostTypeAbilityHandler::SAVE)->handle([
            'id' => $created['id'],
            'expected_revision' => 1,
            'payload' => array_merge($this->payload(), ['description' => 'Library catalogue']),
        ], $this->context())['definition'];
        self::assertSame(2, $updated['revision']);
        self::assertSame($created['slug'], $updated['slug']);

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('CPT write conflict');
        $this->handler($repository, CustomPostTypeAbilityHandler::SAVE)->handle([
            'id' => $created['id'],
            'expected_revision' => 1,
            'payload' => $this->payload(),
        ], $this->context());
    }

    public function testExistingRuntimeKeyCannotBeRenamedThroughCanonicalSaveAbility(): void
    {
        $repository = new InMemoryDefinitionRepository();
        $created = $this->handler($repository, CustomPostTypeAbilityHandler::SAVE)->handle([
            'payload' => $this->payload(),
        ], $this->context())['definition'];

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('post_type_key is immutable');
        $this->handler($repository, CustomPostTypeAbilityHandler::SAVE)->handle([
            'id' => $created['id'],
            'expected_revision' => 1,
            'payload' => array_merge($this->payload(), ['post_type_key' => 'library_volume']),
        ], $this->context());
    }

    public function testStatusTransitionRetainsCanonicalDefinition(): void
    {
        $repository = new InMemoryDefinitionRepository();
        $created = $this->handler($repository, CustomPostTypeAbilityHandler::SAVE)->handle([
            'payload' => $this->payload(),
        ], $this->context())['definition'];

        $published = $this->handler($repository, CustomPostTypeAbilityHandler::STATUS)->handle([
            'id' => $created['id'],
            'expected_revision' => 1,
            'status' => 'published',
        ], $this->context())['definition'];
        self::assertSame('published', $published['status']);
        self::assertSame(2, $published['revision']);

        $disabled = $this->handler($repository, CustomPostTypeAbilityHandler::STATUS)->handle([
            'id' => $created['id'],
            'expected_revision' => 2,
            'status' => 'disabled',
        ], $this->context())['definition'];
        self::assertSame('disabled', $disabled['status']);
        self::assertSame(3, $disabled['revision']);
        self::assertSame($this->payload(), $disabled['payload']);
        self::assertNotNull($repository->get((string) $created['id']));
    }

    public function testDraftSaveStillRejectsReservedRuntimeKey(): void
    {
        $repository = new InMemoryDefinitionRepository();
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('reserved by WordPress');

        $this->handler($repository, CustomPostTypeAbilityHandler::SAVE)->handle([
            'payload' => [
                'post_type_key' => 'post',
                'name' => 'Posts',
                'singular_name' => 'Post',
            ],
        ], $this->context());
    }

    public function testDuplicateCanonicalPostTypeKeyIsRejectedAcrossLifecycleStates(): void
    {
        $repository = new InMemoryDefinitionRepository();
        $created = $this->handler($repository, CustomPostTypeAbilityHandler::SAVE)->handle([
            'payload' => $this->payload(),
        ], $this->context())['definition'];
        $this->handler($repository, CustomPostTypeAbilityHandler::STATUS)->handle([
            'id' => $created['id'],
            'expected_revision' => 1,
            'status' => 'archived',
        ], $this->context());

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('already owned by another canonical CPT definition');
        $this->handler($repository, CustomPostTypeAbilityHandler::SAVE)->handle([
            'payload' => array_merge($this->payload(), [
                'name' => 'Duplicate Books',
                'singular_name' => 'Duplicate Book',
            ]),
        ], $this->context());
    }

    private function handler(InMemoryDefinitionRepository $repository, string $action): CustomPostTypeAbilityHandler
    {
        $projector = new CustomPostTypeDefinitionProjector();
        return new CustomPostTypeAbilityHandler(
            $repository,
            $projector,
            new CustomPostTypeValidationService($repository, $projector),
            $action,
        );
    }

    private function context(): ExecutionContext
    {
        return new ExecutionContext(new Principal(1), 1);
    }

    /** @return array<string,mixed> */
    private function payload(): array
    {
        return [
            'post_type_key' => 'library_book',
            'name' => 'Books',
            'singular_name' => 'Book',
            'public' => true,
            'show_in_rest' => true,
            'supports' => ['title', 'editor'],
        ];
    }
}
