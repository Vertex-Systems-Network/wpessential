<?php

declare(strict_types=1);

namespace WPEssential\Platform\WordPress\Abilities;


if (!defined('ABSPATH')) {
    exit;
}

use InvalidArgumentException;

final class WordPressAbilityNameMapper
{
    public function map(string $internalName): string
    {
        if (!preg_match('#^wpessential/([a-z0-9][a-z0-9-]*)/([a-z0-9][a-z0-9-]*)$#', $internalName, $matches)) {
            throw new InvalidArgumentException('Internal WPE ability name cannot be mapped to WordPress Abilities API.');
        }

        return 'wpessential/' . $matches[1] . '-' . $matches[2];
    }
}
