<?php

declare(strict_types=1);

namespace WPEssential\Tests\Unit\Modules\Fields;

use InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use RuntimeException;
use WPEssential\Modules\Fields\FieldDefinitionNormalizer;
use WPEssential\Modules\Fields\PostMetaValueStore;

final class PostMetaValueStoreNumericHardeningTest extends TestCase
{
    public function testRejectsNonFiniteWriteBeforeNativeMutation(): void
    {
        $updates = 0;
        $store = $this->store(
            [],
            static function (int $postId, string $metaKey, mixed $value) use (&$updates): int|bool {
                ++$updates;
                return true;
            },
        );

        $this->expectException(InvalidArgumentException::class);
        try {
            $store->write($this->numberField(), 'book', 41, INF);
        } finally {
            self::assertSame(0, $updates);
        }
    }

    public function testRejectsPersistedNumberThatOverflowsFiniteRange(): void
    {
        $store = $this->store([41 => ['ratio' => '1e309']]);

        $this->expectException(RuntimeException::class);
        $store->read($this->numberField(), 'book', 41);
    }

    public function testLargeFiniteNumberUsesFloatInsteadOfSaturatingInteger(): void
    {
        $store = $this->store([41 => ['ratio' => '999999999999999999999999']]);
        $value = $store->read($this->numberField(), 'book', 41);

        self::assertIsFloat($value);
        self::assertTrue(is_finite($value));
        self::assertGreaterThan((float) PHP_INT_MAX, $value);
    }

    /**
     * @param array<int,array<string,mixed>> $state
     * @param null|callable(int,string,mixed):int|bool $update
     */
    private function store(array $state, ?callable $update = null): PostMetaValueStore
    {
        $update ??= static fn (int $postId, string $metaKey, mixed $value): int|bool => true;

        return new PostMetaValueStore(
            getPostType: static fn (int $postId): string|false => 'book',
            metadataExists: static fn (int $postId, string $metaKey): bool => array_key_exists($metaKey, $state[$postId] ?? []),
            getPostMeta: static fn (int $postId, string $metaKey, bool $single): mixed => $state[$postId][$metaKey] ?? null,
            updatePostMeta: $update,
            deletePostMeta: static fn (int $postId, string $metaKey): bool => true,
            slash: static fn (mixed $value): mixed => $value,
        );
    }

    /** @return array<string,mixed> */
    private function numberField(): array
    {
        return (new FieldDefinitionNormalizer())->normalize([
            'uuid' => '11111111-1111-4111-8111-111111111111',
            'key' => 'ratio',
            'label' => 'Ratio',
            'type' => 'number',
        ]);
    }
}
