<?php

declare(strict_types=1);

namespace WPEssential\Modules\Roles;

if (!defined('ABSPATH')) {
    exit;
}

interface RoleRuntimeEnvironmentInterface
{
    public function available(): bool;

    public function siteExists(int $siteId): bool;

    public function networkIdForSite(int $siteId): ?int;

    public function isMultisite(): bool;

    /**
     * @return array<string,array{name:string,capabilities:array<string,bool>}>
     */
    public function rolesForSite(int $siteId): array;
}
