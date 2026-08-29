<?php

declare(strict_types=1);

namespace WPEssential\Platform\Integrations;


if (!defined('ABSPATH')) {
    exit;
}

use RuntimeException;

final class IntegrationRegistry
{
    /** @var array<string, IntegrationDescriptor> */
    private array $integrations = [];

    public function register(IntegrationDescriptor $integration): void
    {
        if (isset($this->integrations[$integration->key])) {
            throw new RuntimeException(sprintf('Integration "%s" is already registered.', $integration->key));
        }
        $this->integrations[$integration->key] = $integration;
    }

    public function get(string $key): IntegrationDescriptor
    {
        return $this->integrations[$key] ?? throw new RuntimeException(sprintf('Unknown integration "%s".', $key));
    }

    /** @return list<IntegrationDescriptor> */
    public function supporting(string $capability): array
    {
        return array_values(array_filter(
            $this->integrations,
            static fn (IntegrationDescriptor $integration): bool => $integration->supports($capability),
        ));
    }
}
