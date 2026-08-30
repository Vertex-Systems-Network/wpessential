<?php

declare(strict_types=1);

namespace WPEssential\Modules\CustomPostTypes;

if (!defined('ABSPATH')) {
    exit;
}

use InvalidArgumentException;
use RuntimeException;
use WPEssential\Contracts\AbilityHandlerInterface;
use WPEssential\Contracts\DefinitionRepositoryInterface;
use WPEssential\Platform\Auth\ExecutionContext;
use WPEssential\Platform\Definitions\Definition;
use WPEssential\Platform\Definitions\DefinitionStatus;

final readonly class CustomPostTypeAbilityHandler implements AbilityHandlerInterface
{
    public const LIST = 'list';
    public const GET = 'get';
    public const SAVE = 'save';
    public const STATUS = 'status';

    public function __construct(
        private DefinitionRepositoryInterface $definitions,
        private CustomPostTypeDefinitionProjector $projector,
        private string $action,
    ) {
        if (!in_array($this->action, [self::LIST, self::GET, self::SAVE, self::STATUS], true)) {
            throw new InvalidArgumentException('Unsupported CPT ability action.');
        }
    }

    public function handle(array $input, ExecutionContext $context): mixed
    {
        return match ($this->action) {
            self::LIST => $this->list(),
            self::GET => $this->get($input),
            self::SAVE => $this->save($input),
            self::STATUS => $this->changeStatus($input),
            default => throw new RuntimeException('Unsupported CPT ability action.'),
        };
    }

    /** @return array{definitions:list<array<string,mixed>>} */
    private function list(): array
    {
        $definitions = array_values(array_filter(
            $this->definitions->byType(CustomPostTypeDefinitionProjector::DEFINITION_TYPE),
            static fn (Definition $definition): bool => $definition->ownerSurfaceId === CustomPostTypeDefinitionProjector::OWNER_SURFACE_ID,
        ));
        usort($definitions, static fn (Definition $left, Definition $right): int => [$left->slug, $left->id] <=> [$right->slug, $right->id]);

        return ['definitions' => array_map($this->serialize(...), $definitions)];
    }

    /** @param array<string,mixed> $input @return array{definition:array<string,mixed>} */
    private function get(array $input): array
    {
        return ['definition' => $this->serialize($this->owned($this->requiredUuid($input, 'id')))];
    }

    /** @param array<string,mixed> $input @return array{definition:array<string,mixed>} */
    private function save(array $input): array
    {
        $payload = $input['payload'] ?? null;
        if (!is_array($payload) || array_is_list($payload)) {
            throw new InvalidArgumentException('CPT payload must be an object/map.');
        }

        $id = $input['id'] ?? null;
        $existing = null;
        if ($id !== null) {
            if (!is_string($id) || !$this->isUuid($id)) {
                throw new InvalidArgumentException('CPT id must be a lowercase RFC 4122 UUID.');
            }
            $existing = $this->owned($id);
            $this->assertExpectedRevision($input, $existing);
        }

        $key = $payload['post_type_key'] ?? null;
        if (!is_string($key) || trim($key) === '') {
            throw new InvalidArgumentException('CPT payload requires post_type_key.');
        }
        $key = trim($key);
        if ($existing instanceof Definition) {
            $existingKey = $existing->payload['post_type_key'] ?? null;
            if (!is_string($existingKey) || trim($existingKey) !== $key) {
                throw new InvalidArgumentException('Existing CPT post_type_key is immutable; create a new definition for a different runtime key.');
            }
        }
        $this->assertPostTypeKeyAvailable($key, $existing?->id);

        $status = $this->statusFromInput($input, $existing?->status ?? DefinitionStatus::Draft);
        $candidate = new Definition(
            id: $existing?->id ?? $this->uuid(),
            slug: $existing?->slug ?? ('cpt-' . str_replace('_', '-', $key)),
            type: CustomPostTypeDefinitionProjector::DEFINITION_TYPE,
            schemaVersion: $existing?->schemaVersion ?? 1,
            ownerSurfaceId: CustomPostTypeDefinitionProjector::OWNER_SURFACE_ID,
            status: $status,
            payload: $payload,
            revision: ($existing?->revision ?? 0) + 1,
            dependencies: $existing?->dependencies ?? [],
        );
        $this->validatePayload($candidate);
        $candidate = $this->withChecksum($candidate);
        $this->definitions->save($candidate);

        return ['definition' => $this->serialize($candidate)];
    }

    /** @param array<string,mixed> $input @return array{definition:array<string,mixed>} */
    private function changeStatus(array $input): array
    {
        $existing = $this->owned($this->requiredUuid($input, 'id'));
        $this->assertExpectedRevision($input, $existing);
        $status = $this->statusFromInput($input, $existing->status, required: true);

        $candidate = new Definition(
            id: $existing->id,
            slug: $existing->slug,
            type: $existing->type,
            schemaVersion: $existing->schemaVersion,
            ownerSurfaceId: $existing->ownerSurfaceId,
            status: $status,
            payload: $existing->payload,
            revision: $existing->revision + 1,
            dependencies: $existing->dependencies,
        );
        $this->validatePayload($candidate);
        $candidate = $this->withChecksum($candidate);
        $this->definitions->save($candidate);

        return ['definition' => $this->serialize($candidate)];
    }

    /** @param array<string,mixed> $input */
    private function assertExpectedRevision(array $input, Definition $existing): void
    {
        $expected = $input['expected_revision'] ?? null;
        if (!is_int($expected) || $expected < 1) {
            throw new InvalidArgumentException('Updating a CPT requires a positive expected_revision.');
        }
        if ($expected !== $existing->revision) {
            throw new RuntimeException(sprintf(
                'CPT write conflict: expected revision %d, current revision is %d.',
                $expected,
                $existing->revision,
            ));
        }
    }

    private function assertPostTypeKeyAvailable(string $key, ?string $currentDefinitionId): void
    {
        foreach ($this->definitions->byType(CustomPostTypeDefinitionProjector::DEFINITION_TYPE) as $definition) {
            if ($definition->ownerSurfaceId !== CustomPostTypeDefinitionProjector::OWNER_SURFACE_ID
                || $definition->id === $currentDefinitionId
            ) {
                continue;
            }

            $existingKey = $definition->payload['post_type_key'] ?? null;
            if (is_string($existingKey) && trim($existingKey) === $key) {
                throw new InvalidArgumentException(sprintf(
                    'Post type key "%s" is already owned by another canonical CPT definition.',
                    $key,
                ));
            }
        }
    }

    /** @param array<string,mixed> $input */
    private function statusFromInput(
        array $input,
        DefinitionStatus $default,
        bool $required = false,
    ): DefinitionStatus {
        $value = $input['status'] ?? null;
        if ($value === null && !$required) {
            return $default;
        }
        if (!is_string($value)) {
            throw new InvalidArgumentException('CPT status must be a string.');
        }
        $status = DefinitionStatus::tryFrom($value);
        if (!$status instanceof DefinitionStatus) {
            throw new InvalidArgumentException('CPT status must be draft, published, disabled, or archived.');
        }
        return $status;
    }

    private function owned(string $id): Definition
    {
        $definition = $this->definitions->get($id);
        if (!$definition instanceof Definition
            || $definition->type !== CustomPostTypeDefinitionProjector::DEFINITION_TYPE
            || $definition->ownerSurfaceId !== CustomPostTypeDefinitionProjector::OWNER_SURFACE_ID
        ) {
            throw new RuntimeException('CPT definition was not found in the canonical Surface 1 owner.');
        }
        return $definition;
    }

    private function validatePayload(Definition $definition): void
    {
        $this->projector->project(new Definition(
            id: $definition->id,
            slug: $definition->slug,
            type: $definition->type,
            schemaVersion: $definition->schemaVersion,
            ownerSurfaceId: $definition->ownerSurfaceId,
            status: DefinitionStatus::Published,
            payload: $definition->payload,
            revision: $definition->revision,
            dependencies: $definition->dependencies,
        ));
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

    /** @param array<string,mixed> $input */
    private function requiredUuid(array $input, string $field): string
    {
        $value = $input[$field] ?? null;
        if (!is_string($value) || !$this->isUuid($value)) {
            throw new InvalidArgumentException(sprintf('%s must be a lowercase RFC 4122 UUID.', $field));
        }
        return $value;
    }

    private function isUuid(string $value): bool
    {
        return preg_match('/^[0-9a-f]{8}-[0-9a-f]{4}-[1-5][0-9a-f]{3}-[89ab][0-9a-f]{3}-[0-9a-f]{12}$/', $value) === 1;
    }

    private function uuid(): string
    {
        if (function_exists('wp_generate_uuid4')) {
            $uuid = strtolower((string) wp_generate_uuid4());
            if ($this->isUuid($uuid)) {
                return $uuid;
            }
        }

        $bytes = random_bytes(16);
        $bytes[6] = chr((ord($bytes[6]) & 0x0f) | 0x40);
        $bytes[8] = chr((ord($bytes[8]) & 0x3f) | 0x80);
        $hex = bin2hex($bytes);

        return sprintf('%s-%s-%s-%s-%s',
            substr($hex, 0, 8),
            substr($hex, 8, 4),
            substr($hex, 12, 4),
            substr($hex, 16, 4),
            substr($hex, 20, 12),
        );
    }
}
