<?php

declare(strict_types=1);

namespace WPEssential\Tests\Unit\Modules\Fields;

use InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use RuntimeException;
use WPEssential\Modules\Fields\FieldGroupDefinitionNormalizer;
use WPEssential\Modules\Fields\FieldGroupRuntimeStorageProjection;
use WPEssential\Modules\Fields\FieldQueryConsumer;
use WPEssential\Modules\Fields\FieldValueNormalizer;
use WPEssential\Modules\Fields\FieldValueTargetResolver;
use WPEssential\Modules\Fields\FieldValueWriteConsumer;
use WPEssential\Modules\Fields\PostMetaRegistrationCompiler;
use WPEssential\Modules\Fields\PostMetaValueStore;
use WPEssential\Modules\Fields\WordPressPostResourceAuthorizer;
use WPEssential\Platform\Auth\ExecutionContext;
use WPEssential\Platform\Auth\Principal;
use WPEssential\Platform\Definitions\Definition;
use WPEssential\Platform\Definitions\DefinitionStatus;
use WPEssential\Platform\Definitions\InMemoryDefinitionRepository;

final class FieldValueWriteConsumerTest extends TestCase
{
    private const GROUP_ID = '11111111-1111-4111-8111-111111111191';
    private const FIELD_ID = '22222222-2222-4222-8222-222222222191';
    private const FIELD_REF = 'fields.11111111-1111-4111-8111-111111111191.22222222-2222-4222-8222-222222222191';

    public function testWriteUsesOwnerNormalizationAndReturnsBoundedMetadata(): void
    {
        $state = [];
        $consumer = $this->consumer($state);

        $result = $consumer->writeValue(self::FIELD_REF, 41, 4, '  Hello  ', $this->context());

        self::assertSame(1, $result['contract_version']);
        self::assertSame(self::FIELD_REF, $result['field_ref']);
        self::assertSame(4, $result['group_revision']);
        self::assertSame(self::FIELD_ID, $result['field_uuid']);
        self::assertSame('string', $result['logical_type']);
        self::assertSame('native_post_meta', $result['storage_owner']);
        self::assertSame(41, $result['post_id']);
        self::assertSame('book', $result['post_type']);
        self::assertSame('written', $result['status']);
        self::assertTrue($result['changed']);
        self::assertSame('Hello', $result['value']);
        self::assertSame('Hello', $state[41]['headline']);
        self::assertArrayNotHasKey('meta_key', $result);
    }

    public function testSameCanonicalValuePreservesOwnerUnchangedSemantics(): void
    {
        $state = [41 => ['headline' => 'Hello']];
        $consumer = $this->consumer($state);

        $result = $consumer->writeValue(self::FIELD_REF, 41, 4, '  Hello  ', $this->context());

        self::assertSame('unchanged', $result['status']);
        self::assertFalse($result['changed']);
        self::assertSame('Hello', $result['value']);
        self::assertSame('Hello', $state[41]['headline']);
    }

    public function testNullPreservesOwnerDeletionAndAbsentSemantics(): void
    {
        $state = [41 => ['headline' => 'Old']];
        $consumer = $this->consumer($state);

        $deleted = $consumer->writeValue(self::FIELD_REF, 41, 4, null, $this->context());
        self::assertSame('deleted', $deleted['status']);
        self::assertTrue($deleted['changed']);
        self::assertArrayNotHasKey('headline', $state[41]);

        $absent = $consumer->writeValue(self::FIELD_REF, 41, 4, null, $this->context());
        self::assertSame('absent', $absent['status']);
        self::assertFalse($absent['changed']);
    }

    public function testStaleRevisionFailsBeforeMutation(): void
    {
        $state = [41 => ['headline' => 'Old']];
        $consumer = $this->consumer($state);

        try {
            $consumer->writeValue(self::FIELD_REF, 41, 3, 'New', $this->context());
            self::fail('Stale Field Group revision must fail closed.');
        } catch (RuntimeException $error) {
            self::assertStringContainsString('schema revision conflict', $error->getMessage());
            self::assertSame('Old', $state[41]['headline']);
        }
    }

