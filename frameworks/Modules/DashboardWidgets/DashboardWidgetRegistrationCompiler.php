<?php

declare(strict_types=1);

namespace WPEssential\Modules\DashboardWidgets;

if (!defined('ABSPATH')) {
    exit;
}

use InvalidArgumentException;
use WPEssential\Platform\Definitions\Definition;
use WPEssential\Platform\Definitions\DefinitionStatus;

final readonly class DashboardWidgetRegistrationCompiler
{
    public const SCHEMA_VERSION = 1;

    /** @var list<string> */
    private const PAYLOAD_KEYS = ['widget'];

    /** @var list<string> */
    private const WIDGET_KEYS = ['key', 'title', 'context', 'priority', 'network_dashboard'];

    public function compile(Definition $definition): DashboardWidgetRegistrationDescriptor
    {
        DashboardWidgetDefinition::assertOwned($definition);

        if ($definition->schemaVersion !== self::SCHEMA_VERSION) {
            throw new InvalidArgumentException('Dashboard Widget definition schema version is unsupported.');
        }
        if ($definition->status !== DefinitionStatus::Published) {
            throw new InvalidArgumentException('Only Published Dashboard Widget definitions may compile for registration.');
        }

        $payload = $definition->payload;
        if (array_is_list($payload)) {
            throw new InvalidArgumentException('Dashboard Widget definition payload must be an object/map.');
        }
        $this->assertKnownKeys($payload, self::PAYLOAD_KEYS, 'Dashboard Widget definition payload');

        $widget = $payload['widget'] ?? null;
        if (!is_array($widget) || array_is_list($widget)) {
            throw new InvalidArgumentException('Dashboard Widget registration metadata must be an object/map.');
        }
        $this->assertKnownKeys($widget, self::WIDGET_KEYS, 'Dashboard Widget registration metadata');

        $key = $widget['key'] ?? null;
        if (!is_string($key) || !preg_match('/^[a-z0-9][a-z0-9_-]{0,63}$/', $key)) {
            throw new InvalidArgumentException('Dashboard Widget key must be bounded canonical WordPress-safe text.');
        }

        $title = $widget['title'] ?? null;
        if (
            !is_string($title)
            || $title === ''
            || strlen($title) > 160
            || str_contains($title, '<')
            || str_contains($title, '>')
            || preg_match('/[\x00-\x08\x0B\x0C\x0E-\x1F\x7F]/', $title)
        ) {
            throw new InvalidArgumentException('Dashboard Widget title must be bounded plain text.');
        }

        $context = $widget['context'] ?? null;
        if (!is_string($context) || !in_array($context, DashboardWidgetRegistrationDescriptor::CONTEXTS, true)) {
            throw new InvalidArgumentException('Dashboard Widget context is unsupported.');
        }

        $priority = $widget['priority'] ?? null;
        if (!is_string($priority) || !in_array($priority, DashboardWidgetRegistrationDescriptor::PRIORITIES, true)) {
            throw new InvalidArgumentException('Dashboard Widget priority is unsupported.');
        }

        $networkDashboard = $widget['network_dashboard'] ?? false;
        if (!is_bool($networkDashboard)) {
            throw new InvalidArgumentException('Dashboard Widget network_dashboard target must be boolean.');
        }

        return new DashboardWidgetRegistrationDescriptor(
            definitionId: $definition->id,
            revision: $definition->revision,
            key: $key,
            title: $title,
            context: $context,
            priority: $priority,
            networkDashboard: $networkDashboard,
        );
    }

    /**
     * @param array<string,mixed> $value
     * @param list<string> $allowed
     */
    private function assertKnownKeys(array $value, array $allowed, string $label): void
    {
        foreach (array_keys($value) as $key) {
            if (!is_string($key) || !in_array($key, $allowed, true)) {
                throw new InvalidArgumentException($label . ' contains an unsupported key.');
            }
        }
    }
}
