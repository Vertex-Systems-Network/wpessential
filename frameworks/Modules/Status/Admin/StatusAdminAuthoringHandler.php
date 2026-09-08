<?php

declare(strict_types=1);

namespace WPEssential\Modules\Status\Admin;

if (!defined('ABSPATH')) {
    exit;
}

use InvalidArgumentException;
use RuntimeException;
use WPEssential\Contracts\AbilityHandlerInterface;
use WPEssential\Contracts\DefinitionRepositoryInterface;
use WPEssential\Modules\Status\Definition\StatusDefinitionCompiler;
use WPEssential\Modules\Status\Transition\Definition\StatusTransitionPolicyDefinitionCompiler;
use WPEssential\Platform\Auth\ExecutionContext;
use WPEssential\Platform\Definitions\Definition;
use WPEssential\Platform\Definitions\DefinitionStatus;

final readonly class StatusAdminAuthoringHandler implements AbilityHandlerInterface
{
    public const LIST = 'list';
    public const GET = 'get';
    public const SAVE = 'save';
    public const STATUS = 'status';

    private const OWNER_SURFACE_ID = 5;
    private const STATUS_TYPE = 'status';
    private const POLICY_TYPE = 'status-transition-policy';

    public function __construct(
        private DefinitionRepositoryInterface $definitions,
        private StatusDefinitionCompiler $statusCompiler,
        private StatusTransitionPolicyDefinitionCompiler $policyCompiler,
        private string $action,
    ) {
        if (!in_array($this->action, [self::LIST, self::GET, self::SAVE, self::STATUS], true)) {
            throw new InvalidArgumentException('Unsupported Status admin authoring action.');
        }
    }

    public function handle(array $input, ExecutionContext $context): mixed
    {
        return match ($this->action) {
            self::LIST => $this->list($input),
            self::GET => $this->get($input),
            self::SAVE => $this->save($input),
            self::STATUS => $this->changeStatus($input),
            default => throw new RuntimeException('Unsupported Status admin authoring action.'),
        };
    }

    /** @param array<string,mixed> $input @return array{definitions:list<array<string,mixed>>} */
    private function list(array $input): array
    {
        $type = $this->definitionType($input['definition_type'] ?? null, required: false);
        $definitions = [];
        foreach ($type === null ? [self::STATUS_TYPE, self::POLICY_TYPE] : [$type] as $ownedType) {
            foreach ($this->definitions->byType($ownedType) as $definition) {
                if ($definition->ownerSurfaceId === self::OWNER_SURFACE_ID) {
                    $definitions[] = $definition;
                }
            }
        }
        usort($definitions, static fn (Definition $a, Definition $b): int => [$a->type, $a->slug, $a->id] <=> [$b->type, $b->slug, $b->id]);

        return ['definitions' => array_map($this->serialize(...), $definitions)];
    }

    /** @param array<string,mixed> $input @return array{definition:array<string,mixed>} */
    private function get(array $input): array
    {
        return ['definition' => $this->serialize($this->owned($this->requiredUuid($input, 'id')))];
    }

    /** @param array<string,mixed> $input @return array{definition:array<string,mixed>,preview:array<string,mixed>} */
    private function save(array $input): array
    {
        $type = $this->definitionType($input['definition_type'] ?? null, required: true);
        $payload = $input['payload'] ?? null;
        if (!is_array($payload) || array_is_list($payload)) {
            throw new InvalidArgumentException('Status admin payload must be an object/map.');
        }

        $id = $input['id'] ?? null;
        $existing = null;
        if ($id !== null) {
            if (!is_string($id) || !$this->isUuid($id)) {
                throw new InvalidArgumentException('Status admin id must be a lowercase RFC 4122 UUID.');
            }
            $existing = $this->owned($id);
            if ($existing->type !== $type) {
                throw new InvalidArgumentException('Status Definition type is immutable.');
            }
            $this->assertExpectedRevision($input, $existing);
        }

        $status = $this->statusFromInput($input, $existing?->status ?? DefinitionStatus::Draft);
        if ($existing instanceof Definition && $type === self::STATUS_TYPE) {
            $oldKey = $existing->payload['key'] ?? null;
            $newKey = $payload['key'] ?? null;
            if (!is_string($oldKey) || !is_string($newKey) || !hash_equals($oldKey, $newKey)) {
                throw new InvalidArgumentException('Published Status identity key is immutable; create a new Status instead of renaming it.');
            }
        }

        $candidate = new Definition(
            id: $existing?->id ?? $this->uuid(),
            slug: $existing?->slug ?? $this->newSlug($type, $payload, $input),
            type: $type,
            schemaVersion: 1,
            ownerSurfaceId: self::OWNER_SURFACE_ID,
            status: $status,
            payload: $payload,
            revision: ($existing?->revision ?? 0) + 1,
            dependencies: [],
        );
        $preview = $this->validateAndPreview($candidate);
        $candidate = $this->withChecksum($candidate);
        $this->definitions->save($candidate);

        return ['definition' => $this->serialize($candidate), 'preview' => $preview];
    }

    /** @param array<string,mixed> $input @return array{definition:array<string,mixed>,preview:array<string,mixed>} */
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
            dependencies: [],
        );
        $preview = $this->validateAndPreview($candidate);
        $candidate = $this->withChecksum($candidate);
        $this->definitions->save($candidate);

        return ['definition' => $this->serialize($candidate), 'preview' => $preview];
    }

    /** @return array<string,mixed> */
    private function validateAndPreview(Definition $candidate): array
    {
        $validation = $candidate->status === DefinitionStatus::Published ? $candidate : new Definition(
            id: $candidate->id,
            slug: $candidate->slug,
            type: $candidate->type,
            schemaVersion: $candidate->schemaVersion,
            ownerSurfaceId: $candidate->ownerSurfaceId,
            status: DefinitionStatus::Published,
            payload: $candidate->payload,
            revision: $candidate->revision,
            dependencies: [],
        );

        if ($candidate->type === self::STATUS_TYPE) {
            $compiled = $this->statusCompiler->compile($validation);
            return [
                'kind' => self::STATUS_TYPE,
                'key' => $compiled->key,
                'visibility' => [
                    'public' => $compiled->public,
                    'internal' => $compiled->internal,
                    'protected' => $compiled->protected,
                    'private' => $compiled->private,
                    'publicly_queryable' => $compiled->publiclyQueryable,
                    'exclude_from_search' => $compiled->excludeFromSearch,
                ],
                'admin' => [
                    'show_in_all_list' => $compiled->showInAdminAllList,
                    'show_in_status_list' => $compiled->showInAdminStatusList,
                ],
                'post_types' => $compiled->postTypes,
            ];
        }

        $policy = $this->policyCompiler->compile($validation);
        return [
            'kind' => self::POLICY_TYPE,
            'edges' => array_map(
                static fn ($rule): array => [
                    'from' => $rule->from,
                    'to' => $rule->to,
                    'capability' => $rule->capability,
                    'reason_required' => $rule->reasonRequired,
                    'bulk_allowed' => $rule->bulkAllowed,
                    'programmatic_allowed' => $rule->programmaticAllowed,
                ],
                $policy->rules(),
            ),
        ];
    }

    private function owned(string $id): Definition
    {
        $definition = $this->definitions->get($id);
        if (!$definition instanceof Definition
            || $definition->ownerSurfaceId !== self::OWNER_SURFACE_ID
            || !in_array($definition->type, [self::STATUS_TYPE, self::POLICY_TYPE], true)
        ) {
            throw new RuntimeException('Status Definition was not found in canonical Surface 5.');
        }
        return $definition;
    }

    /** @param array<string,mixed> $input */
    private function assertExpectedRevision(array $input, Definition $existing): void
    {
        $expected = $input['expected_revision'] ?? null;
        if (!is_int($expected) || $expected < 1) {
            throw new InvalidArgumentException('Updating a Status Definition requires a positive expected_revision.');
        }
        if ($expected !== $existing->revision) {
            throw new RuntimeException(sprintf('Status Definition write conflict: expected revision %d, current revision is %d.', $expected, $existing->revision));
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
            throw new InvalidArgumentException('Status Definition lifecycle status must be a string.');
        }
        $status = DefinitionStatus::tryFrom($value);
        if (!$status instanceof DefinitionStatus) {
            throw new InvalidArgumentException('Status Definition lifecycle status must be draft, published, disabled, or archived.');
        }
        return $status;
    }

    private function definitionType(mixed $value, bool $required): ?string
    {
        if ($value === null && !$required) {
            return null;
        }
        if (!is_string($value) || !in_array($value, [self::STATUS_TYPE, self::POLICY_TYPE], true)) {
            throw new InvalidArgumentException('definition_type must be status or status-transition-policy.');
        }
        return $value;
    }

    /** @param array<string,mixed> $payload @param array<string,mixed> $input */
    private function newSlug(string $type, array $payload, array $input): string
    {
        $source = $type === self::STATUS_TYPE ? ($payload['key'] ?? null) : ($input['slug'] ?? null);
        if (!is_string($source) || !preg_match('/^[a-z0-9][a-z0-9_-]{0,63}$/', $source)) {
            throw new InvalidArgumentException('New Status Definition requires a canonical key/slug.');
        }
        $slug = str_replace('_', '-', $source);
        return $type === self::STATUS_TYPE ? 'status-' . $slug : 'status-policy-' . $slug;
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
            dependencies: [],
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
        return sprintf('%s-%s-%s-%s-%s', substr($hex, 0, 8), substr($hex, 8, 4), substr($hex, 12, 4), substr($hex, 16, 4), substr($hex, 20));
    }
}
