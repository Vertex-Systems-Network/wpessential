<?php

declare(strict_types=1);

namespace WPEssential\Platform\WordPress\Registrations;


if (!defined('ABSPATH')) {
    exit;
}

interface CompiledRegistrationStoreInterface
{
    public function active(): ?CompiledRegistrationManifest;

    public function publish(CompiledRegistrationManifest $manifest): void;
}
