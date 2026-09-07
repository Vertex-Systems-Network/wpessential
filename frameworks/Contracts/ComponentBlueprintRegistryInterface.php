<?php

declare(strict_types=1);

namespace WPEssential\Contracts;

if (!defined('ABSPATH')) {
    exit;
}

use WPEssential\Platform\Components\ComponentBlueprintDescriptor;

/** Shared lookup boundary for canonical component blueprints. */
interface ComponentBlueprintRegistryInterface
{
    public const CONTRACT_VERSION = 1;

    public function get(string $blueprintId, int $revision): ?ComponentBlueprintDescriptor;
}
