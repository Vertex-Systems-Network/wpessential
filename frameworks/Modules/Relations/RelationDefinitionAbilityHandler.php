<?php

declare(strict_types=1);

namespace WPEssential\Modules\Relations;

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

final readonly class RelationDefinitionAbilityHandler implements AbilityHandlerInterface
{
    public const LIST = 'list';
    public const GET = 'get';
    public const VALIDATE = 'validate';
    public const SAVE = 'save';
    public const STATUS = 'status';

    private const ADVANCED_POLICY_KEYS = [
        'edge_ordering',
        'storage_mode',
        'storage_config',
        'pivot_enabled',
        'pivot_policy',
        'deletion_policy',
        'editor_policy',
        'permissions_policy',
        'rest_policy',
        'multisite_scope',
        'portability',
    ];

    public function __construct(
        private DefinitionRepositoryInterface $definitions,
        private RelationDefinitionNormalizer $normalizer,
        private RelationDefinitionValidationService $validation,
        private string $action,
    ) {
        if (!in_array($this->action, [self::LIST, self::GET, self::VALIDATE, self::SAVE, self::STATUS], true)) {
            throw new InvalidArgumentException('Unsupported Relation definition ability action.');
        }
    }

    public function handle(array $input, ExecutionContext $context): mixed
    {
        return match ($this->action) {
            self::LIST => $this->list(),
            self::GET => $this->get($input),
            self::VALIDATE => $this->validation->validate($input),
            self::SAVE => $this->save($input),
            self::STATUS => $this->changeStatus($input),
            default => throw new RuntimeException('Unsupported Relation definition ability action.'),
        };
    }

    /** @return array{definitions:list<array<string,mixed>>} */
    private function list(): array
    {
        $definitions = array_values(array_filter(
            $this->definitions->byType(RelationDefinitionNormalizer::DEFINITION_TYPE),
            static fn (Definition $definition): bool => $definition->ownerSurfaceId === RelationDefinitionNormalizer::OWNER_SURFACE_ID,
        ));
        usort(
            $definitions,
            static fn (Definition $left, Definition $right): int => [$left->slug, $left->id] <=> [$right->slug, $right->id],
        );

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
            throw new InvalidArgumentException('Relation payload must be an object/map.');
        }

        $id = $input['id'] ?? null;
        $existing = null;
        if ($id !== null) {
            if (!is_string($id) || !$this->isUuid($id)) {
                throw new InvalidArgumentException('Relation id must be a lowercase RFC 4122 UUID.');
            }
            $existing = $this->owned($id);
            $this->assertExpectedRevision($input, $existing);
            $payload = $this->preserveOmittedAdvancedPolicies($payload, $existing->payload);
        }

        $status = $this->statusFromInput($input, $existing?->status ?? DefinitionStatus::Draft);
        $this->assertValidationAllowsMutation($this->validation->validate([
            'payload' => $payload,
            'status' => $status->value,
        ]));
        $normalized = $this->normalizer->normalize($payload, $status === DefinitionStatus::Published);
        $relationKey = $normalized['relation_key'] ?? null;
        if (!is_string($relationKey)) {
            throw new RuntimeException('Normalized Relation key is missing.');
        }

        if ($existing instanceof Definition) {
            $existingKey = $existing->payload['relation_key'] ?? null;
            if (!is_string($existingKey) || $relationKey !== $existingKey) {
                throw new InvalidArgumentException('Relation key is immutable after creation.');
            }
        }
        $this->assertKeyAvailable($relationKey, $existing?->id);

        $candidate = new Definition(
            id: $existing?->id ?? $this->uuid(),
            slug: $existing?->slug ?? ('relation-' . str_replace('_', '-', $relationKey)),
            type: RelationDefinitionNormalizer::DEFINITION_TYPE,
            schemaVersion: $existing?->schemaVersion ?? 1,
            ownerSurfaceId: RelationDefinitionNormalizer::OWNER_SURFACE_ID,
            status: $status,
            payload: $normalized,
            revision: ($existing?->revision ?? 0) + 1,
            dependencies: $existing?->dependencies ?? [],
        );
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
        $this->assertValidationAllowsMutation($this->validation->validate([
            'payload' => $existing->payload,
            'status' => $status->value,
        ]));
        $normalized = $this->normalizer->normalize(
            $existing->payload,
            $status === DefinitionStatus::Published,
        );

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

        return ['definition' => $this->serialize($candidate)];
    }

    /**
     * Basic editors may submit only currently executable fields. Preserve explicitly authored
     * advanced policy when omitted, while allowing callers to replace or clear a policy by
     * sending the corresponding key explicitly.
     *
     * @param array<string,mixed> $payload
     * @param array<string,mixed> $existing
     * @return array<string,mixed>
     */
    private function preserveOmittedAdvancedPolicies(array $payload, array $existing): array
    {
        foreach (self::ADVANCED_POLICY_KEYS as $key) {
            if (!array_key_exists($key, $payload) && array_key_exists($key, $existing)) {
                $payload[$key] = $existing[$key];
            }
        }

        $existingDirection = $existing['direction'] ?? null;
        $payloadDirection = $payload['direction'] ?? null;
        if (is_array($existingDirection)
            && array_key_exists('parent_relation', $existingDirection)
            && is_array($payloadDirection)
            && !array_key_exists('parent_relation', $payloadDirection)
        ) {
            $payloadDirection['parent_relation'] = $existingDirection['parent_relation'];
            $payload['direction'] = $payloadDirection;
        }

        return $payload;
    }

    /** @param array<string,mixed> $input */
    private function assertExpectedRevision(array $input, Definition $existing): void
    {
        $expected = $input['expected_revision'] ?? null;
        if (!is_int($expected) || $expected < 1) {
            throw new InvalidArgumentException('Updating a Relation requires a positive expected_revision.');
        }
        if ($expected !== $existing->revision) {
            throw new RuntimeException(sprintf(
                'Relation write conflict: expected revision %d, current revision is %d.',
                $expected,
                $existing->revision,
            ));
        }
    }

    /**
     * @param array{
     *   valid:bool,
     *   issues:list<array{id:string,severity:string,field:string,message:string}>,
     *   candidate:array{relation_key:?string,cardinality:?string,from_type:?string,to_type:?string}
     * } $report
     */
    private function assertValidationAllowsMutation(array $report): void
    {
        if ($report['valid']) {
            return;
        }
        foreach ($report['issues'] as $issue) {
            if ($issue['severity'] === 'blocked') {
                throw new InvalidArgumentException($issue['message']);
            }
        }
        throw new InvalidArgumentException('Relation validation blocked the requested mutation.');
    }

    private function assertKeyAvailable(string $relationKey, ?string $currentId): void
    {
        foreach ($this->definitions->byType(RelationDefinitionNormalizer::DEFINITION_TYPE) as $definition) {
            if ($definition->ownerSurfaceId !== RelationDefinitionNormalizer::OWNER_SURFACE_ID
                || $definition->id === $currentId
            ) {
                continue;
            }
            if (($definition->payload['relation_key'] ?? null) === $relationKey) {
                throw new RuntimeException(sprintf('Relation key "%s" is already owned by another definition.', $relationKey));
            }
        }
    }

    /** @param array<string,mixed> $input */
    private function statusFromInput(array $input, DefinitionStatus $default, bool $required = false): DefinitionStatus
    {
        $value = $input['status'] ?? null;
        if ($value === null && !$required) {
            return $default;
        }
        if (!is_string($value)) {
            throw new InvalidArgumentException('Relation status must be a string.');
        }
        $status = DefinitionStatus::tryFrom($value);
        if (!$status instanceof DefinitionStatus) {
            throw new InvalidArgumentException('Relation status must be draft, published, disabled, or archived.');
        }
        return $status;
    }

    private function owned(string $id): Definition
    {
        $definition = $this->definitions->get($id);
        if (!$definition instanceof Definition
            || $definition->type !== RelationDefinitionNormalizer::DEFINITION_TYPE
            || $definition->ownerSurfaceId !== RelationDefinitionNormalizer::OWNER_SURFACE_ID
        ) {
            throw new RuntimeException('Relation definition was not found in canonical Surface 4.');
        }
        return $definition;
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

        return sprintf(
            '%s-%s-%s-%s-%s',
            substr($hex, 0, 8),
            substr($hex, 8, 4),
            substr($hex, 12, 4),
            substr($hex, 16, 4),
            substr($hex, 20, 12),
        );
    }
}
