<?php

declare(strict_types=1);

namespace WPEssential\Modules\Query;

if (!defined('ABSPATH')) {
    exit;
}

final readonly class QueryFieldResolution
{
    public function __construct(
        public QueryDefinition $definition,
        public bool $shortCircuitEmpty,
    ) {
    }
}
