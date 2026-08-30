<?php

declare(strict_types=1);

namespace WPEssential\Platform\WordPress\Ajax;


if (!defined('ABSPATH')) {
    exit;
}

interface AjaxHandlerInterface
{
    /** @param array<string,mixed> $payload */
    public function handle(array $payload): mixed;
}
