<?php

declare(strict_types=1);

namespace WPEssential\Contracts;


if (!defined('ABSPATH')) {
    exit;
}

use WPEssential\Platform\Auth\ExecutionContext;

interface CapabilityCheckerInterface
{
    public function can(ExecutionContext $context, string $capability): bool;
}
