<?php

declare(strict_types=1);

namespace WPEssential\Contracts;

use WPEssential\Platform\Auth\ExecutionContext;

interface CapabilityCheckerInterface
{
    public function can(ExecutionContext $context, string $capability): bool;
}
