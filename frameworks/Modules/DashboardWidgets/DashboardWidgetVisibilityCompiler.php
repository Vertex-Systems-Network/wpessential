<?php

declare(strict_types=1);

namespace WPEssential\Modules\DashboardWidgets;

if (!defined('ABSPATH')) {
    exit;
}

use InvalidArgumentException;
use WPEssential\Platform\Definitions\Definition;
use WPEssential\Platform\Definitions\DefinitionStatus;

final readonly class DashboardWidgetVisibilityCompiler
{
    public const SCHEMA_VERSION = 1;

    /** @var list<string> */
    private const VISIBILITY_KEYS = ['roles', 'capabilities', 'users'];

    public function compile(Definition $definition): DashboardWidgetVisibilityDescriptor
    {
        DashboardWidgetDefinition::assertOwned($definition);

        if ($definition->schemaVersion !== self::SCHEMA_VERSION) {
            throw new InvalidArgumentException('Dashboard Widget definition schema version is unsupported for visibility compilation.');
        }
        if ($definition->status !== DefinitionStatus::Published) {
            throw new InvalidArgumentException('Only Published Dashboard Widget definitions may compile visibility metadata.');
        }

        $payload = $definition->payload;
        if (array_is_list($payload)) {
            throw new InvalidArgumentException('Dashboard Widget definition payload must be an object/map.');
        }

        $widget = $payload['widget'] ?? null;
        if (!is_array($widget) || ($widget !== [] && array_is_list($widget))) {
            throw new InvalidArgumentException('Dashboard Widget metadata must be an object/map.');
        }

        $visibility = $widget['visibility'] ?? [];
        if (!is_array($visibility) || ($visibility !== [] && array_is_list($visibility))) {
            throw new InvalidArgumentException('Dashboard Widget visibility metadata must be an object/map.');
        }

        foreach (array_keys($visibility) as $key) {
            if (!is_string($key) || !in_array($key, self::VISIBILITY_KEYS, true)) {
                throw new InvalidArgumentException('Dashboard Widget visibility metadata contains an unsupported key.');
            }
        }

        return new DashboardWidgetVisibilityDescriptor(
            definitionId: $definition->id,
            revision: $definition->revision,
            roles: $this->machineKeys(
                $visibility['roles'] ?? [],
                DashboardWidgetVisibilityDescriptor::MAX_ROLES,
                'roles',
            ),
            capabilities: $this->machineKeys(
                $visibility['capabilities'] ?? [],
                DashboardWidgetVisibilityDescriptor::MAX_CAPABILITIES,
                'capabilities',
            ),
            users: $this->userIds($visibility['users'] ?? []),
        );
    }

    /**
     * @return list<string>
     */
    private function machineKeys(mixed $value, int $maximum, string $label): array
    {
        if (!is_array($value) || !array_is_list($value) || count($value) > $maximum) {
            throw new InvalidArgumentException(sprintf('Dashboard Widget visibility %s must be a bounded ordered list.', $label));
        }

        $result = [];
        $seen = [];
        foreach ($value as $item) {
            if (!is_string($item) || preg_match('/^[a-z0-9][a-z0-9_-]{0,63}$/', $item) !== 1) {
                throw new InvalidArgumentException(sprintf('Dashboard Widget visibility %s contains a malformed key.', $label));
            }
            if (isset($seen[$item])) {
                throw new InvalidArgumentException(sprintf('Dashboard Widget visibility %s must contain unique keys.', $label));
            }
            $seen[$item] = true;
            $result[] = $item;
        }

        return $result;
    }

    /**
     * @return list<int>
     */
    private function userIds(mixed $value): array
    {
        if (!is_array($value) || !array_is_list($value) || count($value) > DashboardWidgetVisibilityDescriptor::MAX_USERS) {
            throw new InvalidArgumentException('Dashboard Widget visibility users must be a bounded ordered list.');
        }

        $result = [];
        $seen = [];
        foreach ($value as $item) {
            if (!is_int($item) || $item < 1) {
                throw new InvalidArgumentException('Dashboard Widget visibility users must contain positive WordPress user ids.');
            }
            if (isset($seen[$item])) {
                throw new InvalidArgumentException('Dashboard Widget visibility users must contain unique ids.');
            }
            $seen[$item] = true;
            $result[] = $item;
        }

        return $result;
    }
}
