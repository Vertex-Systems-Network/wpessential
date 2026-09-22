<?php

declare(strict_types=1);

namespace WPEssential\Modules\DashboardWidgets;

if (!defined('ABSPATH')) {
    exit;
}

use InvalidArgumentException;
use WPEssential\Contracts\ComponentBlueprintRegistryInterface;
use WPEssential\Platform\Definitions\Definition;

final readonly class DashboardWidgetRenderSourceCompiler
{
    /** @var list<string> */
    private const RENDER_SOURCE_KEYS = ['kind', 'blueprint_id', 'blueprint_revision', 'bindings'];

    /** @var list<string> */
    private const BINDING_ENVELOPE_KEYS = ['source', 'value'];

    private DashboardWidgetContentClassCompiler $contentClassCompiler;
    private DashboardWidgetComponentBlueprintCatalog $componentCatalog;

    public function __construct(
        private ComponentBlueprintRegistryInterface $blueprints,
        ?DashboardWidgetContentClassCompiler $contentClassCompiler = null,
        ?DashboardWidgetComponentBlueprintCatalog $componentCatalog = null,
    ) {
        $this->contentClassCompiler = $contentClassCompiler ?? new DashboardWidgetContentClassCompiler();
        $this->componentCatalog = $componentCatalog ?? new DashboardWidgetComponentBlueprintCatalog();
    }

    public function compile(Definition $definition): DashboardWidgetRenderSourceDescriptor
    {
        $contentClass = $this->contentClassCompiler->compile($definition);

        $payload = $definition->payload;
        $widget = $payload['widget'] ?? null;
        if (!is_array($widget) || array_is_list($widget)) {
            throw new InvalidArgumentException('Dashboard Widget metadata must be an object/map for render-source compilation.');
        }

        $renderSource = $widget['render_source'] ?? null;
        if (!is_array($renderSource) || array_is_list($renderSource)) {
            throw new InvalidArgumentException('Dashboard Widget render_source must be an object/map.');
        }
        $this->assertKnownKeys($renderSource, self::RENDER_SOURCE_KEYS, 'Dashboard Widget render_source');

        if (($renderSource['kind'] ?? null) !== 'component_blueprint') {
            throw new InvalidArgumentException('Dashboard Widget render_source kind must be component_blueprint for V1.');
        }

        $blueprintId = $renderSource['blueprint_id'] ?? null;
        if (
            !is_string($blueprintId)
            || preg_match('/^[0-9a-f]{8}-[0-9a-f]{4}-[1-5][0-9a-f]{3}-[89ab][0-9a-f]{3}-[0-9a-f]{12}$/', $blueprintId) !== 1
        ) {
            throw new InvalidArgumentException('Dashboard Widget render_source blueprint_id must be a lowercase RFC 4122 UUID.');
        }

        $blueprintRevision = $renderSource['blueprint_revision'] ?? null;
        if (!is_int($blueprintRevision) || $blueprintRevision < 1) {
            throw new InvalidArgumentException('Dashboard Widget render_source blueprint_revision must be a positive integer.');
        }

        $canonicalBlueprint = $this->componentCatalog->forContentType($contentClass->contentType);
        if ($canonicalBlueprint === null) {
            throw new InvalidArgumentException('Dashboard Widget trusted content class has no canonical Component Blueprint.');
        }
        if (
            $blueprintId !== $canonicalBlueprint->id
            || $blueprintRevision !== $canonicalBlueprint->revision
        ) {
            throw new InvalidArgumentException(
                'Dashboard Widget render_source Blueprint must match the canonical Blueprint for its trusted content class.',
            );
        }

        $bindings = $renderSource['bindings'] ?? null;
        if (!is_array($bindings) || ($bindings !== [] && array_is_list($bindings))) {
            throw new InvalidArgumentException('Dashboard Widget render_source bindings must be an object/map.');
        }
        if (count($bindings) > 128) {
            throw new InvalidArgumentException('Dashboard Widget render_source bindings exceed the bounded V1 limit.');
        }

        $blueprint = $this->blueprints->get($blueprintId, $blueprintRevision);
        if ($blueprint === null) {
            throw new InvalidArgumentException('Dashboard Widget render_source Blueprint id/revision is not registered.');
        }
        if ($blueprint->ownerSurfaceId !== DashboardWidgetDefinition::OWNER_SURFACE_ID) {
            throw new InvalidArgumentException('Dashboard Widget render_source Blueprint must be owned by Surface 10.');
        }

        $authoredKeys = array_keys($bindings);
        foreach ($authoredKeys as $key) {
            if (!is_string($key) || preg_match('/^[a-z][a-z0-9_.-]{0,127}$/', $key) !== 1) {
                throw new InvalidArgumentException('Dashboard Widget render_source binding keys must be stable semantic identifiers.');
            }
        }

        $schemaKeys = array_keys($blueprint->bindingSchema);
        sort($authoredKeys, SORT_STRING);
        sort($schemaKeys, SORT_STRING);
        if ($authoredKeys !== $schemaKeys) {
            throw new InvalidArgumentException('Dashboard Widget render_source bindings must exactly match the Blueprint binding schema keys.');
        }

        /** @var array<string, scalar|list<scalar>> $compiledBindings */
        $compiledBindings = [];
        foreach ($blueprint->bindingSchema as $key => $type) {
            $envelope = $bindings[$key] ?? null;
            if (!is_array($envelope) || array_is_list($envelope)) {
                throw new InvalidArgumentException('Dashboard Widget render_source binding entries must be object/maps.');
            }
            $this->assertKnownKeys($envelope, self::BINDING_ENVELOPE_KEYS, 'Dashboard Widget render_source binding');

            if (!array_key_exists('source', $envelope) || !array_key_exists('value', $envelope)) {
                throw new InvalidArgumentException('Dashboard Widget render_source binding requires source and value.');
            }
            if ($envelope['source'] !== 'literal') {
                throw new InvalidArgumentException('Dashboard Widget render_source binding source must be literal for V1.');
            }

            $value = $envelope['value'];
            $this->assertValueMatchesType($value, $type);
            /** @var scalar|list<scalar> $value */
            $compiledBindings[$key] = $value;
        }
        ksort($compiledBindings, SORT_STRING);

        return new DashboardWidgetRenderSourceDescriptor(
            definitionId: $definition->id,
            definitionRevision: $definition->revision,
            blueprintId: $blueprintId,
            blueprintRevision: $blueprintRevision,
            bindings: $compiledBindings,
        );
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

    private function assertValueMatchesType(mixed $value, string $type): void
    {
        $valid = match ($type) {
            'string' => is_string($value) && $this->isSafeString($value),
            'int' => is_int($value),
            'float' => is_float($value),
            'bool' => is_bool($value),
            'string_list' => $this->isStringList($value),
            'int_list' => $this->isIntList($value),
            default => false,
        };

        if (!$valid) {
            throw new InvalidArgumentException('Dashboard Widget render_source literal binding does not match the Blueprint binding type.');
        }
    }

    private function isSafeString(string $value): bool
    {
        return preg_match('/<\?(?:php|=)|<script\b|javascript:/i', $value) !== 1;
    }

    private function isStringList(mixed $value): bool
    {
        if (!is_array($value) || !array_is_list($value)) {
            return false;
        }
        foreach ($value as $item) {
            if (!is_string($item) || !$this->isSafeString($item)) {
                return false;
            }
        }
        return true;
    }

    private function isIntList(mixed $value): bool
    {
        if (!is_array($value) || !array_is_list($value)) {
            return false;
        }
        foreach ($value as $item) {
            if (!is_int($item)) {
                return false;
            }
        }
        return true;
    }
}
