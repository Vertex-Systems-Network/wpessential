<?php

declare(strict_types=1);

namespace WPEssential\Platform\Abilities;


if (!defined('ABSPATH')) {
    exit;
}

use RuntimeException;
use WPEssential\Contracts\AbilityHandlerInterface;
use WPEssential\Contracts\InputAuthorizingAbilityHandlerInterface;
use WPEssential\Platform\Auth\AuthorizationRequest;
use WPEssential\Platform\Auth\ExecutionContext;
use WPEssential\Platform\Auth\PolicyDecision;
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

    /** @return list<AbilityDescriptor> */
    public function descriptors(): array
    {
        return array_values(array_map(
            static fn (array $entry): AbilityDescriptor => $entry['descriptor'],
            $this->abilities,
        ));
    }

    /** @param array<string,mixed> $input */
    public function authorize(string $name, ExecutionContext $context, array $input = []): PolicyDecision
    {
        $entry = $this->abilities[$name] ?? null;
        if ($entry === null) {
            return PolicyDecision::deny('ability_not_registered');
        }

        $descriptor = $entry['descriptor'];
        if (!$descriptor->allows($context->channel)) {
            return PolicyDecision::deny('channel_not_exposed');
        }

        $decision = $this->policy->authorize(new AuthorizationRequest(
            context: $context,
            ability: $descriptor->name,
            capability: $descriptor->capability,
        ));
        if (!$decision->allowed) {
            return $decision;
        }

        $handler = $entry['handler'];
        if ($handler instanceof InputAuthorizingAbilityHandlerInterface) {
            return $handler->authorizeInput($input, $context);
        }

        return $decision;
    }

    /** @param array<string, mixed> $input */
    public function execute(string $name, array $input, ExecutionContext $context): mixed
    {
        $entry = $this->abilities[$name] ?? null;
        if ($entry === null) {
            throw new RuntimeException(sprintf('Ability "%s" is not registered.', $name));
        }

        $decision = $this->authorize($name, $context, $input);
        if (!$decision->allowed) {
            throw new RuntimeException(sprintf('Ability "%s" denied: %s.', $name, $decision->reason));
        }

        return $entry['handler']->handle($input, $context);
    }
}
