<?php

declare(strict_types=1);

namespace WPEssential\Modules\AdminColumns;

if (!defined('ABSPATH')) {
    exit;
}

use Closure;
use InvalidArgumentException;
use RuntimeException;
use WPEssential\Contracts\DefinitionRepositoryInterface;
use WPEssential\Platform\Definitions\Definition;
use WPEssential\Platform\Definitions\DefinitionStatus;

final readonly class AdminColumnsViewDefinitionService
{
    public function __construct(
        private DefinitionRepositoryInterface $definitions,
        private AdminColumnsViewDefinitionNormalizer $normalizer,
        private ?Closure $uuidFactory = null,
    ) {}

    /** @return list<Definition> */
    public function all(): array
    {
        $definitions = array_values(array_filter(
            $this->definitions->byType(AdminColumnsViewDefinitionNormalizer::DEFINITION_TYPE),
            static fn (Definition $definition): bool => $definition->ownerSurfaceId === AdminColumnsViewDefinitionNormalizer::OWNER_SURFACE_ID,
        ));
        usort(
            $definitions,
            static fn (Definition $left, Definition $right): int => [$left->slug, $left->id] <=> [$right->slug, $right->id],
        );
        return $definitions;
    }

    public function get(string $id): Definition
    {
        return $this->owned($id);
    }

    /**
     * @param array<string,mixed> $payload
     */
    public function save(
        array $payload,
        DefinitionStatus $status = DefinitionStatus::Draft,
        ?string $id = null,
        ?int $expectedRevision = null,
    ): Definition {
        $normalized = $this->normalizer->normalize($payload);
        $viewKey = $normalized['view_key'] ?? null;
        if (!is_string($viewKey)) {
            throw new RuntimeException('Normalized Admin Columns View key is missing.');
        }

        $existing = null;
        if ($id !== null) {
            $existing = $this->owned($id);
            $this->assertExpectedRevision($existing, $expectedRevision);
            $existingKey = $existing->payload['view_key'] ?? null;
            if (!is_string($existingKey) || $existingKey !== $viewKey) {
                throw new InvalidArgumentException('Admin Columns View key is immutable after creation.');
            }
        } elseif ($expectedRevision !== null) {
            throw new InvalidArgumentException('expectedRevision is only valid when updating an existing View.');
        }

        $this->assertKeyAvailable($viewKey, $existing?->id);

        $candidate = new Definition(
            id: $existing?->id ?? $this->uuid(),
            slug: $existing?->slug ?? ('admin-columns-' . str_replace('_', '-', $viewKey)),
            type: AdminColumnsViewDefinitionNormalizer::DEFINITION_TYPE,
            schemaVersion: $existing?->schemaVersion ?? 1,
            ownerSurfaceId: AdminColumnsViewDefinitionNormalizer::OWNER_SURFACE_ID,
            status: $status,
            payload: $normalized,
            revision: ($existing?->revision ?? 0) + 1,
            dependencies: $existing?->dependencies ?? [],
        );
        $candidate = $this->withChecksum($candidate);
        $this->definitions->save($candidate);
        return $candidate;
    }

    public function changeStatus(string $id, DefinitionStatus $status, int $expectedRevision): Definition
    {
        $existing = $this->owned($id);
        $this->assertExpectedRevision($existing, $expectedRevision);
        $normalized = $this->normalizer->normalize($existing->payload);

        $candidate = new Definition(
            id: $existing->id,
            slug: $existing->slug,
            type: $existing->type,
            schemaVersion: $existing->schemaVersion,
            ownerSurfaceId: $existing->ownerSurfaceId,
            status: $status,
            payload: $normalized,
            revision: $existing->revision + 1,
            dependencies: $existing->dependencies,
        );
        $candidate = $this->withChecksum($candidate);
        $this->definitions->save($candidate);
        return $candidate;
    }

    private function owned(string $id): Definition
    {
        if (!$this->isUuid($id)) {
            throw new InvalidArgumentException('Admin Columns View id must be a lowercase RFC 4122 UUID.');
        }
        $definition = $this->definitions->get($id);
        if (!$definition instanceof Definition
            || $definition->type !== AdminColumnsViewDefinitionNormalizer::DEFINITION_TYPE
            || $definition->ownerSurfaceId !== AdminColumnsViewDefinitionNormalizer::OWNER_SURFACE_ID
        ) {
            throw new RuntimeException('Admin Columns View definition is not available.');
        }
        return $definition;
    }

    private function assertExpectedRevision(Definition $existing, ?int $expectedRevision): void
    {
        if ($expectedRevision === null || $expectedRevision < 1) {
            throw new InvalidArgumentException('Updating an Admin Columns View requires a positive expected revision.');
        }
        if ($expectedRevision !== $existing->revision) {
            throw new RuntimeException(sprintf(
                'Admin Columns View write conflict: expected revision %d, current revision is %d.',
                $expectedRevision,
                $existing->revision,
            ));
        }
    }

    private function assertKeyAvailable(string $viewKey, ?string $currentId): void
    {
        foreach ($this->definitions->byType(AdminColumnsViewDefinitionNormalizer::DEFINITION_TYPE) as $definition) {
            if ($definition->ownerSurfaceId !== AdminColumnsViewDefinitionNormalizer::OWNER_SURFACE_ID
                || $definition->id === $currentId
            ) {
                continue;
            }
            if (($definition->payload['view_key'] ?? null) === $viewKey) {
                throw new RuntimeException(sprintf(
                    'Admin Columns View key "%s" is already owned by another definition.',
                    $viewKey,
                ));
            }
        }
    }

    private function withChecksum(Definition $definition): Definition
    {
        return new Definition(
            id: $definition->id,
            slug: $definition->slug,
            type: $definition->type,
            schemaVersion: $definition->schemaVersion,
            ownerSurfaceId: $definition->ownerSurfaceId,
            status: $definition->status,
            payload: $definition->payload,
            revision: $definition->revision,
            dependencies: $definition->dependencies,
            checksum: $definition->computedChecksum(),
        );
    }

    private function uuid(): string
    {
        if ($this->uuidFactory instanceof Closure) {
            $uuid = ($this->uuidFactory)();
            if (!is_string($uuid) || !$this->isUuid($uuid)) {
                throw new RuntimeException('Admin Columns UUID factory returned an invalid UUID.');
            }
            return $uuid;
        }

        $bytes = random_bytes(16);
        $bytes[6] = chr((ord($bytes[6]) & 0x0f) | 0x40);
        $bytes[8] = chr((ord($bytes[8]) & 0x3f) | 0x80);
        $hex = bin2hex($bytes);
        return sprintf(
            '%s-%s-%s-%s-%s',
            substr($hex, 0, 8),
            substr($hex, 8, 4),
            substr($hex, 12, 4),
            substr($hex, 16, 4),
            substr($hex, 20, 12),
        );
    }

    private function isUuid(string $value): bool
    {
        return preg_match('/^[0-9a-f]{8}-[0-9a-f]{4}-[1-5][0-9a-f]{3}-[89ab][0-9a-f]{3}-[0-9a-f]{12}$/', $value) === 1;
    }
}
