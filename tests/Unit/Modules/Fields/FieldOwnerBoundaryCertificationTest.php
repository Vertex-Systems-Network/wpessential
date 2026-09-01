<?php

declare(strict_types=1);

namespace WPEssential\Tests\Unit\Modules\Fields;

use InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use WPEssential\Modules\Fields\FieldDefinitionNormalizer;
use WPEssential\Modules\Fields\FieldValueNormalizer;
use WPEssential\Modules\Fields\PostMetaRegistrationCompiler;
use WPEssential\Modules\Fields\PostMetaValueStore;

final class FieldOwnerBoundaryCertificationTest extends TestCase
{
    private const OWNER_BOUND_TYPES = [
        'relationship',
        'taxonomy',
        'user',
        'page_link',
        'nav_menu',
        'sidebar',
        'group',
        'repeater',
        'flexible_content',
        'clone',
        'accordion',
        'tab',
    ];

    private FieldDefinitionNormalizer $definitions;

    protected function setUp(): void
    {
        $this->definitions = new FieldDefinitionNormalizer();
    }

    public function testOwnerBoundTypesFailClosedAtRegisteredPostMetaCompiler(): void
    {
        $compiler = new PostMetaRegistrationCompiler();

        foreach (self::OWNER_BOUND_TYPES as $type) {
            try {
                $compiler->compile($this->field($type), 'book');
                self::fail(sprintf('Owner-bound field type "%s" must not compile to native post meta.', $type));
            } catch (InvalidArgumentException $exception) {
                self::assertNotSame('', $exception->getMessage(), sprintf('Type "%s" must fail with an explicit reason.', $type));
            }
        }
    }

    public function testOwnerBoundWritesFailBeforeAnyNativeMutationBoundary(): void
    {
        foreach (self::OWNER_BOUND_TYPES as $type) {
            $updates = 0;
            $deletes = 0;
            $adds = 0;
            $store = $this->store($updates, $deletes, $adds);

            try {
                $store->write($this->field($type), 'book', 41, 'unexpected-owner-value');
                self::fail(sprintf('Owner-bound field type "%s" must fail before native mutation.', $type));
            } catch (InvalidArgumentException) {
                self::assertSame(0, $updates, sprintf('Type "%s" must not call update_post_meta().', $type));
                self::assertSame(0, $deletes, sprintf('Type "%s" must not call delete_post_meta().', $type));
                self::assertSame(0, $adds, sprintf('Type "%s" must not call add_post_meta().', $type));
            }
        }
    }

    public function testCertifiedPostReferenceTypesRemainNativeIntegerContracts(): void
    {
        $compiler = new PostMetaRegistrationCompiler();

        $postObject = $compiler->compile($this->field('post_object'), 'book', showInRest: true);
        self::assertSame('integer', $postObject['args']['type']);
        self::assertTrue($postObject['args']['single']);
        self::assertSame(['schema' => ['type' => 'integer']], $postObject['args']['show_in_rest']);

        $posts = $compiler->compile($this->field('posts'), 'book', showInRest: true);
        self::assertSame('array', $posts['args']['type']);
        self::assertTrue($posts['args']['single']);
        self::assertSame(
            ['schema' => ['type' => 'array', 'items' => ['type' => 'integer']]],
            $posts['args']['show_in_rest'],
        );
    }

    private function store(int &$updates, int &$deletes, int &$adds): PostMetaValueStore
    {
        $values = new FieldValueNormalizer();

        return new PostMetaValueStore(
            compiler: new PostMetaRegistrationCompiler($values),
            values: $values,
            getPostType: static fn (int $postId): string|false => 'book',
            metadataExists: static fn (int $postId, string $metaKey): bool => false,
            getPostMeta: static fn (int $postId, string $metaKey, bool $single): mixed => $single ? null : [],
            updatePostMeta: static function (int $postId, string $metaKey, mixed $value) use (&$updates): int|bool {
                ++$updates;
                return true;
            },
            deletePostMeta: static function (int $postId, string $metaKey) use (&$deletes): bool {
                ++$deletes;
                return true;
            },
            addPostMeta: static function (int $postId, string $metaKey, mixed $value) use (&$adds): int|false {
                ++$adds;
                return 1;
            },
            slash: static fn (mixed $value): mixed => $value,
        );
    }

    /** @return array<string,mixed> */
    private function field(string $type): array
    {
        return $this->definitions->normalize([
            'uuid' => '11111111-1111-4111-8111-111111111111',
            'key' => 'owner_boundary',
            'label' => 'Owner Boundary',
            'type' => $type,
        ]);
    }
}
