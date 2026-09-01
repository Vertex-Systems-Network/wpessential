<?php

declare(strict_types=1);

namespace WPEssential\Tests\Unit\Modules\Fields;

use PHPUnit\Framework\TestCase;
use RuntimeException;
use WPEssential\Modules\Fields\FieldGroupDefinitionNormalizer;
use WPEssential\Modules\Fields\FieldValueAbilityHandler;
use WPEssential\Modules\Fields\FieldValueTargetResolver;
use WPEssential\Modules\Fields\PostMetaValueStore;
use WPEssential\Modules\Fields\WordPressPostResourceAuthorizer;
use WPEssential\Platform\Auth\ExecutionContext;
use WPEssential\Platform\Auth\Principal;
use WPEssential\Platform\Definitions\Definition;
use WPEssential\Platform\Definitions\DefinitionStatus;
use WPEssential\Platform\Definitions\InMemoryDefinitionRepository;

final class FieldValueAbilityHandlerTest extends TestCase
{
    private const GROUP_ID = '11111111-1111-4111-8111-111111111111';
    private const FIELD_ID = '22222222-2222-4222-8222-222222222222';

    public function testWriteThenReadUsesResolvedPublishedFieldAndReturnsStableIdentity(): void
    {
        $repository = $this->repository();
        $state = [];
        $store = $this->store($state);
        $authorization = $this->authorization(read: true, edit: true);
        $targets = $this->targets($repository);

        $write = (new FieldValueAbilityHandler(
            $targets,
            $store,
            $authorization,
            FieldValueAbilityHandler::WRITE,
        ))->handle([
            'group_id' => self::GROUP_ID,
            'field_uuid' => self::FIELD_ID,
            'post_id' => 41,
            'expected_group_revision' => 3,
            'value' => '  Hello  ',
        ], $this->context());

        self::assertSame('written', $write['status']);
        self::assertTrue($write['changed']);
        self::assertSame('Hello', $write['value']);
        self::assertSame(self::GROUP_ID, $write['group_id']);
        self::assertSame(3, $write['group_revision']);
        self::assertSame(self::FIELD_ID, $write['field_uuid']);
        self::assertSame('headline', $write['field_key']);
        self::assertSame('book', $write['post_type']);

        $read = (new FieldValueAbilityHandler(
            $targets,
            $store,
            $authorization,
            FieldValueAbilityHandler::READ,
        ))->handle([
            'group_id' => self::GROUP_ID,
            'field_uuid' => self::FIELD_ID,
            'post_id' => 41,
        ], $this->context());

        self::assertSame('read', $read['status']);
        self::assertFalse($read['changed']);
        self::assertSame('Hello', $read['value']);
    }

    public function testStaleGroupRevisionRejectsBeforeStoreMutation(): void
    {
        $state = [41 => ['headline' => 'Old']];
        $handler = new FieldValueAbilityHandler(
            $this->targets($this->repository()),
            $this->store($state),
            $this->authorization(read: true, edit: true),
            FieldValueAbilityHandler::WRITE,
        );

        try {
            $handler->handle([
                'group_id' => self::GROUP_ID,
                'field_uuid' => self::FIELD_ID,
                'post_id' => 41,
                'expected_group_revision' => 2,
                'value' => 'New',
            ], $this->context());
            self::fail('Stale group revision must reject the write.');
        } catch (RuntimeException $error) {
            self::assertStringContainsString('schema revision conflict', $error->getMessage());
            self::assertSame('Old', $state[41]['headline']);
        }
    }

    public function testResourceAuthorizationRunsBeforeTargetResolution(): void
    {
        $repository = new InMemoryDefinitionRepository();
        $state = [];
        $handler = new FieldValueAbilityHandler(
            $this->targets($repository),
            $this->store($state),
            $this->authorization(read: false, edit: false),
            FieldValueAbilityHandler::READ,
        );

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('resource access denied');
        $handler->handle([
            'group_id' => self::GROUP_ID,
            'field_uuid' => self::FIELD_ID,
            'post_id' => 41,
        ], $this->context());
    }

