<?php

declare(strict_types=1);

namespace WPEssential\Modules\Settings;

if (!defined('ABSPATH')) {
    exit;
}

use InvalidArgumentException;
use WPEssential\Platform\Definitions\Definition;

final class SettingsDefinition
{
    public const TYPE = 'settings';
    public const OWNER_SURFACE_ID = 12;

    public static function assertOwned(Definition $definition): void
    {
        if ($definition->ownerSurfaceId !== self::OWNER_SURFACE_ID) {
            throw new InvalidArgumentException('Settings definition owner surface is invalid.');
        }

        if ($definition->type !== self::TYPE) {
            throw new InvalidArgumentException('Settings definition type is invalid.');
        }
    }

    /**
     * @return array{
     *   id:string,
     *   slug:string,
     *   type:string,
     *   schema_version:int,
     *   owner_surface_id:int,
     *   status:string,
     *   revision:int,
     *   dependencies:list<string>,
     *   payload:array<string,mixed>
     * }
     */
    public static function view(Definition $definition): array
    {
        self::assertOwned($definition);

        return [
            'id' => $definition->id,
            'slug' => $definition->slug,
            'type' => $definition->type,
            'schema_version' => $definition->schemaVersion,
            'owner_surface_id' => $definition->ownerSurfaceId,
            'status' => $definition->status->value,
            'revision' => $definition->revision,
            'dependencies' => $definition->dependencies,
            'payload' => $definition->payload,
        ];
    }
}
