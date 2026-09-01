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

        if ($publishing && $normalized['from'] === $normalized['to']) {
            // Self-relations are valid, but identical endpoints are intentionally allowed.
            // The explicit branch documents that publishing does not infer a prohibition.
        }

        return $normalized;
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

    /** @return array{reciprocal:bool,bidirectional_traversal:bool} */
    private function direction(mixed $value): array
    {
        if (!is_array($value) || array_is_list($value)) {
            throw new InvalidArgumentException('Relation direction must be an object/map.');
        }
        foreach (array_keys($value) as $key) {
            if (!in_array($key, ['reciprocal', 'bidirectional_traversal'], true)) {
                throw new InvalidArgumentException(sprintf('Unsupported Relation direction option "%s".', (string) $key));
            }
        }

        return [
            'reciprocal' => $this->boolean($value['reciprocal'] ?? false, 'Relation reciprocal'),
            'bidirectional_traversal' => $this->boolean(
                $value['bidirectional_traversal'] ?? true,
                'Relation bidirectional_traversal',
            ),
        ];
    }

    /** @return array{object_type:string,object_subtype:?string,label:string} */
    private function endpoint(mixed $value, string $side): array
    {
        if (!is_array($value) || array_is_list($value)) {
            throw new InvalidArgumentException(sprintf('Relation %s endpoint must be an object/map.', $side));
        }
        foreach (array_keys($value) as $key) {
            if (!in_array($key, ['object_type', 'object_subtype', 'label'], true)) {
                throw new InvalidArgumentException(sprintf('Unsupported Relation %s endpoint option "%s".', $side, (string) $key));
            }
        }

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
        if (!is_array($value) || array_is_list($value)) {
            throw new InvalidArgumentException('Relation bounds must be an object/map.');
        }
        foreach (array_keys($value) as $key) {
            if (!in_array($key, ['from_min', 'from_max', 'to_min', 'to_max'], true)) {
                throw new InvalidArgumentException(sprintf('Unsupported Relation bounds option "%s".', (string) $key));
            }
        }

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
}
