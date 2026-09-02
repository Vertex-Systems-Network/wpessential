<?php

declare(strict_types=1);

namespace WPEssential\Modules\Query;

if (!defined('ABSPATH')) {
    exit;
}

final readonly class QueryOrderClause
{
    public function __construct(
        public string $fieldRef,
        public string $direction,
    ) {
    }
}
