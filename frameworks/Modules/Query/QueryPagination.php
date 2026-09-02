<?php

declare(strict_types=1);

namespace WPEssential\Modules\Query;

if (!defined('ABSPATH')) {
    exit;
}

final readonly class QueryPagination
{
    public function __construct(
        public string $mode,
        public int $pageSize,
        public int $offset = 0,
        public ?string $cursor = null,
    ) {
    }
}
