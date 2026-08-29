<?php

declare(strict_types=1);

namespace WPEssential\Platform\WordPress\Registrations;

use RuntimeException;

final class InMemoryCompiledRegistrationStore implements CompiledRegistrationStoreInterface
{
    private ?CompiledRegistrationManifest $active = null;

    public function active(): ?CompiledRegistrationManifest
    {
        return $this->active;
    }

    public function publish(CompiledRegistrationManifest $manifest): void
    {
        if ($this->active !== null && $manifest->generation <= $this->active->generation) {
            throw new RuntimeException('Compiled registration generation must increase monotonically.');
        }
        $this->active = $manifest;
    }
}
