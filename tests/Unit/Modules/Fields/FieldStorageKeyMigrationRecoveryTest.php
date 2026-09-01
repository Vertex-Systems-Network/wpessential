<?php

declare(strict_types=1);

namespace WPEssential\Tests\Unit\Modules\Fields;

use PHPUnit\Framework\TestCase;
use RuntimeException;
use WPEssential\Modules\Fields\FieldGroupDefinitionNormalizer;
use WPEssential\Modules\Fields\FieldGroupPostTypeTargetCompiler;
use WPEssential\Modules\Fields\FieldGroupRuntimeStorageProjection;
use WPEssential\Modules\Fields\FieldStorageKeyMigrationService;
use WPEssential\Modules\Fields\FieldValueNormalizer;
use WPEssential\Modules\Fields\PostMetaRecoveryException;
use WPEssential\Modules\Fields\PostMetaRegistrationCompiler;
use WPEssential\Modules\Fields\PostMetaValueStore;
use WPEssential\Modules\Fields\WordPressPostMetaRegistrar;
use WPEssential\Platform\Definitions\Definition;
use WPEssential\Platform\Definitions\DefinitionStatus;
use WPEssential\Platform\Definitions\InMemoryDefinitionRepository;

final class FieldStorageKeyMigrationRecoveryTest extends TestCase
{
    private const GROUP_ID = '31111111-1111-4111-8111-111111111111';
    private const FIELD_UUID = '32222222-2222-4222-8222-222222222222';

    public function testSourceRegistrationRetirementFailureRestoresDeletedSourceAndRemovesDestination(): void
    {
        $state = [41 => ['headline' => 'Original']];
        $registered = [];
        $repository = new InMemoryDefinitionRepository();
        $service = $this->service(
            $state,
            $registered,
            $repository,
            failSourceUnregister: true,
            failDestinationDelete: false,
        );

        try {
            $service->migrate(self::GROUP_ID, 3, self::FIELD_UUID, 'title');
            self::fail('Source registration retirement failure must keep migration failed.');
        } catch (RuntimeException $exception) {
            self::assertStringContainsString('verified as restored', $exception->getMessage());
        }

        self::assertSame(['headline' => 'Original'], $state[41]);
        self::assertArrayHasKey('headline', $registered['book']);
        self::assertArrayNotHasKey('title', $registered['book']);
        self::assertSame(3, $repository->get(self::GROUP_ID)?->revision);
        self::assertSame('headline', $repository->get(self::GROUP_ID)?->payload['fields'][0]['key']);
    }

    public function testRecoveryFailureRaisesExplicitUncertainStateException(): void
    {
        $state = [41 => ['headline' => 'Original']];
        $registered = [];
        $repository = new InMemoryDefinitionRepository();
        $service = $this->service(
            $state,
            $registered,
            $repository,
            failSourceUnregister: true,
            failDestinationDelete: true,
        );

        try {
            $service->migrate(self::GROUP_ID, 3, self::FIELD_UUID, 'title');
            self::fail('Unverified compensation must throw an explicit recovery exception.');
        } catch (PostMetaRecoveryException $exception) {
            self::assertStringContainsString('state is uncertain', $exception->getMessage());
        }

        self::assertSame('Original', $state[41]['headline']);
        self::assertSame('Original', $state[41]['title']);
        self::assertArrayHasKey('headline', $registered['book']);
        self::assertArrayNotHasKey('title', $registered['book']);
        self::assertSame(3, $repository->get(self::GROUP_ID)?->revision);
    }

