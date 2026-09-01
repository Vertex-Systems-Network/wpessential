<?php

declare(strict_types=1);

namespace WPEssential\Tests\Unit\Modules\Fields;

use InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use RuntimeException;
use WPEssential\Modules\Fields\FieldGroupDefinitionNormalizer;
use WPEssential\Modules\Fields\FieldGroupPostMetaBinder;
use WPEssential\Modules\Fields\FieldGroupPostTypeTargetCompiler;
use WPEssential\Modules\Fields\FieldGroupRuntimeStorageProjection;
use WPEssential\Modules\Fields\PostMetaRegistrationCompiler;
use WPEssential\Modules\Fields\WordPressPostMetaRegistrar;
use WPEssential\Platform\Definitions\Definition;
use WPEssential\Platform\Definitions\DefinitionStatus;

final class FieldGroupPostMetaBinderTest extends TestCase
{
    public function testBindsPublishedNativePostMetaGroupAcrossFiniteTargets(): void
    {
        $calls = [];
        $binder = $this->binder(
            static function (string $postType, string $metaKey, array $args) use (&$calls): bool {
                $calls[] = [$postType, $metaKey, $args];
                return true;
            },
        );

        $binder->bind($this->definition());

        self::assertCount(4, $calls);
        self::assertSame(
            [
                ['page', 'headline'],
                ['page', 'rating'],
                ['post', 'headline'],
                ['post', 'rating'],
            ],
            array_map(static fn (array $call): array => [$call[0], $call[1]], $calls),
        );

        self::assertIsArray($calls[0][2]['show_in_rest']);
        self::assertSame(['type' => 'string'], $calls[0][2]['show_in_rest']['schema']);
        self::assertFalse($calls[1][2]['show_in_rest']);
        self::assertTrue($calls[0][2]['revisions_enabled']);
        self::assertTrue($calls[1][2]['revisions_enabled']);
    }

    public function testRejectsNonPublishedDefinitionBeforeRegistration(): void
    {
        $calls = 0;
        $binder = $this->binder(
            static function (string $postType, string $metaKey, array $args) use (&$calls): bool {
                ++$calls;
                return true;
            },
        );

        $this->expectException(InvalidArgumentException::class);
        try {
            $binder->bind($this->definition(status: DefinitionStatus::Draft));
        } finally {
            self::assertSame(0, $calls);
        }
    }

    public function testRejectsNonNativeStorageBeforeRegistration(): void
    {
        $calls = 0;
        $binder = $this->binder(
            static function (string $postType, string $metaKey, array $args) use (&$calls): bool {
                ++$calls;
                return true;
            },
        );

        $definition = $this->definition();
        $payload = $definition->payload;
        $payload['storage'] = ['mode' => 'unconfigured'];

        $this->expectException(InvalidArgumentException::class);
        try {
            $binder->bind($this->definition(payload: $payload));
        } finally {
            self::assertSame(0, $calls);
        }
    }

    public function testLateOwnershipCollisionIsDetectedBeforeAnyRegistrationMutation(): void
    {
        $calls = 0;
        $binder = $this->binder(
            static function (string $postType, string $metaKey, array $args) use (&$calls): bool {
                ++$calls;
                return true;
            },
            static fn (string $objectType, string $objectSubtype): array => $objectSubtype === 'post'
                ? ['rating' => ['description' => 'Foreign field registration.']]
                : [],
        );

        try {
            $binder->bind($this->definition());
            self::fail('Expected ownership collision during full-plan preflight.');
        } catch (RuntimeException $exception) {
            self::assertStringContainsString('already owned by another registration', $exception->getMessage());
        }

        self::assertSame(0, $calls);
    }

    public function testGroupRestPolicyRemainsHardUpperBound(): void
    {
        $calls = [];
        $binder = $this->binder(
            static function (string $postType, string $metaKey, array $args) use (&$calls): bool {
                $calls[] = [$postType, $metaKey, $args];
                return true;
            },
        );

        $definition = $this->definition();
        $payload = $definition->payload;
        $payload['show_in_rest'] = false;

        $binder->bind($this->definition(payload: $payload));

        self::assertNotEmpty($calls);
        foreach ($calls as $call) {
            self::assertFalse($call[2]['show_in_rest']);
        }
    }

    /**
     * @param callable(string,string,array<string,mixed>):bool $register
     * @param null|callable(string,string):array<string,array<string,mixed>> $registered
     */
    private function binder(callable $register, ?callable $registered = null): FieldGroupPostMetaBinder
    {
        $groups = new FieldGroupDefinitionNormalizer();
        $compiler = new PostMetaRegistrationCompiler();
        $registrar = new WordPressPostMetaRegistrar(
            $register,
            static fn (string $postType, string $feature): bool => in_array($postType, ['page', 'post'], true)
                && in_array($feature, ['revisions', 'custom-fields'], true),
            $registered ?? static fn (string $objectType, string $objectSubtype): array => [],
        );

        return new FieldGroupPostMetaBinder(
            $groups,
            new FieldGroupRuntimeStorageProjection(),
            new FieldGroupPostTypeTargetCompiler(),
            $compiler,
            $registrar,
        );
    }

    /** @param null|array<string,mixed> $payload */
    private function definition(
        DefinitionStatus $status = DefinitionStatus::Published,
        ?array $payload = null,
    ): Definition {
        return new Definition(
            id: '11111111-1111-4111-8111-111111111111',
            slug: 'catalog-fields',
            type: FieldGroupDefinitionNormalizer::DEFINITION_TYPE,
            schemaVersion: 1,
            ownerSurfaceId: FieldGroupDefinitionNormalizer::OWNER_SURFACE_ID,
            status: $status,
            payload: $payload ?? [
                'group_key' => 'catalog_fields',
                'title' => 'Catalog Fields',
                'fields' => [
                    [
                        'uuid' => '22222222-2222-4222-8222-222222222222',
                        'key' => 'headline',
                        'label' => 'Headline',
                        'type' => 'text',
                        'show_in_rest' => true,
                    ],
                    [
                        'uuid' => '33333333-3333-4333-8333-333333333333',
                        'key' => 'rating',
                        'label' => 'Rating',
                        'type' => 'number',
                        'show_in_rest' => false,
                    ],
                ],
                'locations' => [[
                    ['source' => 'post_type', 'operator' => 'in', 'value' => ['post', 'page']],
                ]],
                'storage' => ['mode' => 'native_post_meta'],
                'show_in_rest' => true,
                'revision_policy' => 'enabled',
            ],
            revision: 3,
        );
    }
}
