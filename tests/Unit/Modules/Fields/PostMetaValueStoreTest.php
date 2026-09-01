<?php

declare(strict_types=1);

namespace WPEssential\Tests\Unit\Modules\Fields;

use InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use RuntimeException;
use WPEssential\Modules\Fields\FieldDefinitionNormalizer;
use WPEssential\Modules\Fields\FieldValueNormalizer;
use WPEssential\Modules\Fields\PostMetaRegistrationCompiler;
use WPEssential\Modules\Fields\PostMetaValueStore;
use WPEssential\Modules\Fields\PostMetaValueWriteResult;

final class PostMetaValueStoreTest extends TestCase
{
    private FieldDefinitionNormalizer $definitions;

    protected function setUp(): void
    {
        $this->definitions = new FieldDefinitionNormalizer();
    }

    public function testWritesReadsAndRecognizesUnchangedScalarValue(): void
    {
        $state = [];
        $updates = 0;
        $store = $this->store(
            $state,
            update: static function (int $postId, string $metaKey, mixed $value) use (&$state, &$updates): int|bool {
                ++$updates;
                $state[$postId][$metaKey] = $value;
                return 11;
            },
        );
        $field = $this->field(['key' => 'headline', 'type' => 'text']);

        $written = $store->write($field, 'book', 41, '  Hello  ');
        self::assertSame(PostMetaValueWriteResult::WRITTEN, $written->status);
        self::assertTrue($written->changed());
        self::assertSame('Hello', $written->value);
        self::assertSame('11111111-1111-4111-8111-111111111111', $written->fieldUuid);
        self::assertSame('headline', $written->metaKey);
        self::assertSame('Hello', $store->read($field, 'book', 41));

        $unchanged = $store->write($field, 'book', 41, 'Hello');
        self::assertSame(PostMetaValueWriteResult::UNCHANGED, $unchanged->status);
        self::assertFalse($unchanged->changed());
        self::assertSame(1, $updates, 'idempotent writes must not call the native update boundary');
    }

    public function testCastsNativeScalarTypesBeforeCanonicalValidation(): void
    {
        $state = [
            41 => [
                'count' => '7',
                'ratio' => '7.5',
                'enabled' => '1',
                'disabled' => '',
            ],
        ];
        $store = $this->store($state);

        self::assertSame(7, $store->read($this->field([
            'key' => 'count',
            'type' => 'number',
            'settings' => ['integer' => true],
        ]), 'book', 41));
        self::assertSame(7.5, $store->read($this->field([
            'key' => 'ratio',
            'type' => 'number',
        ]), 'book', 41));
        self::assertTrue($store->read($this->field([
            'key' => 'enabled',
            'type' => 'true_false',
        ]), 'book', 41));
        self::assertFalse($store->read($this->field([
            'key' => 'disabled',
            'type' => 'true_false',
        ]), 'book', 41));
    }

    public function testWritesAndReadsSingleArrayStorage(): void
    {
        $state = [];
        $store = $this->store($state);
        $field = $this->field([
            'key' => 'aliases',
            'type' => 'text',
            'cloneable' => true,
            'max_clones' => 3,
        ]);

        $result = $store->write($field, 'book', 41, [' One ', ' Two ']);

        self::assertSame(PostMetaValueWriteResult::WRITTEN, $result->status);
        self::assertSame(['One', 'Two'], $store->read($field, 'book', 41));
    }

    public function testOptionalNullDeletesExistingMetaAndMissingNullIsIdempotent(): void
    {
        $state = [41 => ['headline' => 'Hello']];
        $deletes = 0;
        $store = $this->store(
            $state,
            delete: static function (int $postId, string $metaKey) use (&$state, &$deletes): bool {
                ++$deletes;
                unset($state[$postId][$metaKey]);
                return true;
            },
        );
        $field = $this->field(['key' => 'headline', 'type' => 'text']);

        $deleted = $store->write($field, 'book', 41, null);
        self::assertSame(PostMetaValueWriteResult::DELETED, $deleted->status);
        self::assertTrue($deleted->changed());
        self::assertNull($store->read($field, 'book', 41));

        $absent = $store->write($field, 'book', 41, null);
        self::assertSame(PostMetaValueWriteResult::ABSENT, $absent->status);
        self::assertFalse($absent->changed());
        self::assertSame(1, $deletes);
    }