    /**
     * @param array<int,array<string,mixed>> $state
     * @param array<string,array<string,array<string,mixed>>> $registered
     */
    private function service(
        array &$state,
        array &$registered,
        InMemoryDefinitionRepository $repository,
        bool $failSourceUnregister,
        bool $failDestinationDelete,
    ): FieldStorageKeyMigrationService {
        $definition = new Definition(
            id: self::GROUP_ID,
            slug: 'migration-recovery',
            type: FieldGroupDefinitionNormalizer::DEFINITION_TYPE,
            schemaVersion: 1,
            ownerSurfaceId: FieldGroupDefinitionNormalizer::OWNER_SURFACE_ID,
            status: DefinitionStatus::Published,
            payload: [
                'group_key' => 'migration_recovery',
                'title' => 'Migration Recovery',
                'fields' => [[
                    'uuid' => self::FIELD_UUID,
                    'key' => 'headline',
                    'label' => 'Headline',
                    'type' => 'text',
                ]],
                'locations' => [[
                    ['source' => 'post_type', 'operator' => 'equals', 'value' => 'book'],
                ]],
                'storage' => ['mode' => 'native_post_meta'],
                'show_in_rest' => false,
                'revision_policy' => 'disabled',
            ],
            revision: 3,
        );
        $repository->save($definition);

        $groups = new FieldGroupDefinitionNormalizer();
        $storage = new FieldGroupRuntimeStorageProjection();
        $targets = new FieldGroupPostTypeTargetCompiler();
        $values = new FieldValueNormalizer();
        $compiler = new PostMetaRegistrationCompiler($values);
        $normalizedGroup = $groups->normalize($definition->payload, true);
        $sourceField = $normalizedGroup['fields'][0] ?? null;
        self::assertIsArray($sourceField);
        $groupRuntime = $storage->projectGroup($normalizedGroup);
        $fieldRuntime = $storage->projectField($sourceField, $groupRuntime['show_in_rest']);
        $sourceRegistration = $compiler->compile(
            $sourceField,
            'book',
            showInRest: $fieldRuntime['show_in_rest'],
            revisionsEnabled: $groupRuntime['revisions_enabled'],
        );
        $registered['book']['headline'] = $sourceRegistration['args'];

        $registrar = new WordPressPostMetaRegistrar(
            static function (string $postType, string $metaKey, array $args) use (&$registered): bool {
                $registered[$postType][$metaKey] = $args;
                return true;
            },
            static fn (string $postType, string $feature): bool => $postType === 'book'
                && in_array($feature, ['custom-fields', 'revisions'], true),
            static function (string $objectType, string $objectSubtype) use (&$registered): array {
                if ($objectType !== 'post' || $objectSubtype === '') {
                    return [];
                }
                return $registered[$objectSubtype] ?? [];
            },
            unregisterMetaKey: static function (string $objectType, string $metaKey, string $objectSubtype) use (&$registered, $failSourceUnregister): bool {
                if ($objectType !== 'post' || !isset($registered[$objectSubtype][$metaKey])) {
                    return false;
                }
                if ($failSourceUnregister && $metaKey === 'headline') {
                    return false;
                }
                unset($registered[$objectSubtype][$metaKey]);
                return true;
            },
        );

        $metadataExists = static function (int $postId, string $metaKey) use (&$state): bool {
            return array_key_exists($metaKey, $state[$postId] ?? []);
        };
        $delete = static function (int $postId, string $metaKey) use (&$state, $failDestinationDelete): bool {
            if ($failDestinationDelete && $metaKey === 'title') {
                return false;
            }
            if (!array_key_exists($metaKey, $state[$postId] ?? [])) {
                return false;
            }
            unset($state[$postId][$metaKey]);
            return true;
        };
        $store = new PostMetaValueStore(
            compiler: $compiler,
            values: $values,
            getPostType: static fn (int $postId): string|false => 'book',
            metadataExists: $metadataExists,
            getPostMeta: static function (int $postId, string $metaKey, bool $single) use (&$state): mixed {
                $value = $state[$postId][$metaKey] ?? null;
                if ($single) {
                    return $value;
                }
                return is_array($value) && array_is_list($value) ? $value : [];
            },
            updatePostMeta: static function (int $postId, string $metaKey, mixed $value) use (&$state): int|bool {
                $state[$postId][$metaKey] = $value;
                return true;
            },
            deletePostMeta: $delete,
            addPostMeta: static function (int $postId, string $metaKey, mixed $value) use (&$state): int|false {
                $state[$postId][$metaKey] ??= [];
                if (!is_array($state[$postId][$metaKey]) || !array_is_list($state[$postId][$metaKey])) {
                    return false;
                }
                $state[$postId][$metaKey][] = $value;
                return count($state[$postId][$metaKey]);
            },
            slash: static fn (mixed $value): mixed => $value,
        );

        return new FieldStorageKeyMigrationService(
            definitions: $repository,
            groups: $groups,
            storage: $storage,
            targets: $targets,
            compiler: $compiler,
            registrar: $registrar,
            values: $store,
            findPostIds: static function (string $postType, string $metaKey) use (&$state): array {
                if ($postType !== 'book') {
                    return [];
                }
                $ids = [];
                foreach ($state as $postId => $meta) {
                    if (array_key_exists($metaKey, $meta)) {
                        $ids[] = $postId;
                    }
                }
                sort($ids, SORT_NUMERIC);
                return $ids;
            },
            metadataExists: $metadataExists,
            deletePostMeta: $delete,
            slash: static fn (mixed $value): mixed => $value,
        );
    }
}
