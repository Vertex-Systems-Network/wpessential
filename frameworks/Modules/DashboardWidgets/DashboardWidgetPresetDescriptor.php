<?php

declare(strict_types=1);

namespace WPEssential\Modules\DashboardWidgets;

if (!defined('ABSPATH')) {
    exit;
}

use InvalidArgumentException;

final readonly class DashboardWidgetPresetDescriptor
{
    public const MAX_WIDGETS = 100;
    public const MAX_ROLES = 50;

    /**
     * @param list<string> $widgetDefinitionIds
     * @param list<string> $roles
     */
    public function __construct(
        public string $definitionId,
        public int $revision,
        public string $label,
        public array $widgetDefinitionIds,
        public array $roles = [],
        public bool $networkDefault = false,
    ) {
        if (preg_match('/^[0-9a-f]{8}-[0-9a-f]{4}-[1-5][0-9a-f]{3}-[89ab][0-9a-f]{3}-[0-9a-f]{12}$/', $this->definitionId) !== 1) {
            throw new InvalidArgumentException('Dashboard Widget preset definition id must be a lowercase RFC 4122 UUID.');
        }
        if ($this->revision < 1) {
            throw new InvalidArgumentException('Dashboard Widget preset revision must be positive.');
        }
        if (
            $this->label === ''
            || strlen($this->label) > 160
            || trim($this->label, " \t\n\r\0\x0B") === ''
            || str_contains($this->label, '<')
            || str_contains($this->label, '>')
            || preg_match('/[\x00-\x08\x0B\x0C\x0E-\x1F\x7F]/', $this->label) === 1
        ) {
            throw new InvalidArgumentException('Dashboard Widget preset label must be bounded plain text.');
        }
        if (!array_is_list($this->widgetDefinitionIds) || $this->widgetDefinitionIds === [] || count($this->widgetDefinitionIds) > self::MAX_WIDGETS) {
            throw new InvalidArgumentException('Dashboard Widget preset widget references must be a bounded non-empty list.');
        }

        $seenWidgets = [];
        foreach ($this->widgetDefinitionIds as $id) {
            if (
                !is_string($id)
                || preg_match('/^[0-9a-f]{8}-[0-9a-f]{4}-[1-5][0-9a-f]{3}-[89ab][0-9a-f]{3}-[0-9a-f]{12}$/', $id) !== 1
                || isset($seenWidgets[$id])
            ) {
                throw new InvalidArgumentException('Dashboard Widget preset widget references must be unique lowercase RFC 4122 UUIDs.');
            }
            $seenWidgets[$id] = true;
        }

        if (!array_is_list($this->roles) || count($this->roles) > self::MAX_ROLES) {
            throw new InvalidArgumentException('Dashboard Widget preset roles must be a bounded normalized list.');
        }
        $previousRole = null;
        foreach ($this->roles as $role) {
            if (
                !is_string($role)
                || preg_match('/^[a-z0-9][a-z0-9_-]{0,63}$/', $role) !== 1
                || ($previousRole !== null && strcmp($previousRole, $role) >= 0)
            ) {
                throw new InvalidArgumentException('Dashboard Widget preset roles must be unique canonical slugs sorted ascending.');
            }
            $previousRole = $role;
        }

        if ($this->roles !== [] && $this->networkDefault) {
            throw new InvalidArgumentException('Dashboard Widget preset cannot be both role-default and network-default in V1.');
        }
    }

    public function isRoleDefault(): bool
    {
        return $this->roles !== [];
    }

    /** @return array<string,mixed> */
    public function toArray(): array
    {
        return [
            'definition_id' => $this->definitionId,
            'revision' => $this->revision,
            'label' => $this->label,
            'widget_definition_ids' => $this->widgetDefinitionIds,
            'assignment' => [
                'roles' => $this->roles,
                'network_default' => $this->networkDefault,
            ],
        ];
    }
}
