<?php

declare(strict_types=1);

namespace WPEssential\Contracts;

if (!defined('ABSPATH')) {
    exit;
}

use WPEssential\Platform\Modules\ModuleManifest;

interface ModuleActivationPolicyInterface
{
    public function allows(ModuleManifest $manifest): bool;
}
