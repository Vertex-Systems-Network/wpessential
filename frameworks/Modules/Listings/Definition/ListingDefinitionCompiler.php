<?php

declare(strict_types=1);

namespace WPEssential\Modules\Listings\Definition;

if (!defined('ABSPATH')) {
    exit;
}

use InvalidArgumentException;
use JsonException;
use WPEssential\Contracts\ComponentBlueprintRegistryInterface;
use WPEssential\Platform\Assets\AssetRegistry;
use WPEssential\Platform\Definitions\Definition;
use WPEssential\Platform\Definitions\DefinitionStatus;

final readonly class ListingDefinitionCompiler
{
    private const OWNER_SURFACE_ID = 9;
    private const TYPE = 'listing';
    private const SCHEMA_VERSION = 1;
    private const MAX_ASSETS = 32;

    /** @var list<string> */
    private const PAYLOAD_KEYS = ['query_source_ref', 'blueprint', 'layout', 'assets'];

    /** @var list<string> */
    private const BLUEPRINT_KEYS = ['id', 'revision'];

    /** @var list<string> */
    private const LAYOUT_KEYS = ['mode', 'columns'];

    public function __construct(
        private ComponentBlueprintRegistryInterface $blueprints,
        private AssetRegistry $assets,
    ) {
    }

    /** @throws JsonException */
    public function compile(Definition $definition): ListingCompiledDescriptor
    {
        if ($definition->type !== self::TYPE || $definition->ownerSurfaceId !== self::OWNER_SURFACE_ID) {
            throw new InvalidArgumentException('Definition is not owned by the canonical Listings surface.');
        }
        if ($definition->schemaVersion !== self::SCHEMA_VERSION) {
            throw new InvalidArgumentException('Listing definition schema version is unsupported.');
        }
        if ($definition->status !== DefinitionStatus::Published) {
            throw new InvalidArgumentException('Only Published Listing definitions may compile.');
        }

        $payload = $definition->payload;
        if (array_is_list($payload)) {
            throw new InvalidArgumentException('Listing definition payload must be an object/map.');
        }
        $this->assertKnownKeys($payload, self::PAYLOAD_KEYS, 'Listing definition payload');

        $querySourceRef = $this->semanticReference($payload['query_source_ref'] ?? null, 'Listing query source reference');
        $blueprint = $this->blueprint($payload['blueprint'] ?? null);
        $layout = $this->layout($payload['layout'] ?? null);
        $listingAssets = $this->assetHandles($payload['assets'] ?? []);

        $blueprintDescriptor = $this->blueprints->get($blueprint['id'], $blueprint['revision']);
        if ($blueprintDescriptor === null) {
            throw new InvalidArgumentException('Listing references an unavailable Component Blueprint revision.');
        }

        $assetHandles = array_values(array_unique(array_merge($blueprintDescriptor->assetHandles, $listingAssets)));
        if (count($assetHandles) > self::MAX_ASSETS) {
            throw new InvalidArgumentException('Listing asset dependency set exceeds the bounded V1 limit.');
        }
        sort($assetHandles, SORT_STRING);
        foreach ($assetHandles as $handle) {
            $this->assets->get($handle);
        }

        $fingerprintPayload = [
            'listing_id' => $definition->id,
            'listing_revision' => $definition->revision,
            'query_source_ref' => $querySourceRef,
            'blueprint_id' => $blueprintDescriptor->id,
            'blueprint_revision' => $blueprintDescriptor->revision,
            'blueprint_component_type' => $blueprintDescriptor->componentType,
            'blueprint_binding_schema' => $blueprintDescriptor->bindingSchema,
            'blueprint_dependencies' => $blueprintDescriptor->dependencyIds,
            'layout' => $layout,
            'assets' => $assetHandles,
        ];

        return new ListingCompiledDescriptor(
            listingId: $definition->id,
            revision: $definition->revision,
            querySourceRef: $querySourceRef,
            blueprintId: $blueprintDescriptor->id,
            blueprintRevision: $blueprintDescriptor->revision,
            layoutMode: $layout['mode'],
            columns: $layout['columns'],
            assetHandles: $assetHandles,
            compatibilityFingerprint: hash(
                'sha256',
                json_encode($fingerprintPayload, JSON_THROW_ON_ERROR | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE),
            ),
        );
    }

    /** @return array{id:string,revision:int} */
    private function blueprint(mixed $value): array
    {
        if (!is_array($value) || array_is_list($value)) {
            throw new InvalidArgumentException('Listing blueprint must be an object/map.');
        }
        $this->assertKnownKeys($value, self::BLUEPRINT_KEYS, 'Listing blueprint');

        $id = $value['id'] ?? null;
        $revision = $value['revision'] ?? null;
        if (!is_string($id) || !preg_match('/^[0-9a-f]{8}-[0-9a-f]{4}-[1-5][0-9a-f]{3}-[89ab][0-9a-f]{3}-[0-9a-f]{12}$/', $id)) {
            throw new InvalidArgumentException('Listing blueprint id must be a lowercase RFC 4122 UUID.');
        }
        if (!is_int($revision) || $revision < 1) {
            throw new InvalidArgumentException('Listing blueprint revision must be positive.');
        }

        return ['id' => $id, 'revision' => $revision];
    }

    /** @return array{mode:string,columns:int} */
    private function layout(mixed $value): array
    {
        if (!is_array($value) || array_is_list($value)) {
            throw new InvalidArgumentException('Listing layout must be an object/map.');
        }
        $this->assertKnownKeys($value, self::LAYOUT_KEYS, 'Listing layout');

        $mode = $value['mode'] ?? null;
        $columns = $value['columns'] ?? null;
        if (!is_string($mode) || !in_array($mode, ['list', 'grid'], true)) {
            throw new InvalidArgumentException('Listing layout mode is unsupported.');
        }
        if (!is_int($columns) || $columns < 1 || $columns > 6) {
            throw new InvalidArgumentException('Listing layout columns must be within 1..6.');
        }
        if ($mode === 'list' && $columns !== 1) {
            throw new InvalidArgumentException('List layout must use exactly one column.');
        }

        return ['mode' => $mode, 'columns' => $columns];
    }

    /** @return list<string> */
    private function assetHandles(mixed $value): array
    {
        if (!is_array($value) || !array_is_list($value)) {
            throw new InvalidArgumentException('Listing assets must be a list.');
        }
        if (count($value) > self::MAX_ASSETS) {
            throw new InvalidArgumentException('Listing assets exceed the bounded V1 limit.');
        }

        $handles = [];
        foreach ($value as $handle) {
            if (!is_string($handle) || !preg_match('/^wpe-[a-z0-9][a-z0-9-]{1,127}$/', $handle)) {
                throw new InvalidArgumentException('Listing assets must use registered WPEssential handles.');
            }
            if (in_array($handle, $handles, true)) {
                throw new InvalidArgumentException('Listing asset handles must be unique.');
            }
            $handles[] = $handle;
        }

        return $handles;
    }

    private function semanticReference(mixed $value, string $label): string
    {
        if (!is_string($value) || !preg_match('/^[a-z][a-z0-9._-]{0,127}$/', $value)) {
            throw new InvalidArgumentException($label . ' must match the canonical Query semantic reference format.');
        }

        return $value;
    }

    /**
     * @param array<string,mixed> $value
     * @param list<string> $allowed
     */
    private function assertKnownKeys(array $value, array $allowed, string $label): void
    {
        foreach (array_keys($value) as $key) {
            if (!is_string($key) || !in_array($key, $allowed, true)) {
                throw new InvalidArgumentException($label . ' contains an unsupported key.');
            }
        }
    }
}
