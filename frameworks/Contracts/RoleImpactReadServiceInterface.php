<?php

declare(strict_types=1);

namespace WPEssential\Contracts;

if (!defined('ABSPATH')) {
    exit;
}

use WPEssential\Platform\Auth\ExecutionContext;

/**
 * Optional cross-package read seam for canonical Surface 30 role-impact truth.
 *
 * Free consumers must tolerate this service being absent when WPEssential Pro
 * is not installed or active; they must not inspect the WordPress role store
 * directly as a private fallback.
 */
interface RoleImpactReadServiceInterface
{
    public const SERVICE_ID = 'module.roles.read-service';

    /** @return array<string,mixed> */
    public function capabilityImpact(string $capability, ExecutionContext $context): array;
}
