<?php

declare(strict_types=1);

namespace WPEssential\Platform\Components;

if (!defined('ABSPATH')) {
    exit;
}

use InvalidArgumentException;

final readonly class ComponentBlueprintDescriptor
{
    /**
     * @param list<string> $dependencyIds
     * @param list<string> $assetHandles
     * @param array<string,string> $bindingSchema
     */
    public function __construct(
        public string $id,
        public int $revision,
        public int $ownerSurfaceId,
        public string $componentType,
        public array $dependencyIds = [],
        public array $assetHandles = [],
        public array $bindingSchema = [],
    ) {
        if (!preg_match('/^[0-9a-f]{8}-[0-9a-f]{4}-[1-5][0-9a-f]{3}-[89ab][0-9a-f]{3}-[0-9a-f]{12}$/i', $this->id)) {
            throw new InvalidArgumentException('Blueprint id must be an RFC 4122 UUID.');
        }
        if ($this->revision < 1) {
            throw new InvalidArgumentException('Blueprint revision must be positive.');
        }
        if ($this->ownerSurfaceId < 1 || $this->ownerSurfaceId > 56) {
            throw new InvalidArgumentException('Blueprint owner must be a canonical surface id 1..56.');
        }
        if (!preg_match('/^[a-z][a-z0-9_.-]{0,127}$/', $this->componentType)) {
            throw new InvalidArgumentException('Component type must be a stable semantic identifier.');
        }
        if (count($this->dependencyIds) !== count(array_unique($this->dependencyIds))) {
            throw new InvalidArgumentException('Blueprint dependencies must be unique.');
        }
        if (in_array($this->id, $this->dependencyIds, true)) {
            throw new InvalidArgumentException('Blueprint cannot depend on itself.');
        }
        foreach ($this->dependencyIds as $dependencyId) {
            if (!preg_match('/^[0-9a-f]{8}-[0-9a-f]{4}-[1-5][0-9a-f]{3}-[89ab][0-9a-f]{3}-[0-9a-f]{12}$/i', $dependencyId)) {
                throw new InvalidArgumentException('Blueprint dependency ids must be RFC 4122 UUIDs.');
            }
        }
        foreach ($this->assetHandles as $handle) {
            if (!preg_match('/^wpe-[a-z0-9][a-z0-9-]{1,127}$/', $handle)) {
                throw new InvalidArgumentException('Blueprint assets must use registered WPEssential handles.');
            }
        }
        foreach ($this->bindingSchema as $binding => $type) {
            if (!preg_match('/^[a-z][a-z0-9_.-]{0,127}$/', $binding) || !in_array($type, ['string', 'int', 'float', 'bool', 'string_list', 'int_list'], true)) {
                throw new InvalidArgumentException('Blueprint binding schema must use bounded supported scalar types.');
            }
        }
    }
}
