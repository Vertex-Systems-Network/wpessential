<?php

declare(strict_types=1);

namespace WPEssential\Platform\WordPress\Registrations;

if (!defined('ABSPATH')) {
    exit;
}

use InvalidArgumentException;
use RuntimeException;

final class RegistrationDefinitionProviderRegistry
{
    /** @var array<string, RegistrationDefinitionProviderInterface> */
    private array $providers = [];

    public function register(RegistrationDefinitionProviderInterface $provider): void
    {
        $id = $provider->id();
        if (preg_match('/^[a-z0-9][a-z0-9-]*$/', $id) !== 1) {
            throw new InvalidArgumentException('Registration provider id must be a lowercase slug.');
        }
        if (isset($this->providers[$id])) {
            throw new RuntimeException(sprintf('Registration provider "%s" is already registered.', $id));
        }

        $this->providers[$id] = $provider;
    }

    /** @return iterable<RegistrationDefinition> */
    public function definitions(): iterable
    {
        ksort($this->providers, SORT_STRING);
        foreach ($this->providers as $provider) {
            foreach ($provider->definitions() as $definition) {
                if (!$definition instanceof RegistrationDefinition) {
                    throw new RuntimeException(sprintf(
                        'Registration provider "%s" returned an invalid definition.',
                        $provider->id(),
                    ));
                }
                yield $definition;
            }
        }
    }
}
