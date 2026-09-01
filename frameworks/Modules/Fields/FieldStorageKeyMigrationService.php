<?php

declare(strict_types=1);

namespace WPEssential\Modules\Fields;

if (!defined('ABSPATH')) {
    exit;
}

use Closure;
use InvalidArgumentException;
use LogicException;
use RuntimeException;
use Throwable;
use WPEssential\Contracts\DefinitionRepositoryInterface;
use WPEssential\Platform\Definitions\Definition;
use WPEssential\Platform\Definitions\DefinitionStatus;

final class FieldStorageKeyMigrationService
{
    /** @var Closure(string,string):list<int> */
    private Closure $findPostIds;

    /** @var Closure(int,string):bool */
    private Closure $metadataExists;

    /** @var Closure(int,string):bool */
    private Closure $deletePostMeta;

    /** @var Closure(mixed):mixed */
    private Closure $slash;

    private PostMetaValueStore $values;

    /**
     * @param null|callable(string,string):list<int> $findPostIds
     * @param null|callable(int,string):bool $metadataExists
     * @param null|callable(int,string):bool $deletePostMeta
     * @param null|callable(mixed):mixed $slash
     */
    public function __construct(
        private readonly DefinitionRepositoryInterface $definitions,
        private readonly FieldGroupDefinitionNormalizer $groups = new FieldGroupDefinitionNormalizer(),
        private readonly FieldGroupRuntimeStorageProjection $storage = new FieldGroupRuntimeStorageProjection(),
        private readonly FieldGroupPostTypeTargetCompiler $targets = new FieldGroupPostTypeTargetCompiler(),
        private readonly PostMetaRegistrationCompiler $compiler = new PostMetaRegistrationCompiler(),
        private readonly WordPressPostMetaRegistrar $registrar = new WordPressPostMetaRegistrar(),
        ?PostMetaValueStore $values = null,
        ?callable $findPostIds = null,
        ?callable $metadataExists = null,
        ?callable $deletePostMeta = null,
        ?callable $slash = null,
    ) {
        $this->values = $values ?? new PostMetaValueStore($this->compiler);
        $this->findPostIds = $findPostIds !== null
            ? Closure::fromCallable($findPostIds)
            : static function (string $postType, string $metaKey): array {
                if (!function_exists('get_posts')) {
                    throw new LogicException('WordPress get_posts() is unavailable.');
                }

                $postStatuses = 'any';
                if (function_exists('get_post_stati')) {
                    $registeredStatuses = array_values(get_post_stati([], 'names'));
                    if ($registeredStatuses !== []) {
                        $postStatuses = $registeredStatuses;
                    }
                }

                $ids = get_posts([
                    'post_type' => $postType,
                    'post_status' => $postStatuses,
                    'posts_per_page' => -1,
                    'nopaging' => true,
                    'fields' => 'ids',
                    'meta_key' => $metaKey,
                    'orderby' => 'ID',
                    'order' => 'ASC',
                    'suppress_filters' => false,
                    'no_found_rows' => true,
                    'update_post_meta_cache' => false,
                    'update_post_term_cache' => false,
                ]);
                if (!is_array($ids)) {
                    throw new RuntimeException('WordPress post-meta migration query returned an invalid result.');
                }

                $unique = [];
                foreach ($ids as $id) {
                    if (!is_int($id) || $id < 1) {
                        throw new RuntimeException('WordPress post-meta migration query returned an invalid post id.');
                    }
                    $unique[$id] = true;
                }

                $result = array_keys($unique);
                sort($result, SORT_NUMERIC);
                return $result;
            };
        $this->metadataExists = $metadataExists !== null
            ? Closure::fromCallable($metadataExists)
            : static function (int $postId, string $metaKey): bool {
                if (!function_exists('metadata_exists')) {
                    throw new LogicException('WordPress metadata_exists() is unavailable.');
                }
                return metadata_exists('post', $postId, $metaKey);
            };
        $this->deletePostMeta = $deletePostMeta !== null
            ? Closure::fromCallable($deletePostMeta)
            : static function (int $postId, string $metaKey): bool {
                if (!function_exists('delete_post_meta')) {
                    throw new LogicException('WordPress delete_post_meta() is unavailable.');
                }
                return delete_post_meta($postId, $metaKey);
            };
        $this->slash = $slash !== null
            ? Closure::fromCallable($slash)
            : static function (mixed $value): mixed {
                if (!function_exists('wp_slash')) {
                    throw new LogicException('WordPress wp_slash() is unavailable.');
                }
                return is_string($value) || is_array($value) ? wp_slash($value) : $value;
            };
    }

