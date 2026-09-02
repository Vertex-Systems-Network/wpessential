<?php

declare(strict_types=1);

namespace WPEssential\Modules\Query;

if (!defined('ABSPATH')) {
    exit;
}

use RuntimeException;

final class QueryPlanningException extends RuntimeException
{
    public function __construct(
        public readonly string $errorCode,
        public readonly string $path,
        string $message,
    ) {
        parent::__construct($message);
    }
}
