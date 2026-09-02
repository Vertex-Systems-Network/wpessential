<?php

declare(strict_types=1);

namespace WPEssential\Modules\Query;

if (!defined('ABSPATH')) {
    exit;
}

final readonly class QueryPredicate
{
    /**
     * @param array<string,mixed> $payload
     * @param list<QueryPredicate> $children
     */
    public function __construct(
        public QueryPredicateType $type,
        public array $payload = [],
        public array $children = [],
    ) {
    }
}
