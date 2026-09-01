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
use WPEssential\Modules\Fields\PostMetaRegistrationCompiler;
use WPEssential\Modules\Fields\PostMetaValueStore;
use WPEssential\Modules\Fields\WordPressPostMetaRegistrar;
use WPEssential\Platform\Definitions\Definition;
use WPEssential\Platform\Definitions\DefinitionStatus;
use WPEssential\Platform\Definitions\InMemoryDefinitionRepository;

final class FieldStorageKeyMigrationServiceTest extends TestCase
{
    private const GROUP_ID = '11111111-1111-4111-8111-111111111111';
    private const FIELD_UUID = '22222222-2222-4222-8222-222222222222';

    public function testUnchangedKeyIsNoOpWithoutRevisionAdvance(): void
    {
        $state = [41 => ['headline' => 'Hello']];
        $registered = [];
        $repository = new InMemoryDefinitionRepository();
        $service = $this->service($state, $registered, $repository, $this->textField());

        $result = $service->migrate(self::GROUP_ID, 3, self::FIELD_UUID, 'headline');

        self::assertFalse($result->changed);
        self::assertSame(0, $result->migratedObjects);
        self::assertSame(3, $result->definition->revision);
        self::assertSame('Hello', $state[41]['headline']);
        self::assertArrayHasKey('headline', $registered['book']);
    }

    public function testMigratesScalarValueAndAdvancesDefinitionRevision(): void
    {
        $state = [
            41 => ['headline' => 'Hello'],
            42 => ['headline' => 'World'],
        ];
        $registered = [];
        $repository = new InMemoryDefinitionRepository();
        $service = $this->service($state, $registered, $repository, $this->textField());

        $result = $service->migrate(self::GROUP_ID, 3, self::FIELD_UUID, 'title');

        self::assertTrue($result->changed);
        self::assertSame(2, $result->migratedObjects);
        self::assertSame(['book'], $result->postTypes);
        self::assertSame('Hello', $state[41]['title']);
        self::assertSame('World', $state[42]['title']);
        self::assertArrayNotHasKey('headline', $state[41]);
        self::assertArrayNotHasKey('headline', $state[42]);
        self::assertArrayNotHasKey('headline', $registered['book']);
        self::assertArrayHasKey('title', $registered['book']);

        $persisted = $repository->get(self::GROUP_ID);
        self::assertInstanceOf(Definition::class, $persisted);
        self::assertSame(4, $persisted->revision);
        self::assertSame(self::FIELD_UUID, $persisted->payload['fields'][0]['uuid']);
        self::assertSame('title', $persisted->payload['fields'][0]['key']);
        self::assertSame($persisted->computedChecksum(), $persisted->checksum);
    }

    public function testMigratesSingleArrayStorageWithoutChangingListOrder(): void
    {
        $state = [41 => ['aliases' => ['One', 'Two', 'One']]];
        $registered = [];
        $repository = new InMemoryDefinitionRepository();
        $service = $this->service($state, $registered, $repository, [
            'uuid' => self::FIELD_UUID,
            'key' => 'aliases',
            'label' => 'Aliases',
            'type' => 'text',
            'cloneable' => true,
            'max_clones' => 10,
        ]);

        $result = $service->migrate(self::GROUP_ID, 3, self::FIELD_UUID, 'alternate_names');

        self::assertTrue($result->changed);
        self::assertSame(['One', 'Two', 'One'], $state[41]['alternate_names']);
        self::assertArrayNotHasKey('aliases', $state[41]);
    }

