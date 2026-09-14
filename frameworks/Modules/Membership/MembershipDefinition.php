<?php

declare(strict_types=1);

namespace WPEssential\Modules\Membership;

if (!defined('ABSPATH')) {
    exit;
}

use InvalidArgumentException;
use WPEssential\Platform\Definitions\Definition;

final class MembershipDefinition
{
    public const TYPE = 'membership';
    public const OWNER_SURFACE_ID = 15;

    public static function assertOwned(Definition $definition): void
    {
        if ($definition->ownerSurfaceId !== self::OWNER_SURFACE_ID) {
            throw new InvalidArgumentException('Membership definition owner surface is invalid.');
        }
        if ($definition->type !== self::TYPE) {
            throw new InvalidArgumentException('Membership definition type is invalid.');
        }
    }

    /** @return array<string,mixed> */
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
