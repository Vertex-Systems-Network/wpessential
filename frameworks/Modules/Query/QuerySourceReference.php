<?php

declare(strict_types=1);

namespace WPEssential\Modules\Query;

if (!defined('ABSPATH')) {
    exit;
}

final readonly class QuerySourceReference
{
    public function __construct(
        public string $sourceRef,
        public string $sourceType,
        public int $capabilityVersion,
    ) {
    }
}
