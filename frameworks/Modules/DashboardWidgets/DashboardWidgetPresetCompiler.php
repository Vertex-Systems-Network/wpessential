<?php

declare(strict_types=1);

namespace WPEssential\Modules\DashboardWidgets;

if (!defined('ABSPATH')) {
    exit;
}

use InvalidArgumentException;
use WPEssential\Contracts\DefinitionRepositoryInterface;
use WPEssential\Platform\Definitions\Definition;
use WPEssential\Platform\Definitions\DefinitionStatus;

final readonly class DashboardWidgetPresetCompiler
{
    /** @var list<string> */
    private const PRESET_KEYS = ['assignment', 'label', 'widget_definition_ids'];

    /** @var list<string> */
    private const ASSIGNMENT_KEYS = ['network_default', 'roles'];

    public function __construct(private DefinitionRepositoryInterface $definitions) {}

    public function compile(Definition $definition): DashboardWidgetPresetDescriptor
    {
        DashboardWidgetPresetDefinition::assertOwned($definition);
        if ($definition->schemaVersion !== 1) {
            throw new InvalidArgumentException('Dashboard Widget preset schema version must equal 1.');
        }

        $payload = $definition->payload;
        if (array_keys($payload) !== ['preset']) {
            throw new InvalidArgumentException('Dashboard Widget preset payload must contain only the preset object.');
        }
        $preset = $payload['preset'] ?? null;
        if (!is_array($preset) || array_is_list($preset)) {
            throw new InvalidArgumentException('Dashboard Widget preset payload must be an object/map.');
        }
        $this->assertKnownKeys($preset, self::PRESET_KEYS, 'Dashboard Widget preset');
        foreach (['label', 'widget_definition_ids'] as $required) {
            if (!array_key_exists($required, $preset)) {
                throw new InvalidArgumentException('Dashboard Widget preset is missing a required field.');
            }
        }

        $label = $preset['label'];
        if (!is_string($label)) {
            throw new InvalidArgumentException('Dashboard Widget preset label must be text.');
        }

        $widgetIds = $preset['widget_definition_ids'];
        if (!is_array($widgetIds) || !array_is_list($widgetIds)) {
            throw new InvalidArgumentException('Dashboard Widget preset widget_definition_ids must be a list.');
        }

        $normalizedWidgetIds = [];
        $seen = [];
        foreach ($widgetIds as $id) {
            if (
                !is_string($id)
                || preg_match('/^[0-9a-f]{8}-[0-9a-f]{4}-[1-5][0-9a-f]{3}-[89ab][0-9a-f]{3}-[0-9a-f]{12}$/', $id) !== 1
                || isset($seen[$id])
            ) {
                throw new InvalidArgumentException('Dashboard Widget preset widget references must be unique lowercase RFC 4122 UUIDs.');
            }

            $widget = $this->definitions->get($id);
            if (
                !$widget instanceof Definition
                || $widget->ownerSurfaceId !== DashboardWidgetDefinition::OWNER_SURFACE_ID
                || $widget->type !== DashboardWidgetDefinition::TYPE
                || $widget->status !== DefinitionStatus::Published
            ) {
                throw new InvalidArgumentException('Dashboard Widget preset references must resolve to Published Surface-10 Dashboard Widget definitions.');
            }

            $normalizedWidgetIds[] = $id;
            $seen[$id] = true;
        }

        $roles = [];
        $networkDefault = false;
        if (array_key_exists('assignment', $preset)) {
            $assignment = $preset['assignment'];
            if (!is_array($assignment) || ($assignment !== [] && array_is_list($assignment))) {
                throw new InvalidArgumentException('Dashboard Widget preset assignment must be an object/map.');
            }
            $this->assertKnownKeys($assignment, self::ASSIGNMENT_KEYS, 'Dashboard Widget preset assignment');

            if (array_key_exists('roles', $assignment)) {
                if (!is_array($assignment['roles']) || !array_is_list($assignment['roles'])) {
                    throw new InvalidArgumentException('Dashboard Widget preset assignment roles must be a list.');
                }
                foreach ($assignment['roles'] as $role) {
                    if (!is_string($role) || preg_match('/^[a-z0-9][a-z0-9_-]{0,63}$/', $role) !== 1) {
                        throw new InvalidArgumentException('Dashboard Widget preset assignment role references must be canonical role slugs.');
                    }
                    $roles[] = $role;
                }
                $roles = array_values(array_unique($roles));
                sort($roles, SORT_STRING);
            }

            if (array_key_exists('network_default', $assignment)) {
                if (!is_bool($assignment['network_default'])) {
                    throw new InvalidArgumentException('Dashboard Widget preset assignment network_default must be boolean.');
                }
                $networkDefault = $assignment['network_default'];
            }
        }

        return new DashboardWidgetPresetDescriptor(
            definitionId: $definition->id,
            revision: $definition->revision,
            label: $label,
            widgetDefinitionIds: $normalizedWidgetIds,
            roles: $roles,
            networkDefault: $networkDefault,
        );
    }

    /** @param array<string,mixed> $value @param list<string> $allowed */
    private function assertKnownKeys(array $value, array $allowed, string $label): void
    {
        foreach (array_keys($value) as $key) {
            if (!is_string($key) || !in_array($key, $allowed, true)) {
                throw new InvalidArgumentException(sprintf('%s contains an unsupported field.', $label));
            }
        }
    }
}
