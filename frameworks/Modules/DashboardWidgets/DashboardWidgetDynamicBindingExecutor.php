<?php

declare(strict_types=1);

namespace WPEssential\Modules\DashboardWidgets;

if (!defined('ABSPATH')) {
    exit;
}

use RuntimeException;
use WPEssential\Contracts\DynamicValueResolverInterface;
use WPEssential\Platform\Auth\ExecutionContext;
use WPEssential\Platform\DynamicValues\DynamicValueRequest;

final readonly class DashboardWidgetDynamicBindingExecutor
{
    public function __construct(private DynamicValueResolverInterface $dynamicValues) {}

    public function resolve(
        DashboardWidgetRenderSourceDescriptor $renderSource,
        ExecutionContext $context,
    ): DashboardWidgetRenderSourceDescriptor {
        if ($renderSource->dynamicBindings === []) {
            return $renderSource;
        }
        if ($renderSource->query !== null) {
            throw new RuntimeException('Dashboard Widget Query bindings must resolve before Dynamic bindings.');
        }

        $bindings = $renderSource->bindings;
        foreach ($renderSource->dynamicBindings as $bindingKey => $binding) {
            $resourceId = match ($binding['resource']) {
                'site' => $context->siteId,
                'user' => $context->principal->userId
                    ?? throw new RuntimeException('Dashboard Widget Dynamic user resource requires an authenticated principal.'),
                'network' => $context->networkId
                    ?? throw new RuntimeException('Dashboard Widget Dynamic network resource requires a network context.'),
                default => throw new RuntimeException('Dashboard Widget Dynamic resource is unsupported.'),
            };

            $result = $this->dynamicValues->resolve(
                new DynamicValueRequest(
                    sourceRef: $binding['source_ref'],
                    valueRef: $binding['value_ref'],
                    resourceType: $binding['resource'],
                    resourceId: $resourceId,
                ),
                $context,
            );

            if (!$result->resolved || $result->failure !== null || $result->value === null) {
                throw new RuntimeException('Dashboard Widget Dynamic value could not be resolved.');
            }

            $this->assertValueMatchesType($result->value, $binding['binding_type']);
            $this->assertSafeValue($result->value);
            /** @var scalar|list<scalar> $value */
            $value = $result->value;
            $bindings[$bindingKey] = $value;
        }

        ksort($bindings, SORT_STRING);

        return $renderSource->resolvedDynamicWith($bindings);
    }

    private function assertValueMatchesType(mixed $value, string $type): void
    {
        $valid = match ($type) {
            'string' => is_string($value),
            'int' => is_int($value),
            'float' => is_float($value),
            'bool' => is_bool($value),
            'string_list' => $this->isStringList($value),
            'int_list' => $this->isIntList($value),
            default => false,
        };

        if (!$valid) {
            throw new RuntimeException('Dashboard Widget Dynamic value does not match the Blueprint binding type.');
        }
    }

    private function assertSafeValue(mixed $value): void
    {
        if (is_string($value) && preg_match('/<\?(?:php|=)|<script\b|javascript:/i', $value)) {
            throw new RuntimeException('Dashboard Widget Dynamic value contains an executable string marker.');
        }
        if (is_array($value)) {
            foreach ($value as $item) {
                if (is_string($item) && preg_match('/<\?(?:php|=)|<script\b|javascript:/i', $item)) {
                    throw new RuntimeException('Dashboard Widget Dynamic value list contains an executable string marker.');
                }
            }
        }
    }

    private function isStringList(mixed $value): bool
    {
        if (!is_array($value) || !array_is_list($value)) {
            return false;
        }
        foreach ($value as $item) {
            if (!is_string($item)) {
                return false;
            }
        }
        return true;
    }

    private function isIntList(mixed $value): bool
    {
        if (!is_array($value) || !array_is_list($value)) {
            return false;
        }
        foreach ($value as $item) {
            if (!is_int($item)) {
                return false;
            }
        }
        return true;
    }
}