    public function migrate(
        string $groupId,
        int $expectedRevision,
        string $fieldUuid,
        string $destinationKey,
    ): FieldStorageKeyMigrationResult {
        $this->assertUuid($groupId, 'Field Group id');
        $this->assertUuid($fieldUuid, 'Field uuid');
        if ($expectedRevision < 1) {
            throw new InvalidArgumentException('Field storage-key migration requires a positive expected revision.');
        }
        if (preg_match('/^[a-z0-9][a-z0-9_-]{0,63}$/', $destinationKey) !== 1) {
            throw new InvalidArgumentException('Destination Field key must be a lowercase machine key up to 64 characters.');
        }

        $definition = $this->ownedPublishedGroup($groupId);
        if ($definition->revision !== $expectedRevision) {
            throw new RuntimeException(sprintf(
                'Field storage-key migration conflict: expected revision %d, current revision is %d.',
                $expectedRevision,
                $definition->revision,
            ));
        }

        $group = $this->groups->normalize($definition->payload, true);
        [$fieldIndex, $sourceField] = $this->directField($group, $fieldUuid);
        $sourceKey = $sourceField['key'] ?? null;
        if (!is_string($sourceKey) || $sourceKey === '') {
            throw new RuntimeException('Canonical source Field storage key is missing.');
        }

        if ($destinationKey === $sourceKey) {
            return new FieldStorageKeyMigrationResult(
                definition: $definition,
                fieldUuid: $fieldUuid,
                sourceKey: $sourceKey,
                destinationKey: $destinationKey,
                postTypes: [],
                migratedObjects: 0,
                changed: false,
            );
        }

        $candidatePayload = $definition->payload;
        $candidateFields = $candidatePayload['fields'] ?? null;
        if (!is_array($candidateFields) || !array_is_list($candidateFields) || !isset($candidateFields[$fieldIndex]) || !is_array($candidateFields[$fieldIndex])) {
            throw new RuntimeException('Persisted Field Group field ordering no longer matches its canonical normalized form.');
        }
        if (($candidateFields[$fieldIndex]['uuid'] ?? null) !== $fieldUuid) {
            throw new RuntimeException('Persisted Field Group field identity no longer matches its canonical normalized position.');
        }
        $candidateFields[$fieldIndex]['key'] = $destinationKey;
        $candidatePayload['fields'] = $candidateFields;
        $candidateGroup = $this->groups->normalize($candidatePayload, true);
        [, $destinationField] = $this->directField($candidateGroup, $fieldUuid);

        $groupRuntime = $this->storage->projectGroup($group);
        $candidateRuntime = $this->storage->projectGroup($candidateGroup);
        if ($groupRuntime !== $candidateRuntime) {
            throw new RuntimeException('Storage-key migration cannot change the Field Group runtime storage contract.');
        }

        $postTypes = $this->targets->compile($group);
        if ($this->targets->compile($candidateGroup) !== $postTypes) {
            throw new RuntimeException('Storage-key migration cannot change the Field Group target post types.');
        }

        $sourceRuntime = $this->storage->projectField($sourceField, $groupRuntime['show_in_rest']);
        $destinationRuntime = $this->storage->projectField($destinationField, $candidateRuntime['show_in_rest']);
        if ($sourceRuntime !== $destinationRuntime) {
            throw new RuntimeException('Storage-key migration cannot change the Field runtime storage contract.');
        }

        $sourceRegistrations = [];
        $destinationRegistrations = [];
        $destinationNeedsRegistration = [];
        foreach ($postTypes as $postType) {
            $sourceRegistration = $this->compiler->compile(
                $sourceField,
                $postType,
                showInRest: $sourceRuntime['show_in_rest'],
                revisionsEnabled: $groupRuntime['revisions_enabled'],
            );
            $destinationRegistration = $this->compiler->compile(
                $destinationField,
                $postType,
                showInRest: $destinationRuntime['show_in_rest'],
                revisionsEnabled: $candidateRuntime['revisions_enabled'],
            );

            $this->registrar->assertOwned($sourceRegistration);
            $destinationNeedsRegistration[$postType] = $this->registrar->preflight($destinationRegistration);
            $sourceRegistrations[$postType] = $sourceRegistration;
            $destinationRegistrations[$postType] = $destinationRegistration;
        }

        $snapshots = [];
        foreach ($postTypes as $postType) {
            $destinationIds = ($this->findPostIds)($postType, $destinationKey);
            if ($destinationIds !== []) {
                throw new RuntimeException(sprintf(
                    'Destination post-meta key "%s" already contains data on post type "%s"; refusing overwrite.',
                    $destinationKey,
                    $postType,
                ));
            }

            foreach (($this->findPostIds)($postType, $sourceKey) as $postId) {
                if (!($this->metadataExists)($postId, $sourceKey)) {
                    throw new RuntimeException(sprintf(
                        'Source post-meta key "%s" disappeared from post %d during migration preflight.',
                        $sourceKey,
                        $postId,
                    ));
                }
                $snapshots[] = [
                    'post_type' => $postType,
                    'post_id' => $postId,
                    'value' => $this->values->read($sourceField, $postType, $postId),
                ];
            }
        }

        $createdDestinationRegistrations = [];
        $sourceRetirementAttempted = [];
        $destinationTouched = [];
        $candidate = $this->candidateDefinition($definition, $candidateGroup);

        try {
            foreach ($postTypes as $postType) {
                if ($destinationNeedsRegistration[$postType] === true) {
                    $this->registrar->register($destinationRegistrations[$postType]);
                    $createdDestinationRegistrations[$postType] = true;
                }
            }

            foreach ($snapshots as $snapshot) {
                $postType = $snapshot['post_type'];
                $postId = $snapshot['post_id'];
                $value = $snapshot['value'];
                if (!is_string($postType) || !is_int($postId)) {
                    throw new RuntimeException('Field storage-key migration snapshot is malformed.');
                }

                $destinationTouched[$postType . ':' . $postId] = [$postType, $postId];
                $this->values->write($destinationField, $postType, $postId, $value);
                $persisted = $this->values->read($destinationField, $postType, $postId);
                if ($persisted !== $value) {
                    throw new RuntimeException(sprintf(
                        'Destination post-meta verification failed for "%s" on post %d.',
                        $destinationKey,
                        $postId,
                    ));
                }

                if (!($this->metadataExists)($postId, $sourceKey)) {
                    throw new RuntimeException(sprintf(
                        'Source post-meta key "%s" disappeared from post %d before retirement.',
                        $sourceKey,
                        $postId,
                    ));
                }
                $currentSource = $this->values->read($sourceField, $postType, $postId);
                if ($currentSource !== $value) {
                    throw new RuntimeException(sprintf(
                        'Source post-meta key "%s" changed on post %d during migration; refusing destructive retirement.',
                        $sourceKey,
                        $postId,
                    ));
                }

                $this->deleteAndVerify($postId, $sourceKey);
            }

            foreach ($postTypes as $postType) {
                $sourceRetirementAttempted[$postType] = true;
                $this->registrar->retire($sourceRegistrations[$postType]);
            }

            $this->definitions->save($candidate);
        } catch (Throwable $failure) {
            $this->rollback(
                original: $definition,
                candidate: $candidate,
                sourceField: $sourceField,
                destinationField: $destinationField,
                snapshots: $snapshots,
                sourceRegistrations: $sourceRegistrations,
                destinationRegistrations: $destinationRegistrations,
                createdDestinationRegistrations: $createdDestinationRegistrations,
                sourceRetirementAttempted: $sourceRetirementAttempted,
                destinationTouched: $destinationTouched,
                sourceKey: $sourceKey,
                destinationKey: $destinationKey,
                failure: $failure,
            );
        }

        return new FieldStorageKeyMigrationResult(
            definition: $candidate,
            fieldUuid: $fieldUuid,
            sourceKey: $sourceKey,
            destinationKey: $destinationKey,
            postTypes: $postTypes,
            migratedObjects: count($snapshots),
            changed: true,
        );
    }

