<?php

declare(strict_types=1);

namespace WPEssential\Modules\Fields;

if (!defined('ABSPATH')) {
    exit;
}

use InvalidArgumentException;
use WPEssential\Contracts\AbilityHandlerInterface;
use WPEssential\Platform\Auth\ExecutionContext;
use WPEssential\Platform\Definitions\Definition;

final readonly class FieldStorageKeyMigrationAbilityHandler implements AbilityHandlerInterface
{
    public function __construct(private FieldStorageKeyMigrationService $migrations) {}

    public function handle(array $input, ExecutionContext $context): mixed
    {
        unset($context);

        $groupId = $input['group_id'] ?? null;
        $fieldUuid = $input['field_uuid'] ?? null;
        $expectedRevision = $input['expected_group_revision'] ?? null;
        $destinationKey = $input['destination_key'] ?? null;

        if (!is_string($groupId) || !is_string($fieldUuid)) {
            throw new InvalidArgumentException('Field storage-key migration requires group_id and field_uuid strings.');
        }
        if (!is_int($expectedRevision) || $expectedRevision < 1) {
            throw new InvalidArgumentException('Field storage-key migration requires a positive expected_group_revision.');
        }
        if (!is_string($destinationKey)) {
            throw new InvalidArgumentException('Field storage-key migration requires a destination_key string.');
        }

        $result = $this->migrations->migrate($groupId, $expectedRevision, $fieldUuid, $destinationKey);

        return [
            'group_id' => $result->definition->id,
            'group_revision' => $result->definition->revision,
            'field_uuid' => $result->fieldUuid,
            'source_key' => $result->sourceKey,
            'destination_key' => $result->destinationKey,
            'post_types' => $result->postTypes,
            'migrated_objects' => $result->migratedObjects,
            'changed' => $result->changed,
            'definition' => $this->serialize($result->definition),
        ];
    }

    /** @return array<string,mixed> */
    private function serialize(Definition $definition): array
    {
        return [
            'id' => $definition->id,
            'slug' => $definition->slug,
            'type' => $definition->type,
            'schema_version' => $definition->schemaVersion,
            'owner_surface_id' => $definition->ownerSurfaceId,
            'status' => $definition->status->value,
            'payload' => $definition->payload,
            'revision' => $definition->revision,
            'dependencies' => $definition->dependencies,
            'checksum' => $definition->checksum,
        ];
    }
}
