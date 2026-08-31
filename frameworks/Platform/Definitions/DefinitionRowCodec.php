<?php

declare(strict_types=1);

namespace WPEssential\Platform\Definitions;


if (!defined('ABSPATH')) {
    exit;
}

use JsonException;
use RuntimeException;

final class DefinitionRowCodec
{
    /** @return array<string, scalar|null> */
    public function encode(Definition $definition): array
    {
        $checksum = $definition->checksum ?? $definition->computedChecksum();
        if (!hash_equals($checksum, $definition->computedChecksum())) {
            throw new RuntimeException('Definition checksum does not match canonical payload.');
        }

        return [
            'id' => $definition->id,
            'slug' => $definition->slug,
            'type' => $definition->type,
            'schema_version' => $definition->schemaVersion,
            'owner_surface_id' => $definition->ownerSurfaceId,
            'status' => $definition->status->value,
            'payload_json' => json_encode($definition->payload, JSON_THROW_ON_ERROR | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE),
            'revision' => $definition->revision,
            'checksum' => $checksum,
        ];
    }

    /**
     * @param array<string, scalar|null> $row
     * @param list<string> $dependencies
     * @throws JsonException
     */
    public function decode(array $row, array $dependencies = []): Definition
    {
        $payloadJson = $row['payload_json'] ?? null;
        if (!is_string($payloadJson)) {
            throw new RuntimeException('Persisted definition payload_json is missing.');
        }
        $payload = json_decode($payloadJson, true, flags: JSON_THROW_ON_ERROR);
        if (!is_array($payload)) {
            throw new RuntimeException('Persisted definition payload must decode to an object/array.');
        }

        $definition = new Definition(
            id: (string) ($row['id'] ?? ''),
            slug: (string) ($row['slug'] ?? ''),
            type: (string) ($row['type'] ?? ''),
            schemaVersion: (int) ($row['schema_version'] ?? 0),
            ownerSurfaceId: (int) ($row['owner_surface_id'] ?? 0),
            status: DefinitionStatus::from((string) ($row['status'] ?? '')),
            payload: $payload,
            revision: (int) ($row['revision'] ?? 0),
            dependencies: $dependencies,
            checksum: (string) ($row['checksum'] ?? ''),
        );

        if (!hash_equals($definition->checksum ?? '', $definition->computedChecksum())) {
            throw new RuntimeException('Persisted definition checksum verification failed.');
        }

        return $definition;
    }
}
