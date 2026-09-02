<?php

declare(strict_types=1);

namespace WPEssential\Modules\Relations;

if (!defined('ABSPATH')) {
    exit;
}

use InvalidArgumentException;

final readonly class RelationDefinitionNormalizer
{
    public const DEFINITION_TYPE = 'relation';
    public const OWNER_SURFACE_ID = 4;

    private const TOP_LEVEL_KEYS = [
        'relation_key',
        'title',
        'description',
        'cardinality',
        'direction',
        'from',
        'to',
        'bounds',
        'unique_edge',
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

    private const CARDINALITIES = [
        'one_to_one',
        'one_to_many',
        'many_to_one',
        'many_to_many',
    ];

    private const ENDPOINT_TYPES = [
        'post',
        'term',
        'user',
        'comment',
        'media',
        'custom_table',
        'registered_entity',
    ];

    private const STORAGE_MODES = [
        'shared_relation_table',
        'dedicated_relation_table',
        'native_taxonomy_adapter',
        'native_post_parent_adapter',
        'provider',
    ];

    private const DELETION_POLICIES = ['detach', 'restrict', 'cascade_provider'];
    private const MULTISITE_SCOPES = ['site', 'network'];

    /**
     * @param array<string,mixed> $payload
     * @return array<string,mixed>
     */
    public function normalize(array $payload, bool $publishing = false): array
    {
        $this->assertKnownKeys($payload);

        $relationKey = $this->machineKey($payload['relation_key'] ?? null, 'Relation key');
        $title = $payload['title'] ?? null;
        if (!is_string($title) || trim($title) === '') {
            throw new InvalidArgumentException('Relation title is required.');
        }

        $description = $payload['description'] ?? '';
        if (!is_string($description)) {
            throw new InvalidArgumentException('Relation description must be a string.');
        }

        $cardinality = $payload['cardinality'] ?? 'many_to_many';
        if (!is_string($cardinality) || !in_array($cardinality, self::CARDINALITIES, true)) {
            throw new InvalidArgumentException('Relation cardinality is not supported.');
        }

        $normalized = [
            'relation_key' => $relationKey,
            'title' => trim($title),
            'description' => trim($description),
            'cardinality' => $cardinality,
            'direction' => $this->direction($payload['direction'] ?? []),
            'from' => $this->endpoint($payload['from'] ?? null, 'from'),
            'to' => $this->endpoint($payload['to'] ?? null, 'to'),
            'bounds' => $this->bounds($payload['bounds'] ?? [], $cardinality),
            'unique_edge' => $this->boolean($payload['unique_edge'] ?? true, 'Relation unique_edge'),
        ];

        $this->normalizeOptionalPolicies($payload, $normalized);

        if ($publishing && $normalized['from'] === $normalized['to']) {
            // Self-relations are valid, but identical endpoints are intentionally allowed.
            // The explicit branch documents that publishing does not infer a prohibition.
        }

        return $normalized;
    }

    /**
     * Advanced Atomic Option policy keys are additive. Missing keys remain absent so
     * existing V1 payload checksums stay stable until an explicit save/import adds them.
     *
     * @param array<string,mixed> $payload
     * @param array<string,mixed> $normalized
     */
    private function normalizeOptionalPolicies(array $payload, array &$normalized): void
    {
        if (array_key_exists('edge_ordering', $payload)) {
            $normalized['edge_ordering'] = $this->edgeOrdering($payload['edge_ordering']);
        }
        if (array_key_exists('storage_mode', $payload)) {
            $normalized['storage_mode'] = $this->enum(
                $payload['storage_mode'],
                self::STORAGE_MODES,
                'Relation storage_mode',
            );
        }
        if (array_key_exists('storage_config', $payload)) {
            $normalized['storage_config'] = $this->storageConfig($payload['storage_config']);
        }
        if (array_key_exists('pivot_enabled', $payload)) {
            $normalized['pivot_enabled'] = $this->boolean($payload['pivot_enabled'], 'Relation pivot_enabled');
        }
        if (array_key_exists('pivot_policy', $payload)) {
            $normalized['pivot_policy'] = $this->pivotPolicy($payload['pivot_policy']);
        }
        if (array_key_exists('deletion_policy', $payload)) {
            $normalized['deletion_policy'] = $this->deletionPolicy($payload['deletion_policy']);
        }
        if (array_key_exists('editor_policy', $payload)) {
            $normalized['editor_policy'] = $this->editorPolicy($payload['editor_policy']);
        }
        if (array_key_exists('permissions_policy', $payload)) {
            $normalized['permissions_policy'] = $this->permissionsPolicy($payload['permissions_policy']);
        }
        if (array_key_exists('rest_policy', $payload)) {
            $normalized['rest_policy'] = $this->restPolicy($payload['rest_policy']);
        }
        if (array_key_exists('multisite_scope', $payload)) {
            $normalized['multisite_scope'] = $this->enum(
                $payload['multisite_scope'],
                self::MULTISITE_SCOPES,
                'Relation multisite_scope',
            );
        }
        if (array_key_exists('portability', $payload)) {
            $normalized['portability'] = $this->portability($payload['portability']);
        }
    }

    /** @param array<string,mixed> $payload */
    private function assertKnownKeys(array $payload): void
    {
        foreach (array_keys($payload) as $key) {
            if (!in_array($key, self::TOP_LEVEL_KEYS, true)) {
                throw new InvalidArgumentException(sprintf('Unsupported Relation option "%s".', (string) $key));
            }
        }
    }

    /** @return array<string,mixed> */
    private function direction(mixed $value): array
    {
        $value = $this->objectMap($value, 'Relation direction');
        $this->assertObjectKeys($value, ['reciprocal', 'bidirectional_traversal', 'parent_relation'], 'Relation direction');

        $normalized = [
            'reciprocal' => $this->boolean($value['reciprocal'] ?? false, 'Relation reciprocal'),
            'bidirectional_traversal' => $this->boolean(
                $value['bidirectional_traversal'] ?? true,
                'Relation bidirectional_traversal',
            ),
        ];
        if (array_key_exists('parent_relation', $value)) {
            $normalized['parent_relation'] = $this->nullableUuid(
                $value['parent_relation'],
                'Relation parent_relation',
            );
        }
        return $normalized;
    }

    /** @return array{object_type:string,object_subtype:?string,label:string} */
    private function endpoint(mixed $value, string $side): array
    {
        $value = $this->objectMap($value, sprintf('Relation %s endpoint', $side));
        $this->assertObjectKeys($value, ['object_type', 'object_subtype', 'label'], sprintf('Relation %s endpoint', $side));

        $objectType = $value['object_type'] ?? null;
        if (!is_string($objectType) || !in_array($objectType, self::ENDPOINT_TYPES, true)) {
            throw new InvalidArgumentException(sprintf('Relation %s endpoint object_type is not supported.', $side));
        }

        $subtype = $value['object_subtype'] ?? null;
        $requiresSubtype = in_array($objectType, ['post', 'term', 'custom_table', 'registered_entity'], true);
        if ($requiresSubtype) {
            $subtype = $this->machineKey($subtype, sprintf('Relation %s endpoint object_subtype', $side));
        } elseif ($subtype !== null && $subtype !== '') {
            throw new InvalidArgumentException(sprintf(
                'Relation %s endpoint object_subtype is not applicable to %s endpoints.',
                $side,
                $objectType,
            ));
        } else {
            $subtype = null;
        }

        $label = $value['label'] ?? str_replace('_', ' ', ucfirst($objectType));
        if (!is_string($label) || trim($label) === '') {
            throw new InvalidArgumentException(sprintf('Relation %s endpoint label must be a non-empty string.', $side));
        }

        return [
            'object_type' => $objectType,
            'object_subtype' => $subtype,
            'label' => trim($label),
        ];
    }

    /** @return array{from_min:int,from_max:?int,to_min:int,to_max:?int} */
    private function bounds(mixed $value, string $cardinality): array
    {
        $value = $this->objectMap($value, 'Relation bounds');
        $this->assertObjectKeys($value, ['from_min', 'from_max', 'to_min', 'to_max'], 'Relation bounds');

        [$defaultFromMax, $defaultToMax] = match ($cardinality) {
            'one_to_one' => [1, 1],
            'one_to_many' => [null, 1],
            'many_to_one' => [1, null],
            'many_to_many' => [null, null],
            default => throw new InvalidArgumentException('Relation cardinality is not supported.'),
        };

        $fromMin = $this->nonNegativeInteger($value['from_min'] ?? 0, 'Relation bounds from_min');
        $toMin = $this->nonNegativeInteger($value['to_min'] ?? 0, 'Relation bounds to_min');
        $fromMax = $this->nullablePositiveInteger($value['from_max'] ?? $defaultFromMax, 'Relation bounds from_max');
        $toMax = $this->nullablePositiveInteger($value['to_max'] ?? $defaultToMax, 'Relation bounds to_max');

        $this->assertRange($fromMin, $fromMax, 'from');
        $this->assertRange($toMin, $toMax, 'to');
        if ($defaultFromMax === 1 && $fromMax !== 1) {
            throw new InvalidArgumentException('Relation cardinality requires from_max to equal 1.');
        }
        if ($defaultToMax === 1 && $toMax !== 1) {
            throw new InvalidArgumentException('Relation cardinality requires to_max to equal 1.');
        }

        return [
            'from_min' => $fromMin,
            'from_max' => $fromMax,
            'to_min' => $toMin,
            'to_max' => $toMax,
        ];
    }

    /** @return array{ordered_from:bool,ordered_to:bool,order_mode:?string} */
    private function edgeOrdering(mixed $value): array
    {
        $value = $this->objectMap($value, 'Relation edge_ordering');
        $this->assertObjectKeys($value, ['ordered_from', 'ordered_to', 'order_mode'], 'Relation edge_ordering');

        return [
            'ordered_from' => $this->boolean($value['ordered_from'] ?? false, 'Relation edge_ordering ordered_from'),
            'ordered_to' => $this->boolean($value['ordered_to'] ?? false, 'Relation edge_ordering ordered_to'),
            'order_mode' => $this->nullableMachineKey($value['order_mode'] ?? null, 'Relation edge_ordering order_mode'),
        ];
    }

    /** @return array{separate_table:bool,table_name:?string,index_strategy:?string,foreign_keys:bool} */
    private function storageConfig(mixed $value): array
    {
        $value = $this->objectMap($value, 'Relation storage_config');
        $this->assertObjectKeys(
            $value,
            ['separate_table', 'table_name', 'index_strategy', 'foreign_keys'],
            'Relation storage_config',
        );

        return [
            'separate_table' => $this->boolean($value['separate_table'] ?? false, 'Relation storage_config separate_table'),
            'table_name' => $this->nullableMachineKey($value['table_name'] ?? null, 'Relation storage_config table_name'),
            'index_strategy' => $this->nullableMachineKey(
                $value['index_strategy'] ?? null,
                'Relation storage_config index_strategy',
            ),
            'foreign_keys' => $this->boolean($value['foreign_keys'] ?? false, 'Relation storage_config foreign_keys'),
        ];
    }

    /** @return array{required_validation:bool,queryable:bool,index_policy:?string} */
    private function pivotPolicy(mixed $value): array
    {
        $value = $this->objectMap($value, 'Relation pivot_policy');
        $this->assertObjectKeys($value, ['required_validation', 'queryable', 'index_policy'], 'Relation pivot_policy');

        return [
            'required_validation' => $this->boolean(
                $value['required_validation'] ?? false,
                'Relation pivot_policy required_validation',
            ),
            'queryable' => $this->boolean($value['queryable'] ?? false, 'Relation pivot_policy queryable'),
            'index_policy' => $this->nullableMachineKey(
                $value['index_policy'] ?? null,
                'Relation pivot_policy index_policy',
            ),
        ];
    }

    /** @return array{delete_edges:bool,from_object:string,to_object:string} */
    private function deletionPolicy(mixed $value): array
    {
        $value = $this->objectMap($value, 'Relation deletion_policy');
        $this->assertObjectKeys($value, ['delete_edges', 'from_object', 'to_object'], 'Relation deletion_policy');

        return [
            'delete_edges' => $this->boolean($value['delete_edges'] ?? true, 'Relation deletion_policy delete_edges'),
            'from_object' => $this->enum(
                $value['from_object'] ?? 'detach',
                self::DELETION_POLICIES,
                'Relation deletion_policy from_object',
            ),
            'to_object' => $this->enum(
                $value['to_object'] ?? 'detach',
                self::DELETION_POLICIES,
                'Relation deletion_policy to_object',
            ),
        ];
    }

    /** @return array<string,mixed> */
    private function editorPolicy(mixed $value): array
    {
        $value = $this->objectMap($value, 'Relation editor_policy');
        $this->assertObjectKeys(
            $value,
            ['from', 'to', 'search', 'ajax', 'exclude_connected', 'show_inverse'],
            'Relation editor_policy',
        );

        return [
            'from' => $this->editorSide($value['from'] ?? [], 'from'),
            'to' => $this->editorSide($value['to'] ?? [], 'to'),
            'search' => $this->boolean($value['search'] ?? true, 'Relation editor_policy search'),
            'ajax' => $this->boolean($value['ajax'] ?? false, 'Relation editor_policy ajax'),
            'exclude_connected' => $this->boolean(
                $value['exclude_connected'] ?? true,
                'Relation editor_policy exclude_connected',
            ),
            'show_inverse' => $this->boolean($value['show_inverse'] ?? false, 'Relation editor_policy show_inverse'),
        ];
    }

    /** @return array{enabled:bool,context:?string,position:?string,collapsed:bool} */
    private function editorSide(mixed $value, string $side): array
    {
        $value = $this->objectMap($value, sprintf('Relation editor_policy %s', $side));
        $this->assertObjectKeys(
            $value,
            ['enabled', 'context', 'position', 'collapsed'],
            sprintf('Relation editor_policy %s', $side),
        );

        return [
            'enabled' => $this->boolean($value['enabled'] ?? true, sprintf('Relation editor_policy %s enabled', $side)),
            'context' => $this->nullableMachineKey(
                $value['context'] ?? null,
                sprintf('Relation editor_policy %s context', $side),
            ),
            'position' => $this->nullableMachineKey(
                $value['position'] ?? null,
                sprintf('Relation editor_policy %s position', $side),
            ),
            'collapsed' => $this->boolean(
                $value['collapsed'] ?? false,
                sprintf('Relation editor_policy %s collapsed', $side),
            ),
        ];
    }

    /** @return array<string,?string> */
    private function permissionsPolicy(mixed $value): array
    {
        $value = $this->objectMap($value, 'Relation permissions_policy');
        $keys = [
            'view',
            'connect',
            'disconnect',
            'manage_definition',
            'from_capability',
            'to_capability',
            'rest_write',
        ];
        $this->assertObjectKeys($value, $keys, 'Relation permissions_policy');

        $normalized = [];
        foreach ($keys as $key) {
            $normalized[$key] = $this->nullableCapability(
                $value[$key] ?? null,
                sprintf('Relation permissions_policy %s', $key),
            );
        }
        return $normalized;
    }

    /** @return array{expose:bool,namespace:?string} */
    private function restPolicy(mixed $value): array
    {
        $value = $this->objectMap($value, 'Relation rest_policy');
        $this->assertObjectKeys($value, ['expose', 'namespace'], 'Relation rest_policy');

        return [
            'expose' => $this->boolean($value['expose'] ?? true, 'Relation rest_policy expose'),
            'namespace' => $this->nullableNamespace($value['namespace'] ?? null),
        ];
    }

    /** @return array{definition:bool,edges:bool,pivot:bool} */
    private function portability(mixed $value): array
    {
        $value = $this->objectMap($value, 'Relation portability');
        $this->assertObjectKeys($value, ['definition', 'edges', 'pivot'], 'Relation portability');

        return [
            'definition' => $this->boolean($value['definition'] ?? true, 'Relation portability definition'),
            'edges' => $this->boolean($value['edges'] ?? false, 'Relation portability edges'),
            'pivot' => $this->boolean($value['pivot'] ?? false, 'Relation portability pivot'),
        ];
    }

    /** @param list<string> $allowed */
    private function enum(mixed $value, array $allowed, string $label): string
    {
        if (!is_string($value) || !in_array($value, $allowed, true)) {
            throw new InvalidArgumentException(sprintf('%s is not supported.', $label));
        }
        return $value;
    }

    /** @return array<string,mixed> */
    private function objectMap(mixed $value, string $label): array
    {
        if (!is_array($value) || array_is_list($value)) {
            throw new InvalidArgumentException(sprintf('%s must be an object/map.', $label));
        }
        return $value;
    }

    /** @param array<string,mixed> $value @param list<string> $allowed */
    private function assertObjectKeys(array $value, array $allowed, string $label): void
    {
        foreach (array_keys($value) as $key) {
            if (!in_array($key, $allowed, true)) {
                throw new InvalidArgumentException(sprintf('Unsupported %s option "%s".', $label, (string) $key));
            }
        }
    }

    private function assertRange(int $minimum, ?int $maximum, string $side): void
    {
        if ($maximum !== null && $minimum > $maximum) {
            throw new InvalidArgumentException(sprintf('Relation %s minimum cannot exceed maximum.', $side));
        }
    }

    private function nonNegativeInteger(mixed $value, string $label): int
    {
        if (!is_int($value) || $value < 0) {
            throw new InvalidArgumentException(sprintf('%s must be a non-negative integer.', $label));
        }
        return $value;
    }

    private function nullablePositiveInteger(mixed $value, string $label): ?int
    {
        if ($value === null) {
            return null;
        }
        if (!is_int($value) || $value < 1) {
            throw new InvalidArgumentException(sprintf('%s must be null or a positive integer.', $label));
        }
        return $value;
    }

    private function boolean(mixed $value, string $label): bool
    {
        if (!is_bool($value)) {
            throw new InvalidArgumentException(sprintf('%s must be boolean.', $label));
        }
        return $value;
    }

    private function machineKey(mixed $value, string $label): string
    {
        if (!is_string($value) || preg_match('/^[a-z0-9][a-z0-9_-]{0,63}$/', $value) !== 1) {
            throw new InvalidArgumentException(sprintf('%s must be a lowercase machine key up to 64 characters.', $label));
        }
        return $value;
    }

    private function nullableMachineKey(mixed $value, string $label): ?string
    {
        if ($value === null || $value === '') {
            return null;
        }
        return $this->machineKey($value, $label);
    }

    private function nullableUuid(mixed $value, string $label): ?string
    {
        if ($value === null || $value === '') {
            return null;
        }
        if (!is_string($value)
            || preg_match('/^[0-9a-f]{8}-[0-9a-f]{4}-[1-5][0-9a-f]{3}-[89ab][0-9a-f]{3}-[0-9a-f]{12}$/', $value) !== 1
        ) {
            throw new InvalidArgumentException(sprintf('%s must be null or a lowercase RFC 4122 UUID.', $label));
        }
        return $value;
    }

    private function nullableCapability(mixed $value, string $label): ?string
    {
        if ($value === null || $value === '') {
            return null;
        }
        if (!is_string($value) || preg_match('/^[a-z][a-z0-9_]{0,63}$/', $value) !== 1) {
            throw new InvalidArgumentException(sprintf('%s must be null or a lowercase capability reference.', $label));
        }
        return $value;
    }

    private function nullableNamespace(mixed $value): ?string
    {
        if ($value === null || $value === '') {
            return null;
        }
        if (!is_string($value) || preg_match('#^[a-z0-9][a-z0-9._/-]{0,95}$#', $value) !== 1) {
            throw new InvalidArgumentException(
                'Relation rest_policy namespace must be null or a lowercase REST namespace token.',
            );
        }
        return $value;
    }
}
