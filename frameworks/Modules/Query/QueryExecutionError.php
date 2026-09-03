<?php

declare(strict_types=1);

namespace WPEssential\Modules\Query;

if (!defined('ABSPATH')) {
    exit;
}

final readonly class QueryExecutionError
{
    public function __construct(
        public string $errorCode,
        public string $path,
        public string $message,
    ) {
    }
}
