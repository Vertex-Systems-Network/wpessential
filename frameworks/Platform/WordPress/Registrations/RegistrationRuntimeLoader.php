<?php

declare(strict_types=1);

namespace WPEssential\Platform\WordPress\Registrations;

final class RegistrationRuntimeLoader
{
    public function __construct(private readonly CompiledRegistrationStoreInterface $store) {}

    /** @return array<string,array<string,array<string,mixed>>> */
    public function activeEntries(): array
    {
        return $this->store->active()?->entries ?? [];
    }

    /** @return array<string,array<string,mixed>> */
    public function forKind(RegistrationKind $kind): array
    {
        return $this->activeEntries()[$kind->value] ?? [];
    }
}
