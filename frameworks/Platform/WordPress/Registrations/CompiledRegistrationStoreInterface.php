<?php

declare(strict_types=1);

namespace WPEssential\Platform\WordPress\Registrations;

interface CompiledRegistrationStoreInterface
{
    public function active(): ?CompiledRegistrationManifest;

    public function publish(CompiledRegistrationManifest $manifest): void;
}
