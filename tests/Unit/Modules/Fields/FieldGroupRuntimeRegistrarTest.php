<?php

declare(strict_types=1);

namespace WPEssential\Tests\Unit\Modules\Fields;

use PHPUnit\Framework\TestCase;
use WPEssential\Modules\Fields\FieldGroupDefinitionNormalizer;
use WPEssential\Modules\Fields\FieldGroupPostMetaBinder;
use WPEssential\Modules\Fields\FieldGroupPostTypeTargetCompiler;
use WPEssential\Modules\Fields\FieldGroupRuntimeRegistrar;
use WPEssential\Modules\Fields\FieldGroupRuntimeStorageProjection;
use WPEssential\Modules\Fields\PostMetaRegistrationCompiler;
use WPEssential\Modules\Fields\WordPressPostMetaRegistrar;
use WPEssential\Platform\Definitions\Definition;
use WPEssential\Platform\Definitions\DefinitionStatus;
use WPEssential\Platform\Definitions\InMemoryDefinitionRepository;

final class FieldGroupRuntimeRegistrarTest extends TestCase
{
    public function testRegistersAtInitThirtyAndBindsOnlyCanonicalPublishedGroupsOnceInDeterministicOrder(): void
    {
        $repository = new InMemoryDefinitionRepository();
        $repository->save($this->definition(
            '22222222-2222-4222-8222-222222222222',
            'field-group-zulu',
            'zulu',
            'bbbbbbbb-bbbb-4bbb-8bbb-bbbbbbbbbbbb',
            'zulu_value',
        ));
        $repository->save($this->definition(
            '11111111-1111-4111-8111-111111111111',
            'field-group-alpha',
            'alpha',
            'aaaaaaaa-aaaa-4aaa-8aaa-aaaaaaaaaaaa',
            'alpha_value',
        ));
        $repository->save($this->definition(
            '33333333-3333-4333-8333-333333333333',
            'field-group-draft',
            'draft',
            'cccccccc-cccc-4ccc-8ccc-cccccccccccc',
            'draft_value',
            status: DefinitionStatus::Draft,
        ));
        $repository->save($this->definition(
            '44444444-4444-4444-8444-444444444444',
            'field-group-foreign',
            'foreign',
            'dddddddd-dddd-4ddd-8ddd-dddddddddddd',
            'foreign_value',
            ownerSurfaceId: 4,
        ));

        $nativeCalls = [];
        $binder = $this->binder(static function (string $postType, string $metaKey, array $args) use (&$nativeCalls): bool {
            $nativeCalls[] = [$postType, $metaKey];
            return true;
        });
        $hook = null;
        $callback = null;
        $priority = null;
        $runtime = new FieldGroupRuntimeRegistrar(
            $repository,
            $binder,
            static function (string $registeredHook, callable $registeredCallback, int $registeredPriority) use (
                &$hook,
                &$callback,
                &$priority,
            ): void {
                $hook = $registeredHook;
                $callback = $registeredCallback;
                $priority = $registeredPriority;
            },
        );

        $runtime->register();

        self::assertSame('init', $hook);
        self::assertSame(30, $priority);
        self::assertIsCallable($callback);
        $callback();
        $callback();

        self::assertTrue($runtime->processed());
        self::assertSame([
            '11111111-1111-4111-8111-111111111111',
            '22222222-2222-4222-8222-222222222222',
        ], $runtime->bound());
        self::assertSame([], $runtime->errors());
        self::assertSame([
            ['book', 'alpha_value'],
            ['book', 'zulu_value'],
        ], $nativeCalls);
    }

    public function testCrossGroupStorageKeyCollisionFailsCombinedPlanBeforeFirstNativeMutation(): void
    {
        $repository = new InMemoryDefinitionRepository();
        $repository->save($this->definition(
            '11111111-1111-4111-8111-111111111111',
            'field-group-alpha',
            'alpha',
            'aaaaaaaa-aaaa-4aaa-8aaa-aaaaaaaaaaaa',
            'shared_key',
        ));
        $repository->save($this->definition(
            '22222222-2222-4222-8222-222222222222',
            'field-group-beta',
            'beta',
            'bbbbbbbb-bbbb-4bbb-8bbb-bbbbbbbbbbbb',
            'shared_key',
        ));

        $nativeCalls = 0;
        $runtime = new FieldGroupRuntimeRegistrar(
            $repository,
            $this->binder(static function (string $postType, string $metaKey, array $args) use (&$nativeCalls): bool {
                ++$nativeCalls;
                return true;
            }),
        );

        $runtime->registerActive();

        self::assertTrue($runtime->processed());
        self::assertSame([], $runtime->bound());
        self::assertSame(0, $nativeCalls);
        self::assertArrayHasKey('runtime', $runtime->errors());
        self::assertStringContainsString('duplicate key "shared_key"', $runtime->errors()['runtime']);
    }

    /** @param callable(string,string,array<string,mixed>):bool $register */
    private function binder(callable $register): FieldGroupPostMetaBinder
    {
        return new FieldGroupPostMetaBinder(
            new FieldGroupDefinitionNormalizer(),
            new FieldGroupRuntimeStorageProjection(),
            new FieldGroupPostTypeTargetCompiler(),
            new PostMetaRegistrationCompiler(),
            new WordPressPostMetaRegistrar(
                $register,
                static fn (string $postType, string $feature): bool => true,
                static fn (string $objectType, string $objectSubtype): array => [],
            ),
        );
    }

    private function definition(
        string $id,
        string $slug,
        string $groupKey,
        string $fieldUuid,
        string $fieldKey,
        DefinitionStatus $status = DefinitionStatus::Published,
        int $ownerSurfaceId = FieldGroupDefinitionNormalizer::OWNER_SURFACE_ID,
    ): Definition {
        $payload = (new FieldGroupDefinitionNormalizer())->normalize([
            'group_key' => $groupKey,
            'title' => ucfirst($groupKey),
            'fields' => [[
                'uuid' => $fieldUuid,
                'key' => $fieldKey,
                'label' => ucfirst(str_replace('_', ' ', $fieldKey)),
                'type' => 'text',
            ]],
            'locations' => [[
                ['source' => 'post_type', 'operator' => 'equals', 'value' => 'book'],
            ]],
            'storage' => ['mode' => 'native_post_meta'],
        ], $status === DefinitionStatus::Published);

        return new Definition(
            id: $id,
            slug: $slug,
            type: FieldGroupDefinitionNormalizer::DEFINITION_TYPE,
            schemaVersion: 1,
            ownerSurfaceId: $ownerSurfaceId,
            status: $status,
            payload: $payload,
            revision: 1,
        );
    }
}
