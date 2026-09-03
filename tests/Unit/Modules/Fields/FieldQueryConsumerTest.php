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
use WPEssential\Modules\Fields\PostMetaRegistrationCompiler;
use WPEssential\Modules\Fields\PostMetaValueStore;
use WPEssential\Modules\Fields\WordPressPostResourceAuthorizer;
use WPEssential\Platform\Auth\ExecutionContext;
use WPEssential\Platform\Auth\Principal;
use WPEssential\Platform\Definitions\Definition;
use WPEssential\Platform\Definitions\DefinitionStatus;
use WPEssential\Platform\Definitions\InMemoryDefinitionRepository;

final class FieldQueryConsumerTest extends TestCase
{
    private const GROUP_ID = '11111111-1111-4111-8111-111111111161';
    private const FIELD_ID = '22222222-2222-4222-8222-222222222161';
    private const FIELD_REF = 'fields.11111111-1111-4111-8111-111111111161.22222222-2222-4222-8222-222222222161';

    public function testDescribeExposesBoundedOwnerContractWithoutStorageKey(): void
    {
        $consumer = $this->consumer([10 => 'alpha']);

        $description = $consumer->describe(self::FIELD_REF, $this->context());

        self::assertSame(1, $description['contract_version']);
        self::assertSame(self::FIELD_REF, $description['field_ref']);
        self::assertSame(self::FIELD_ID, $description['field_uuid']);
        self::assertSame('string', $description['logical_type']);
        self::assertSame(['eq', 'neq', 'in', 'not_in'], $description['operators']);
        self::assertSame(100, $description['max_candidate_ids']);
        self::assertSame(100, $description['max_result_ids']);
        self::assertSame('native_post_meta', $description['storage_owner']);
        self::assertArrayNotHasKey('meta_key', $description);
        self::assertArrayNotHasKey('field_key', $description);
    }

    public function testMatchingUsesBoundedAuthorizedOwnerReadPath(): void
    {
        $consumer = $this->consumer([
            10 => 'alpha',
            11 => 'beta',
            12 => 'alpha',
        ]);

        self::assertSame(
            [10, 12],
            $consumer->matchingPostIds(self::FIELD_REF, 'eq', 'alpha', [10, 11, 12], 100, $this->context()),
        );
        self::assertSame(
            [11],
            $consumer->matchingPostIds(self::FIELD_REF, 'in', ['beta'], [10, 11, 12], 100, $this->context()),
        );
        self::assertSame(
            [10],
            $consumer->matchingPostIds(self::FIELD_REF, 'neq', 'beta', [10, 11, 12], 1, $this->context()),
        );
    }

    public function testAuthorizedCandidateOutsideFieldTargetIsANonMatch(): void
    {
        $consumer = $this->consumer(
            [10 => 'alpha', 20 => 'alpha', 11 => 'alpha'],
            postTypes: [20 => 'page'],
        );

        self::assertSame(
            [10, 11],
            $consumer->matchingPostIds(self::FIELD_REF, 'eq', 'alpha', [10, 20, 11], 100, $this->context()),
        );
    }

    public function testCandidateBoundsAndDuplicatesFailClosed(): void
    {
        $consumer = $this->consumer([10 => 'alpha']);

        try {
            $consumer->matchingPostIds(self::FIELD_REF, 'eq', 'alpha', array_fill(0, 101, 10), 100, $this->context());
            self::fail('Oversized candidates must fail closed.');
        } catch (InvalidArgumentException) {
            self::assertTrue(true);
        }

        $this->expectException(InvalidArgumentException::class);
        $consumer->matchingPostIds(self::FIELD_REF, 'eq', 'alpha', [10, 10], 100, $this->context());
    }

    public function testUnauthorizedCandidateFailsWholeConsumerCall(): void
    {
        $consumer = $this->consumer([10 => 'alpha', 13 => 'alpha'], deniedPostId: 13);

        $this->expectException(RuntimeException::class);
        $consumer->matchingPostIds(self::FIELD_REF, 'eq', 'alpha', [10, 13], 100, $this->context());
    }

    public function testNonScalarNativeStorageRemainsUnsupported(): void
    {
        $consumer = $this->consumer([10 => [4, 5]], fieldType: 'gallery');

        $this->expectException(RuntimeException::class);
        $consumer->describe(self::FIELD_REF, $this->context());
    }

    /** @param array<int,mixed> $values */
    private function consumer(
        array $values,
        ?int $deniedPostId = null,
        string $fieldType = 'text',
        array $postTypes = [],
    ): FieldQueryConsumer
    {
        $repository = new InMemoryDefinitionRepository();
        $groups = new FieldGroupDefinitionNormalizer();
        $repository->save($this->definition($groups, $fieldType));
        $normalizer = new FieldValueNormalizer();
        $compiler = new PostMetaRegistrationCompiler($normalizer);
        $targets = new FieldValueTargetResolver(
            $repository,
            $groups,
            getPostType: static fn (int $postId): string => $postTypes[$postId] ?? 'book',
            getPostStatus: static fn (int $postId): string => 'publish',
        );
        $store = new PostMetaValueStore(
            compiler: $compiler,
            values: $normalizer,
            getPostType: static fn (int $postId): string => $postTypes[$postId] ?? 'book',
            metadataExists: static fn (int $postId, string $metaKey): bool => array_key_exists($postId, $values),
            getPostMeta: static fn (int $postId, string $metaKey, bool $single): mixed => $values[$postId] ?? null,
        );
        $authorization = new WordPressPostResourceAuthorizer(
            currentUserId: static fn (): int => 7,
            currentSiteId: static fn (): int => 1,
            currentNetworkId: static fn (): int => 1,
            currentUserCan: static fn (string $capability, int $postId): bool => $postId !== $deniedPostId,
        );

        return new FieldQueryConsumer(
            $repository,
            $groups,
            new FieldGroupRuntimeStorageProjection(),
            $targets,
            $compiler,
            $store,
            $normalizer,
            $authorization,
        );
    }

    private function definition(FieldGroupDefinitionNormalizer $groups, string $fieldType): Definition
    {
        $payload = $groups->normalize([
            'group_key' => 'query_books',
            'title' => 'Query Books',
            'fields' => [[
                'uuid' => self::FIELD_ID,
                'key' => 'query_headline',
                'label' => 'Query headline',
                'type' => $fieldType,
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
            slug: 'field-group-query-books',
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