    /**
     * @param array<string,mixed> $group
     * @return array{0:int,1:array<string,mixed>}
     */
    private function directField(array $group, string $fieldUuid): array
    {
        $fields = $group['fields'] ?? null;
        if (!is_array($fields) || !array_is_list($fields)) {
            throw new RuntimeException('Canonical Field Group fields must be a list.');
        }

        foreach ($fields as $index => $field) {
            if (!is_array($field) || array_is_list($field)) {
                throw new RuntimeException('Canonical Field Group contains a malformed Field.');
            }
            if (($field['uuid'] ?? null) === $fieldUuid) {
                return [$index, $field];
            }
        }

        if ($this->containsUuid($fields, $fieldUuid)) {
            throw new InvalidArgumentException('Nested Field storage-key migration is not certified in V1.');
        }

        throw new InvalidArgumentException('Field uuid is not owned by the requested Field Group.');
    }

    /** @param list<mixed> $fields */
    private function containsUuid(array $fields, string $fieldUuid): bool
    {
        foreach ($fields as $field) {
            if (!is_array($field) || array_is_list($field)) {
                continue;
            }
            if (($field['uuid'] ?? null) === $fieldUuid) {
                return true;
            }
            $subfields = $field['subfields'] ?? [];
            if (is_array($subfields) && array_is_list($subfields) && $this->containsUuid($subfields, $fieldUuid)) {
                return true;
            }
        }
        return false;
    }