    public function testRequiredNullIsRejectedBeforeMutation(): void
    {
        $state = [];
        $updates = 0;
        $store = $this->store(
            $state,
            update: static function (int $postId, string $metaKey, mixed $value) use (&$updates): int|bool {
                ++$updates;
                return true;
            },
        );
        $field = $this->field([
            'key' => 'headline',
            'type' => 'text',
            'required' => true,
        ]);

        try {
            $store->write($field, 'book', 41, null);
            self::fail('Required null must be rejected.');
        } catch (InvalidArgumentException) {
            self::assertSame(0, $updates);
        }
    }

    public function testNativeUpdateFalseFailsWhenDesiredStateWasNotPersisted(): void
    {
        $state = [41 => ['headline' => 'Old']];
        $store = $this->store(
            $state,
            update: static fn (int $postId, string $metaKey, mixed $value): int|bool => false,
        );
        $field = $this->field(['key' => 'headline', 'type' => 'text']);

        $this->expectException(RuntimeException::class);
        $store->write($field, 'book', 41, 'New');
    }

    public function testNativeFalseMayStillBeVerifiedWhenDesiredStateExistsAfterAttempt(): void
    {
        $state = [41 => ['headline' => 'Old']];
        $store = $this->store(
            $state,
            update: static function (int $postId, string $metaKey, mixed $value) use (&$state): int|bool {
                $state[$postId][$metaKey] = $value;
                return false;
            },
        );
        $field = $this->field(['key' => 'headline', 'type' => 'text']);

        $result = $store->write($field, 'book', 41, 'New');
        self::assertSame(PostMetaValueWriteResult::WRITTEN, $result->status);
        self::assertSame('New', $result->value);
    }

    public function testDeleteFailureThrowsWhenMetaStillExists(): void
    {
        $state = [41 => ['headline' => 'Old']];
        $store = $this->store(
            $state,
            delete: static fn (int $postId, string $metaKey): bool => false,
        );
        $field = $this->field(['key' => 'headline', 'type' => 'text']);

        $this->expectException(RuntimeException::class);
        $store->write($field, 'book', 41, null);
    }

    public function testMultipleRowReplacementPreservesOrderDuplicatesAndCanonicalTypes(): void
    {
        $state = [41 => ['scores' => ['4', '9']]];
        $store = $this->store($state);
        $field = $this->multipleRowField();

        self::assertSame([4, 9], $store->read($field, 'book', 41));

        $written = $store->write($field, 'book', 41, [9, 4, 9]);

        self::assertSame(PostMetaValueWriteResult::WRITTEN, $written->status);
        self::assertSame([9, 4, 9], $written->value);
        self::assertSame([9, 4, 9], $store->read($field, 'book', 41));
    }

    public function testMultipleRowUnchangedWriteAvoidsDestructiveNativeBoundaries(): void
    {
        $state = [41 => ['scores' => ['4', '9']]];
        $deletes = 0;
        $adds = 0;
        $store = $this->store(
            $state,
            delete: static function (int $postId, string $metaKey) use (&$state, &$deletes): bool {
                ++$deletes;
                unset($state[$postId][$metaKey]);
                return true;
            },
            add: static function (int $postId, string $metaKey, mixed $value) use (&$state, &$adds): int|bool {
                ++$adds;
                $state[$postId][$metaKey][] = $value;
                return $adds;
            },
        );

        $unchanged = $store->write($this->multipleRowField(), 'book', 41, [4, 9]);

        self::assertSame(PostMetaValueWriteResult::UNCHANGED, $unchanged->status);
        self::assertSame(0, $deletes);
        self::assertSame(0, $adds);
    }

    public function testEmptyMultipleRowListDeletesAllRowsAndThenBecomesAbsent(): void
    {
        $state = [41 => ['scores' => ['4', '9']]];
        $store = $this->store($state);
        $field = $this->multipleRowField();

        $deleted = $store->write($field, 'book', 41, []);
        self::assertSame(PostMetaValueWriteResult::DELETED, $deleted->status);
        self::assertTrue($deleted->changed());
        self::assertNull($store->read($field, 'book', 41));

        $absent = $store->write($field, 'book', 41, null);
        self::assertSame(PostMetaValueWriteResult::ABSENT, $absent->status);
        self::assertFalse($absent->changed());
    }

