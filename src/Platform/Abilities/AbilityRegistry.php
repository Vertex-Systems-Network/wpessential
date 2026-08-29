<?php

declare(strict_types=1);

namespace WPEssential\Platform\Abilities;

use RuntimeException;
use WPEssential\Contracts\AbilityHandlerInterface;
use WPEssential\Platform\Auth\AuthorizationRequest;
use WPEssential\Platform\Auth\ExecutionContext;
use WPEssential\Platform\Auth\PolicyEngine;

final class AbilityRegistry
{
    /** @var array<string, array{descriptor: AbilityDescriptor, handler: AbilityHandlerInterface}> */
    private array $abilities = [];

    public function __construct(private readonly PolicyEngine $policy) {}

    public function register(AbilityDescriptor $descriptor, AbilityHandlerInterface $handler): void
    {
        if (isset($this->abilities[$descriptor->name])) {
            throw new RuntimeException(sprintf('Ability "%s" is already registered.', $descriptor->name));
        }
        $this->abilities[$descriptor->name] = ['descriptor' => $descriptor, 'handler' => $handler];
    }

    public function descriptor(string $name): ?AbilityDescriptor
    {
        return $this->abilities[$name]['descriptor'] ?? null;
    }

    /** @param array<string, mixed> $input */
    public function execute(string $name, array $input, ExecutionContext $context): mixed
    {
        $entry = $this->abilities[$name] ?? null;
        if ($entry === null) {
            throw new RuntimeException(sprintf('Ability "%s" is not registered.', $name));
        }

        $descriptor = $entry['descriptor'];
        if (!$descriptor->allows($context->channel)) {
            throw new RuntimeException(sprintf('Ability "%s" is not exposed to channel "%s".', $name, $context->channel->value));
        }

        $decision = $this->policy->authorize(new AuthorizationRequest(
            context: $context,
            ability: $descriptor->name,
            capability: $descriptor->capability,
        ));
        if (!$decision->allowed) {
            throw new RuntimeException(sprintf('Ability "%s" denied: %s.', $name, $decision->reason));
        }

        return $entry['handler']->handle($input, $context);
    }
}