    private function deleteAndVerify(int $postId, string $metaKey): void
    {
        if (!($this->metadataExists)($postId, $metaKey)) {
            return;
        }

        $nativeResult = ($this->deletePostMeta)($postId, (string) ($this->slash)($metaKey));
        if (($this->metadataExists)($postId, $metaKey)) {
            throw new RuntimeException(sprintf(
                'WordPress %s deleting post-meta key "%s" on post %d, but the source value still exists.',
                $nativeResult ? 'reported success' : 'reported failure',
                $metaKey,
                $postId,
            ));
        }
    }

    /**
     * @param list<array{post_type:string,post_id:int,value:mixed}> $snapshots
     * @param array<string,array<string,mixed>> $sourceRegistrations
     * @param array<string,array<string,mixed>> $destinationRegistrations
     * @param array<string,bool> $createdDestinationRegistrations
     * @param array<string,bool> $sourceRetirementAttempted
     * @param array<string,array{0:string,1:int}> $destinationTouched
     */
    private function rollback(
        Definition $original,
        Definition $candidate,
        array $sourceField,
        array $destinationField,
        array $snapshots,
        array $sourceRegistrations,
        array $destinationRegistrations,
        array $createdDestinationRegistrations,
        array $sourceRetirementAttempted,
        array $destinationTouched,
        string $sourceKey,
        string $destinationKey,
        Throwable $failure,
    ): never {
        $recoveryErrors = [];

        foreach (array_keys($sourceRetirementAttempted) as $postType) {
            try {
                $this->registrar->register($sourceRegistrations[$postType]);
            } catch (Throwable $error) {
                $recoveryErrors[] = $error;
            }
        }

        foreach (array_reverse($snapshots) as $snapshot) {
            try {
                $this->values->write(
                    $sourceField,
                    $snapshot['post_type'],
                    $snapshot['post_id'],
                    $snapshot['value'],
                );
                if ($this->values->read($sourceField, $snapshot['post_type'], $snapshot['post_id']) !== $snapshot['value']) {
                    throw new RuntimeException('Source post-meta recovery verification did not match the migration snapshot.');
                }
            } catch (Throwable $error) {
                $recoveryErrors[] = $error;
            }
        }

        foreach (array_reverse(array_values($destinationTouched)) as [, $postId]) {
            try {
                $this->deleteAndVerify($postId, $destinationKey);
            } catch (Throwable $error) {
                $recoveryErrors[] = $error;
            }
        }

        foreach (array_keys($createdDestinationRegistrations) as $postType) {
            try {
                if (!$this->registrar->preflight($destinationRegistrations[$postType])) {
                    $this->registrar->retire($destinationRegistrations[$postType]);
                }
            } catch (Throwable $error) {
                $recoveryErrors[] = $error;
            }
        }

        try {
            $current = $this->definitions->get($original->id);
            if (!$current instanceof Definition) {
                throw new RuntimeException('Field Group definition disappeared during storage-key recovery.');
            }
            if ($current->revision === $candidate->revision && $current->computedChecksum() === $candidate->computedChecksum()) {
                $this->definitions->save($this->restoredDefinition($current, $original));
            } elseif ($current->revision !== $original->revision || $current->computedChecksum() !== $original->computedChecksum()) {
                throw new RuntimeException('Field Group definition drifted during storage-key recovery; refusing to overwrite concurrent state.');
            }
        } catch (Throwable $error) {
            $recoveryErrors[] = $error;
        }

        if ($recoveryErrors !== []) {
            throw new PostMetaRecoveryException(sprintf(
                'Field storage-key migration from "%s" to "%s" failed and recovery could not be fully verified; state is uncertain.',
                $sourceKey,
                $destinationKey,
            ), 0, $recoveryErrors[0]);
        }

        throw new RuntimeException(sprintf(
            'Field storage-key migration from "%s" to "%s" failed; original registration, values, and definition were verified as restored.',
            $sourceKey,
            $destinationKey,
        ), 0, $failure);
    }

