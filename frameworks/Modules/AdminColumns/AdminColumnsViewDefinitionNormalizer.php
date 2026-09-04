<?php

declare(strict_types=1);

namespace WPEssential\Modules\AdminColumns;

if (!defined('ABSPATH')) {
    exit;
}

use InvalidArgumentException;

final readonly class AdminColumnsViewDefinitionNormalizer
{
    public const DEFINITION_TYPE = 'admin_columns_view';
    public const OWNER_SURFACE_ID = 8;

    private const TOP_LEVEL_KEYS = [
        'view_key',
        'name',
        'enabled',
        'target',
        'assignment',
        'layout',
        'columns',
        'visibility',
    ];

    private const TARGET_TYPES = ['post_type', 'taxonomy', 'users', 'comments', 'media'];
    private const SOURCE_OWNERS = [
        'native',
        'fields',
        'taxonomy',
        'relations',
        'media',
        'status',
        'query',
        'provider',
        'renderer',
    ];
    private const FORMATS = ['text', 'number', 'date', 'boolean', 'image', 'badge', 'link'];
    private const ALIGNMENTS = ['left', 'center', 'right'];
    private const VISIBILITY_MODES = ['visible', 'hidden'];

    /**
     * @param array<string,mixed> $payload
     * @return array<string,mixed>
     */
    public function normalize(array $payload): array
    {
        $this->assertKnownKeys($payload, self::TOP_LEVEL_KEYS, 'Admin Columns View');

        $viewKey = $this->machineKey($payload['view_key'] ?? null, 'View key');
        $name = $this->nonEmptyString($payload['name'] ?? null, 'View name');
        $enabled = $this->boolean($payload['enabled'] ?? true, 'View enabled');
        $target = $this->target($payload['target'] ?? null);
        $columns = $this->columns($payload['columns'] ?? null);

        $normalized = [
            'view_key' => $viewKey,
            'name' => $name,
            'enabled' => $enabled,
            'target' => $target,
            'columns' => $columns,
        ];

        if (array_key_exists('assignment', $payload)) {
            $normalized['assignment'] = $this->assignment($payload['assignment']);
        }
        if (array_key_exists('layout', $payload)) {
            $normalized['layout'] = $this->viewLayout($payload['layout']);
        }
        if (array_key_exists('visibility', $payload)) {
            $normalized['visibility'] = $this->visibility($payload['visibility']);
        }

        return $normalized;
    }

    /** @return array{type:string,key:string} */
    private function target(mixed $value): array
    {
        $value = $this->objectMap($value, 'View target');
        $this->assertKnownKeys($value, ['type', 'key'], 'View target');

        $type = $value['type'] ?? null;
        if (!is_string($type) || !in_array($type, self::TARGET_TYPES, true)) {
            throw new InvalidArgumentException('View target type is not supported by the bounded V1 foundation.');
        }

        $key = $this->reference($value['key'] ?? null, 'View target key');
        return ['type' => $type, 'key' => $key];
    }

    /** @return list<array<string,mixed>> */
    private function columns(mixed $value): array
    {
        if (!is_array($value) || !array_is_list($value) || $value === []) {
            throw new InvalidArgumentException('View columns must be a non-empty list.');
        }
        if (count($value) > 100) {
            throw new InvalidArgumentException('View columns exceed the bounded V1 maximum of 100.');
        }

        $normalized = [];
        $uuids = [];
        $keys = [];
        $primaryCount = 0;
        foreach ($value as $index => $column) {
            if (!is_array($column) || array_is_list($column)) {
                throw new InvalidArgumentException(sprintf('Column %d must be an object/map.', $index));
            }
            $candidate = $this->column($column, $index);
            $uuid = $candidate['uuid'];
            $key = $candidate['key'];
            if (isset($uuids[$uuid])) {
                throw new InvalidArgumentException(sprintf('Duplicate Column UUID "%s".', $uuid));
            }
            if (isset($keys[$key])) {
                throw new InvalidArgumentException(sprintf('Duplicate Column key "%s".', $key));
            }
            $uuids[$uuid] = true;
            $keys[$key] = true;
            if ($candidate['primary']) {
                ++$primaryCount;
                if ($primaryCount > 1) {
                    throw new InvalidArgumentException('A View may declare at most one primary Column.');
                }
            }
            $normalized[] = $candidate;
        }

        return $normalized;
    }

    /** @param array<string,mixed> $column @return array<string,mixed> */
    private function column(array $column, int $index): array
    {
        $this->assertKnownKeys(
            $column,
            ['uuid', 'key', 'label', 'enabled', 'source', 'format', 'layout', 'primary'],
            sprintf('Column %d', $index),
        );

        $uuid = $this->uuid($column['uuid'] ?? null, sprintf('Column %d UUID', $index));
        $key = $this->machineKey($column['key'] ?? null, sprintf('Column %d key', $index));
        $label = $this->nonEmptyString($column['label'] ?? null, sprintf('Column %d label', $index));

        $normalized = [
            'uuid' => $uuid,
            'key' => $key,
            'label' => $label,
            'enabled' => $this->boolean($column['enabled'] ?? true, sprintf('Column %d enabled', $index)),
            'source' => $this->source($column['source'] ?? null, $index),
            'format' => $this->format($column['format'] ?? 'text', $index),
            'primary' => $this->boolean($column['primary'] ?? false, sprintf('Column %d primary', $index)),
        ];

        if (array_key_exists('layout', $column)) {
            $normalized['layout'] = $this->columnLayout($column['layout'], $index);
        }

        return $normalized;
    }

    /** @return array{owner:string,reference:string} */
    private function source(mixed $value, int $index): array
    {
        $value = $this->objectMap($value, sprintf('Column %d source', $index));
        $this->assertKnownKeys($value, ['owner', 'reference'], sprintf('Column %d source', $index));

        $owner = $value['owner'] ?? null;
        if (!is_string($owner) || !in_array($owner, self::SOURCE_OWNERS, true)) {
            throw new InvalidArgumentException(sprintf('Column %d source owner is not supported.', $index));
        }

        return [
            'owner' => $owner,
            'reference' => $this->reference($value['reference'] ?? null, sprintf('Column %d source reference', $index)),
        ];
    }

    private function format(mixed $value, int $index): string
    {
        if (!is_string($value) || !in_array($value, self::FORMATS, true)) {
            throw new InvalidArgumentException(sprintf('Column %d format is not supported.', $index));
        }
        return $value;
    }

    /** @return array{width:?int,min_width:?int,max_width:?int,alignment:string,responsive_priority:int,sticky:bool} */
    private function columnLayout(mixed $value, int $index): array
    {
        $value = $this->objectMap($value, sprintf('Column %d layout', $index));
        $this->assertKnownKeys(
            $value,
            ['width', 'min_width', 'max_width', 'alignment', 'responsive_priority', 'sticky'],
            sprintf('Column %d layout', $index),
        );

        $width = $this->nullableBoundedInt($value['width'] ?? null, 40, 2000, sprintf('Column %d width', $index));
        $min = $this->nullableBoundedInt($value['min_width'] ?? null, 40, 2000, sprintf('Column %d min_width', $index));
        $max = $this->nullableBoundedInt($value['max_width'] ?? null, 40, 2000, sprintf('Column %d max_width', $index));
        if ($min !== null && $max !== null && $min > $max) {
            throw new InvalidArgumentException(sprintf('Column %d min_width cannot exceed max_width.', $index));
        }
        if ($width !== null && (($min !== null && $width < $min) || ($max !== null && $width > $max))) {
            throw new InvalidArgumentException(sprintf('Column %d width must remain within min/max bounds.', $index));
        }

        $alignment = $value['alignment'] ?? 'left';
        if (!is_string($alignment) || !in_array($alignment, self::ALIGNMENTS, true)) {
            throw new InvalidArgumentException(sprintf('Column %d alignment is not supported.', $index));
        }
        $priority = $value['responsive_priority'] ?? 50;
        if (!is_int($priority) || $priority < 0 || $priority > 100) {
            throw new InvalidArgumentException(sprintf('Column %d responsive_priority must be 0..100.', $index));
        }

        return [
            'width' => $width,
            'min_width' => $min,
            'max_width' => $max,
            'alignment' => $alignment,
            'responsive_priority' => $priority,
            'sticky' => $this->boolean($value['sticky'] ?? false, sprintf('Column %d sticky', $index)),
        ];
    }

    /** @return array{roles:list<string>,users:list<int>,capabilities:list<string>} */
    private function assignment(mixed $value): array
    {
        $value = $this->objectMap($value, 'View assignment');
        $this->assertKnownKeys($value, ['roles', 'users', 'capabilities'], 'View assignment');

        return [
            'roles' => $this->uniqueMachineList($value['roles'] ?? [], 'View assignment roles'),
            'users' => $this->uniquePositiveIntList($value['users'] ?? [], 'View assignment users'),
            'capabilities' => $this->uniqueMachineList($value['capabilities'] ?? [], 'View assignment capabilities'),
        ];
    }

    /** @return array{density:string,sticky_header:bool,horizontal_scroll:bool} */
    private function viewLayout(mixed $value): array
    {
        $value = $this->objectMap($value, 'View layout');
        $this->assertKnownKeys($value, ['density', 'sticky_header', 'horizontal_scroll'], 'View layout');
        $density = $value['density'] ?? 'comfortable';
        if (!is_string($density) || !in_array($density, ['compact', 'comfortable'], true)) {
            throw new InvalidArgumentException('View layout density is not supported.');
        }

        return [
            'density' => $density,
            'sticky_header' => $this->boolean($value['sticky_header'] ?? false, 'View sticky_header'),
            'horizontal_scroll' => $this->boolean($value['horizontal_scroll'] ?? true, 'View horizontal_scroll'),
        ];
    }

    /** @return array{mode:string,reason:?string} */
    private function visibility(mixed $value): array
    {
        $value = $this->objectMap($value, 'View visibility');
        $this->assertKnownKeys($value, ['mode', 'reason'], 'View visibility');
        $mode = $value['mode'] ?? 'visible';
        if (!is_string($mode) || !in_array($mode, self::VISIBILITY_MODES, true)) {
            throw new InvalidArgumentException('View visibility mode is not supported.');
        }
        $reason = $value['reason'] ?? null;
        if ($reason !== null && (!is_string($reason) || trim($reason) === '')) {
            throw new InvalidArgumentException('View visibility reason must be null or a non-empty string.');
        }

        return ['mode' => $mode, 'reason' => is_string($reason) ? trim($reason) : null];
    }

    /** @param array<string,mixed> $map @param list<string> $allowed */
    private function assertKnownKeys(array $map, array $allowed, string $label): void
    {
        foreach (array_keys($map) as $key) {
            if (!in_array($key, $allowed, true)) {
                throw new InvalidArgumentException(sprintf('%s contains unsupported option "%s".', $label, (string) $key));
            }
        }
    }

    /** @return array<string,mixed> */
    private function objectMap(mixed $value, string $label): array
    {
        if (!is_array($value) || array_is_list($value)) {
            throw new InvalidArgumentException($label . ' must be an object/map.');
        }
        return $value;
    }

    private function machineKey(mixed $value, string $label): string
    {
        if (!is_string($value) || preg_match('/^[a-z0-9][a-z0-9_-]{0,63}$/', $value) !== 1) {
            throw new InvalidArgumentException($label . ' must be a stable lowercase machine key.');
        }
        return $value;
    }

    private function reference(mixed $value, string $label): string
    {
        if (!is_string($value) || preg_match('/^[a-z0-9][a-z0-9._:-]{0,191}$/', $value) !== 1) {
            throw new InvalidArgumentException($label . ' must be a bounded typed reference.');
        }
        return $value;
    }

    private function uuid(mixed $value, string $label): string
    {
        if (!is_string($value) || preg_match('/^[0-9a-f]{8}-[0-9a-f]{4}-[1-5][0-9a-f]{3}-[89ab][0-9a-f]{3}-[0-9a-f]{12}$/', $value) !== 1) {
            throw new InvalidArgumentException($label . ' must be a lowercase RFC 4122 UUID.');
        }
        return $value;
    }

    private function nonEmptyString(mixed $value, string $label): string
    {
        if (!is_string($value) || trim($value) === '' || mb_strlen(trim($value)) > 191) {
            throw new InvalidArgumentException($label . ' must be a non-empty bounded string.');
        }
        return trim($value);
    }

    private function boolean(mixed $value, string $label): bool
    {
        if (!is_bool($value)) {
            throw new InvalidArgumentException($label . ' must be boolean.');
        }
        return $value;
    }

    private function nullableBoundedInt(mixed $value, int $min, int $max, string $label): ?int
    {
        if ($value === null) {
            return null;
        }
        if (!is_int($value) || $value < $min || $value > $max) {
            throw new InvalidArgumentException(sprintf('%s must be null or %d..%d.', $label, $min, $max));
        }
        return $value;
    }

    /** @return list<string> */
    private function uniqueMachineList(mixed $value, string $label): array
    {
        if (!is_array($value) || !array_is_list($value) || count($value) > 100) {
            throw new InvalidArgumentException($label . ' must be a bounded list.');
        }
        $result = [];
        $seen = [];
        foreach ($value as $item) {
            $key = $this->machineKey($item, $label . ' item');
            if (isset($seen[$key])) {
                throw new InvalidArgumentException($label . ' must contain unique values.');
            }
            $seen[$key] = true;
            $result[] = $key;
        }
        return $result;
    }

    /** @return list<int> */
    private function uniquePositiveIntList(mixed $value, string $label): array
    {
        if (!is_array($value) || !array_is_list($value) || count($value) > 100) {
            throw new InvalidArgumentException($label . ' must be a bounded list.');
        }
        $result = [];
        $seen = [];
        foreach ($value as $item) {
            if (!is_int($item) || $item < 1 || isset($seen[$item])) {
                throw new InvalidArgumentException($label . ' must contain unique positive integers.');
            }
            $seen[$item] = true;
            $result[] = $item;
        }
        return $result;
    }
}
