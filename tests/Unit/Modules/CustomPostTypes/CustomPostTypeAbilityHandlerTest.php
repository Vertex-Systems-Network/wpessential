<?php

declare(strict_types=1);

namespace WPEssential\Tests\Unit\Modules\CustomPostTypes;

use InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use RuntimeException;
use WPEssential\Modules\CustomPostTypes\CustomPostTypeAbilityHandler;
use WPEssential\Modules\CustomPostTypes\CustomPostTypeDefinitionProjector;
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
        return new CustomPostTypeAbilityHandler(
            $repository,
            new CustomPostTypeDefinitionProjector(),
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