    public function testMigratesMultiRowStoragePreservingOrderAndDuplicates(): void
    {
        $state = [41 => ['aliases' => ['One', 'One', 'Two']]];
        $registered = [];
        $repository = new InMemoryDefinitionRepository();
        $service = $this->service($state, $registered, $repository, [
            'uuid' => self::FIELD_UUID,
            'key' => 'aliases',
            'label' => 'Aliases',
            'type' => 'text',
            'cloneable' => true,
            'clone_as_multiple' => true,
            'max_clones' => 10,
        ]);

        $service->migrate(self::GROUP_ID, 3, self::FIELD_UUID, 'alternate_names');

        self::assertSame(['One', 'One', 'Two'], $state[41]['alternate_names']);
        self::assertArrayNotHasKey('aliases', $state[41]);
    }

    public function testDestinationDataCollisionFailsBeforeMutation(): void
    {
        $state = [41 => ['headline' => 'Original', 'title' => 'Existing']];
        $registered = [];
        $repository = new InMemoryDefinitionRepository();
        $service = $this->service($state, $registered, $repository, $this->textField());

        try {
            $service->migrate(self::GROUP_ID, 3, self::FIELD_UUID, 'title');
            self::fail('Destination data collision must fail closed.');
        } catch (RuntimeException $exception) {
            self::assertStringContainsString('already contains data', $exception->getMessage());
        }

        self::assertSame('Original', $state[41]['headline']);
        self::assertSame('Existing', $state[41]['title']);
        self::assertSame(3, $repository->get(self::GROUP_ID)?->revision);
    }

    public function testForeignDestinationRegistrationFailsBeforeValueMutation(): void
    {
        $state = [41 => ['headline' => 'Original']];
        $registered = [];
        $repository = new InMemoryDefinitionRepository();
        $service = $this->service($state, $registered, $repository, $this->textField());
        $registered['book']['title'] = $registered['book']['headline'];
        $registered['book']['title']['description'] = 'Foreign field registration.';

        try {
            $service->migrate(self::GROUP_ID, 3, self::FIELD_UUID, 'title');
            self::fail('Foreign destination registration must block migration.');
        } catch (RuntimeException $exception) {
            self::assertStringContainsString('already owned by another registration', $exception->getMessage());
        }

        self::assertSame(['headline' => 'Original'], $state[41]);
        self::assertSame(3, $repository->get(self::GROUP_ID)?->revision);
    }

    public function testSourceOwnershipDriftBlocksBeforeDestructiveMutation(): void
    {
        $state = [41 => ['headline' => 'Original']];
        $registered = [];
        $repository = new InMemoryDefinitionRepository();
        $service = $this->service($state, $registered, $repository, $this->textField());
        $registered['book']['headline']['description'] = 'Foreign field registration.';

        try {
            $service->migrate(self::GROUP_ID, 3, self::FIELD_UUID, 'title');
            self::fail('Source ownership drift must block migration.');
        } catch (RuntimeException $exception) {
            self::assertStringContainsString('already owned by another registration', $exception->getMessage());
        }

        self::assertSame(['headline' => 'Original'], $state[41]);
        self::assertArrayNotHasKey('title', $registered['book']);
    }

    public function testConcurrentSourceEditIsPreservedAndDestinationIsRolledBack(): void
    {
        $state = [41 => ['headline' => 'Original']];
        $registered = [];
        $repository = new InMemoryDefinitionRepository();
        $service = $this->service(
            $state,
            $registered,
            $repository,
            $this->textField(),
            onUpdate: static function (int $postId, string $metaKey, mixed $value) use (&$state): void {
                if ($metaKey === 'title') {
                    $state[$postId]['headline'] = 'Concurrent edit';
                }
            },
        );

        try {
            $service->migrate(self::GROUP_ID, 3, self::FIELD_UUID, 'title');
            self::fail('Concurrent source edit must abort destructive retirement.');
        } catch (RuntimeException $exception) {
            self::assertStringContainsString('verified as restored', $exception->getMessage());
        }

        self::assertSame('Concurrent edit', $state[41]['headline']);
        self::assertArrayNotHasKey('title', $state[41]);
        self::assertArrayHasKey('headline', $registered['book']);
        self::assertArrayNotHasKey('title', $registered['book']);
        self::assertSame(3, $repository->get(self::GROUP_ID)?->revision);
    }

