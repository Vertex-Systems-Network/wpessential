<?php

declare(strict_types=1);

namespace WPEssential\Contracts;

if (!defined('ABSPATH')) {
    exit;
}

use WPEssential\Platform\Auth\ExecutionContext;

interface RoleImpactReadServiceInterface
{
    public const SERVICE_ID = 'module.roles.read-service';

    /** @return array<string,mixed> */
    public function capabilityImpact(string $capability, ExecutionContext $context): array;
}
