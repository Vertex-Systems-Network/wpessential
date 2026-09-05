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
use WPEssential\Modules\Fields\FieldValueReadConsumer;
use WPEssential\Modules\Fields\FieldValueTargetResolver;
use WPEssential\Modules\Fields\PostMetaRegistrationCompiler;
use WPEssential\Modules\Fields\PostMetaValueStore;
use WPEssential\Modules\Fields\WordPressPostResourceAuthorizer;
use WPEssential\Platform\Auth\ExecutionContext;
use WPEssential\Platform\Auth\Principal;
use WPEssential\Platform\Definitions\Definition;
use WPEssential\Platform\Definitions\DefinitionStatus;
use WPEssential\Platform\Definitions\InMemoryDefinitionRepository;

final class FieldValueReadConsumerTest extends TestCase
{
    private const GROUP_ID = '11111111-1111-4111-8111-111111111181';
    private const FIELD_ID = '22222222-2222-4222-8222-222222222181';
    private const FIELD_REF = 'fields.11111111-1111-4111-8111-111111111181.22222222-2222-4222-8222-222222222181';

    public function testReadValuesPreloadsOnceAndPreservesOwnerMetadataAndInputOrder(): void
    {
        $tracker = new class {
            public int $calls = 0;
            /** @var list<int> */
            public array $ids = [];
        };
        $consumer = $this->consumer(
            [10 => 'alpha', 11 => 'beta', 12 => 'gamma'],
            tracker: $tracker,
        );

        $result = $consumer->readValues(self::FIELD_REF, [12, 10, 11], $this->context());

        self::assertSame(1, $tracker->calls);
        self::assertSame([12, 10, 11], $tracker->ids);
        self::assertSame(1, $result['contract_version']);
        self::assertSame(self::FIELD_REF, $result['field_ref']);
        self::assertSame(4, $result['group_revision']);
        self::assertSame(self::FIELD_ID, $result['field_uuid']);
        self::assertSame('string', $result['logical_type']);
        self::assertSame('native_post_meta', $result['storage_owner']);
        self::assertSame([
            ['post_id' => 12, 'value' => 'gamma'],
            ['post_id' => 10, 'value' => 'alpha'],
            ['post_id' => 11, 'value' => 'beta'],
        ], $result['rows']);
    }

    public function testPostIdBoundsAndDuplicatesFailBeforePreload(): void
    {
        $tracker = new class {
            public int $calls = 0;
        };
        $consumer = $this->consumer([10 => 'alpha'], tracker: $tracker);

        try {
            $consumer->readValues(self::FIELD_REF, array_fill(0, 101, 10), $this->context());
            self::fail('Oversized Field value read must fail closed.');
        } catch (InvalidArgumentException) {
            self::assertSame(0, $tracker->calls);
        }

        try {
            $consumer->readValues(self::FIELD_REF, [10, 10], $this->context());
            self::fail('Duplicate Field value read ids must fail closed.');
        } catch (InvalidArgumentException) {
            self::assertSame(0, $tracker->calls);
        }
    }

    public function testUnauthorizedTargetFailsWholeReadAfterOnePreload(): void
    {
        $tracker = new class {
            public int $calls = 0;
            /** @var list<int> */
            public array $ids = [];
        };
        $consumer = $this->consumer(
            [10 => 'alpha', 11 => 'beta', 12 => 'gamma'],
            deniedPostId: 11,
            tracker: $tracker,
        );

        try {
            $consumer->readValues(self::FIELD_REF, [10, 11, 12], $this->context());
            self::fail('Unauthorized Field value read target must fail the complete call.');
        } catch (RuntimeException) {
            self::assertSame(1, $tracker->calls);
            self::assertSame([10, 11, 12], $tracker->ids);
        }
    }

    public function testMalformedReferenceFailsBeforeOwnerLookupOrPreload(): void
    {
        $tracker = new class {
            public int $calls = 0;
        };
        $consumer = $this->consumer([10 => 'alpha'], tracker: $tracker);

        $this->expectException(InvalidArgumentException::class);
        try {
            $consumer->readValues('fields.not-a-uuid.bad', [10], $this->context());
        } finally {
            self::assertSame(0, $tracker->calls);
        }
    }

    /**
     * @param array<int,mixed> $storedValues
     * @param object{calls:int} $tracker
     */
    private function consumer(
        array $storedValues,
        ?int $deniedPostId = null,
        ?object $tracker = null,
    ): FieldValueReadConsumer {
        $tracker ??= new class {
            public int $calls = 0;
            /** @var list<int> */
            public array $ids = [];
        };

        $repository = new InMemoryDefinitionRepository();
        $groups = new FieldGroupDefinitionNormalizer();
        $repository->save($this->definition($groups));
        $normalizer = new FieldValueNormalizer();
        $compiler = new PostMetaRegistrationCompiler($normalizer);
        $targets = new FieldValueTargetResolver(
            $repository,
            $groups,
            getPostType: static fn (int $postId): string => 'book',
            getPostStatus: static fn (int $postId): string => 'publish',
        );
        $store = new PostMetaValueStore(
            compiler: $compiler,
            values: $normalizer,
            getPostType: static fn (int $postId): string => 'book',
            metadataExists: static fn (int $postId, string $metaKey): bool => array_key_exists($postId, $storedValues),
            getPostMeta: static fn (int $postId, string $metaKey, bool $single): mixed => $storedValues[$postId] ?? null,
        );
        $authorization = new WordPressPostResourceAuthorizer(
            currentUserId: static fn (): int => 7,
            currentSiteId: static fn (): int => 1,
            currentNetworkId: static fn (): int => 1,
            currentUserCan: static fn (string $capability, int $postId): bool => $postId !== $deniedPostId,
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

        return new FieldValueReadConsumer(
            $query,
            $targets,
            $store,
            $authorization,
            primePostCaches: static function (array $postIds) use ($tracker): void {
                ++$tracker->calls;
                if (property_exists($tracker, 'ids')) {
                    $tracker->ids = $postIds;
                }
            },
        );
    }

    private function definition(FieldGroupDefinitionNormalizer $groups): Definition
    {
        $payload = $groups->normalize([
            'group_key' => 'read_books',
            'title' => 'Read Books',
            'fields' => [[
                'uuid' => self::FIELD_ID,
                'key' => 'read_headline',
                'label' => 'Read headline',
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
            slug: 'field-group-read-books',
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
