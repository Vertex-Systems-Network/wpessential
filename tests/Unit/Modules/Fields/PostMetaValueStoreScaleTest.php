<?php

declare(strict_types=1);

namespace WPEssential\Tests\Unit\Modules\Fields;

use PHPUnit\Framework\TestCase;
use WPEssential\Modules\Fields\FieldDefinitionNormalizer;
use WPEssential\Modules\Fields\FieldValueNormalizer;
use WPEssential\Modules\Fields\PostMetaRegistrationCompiler;
use WPEssential\Modules\Fields\PostMetaValueStore;
use WPEssential\Modules\Fields\PostMetaValueWriteResult;

final class PostMetaValueStoreScaleTest extends TestCase
{
    public function testSingleValueWriteAndReadUseFixedMetadataCallBudget(): void
    {
        $state = [];
        $calls = [
            'post_type' => 0,
            'exists' => 0,
            'read' => 0,
            'update' => 0,
            'delete' => 0,
            'add' => 0,
        ];
        $store = $this->store($state, $calls);
        $field = (new FieldDefinitionNormalizer())->normalize([
            'uuid' => '11111111-1111-4111-8111-111111111111',
            'key' => 'headline',
            'label' => 'Headline',
            'type' => 'text',
        ]);

        $written = $store->write($field, 'book', 41, 'Hello');
        self::assertSame(PostMetaValueWriteResult::WRITTEN, $written->status);
        self::assertSame([
            'post_type' => 1,
            'exists' => 2,
            'read' => 1,
            'update' => 1,
            'delete' => 0,
            'add' => 0,
        ], $calls);

        self::assertSame('Hello', $store->read($field, 'book', 41));
        self::assertSame([
            'post_type' => 2,
            'exists' => 3,
            'read' => 2,
            'update' => 1,
            'delete' => 0,
            'add' => 0,
        ], $calls, 'read budget must remain fixed and independent of unrelated posts or fields');
    }

    public function testLargeMultiRowReplacementAddsOncePerDesiredRowWithBoundedVerification(): void
    {
        $state = [];
        $calls = [
            'post_type' => 0,
            'exists' => 0,
            'read' => 0,
            'update' => 0,
            'delete' => 0,
            'add' => 0,
        ];
        $store = $this->store($state, $calls);
        $field = (new FieldDefinitionNormalizer())->normalize([
            'uuid' => '22222222-2222-4222-8222-222222222222',
            'key' => 'rows',
            'label' => 'Rows',
            'type' => 'text',
            'cloneable' => true,
            'clone_as_multiple' => true,
        ]);
        $desired = [];
        for ($index = 1; $index <= 128; ++$index) {
            $desired[] = sprintf('Row %03d', $index);
        }

        $written = $store->write($field, 'book', 41, $desired);

        self::assertSame(PostMetaValueWriteResult::WRITTEN, $written->status);
        self::assertSame($desired, $written->value);
        self::assertSame(128, $calls['add'], 'multi-row replacement must perform one WordPress add per desired row');
        self::assertSame(4, $calls['exists'], 'existence verification must remain bounded, not per row');
        self::assertSame(1, $calls['read'], 'canonical verification must read the completed row set once');
        self::assertSame(1, $calls['post_type']);
        self::assertSame(0, $calls['update']);
        self::assertSame(0, $calls['delete']);
    }

    /**
     * @param array<int,array<string,mixed>> $state
     * @param array{post_type:int,exists:int,read:int,update:int,delete:int,add:int} $calls
     */
    private function store(array &$state, array &$calls): PostMetaValueStore
    {
        $values = new FieldValueNormalizer();
        $compiler = new PostMetaRegistrationCompiler($values);

        return new PostMetaValueStore(
            compiler: $compiler,
            values: $values,
            getPostType: static function (int $postId) use (&$calls): string|false {
                ++$calls['post_type'];
                return 'book';
            },
            metadataExists: static function (int $postId, string $metaKey) use (&$state, &$calls): bool {
                ++$calls['exists'];
                return array_key_exists($metaKey, $state[$postId] ?? []);
            },
            getPostMeta: static function (int $postId, string $metaKey, bool $single) use (&$state, &$calls): mixed {
                ++$calls['read'];
                $value = $state[$postId][$metaKey] ?? null;
                if ($single) {
                    return $value;
                }
                return is_array($value) && array_is_list($value) ? $value : [];
            },
            updatePostMeta: static function (int $postId, string $metaKey, mixed $value) use (&$state, &$calls): int|bool {
                ++$calls['update'];
                $state[$postId][$metaKey] = $value;
                return true;
            },
            deletePostMeta: static function (int $postId, string $metaKey) use (&$state, &$calls): bool {
                ++$calls['delete'];
                unset($state[$postId][$metaKey]);
                return true;
            },
            addPostMeta: static function (int $postId, string $metaKey, mixed $value) use (&$state, &$calls): int|false {
                ++$calls['add'];
                $state[$postId][$metaKey] ??= [];
                if (!is_array($state[$postId][$metaKey]) || !array_is_list($state[$postId][$metaKey])) {
                    return false;
                }
                $state[$postId][$metaKey][] = $value;
                return $calls['add'];
            },
            slash: static fn (mixed $value): mixed => $value,
        );
    }
}
