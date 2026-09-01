<?php

declare(strict_types=1);

namespace WPEssential\Platform\Modules;

if (!defined('ABSPATH')) {
    exit;
}

use WPEssential\Contracts\ModuleActivationPolicyInterface;

final readonly class DefaultModuleActivationPolicy implements ModuleActivationPolicyInterface
{
    public function allows(ModuleManifest $manifest): bool
    {
        return $manifest->edition === 'free';
    }
}
