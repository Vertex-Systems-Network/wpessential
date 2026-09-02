<?php

declare(strict_types=1);

namespace WPEssential\Modules\Query;

if (!defined('ABSPATH')) {
    exit;
}

final readonly class QueryValidationIssue
{
    public function __construct(
        public string $code,
        public string $path,
        public string $message,
    ) {
    }
}