    public function testMultipleRowFailureRestoresPreviousCanonicalRows(): void
    {
        $state = [41 => ['scores' => ['4', '9']]];
        $store = $this->store(
            $state,
            add: static function (int $postId, string $metaKey, mixed $value) use (&$state): int|bool {
                if ($value === 10) {
                    return false;
                }
                $state[$postId][$metaKey][] = $value;
                return count($state[$postId][$metaKey]);
            },
        );
        $field = $this->multipleRowField();

        try {
            $store->write($field, 'book', 41, [5, 10]);
            self::fail('A partially persisted multi-row target must fail verification.');
        } catch (RuntimeException $error) {
            self::assertStringContainsString('previous canonical rows were restored', $error->getMessage());
        }

        self::assertSame([4, 9], $store->read($field, 'book', 41));
    }

    public function testMultipleRowFailureReportsUnrecoverableStateWhenRollbackCannotBeVerified(): void
    {
        $state = [41 => ['scores' => ['4', '9']]];
        $store = $this->store(
            $state,
            add: static function (int $postId, string $metaKey, mixed $value) use (&$state): int|bool {
                if ($value === 10 || $value === 9) {
                    return false;
                }
                $state[$postId][$metaKey][] = $value;
                return count($state[$postId][$metaKey]);
            },
        );

        try {
            $store->write($this->multipleRowField(), 'book', 41, [5, 10]);
            self::fail('Failed target and failed rollback must be surfaced.');
        } catch (RuntimeException $error) {
            self::assertStringContainsString('could not be restored', $error->getMessage());
        }
    }

    public function testCorruptPersistedValueFailsClosed(): void
    {
        $state = [41 => ['enabled' => 'yes']];
        $store = $this->store($state);
        $field = $this->field(['key' => 'enabled', 'type' => 'true_false']);

        $this->expectException(RuntimeException::class);
        $store->read($field, 'book', 41);
    }

    public function testPostSubtypeMismatchRejectsReadAndWrite(): void
    {
        $state = [];
        $store = $this->store($state, postType: 'page');
        $field = $this->field(['key' => 'headline', 'type' => 'text']);

        $this->expectException(InvalidArgumentException::class);
        $store->read($field, 'book', 41);
    }

    /**
     * @param array<int,array<string,mixed>> $state
     * @param null|callable(int,string,mixed):int|bool $update
     * @param null|callable(int,string):bool $delete
     * @param null|callable(int,string,mixed):int|bool $add
     */
    private function store(
        array &$state,
        ?callable $update = null,
        ?callable $delete = null,
        string $postType = 'book',
        ?callable $add = null,
    ): PostMetaValueStore {
        $values = new FieldValueNormalizer();
        $compiler = new PostMetaRegistrationCompiler($values);

        $update ??= static function (int $postId, string $metaKey, mixed $value) use (&$state): int|bool {
            $state[$postId][$metaKey] = $value;
            return true;
        };
        $delete ??= static function (int $postId, string $metaKey) use (&$state): bool {
            unset($state[$postId][$metaKey]);
            return true;
        };
        $add ??= static function (int $postId, string $metaKey, mixed $value) use (&$state): int|bool {
            $state[$postId][$metaKey] ??= [];
            if (!is_array($state[$postId][$metaKey]) || !array_is_list($state[$postId][$metaKey])) {
                return false;
            }
            $state[$postId][$metaKey][] = $value;
            return count($state[$postId][$metaKey]);
        };

        return new PostMetaValueStore(
            compiler: $compiler,
            values: $values,
            getPostType: static fn (int $postId): string|false => $postType,
            metadataExists: static function (int $postId, string $metaKey) use (&$state): bool {
                return array_key_exists($metaKey, $state[$postId] ?? []);
            },
            getPostMeta: static function (int $postId, string $metaKey, bool $single) use (&$state): mixed {
                $value = $state[$postId][$metaKey] ?? null;
                if ($single) {
                    return $value;
                }
                return is_array($value) && array_is_list($value) ? $value : [];
            },
            updatePostMeta: $update,
            deletePostMeta: $delete,
            slash: static fn (mixed $value): mixed => $value,
            addPostMeta: $add,
        );
    }

    /** @return array<string,mixed> */
    private function multipleRowField(): array
    {
        return $this->field([
            'key' => 'scores',
            'type' => 'number',
            'settings' => ['integer' => true],
            'cloneable' => true,
            'clone_as_multiple' => true,
        ]);
    }

    /** @param array<string,mixed> $definition @return array<string,mixed> */
    private function field(array $definition): array
    {
        $definition['uuid'] = $definition['uuid'] ?? '11111111-1111-4111-8111-111111111111';
        $definition['label'] = $definition['label'] ?? ucfirst((string) ($definition['key'] ?? 'field'));
        return $this->definitions->normalize($definition);
    }
}