    public function testWriteRequiresEditPostEvenWhenReadPostIsAllowed(): void
    {
        $state = [];
        $handler = new FieldValueAbilityHandler(
            $this->targets($this->repository()),
            $this->store($state),
            $this->authorization(read: true, edit: false),
            FieldValueAbilityHandler::WRITE,
        );

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('resource mutation denied');
        $handler->handle([
            'group_id' => self::GROUP_ID,
            'field_uuid' => self::FIELD_ID,
            'post_id' => 41,
            'expected_group_revision' => 3,
            'value' => 'Denied',
        ], $this->context());
    }

    public function testExecutionContextMustMatchActiveUserAndSite(): void
    {
        $state = [];
        $handler = new FieldValueAbilityHandler(
            $this->targets($this->repository()),
            $this->store($state),
            $this->authorization(read: true, edit: true),
            FieldValueAbilityHandler::READ,
        );

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('execution context is not bound');
        $handler->handle([
            'group_id' => self::GROUP_ID,
            'field_uuid' => self::FIELD_ID,
            'post_id' => 41,
        ], new ExecutionContext(new Principal(99), 1, networkId: 1));
    }

    private function repository(): InMemoryDefinitionRepository
    {
        $repository = new InMemoryDefinitionRepository();
        $payload = (new FieldGroupDefinitionNormalizer())->normalize([
            'group_key' => 'book_meta',
            'title' => 'Book Meta',
            'fields' => [[
                'uuid' => self::FIELD_ID,
                'key' => 'headline',
                'label' => 'Headline',
                'type' => 'text',
            ]],
            'locations' => [[
                ['source' => 'post_type', 'operator' => 'equals', 'value' => 'book'],
            ]],
        ], true);
        $repository->save(new Definition(
            id: self::GROUP_ID,
            slug: 'field-group-book-meta',
            type: FieldGroupDefinitionNormalizer::DEFINITION_TYPE,
            schemaVersion: 1,
            ownerSurfaceId: FieldGroupDefinitionNormalizer::OWNER_SURFACE_ID,
            status: DefinitionStatus::Published,
            payload: $payload,
            revision: 3,
        ));
        return $repository;
    }

    private function targets(InMemoryDefinitionRepository $repository): FieldValueTargetResolver
    {
        return new FieldValueTargetResolver(
            $repository,
            new FieldGroupDefinitionNormalizer(),
            getPostType: static fn (int $postId): string|false => 'book',
            getPostStatus: static fn (int $postId): string|false => 'publish',
        );
    }

    /** @param array<int,array<string,mixed>> $state */
    private function store(array &$state): PostMetaValueStore
    {
        return new PostMetaValueStore(
            getPostType: static fn (int $postId): string|false => 'book',
            metadataExists: static function (int $postId, string $key) use (&$state): bool {
                return array_key_exists($key, $state[$postId] ?? []);
            },
            getPostMeta: static function (int $postId, string $key, bool $single) use (&$state): mixed {
                return $state[$postId][$key] ?? null;
            },
            updatePostMeta: static function (int $postId, string $key, mixed $value) use (&$state): int|bool {
                $state[$postId][$key] = $value;
                return true;
            },
            deletePostMeta: static function (int $postId, string $key) use (&$state): bool {
                unset($state[$postId][$key]);
                return true;
            },
            slash: static fn (mixed $value): mixed => $value,
        );
    }

    private function authorization(bool $read, bool $edit): WordPressPostResourceAuthorizer
    {
        return new WordPressPostResourceAuthorizer(
            currentUserId: static fn (): ?int => 7,
            currentSiteId: static fn (): int => 1,
            currentNetworkId: static fn (): ?int => 1,
            currentUserCan: static fn (string $capability, int $postId): bool => match ($capability) {
                'read_post' => $read,
                'edit_post' => $edit,
                default => false,
            },
        );
    }

    private function context(): ExecutionContext
    {
        return new ExecutionContext(new Principal(7), 1, networkId: 1);
    }
}
