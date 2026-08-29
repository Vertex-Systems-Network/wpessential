<?php

declare(strict_types=1);

namespace WPEssential\Contracts;


if (!defined('ABSPATH')) {
    exit;
}

use WPEssential\Platform\Modules\ModuleManifest;

interface ModuleInterface
{
    public function manifest(): ModuleManifest;

    public function register(ServiceRegistryInterface $services): void;

    public function boot(ServiceRegistryInterface $services): void;
}
