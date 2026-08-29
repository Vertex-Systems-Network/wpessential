<?php

declare(strict_types=1);

namespace WPEssential\Platform\WordPress\Ajax;

if (!defined('ABSPATH')) {
    exit;
}

use RuntimeException;

final class AjaxAuthorizationException extends RuntimeException
{
    public function __construct(public readonly string $reason)
    {
        parent::__construct('AJAX ability authorization denied.');
    }
}