    /** @param array<string,mixed> $payload */
    private function candidateDefinition(Definition $definition, array $payload): Definition
    {
        $candidate = new Definition(
            id: $definition->id,
            slug: $definition->slug,
            type: $definition->type,
            schemaVersion: $definition->schemaVersion,
            ownerSurfaceId: $definition->ownerSurfaceId,
            status: $definition->status,
            payload: $payload,
            revision: $definition->revision + 1,
            dependencies: $definition->dependencies,
        );

        return new Definition(
            id: $candidate->id,
            slug: $candidate->slug,
            type: $candidate->type,
            schemaVersion: $candidate->schemaVersion,
            ownerSurfaceId: $candidate->ownerSurfaceId,
            status: $candidate->status,
            payload: $candidate->payload,
            revision: $candidate->revision,
            dependencies: $candidate->dependencies,
            checksum: $candidate->computedChecksum(),
        );
    }

    private function restoredDefinition(Definition $current, Definition $original): Definition
    {
        $restored = new Definition(
            id: $original->id,
            slug: $original->slug,
            type: $original->type,
            schemaVersion: $original->schemaVersion,
            ownerSurfaceId: $original->ownerSurfaceId,
            status: $original->status,
            payload: $original->payload,
            revision: $current->revision + 1,
            dependencies: $original->dependencies,
        );

        return new Definition(
            id: $restored->id,
            slug: $restored->slug,
            type: $restored->type,
            schemaVersion: $restored->schemaVersion,
            ownerSurfaceId: $restored->ownerSurfaceId,
            status: $restored->status,
            payload: $restored->payload,
            revision: $restored->revision,
            dependencies: $restored->dependencies,
            checksum: $restored->computedChecksum(),
        );
    }

    private function ownedPublishedGroup(string $id): Definition
    {
        $definition = $this->definitions->get($id);
        if (!$definition instanceof Definition
            || $definition->type !== FieldGroupDefinitionNormalizer::DEFINITION_TYPE
            || $definition->ownerSurfaceId !== FieldGroupDefinitionNormalizer::OWNER_SURFACE_ID
        ) {
            throw new RuntimeException('Field Group definition was not found in canonical Surface 3.');
        }
        if ($definition->status !== DefinitionStatus::Published) {
            throw new InvalidArgumentException('Storage-key migration V1 requires a Published Field Group definition.');
        }
        return $definition;
    }

    private function assertUuid(string $value, string $label): void
    {
        if (preg_match('/^[0-9a-f]{8}-[0-9a-f]{4}-[1-5][0-9a-f]{3}-[89ab][0-9a-f]{3}-[0-9a-f]{12}$/', $value) !== 1) {
            throw new InvalidArgumentException(sprintf('%s must be a lowercase RFC 4122 UUID.', $label));
        }
    }
}