    public function testExpectedRevisionConflictFailsBeforeMigration(): void
    {
        $state = [41 => ['headline' => 'Original']];
        $registered = [];
        $repository = new InMemoryDefinitionRepository();
        $service = $this->service($state, $registered, $repository, $this->textField());

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('expected revision 2, current revision is 3');
        $service->migrate(self::GROUP_ID, 2, self::FIELD_UUID, 'title');
    }

    /** @return array<string,mixed> */
    private function textField(): array
    {
        return [
            'uuid' => self::FIELD_UUID,
            'key' => 'headline',
            'label' => 'Headline',
            'type' => 'text',
        ];
    }

    /**
     * @param array<int,array<string,mixed>> $state
     * @param array<string,array<string,array<string,mixed>>> $registered
     * @param array<string,mixed> $field
     * @param null|callable(int,string,mixed):void $onUpdate
     */
    private function service(
        array &$state,
        array &$registered,
        InMemoryDefinitionRepository $repository,
        array $field,
        ?callable $onUpdate = null,
    ): FieldStorageKeyMigrationService {
        $definition = $this->definition($field);
        $repository->save($definition);

        $groups = new FieldGroupDefinitionNormalizer();
        $storage = new FieldGroupRuntimeStorageProjection();
        $targets = new FieldGroupPostTypeTargetCompiler();
        $values = new FieldValueNormalizer();
        $compiler = new PostMetaRegistrationCompiler($values);
        $normalizedGroup = $groups->normalize($definition->payload, true);
        $normalizedField = $normalizedGroup['fields'][0];
        self::assertIsArray($normalizedField);
        $groupRuntime = $storage->projectGroup($normalizedGroup);
        $fieldRuntime = $storage->projectField($normalizedField, $groupRuntime['show_in_rest']);
        $sourceRegistration = $compiler->compile(
            $normalizedField,
            'book',
            showInRest: $fieldRuntime['show_in_rest'],
            revisionsEnabled: $groupRuntime['revisions_enabled'],
        );
        $registered['book'][$normalizedField['key']] = $sourceRegistration['args'];

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
            unregisterMetaKey: static function (string $objectType, string $metaKey, string $objectSubtype) use (&$registered): bool {
                if ($objectType !== 'post' || !isset($registered[$objectSubtype][$metaKey])) {
                    return false;
                }
                unset($registered[$objectSubtype][$metaKey]);
                return true;
            },
        );

        $metadataExists = static function (int $postId, string $metaKey) use (&$state): bool {
            return array_key_exists($metaKey, $state[$postId] ?? []);
        };
        $delete = static function (int $postId, string $metaKey) use (&$state): bool {
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
            updatePostMeta: static function (int $postId, string $metaKey, mixed $value) use (&$state, $onUpdate): int|bool {
                $state[$postId][$metaKey] = $value;
                if ($onUpdate !== null) {
                    $onUpdate($postId, $metaKey, $value);
                }
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

    /** @param array<string,mixed> $field */
    private function definition(array $field): Definition
    {
        return new Definition(
            id: self::GROUP_ID,
            slug: 'catalog-fields',
            type: FieldGroupDefinitionNormalizer::DEFINITION_TYPE,
            schemaVersion: 1,
            ownerSurfaceId: FieldGroupDefinitionNormalizer::OWNER_SURFACE_ID,
            status: DefinitionStatus::Published,
            payload: [
                'group_key' => 'catalog_fields',
                'title' => 'Catalog Fields',
                'fields' => [$field],
                'locations' => [[
                    ['source' => 'post_type', 'operator' => 'equals', 'value' => 'book'],
                ]],
                'storage' => ['mode' => 'native_post_meta'],
                'show_in_rest' => false,
                'revision_policy' => 'disabled',
            ],
            revision: 3,
        );
    }
}
