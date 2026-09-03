<?php

declare(strict_types=1);

namespace WPEssential\Modules\Query;

if (!defined('ABSPATH')) {
    exit;
}

final readonly class QueryExecutionResult
{
    /**
     * @param list<array<string,mixed>> $rows
     * @param list<string> $projection
     */
    public function __construct(
        public string $provider,
        public string $sourceRef,
        public array $projection,
        public array $rows,
        public int $returned,
    ) {
    }
}