    public function testUnauthorizedWriteFailsBeforeMutation(): void
    {
        $state = [41 => ['headline' => 'Old']];
        $consumer = $this->consumer($state, edit: false);

        try {
            $consumer->writeValue(self::FIELD_REF, 41, 4, 'New', $this->context());
            self::fail('Unauthorized owner mutation must fail closed.');
        } catch (RuntimeException $error) {
            self::assertStringContainsString('resource mutation denied', $error->getMessage());
            self::assertSame('Old', $state[41]['headline']);
        }
    }

    public function testMalformedReferenceAndInvalidIdsFailClosed(): void
    {
        $state = [];
        $consumer = $this->consumer($state);

        foreach ([
            ['fields.bad.reference', 41, 4],
            [self::FIELD_REF, 0, 4],
            [self::FIELD_REF, 41, 0],
        ] as [$reference, $postId, $revision]) {
            try {
                $consumer->writeValue($reference, $postId, $revision, 'Value', $this->context());
                self::fail('Malformed write input must fail closed.');
            } catch (InvalidArgumentException) {
                self::assertSame([], $state);
            }
        }
    }

    public function testTargetLocationMismatchFailsBeforeStoreMutation(): void
    {
        $state = [];
        $consumer = $this->consumer($state, postType: 'movie');

        $this->expectException(RuntimeException::class);
        try {
            $consumer->writeValue(self::FIELD_REF, 41, 4, 'Nope', $this->context());
        } finally {
            self::assertSame([], $state);
        }
    }

    /** @param array<int,array<string,mixed>> $state */
    private function consumer(
        array &$state,
        bool $edit = true,
        string $postType = 'book',
    ): FieldValueWriteConsumer {
        $repository = new InMemoryDefinitionRepository();
        $groups = new FieldGroupDefinitionNormalizer();
        $repository->save($this->definition($groups));
        $normalizer = new FieldValueNormalizer();
        $compiler = new PostMetaRegistrationCompiler($normalizer);
        $targets = new FieldValueTargetResolver(
            $repository,
            $groups,
            getPostType: static fn (int $postId): string|false => $postType,
            getPostStatus: static fn (int $postId): string|false => 'publish',
        );
        $store = new PostMetaValueStore(
            compiler: $compiler,
            values: $normalizer,
            getPostType: static fn (int $postId): string|false => $postType,
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
        $authorization = new WordPressPostResourceAuthorizer(
            currentUserId: static fn (): ?int => 7,
            currentSiteId: static fn (): int => 1,
            currentNetworkId: static fn (): ?int => 1,
            currentUserCan: static fn (string $capability, int $postId): bool => match ($capability) {
                'read_post' => true,
                'edit_post' => $edit,
                default => false,
            },
        );
        $query = new FieldQueryConsumer(
            $repository,
            $groups,
            new FieldGroupRuntimeStorageProjection(),
            $targets,
            $compiler,
            $store,
            $normalizer,
            $authorization,
        );

        return new FieldValueWriteConsumer($query, $targets, $store, $authorization);
    }

    private function definition(FieldGroupDefinitionNormalizer $groups): Definition
    {
        $payload = $groups->normalize([
            'group_key' => 'write_books',
            'title' => 'Write Books',
            'fields' => [[
                'uuid' => self::FIELD_ID,
                'key' => 'headline',
                'label' => 'Headline',
                'type' => 'text',
            ]],
            'locations' => [[[
                'source' => 'post_type',
                'operator' => 'equals',
                'value' => 'book',
            ]]],
            'storage' => ['mode' => 'native_post_meta'],
        ], true);

        return new Definition(
            id: self::GROUP_ID,
            slug: 'field-group-write-books',
            type: FieldGroupDefinitionNormalizer::DEFINITION_TYPE,
            schemaVersion: 1,
            ownerSurfaceId: FieldGroupDefinitionNormalizer::OWNER_SURFACE_ID,
            status: DefinitionStatus::Published,
            payload: $payload,
            revision: 4,
        );
    }

    private function context(): ExecutionContext
    {
        return new ExecutionContext(new Principal(7), 1, networkId: 1);
    }
}
