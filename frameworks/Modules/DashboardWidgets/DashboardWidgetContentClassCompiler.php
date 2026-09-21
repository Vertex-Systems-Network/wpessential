<?php

declare(strict_types=1);

namespace WPEssential\Modules\DashboardWidgets;

if (!defined('ABSPATH')) {
    exit;
}

use InvalidArgumentException;
use WPEssential\Platform\Definitions\Definition;
use WPEssential\Platform\Definitions\DefinitionStatus;

final readonly class DashboardWidgetContentClassCompiler
{
    public const SCHEMA_VERSION = 1;

    public function compile(Definition $definition): DashboardWidgetContentClassDescriptor
    {
        DashboardWidgetDefinition::assertOwned($definition);

        if ($definition->schemaVersion !== self::SCHEMA_VERSION) {
            throw new InvalidArgumentException('Dashboard Widget definition schema version is unsupported for content-class compilation.');
        }
        if ($definition->status !== DefinitionStatus::Published) {
            throw new InvalidArgumentException('Only Published Dashboard Widget definitions may compile trusted content classes.');
        }

        $payload = $definition->payload;
        if (array_is_list($payload)) {
            throw new InvalidArgumentException('Dashboard Widget definition payload must be an object/map.');
        }

        $widget = $payload['widget'] ?? null;
        if (!is_array($widget) || array_is_list($widget)) {
            throw new InvalidArgumentException('Dashboard Widget metadata must be an object/map for content-class compilation.');
        }

        $contentType = $widget['type'] ?? null;
        if (
            !is_string($contentType)
            || preg_match('/^[a-z0-9][a-z0-9_-]{0,63}$/', $contentType) !== 1
        ) {
            throw new InvalidArgumentException('Dashboard Widget type must be bounded canonical machine text.');
        }

        if (!in_array($contentType, DashboardWidgetContentClassDescriptor::TRUSTED_TYPES, true)) {
            throw new InvalidArgumentException('Dashboard Widget type is not trusted for content-class V1.');
        }

        return new DashboardWidgetContentClassDescriptor(
            definitionId: $definition->id,
            revision: $definition->revision,
            contentType: $contentType,
        );
    }
}
