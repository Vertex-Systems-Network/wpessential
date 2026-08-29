<?php

declare(strict_types=1);

namespace WPEssential\Platform\Definitions;


if (!defined('ABSPATH')) {
    exit;
}

use RuntimeException;
use WPEssential\Platform\Database\DatabaseAdapterInterface;

final readonly class DefinitionTableNames
{
    public string $definitions;
    public string $dependencies;

    public function __construct(DatabaseAdapterInterface $database)
    {
        $prefix = $database->networkTablePrefix();
        if (preg_match('/^[A-Za-z0-9_]+$/', $prefix) !== 1) {
            throw new RuntimeException('Definition persistence table prefix is invalid.');
        }

        $this->definitions = $prefix . 'wpe_definitions';
        $this->dependencies = $prefix . 'wpe_definition_dependencies';
    }
}
