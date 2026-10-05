<?php

declare(strict_types=1);

namespace WPEssential\Modules\DashboardWidgets;

if (!defined('ABSPATH')) {
    exit;
}

use InvalidArgumentException;

final readonly class DashboardWidgetPresetResolution
{
    public const STATUS_RESOLVED = 'resolved';
    public const STATUS_NONE = 'none';
    public const STATUS_CONFLICT = 'conflict';
    public const STATUS_INVALID_CATALOG = 'invalid_catalog';
    public const STATUS_UNAVAILABLE = 'unavailable';

    public const SOURCE_ROLE_DEFAULT = 'role_default';
    public const SOURCE_NETWORK_DEFAULT = 'network_default';

    /** @param list<string> $widgetDefinitionIds */
    private function __construct(
        public string $status,
        public ?string $source = null,
        public ?string $presetId = null,
        public ?int $presetRevision = null,
        public array $widgetDefinitionIds = [],
    ) {
        if (!in_array($this->status, [
            self::STATUS_RESOLVED,
            self::STATUS_NONE,
            self::STATUS_CONFLICT,
            self::STATUS_INVALID_CATALOG,
            self::STATUS_UNAVAILABLE,
        ], true)) {
            throw new InvalidArgumentException('Dashboard Widget preset resolution status is invalid.');
        }

        if ($this->status === self::STATUS_RESOLVED) {
            if (
                !in_array($this->source, [self::SOURCE_ROLE_DEFAULT, self::SOURCE_NETWORK_DEFAULT], true)
                || $this->presetId === null
                || $this->presetRevision === null
                || $this->presetRevision < 1
                || $this->widgetDefinitionIds === []
            ) {
                throw new InvalidArgumentException('Resolved Dashboard Widget preset evidence is incomplete.');
            }
            return;
        }

        if (
            $this->source !== null
            || $this->presetId !== null
            || $this->presetRevision !== null
            || $this->widgetDefinitionIds !== []
        ) {
            throw new InvalidArgumentException('Unresolved Dashboard Widget preset evidence must not leak preset details.');
        }
    }

    public static function resolved(DashboardWidgetPresetDescriptor $preset, string $source): self
    {
        return new self(
            self::STATUS_RESOLVED,
            $source,
            $preset->definitionId,
            $preset->revision,
            $preset->widgetDefinitionIds,
        );
    }

    public static function none(): self { return new self(self::STATUS_NONE); }
    public static function conflict(): self { return new self(self::STATUS_CONFLICT); }
    public static function invalidCatalog(): self { return new self(self::STATUS_INVALID_CATALOG); }
    public static function unavailable(): self { return new self(self::STATUS_UNAVAILABLE); }

    /** @return array<string,mixed> */
    public function toArray(): array
    {
        return [
            'status' => $this->status,
            'source' => $this->source,
            'preset_id' => $this->presetId,
            'preset_revision' => $this->presetRevision,
            'widget_definition_ids' => $this->widgetDefinitionIds,
        ];
    }
}
