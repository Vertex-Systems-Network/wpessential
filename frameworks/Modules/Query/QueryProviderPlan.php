<?php

declare(strict_types=1);

namespace WPEssential\Modules\Query;

if (!defined('ABSPATH')) {
    exit;
}

final readonly class QueryProviderPlan
{
    /**
     * @param array<string,mixed> $arguments
     * @param list<string> $projection
     */
    public function __construct(
        public string $provider,
        public string $sourceRef,
        public array $arguments,
        public array $projection,
    ) {
    }
}
