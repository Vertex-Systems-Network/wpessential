<?php

declare(strict_types=1);

namespace WPEssential\Modules\Chat;

if (!defined('ABSPATH')) {
    exit;
}

use InvalidArgumentException;
use WPEssential\Platform\Definitions\Definition;

final class ConversationDefinition
{
    public const TYPE = 'conversation';
    public const OWNER_SURFACE_ID = 21;

    public static function assertOwned(Definition $definition): void
    {
        if ($definition->ownerSurfaceId !== self::OWNER_SURFACE_ID) {
            throw new InvalidArgumentException('Conversation definition owner surface is invalid.');
        }
        if ($definition->type !== self::TYPE) {
            throw new InvalidArgumentException('Conversation definition type is invalid.');
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
