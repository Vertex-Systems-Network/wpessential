<?php

declare(strict_types=1);

namespace WPEssential\Modules\DashboardWidgets;

if (!defined('ABSPATH')) {
    exit;
}

use InvalidArgumentException;
use WPEssential\Platform\Rendering\RenderInput;

final readonly class DashboardWidgetEmptyStateDescriptor
{
    public const MAX_STRING_BYTES = 2048;
    public const MAX_ENCODED_BYTES = 4096;

    private const RICH_TEXT_BLUEPRINT_ID = '31000000-0000-4000-8000-000000000001';
    private const ANNOUNCEMENT_BLUEPRINT_ID = '31000000-0000-4000-8000-000000000005';

    /**
     * @param array<string,string> $bindings
     */
    public function __construct(
        public string $blueprintId,
        public int $blueprintRevision,
        public array $bindings,
    ) {
        $expectedKeys = match ($this->blueprintId) {
            self::RICH_TEXT_BLUEPRINT_ID => ['content'],
            self::ANNOUNCEMENT_BLUEPRINT_ID => ['text', 'title'],
            default => throw new InvalidArgumentException(
                'Dashboard Widget empty-state Blueprint is outside the bounded V1 allowlist.',
            ),
        };

        if ($this->blueprintRevision !== DashboardWidgetComponentBlueprintCatalog::REVISION) {
            throw new InvalidArgumentException('Dashboard Widget empty-state Blueprint revision must equal canonical revision 1.');
        }

        $actualKeys = array_keys($this->bindings);
        sort($actualKeys, SORT_STRING);
        sort($expectedKeys, SORT_STRING);
        if ($actualKeys !== $expectedKeys) {
            throw new InvalidArgumentException(
                'Dashboard Widget empty-state bindings must exactly match the trusted Blueprint schema.',
            );
        }

        $normalizedBindings = [];
        foreach ($this->bindings as $key => $value) {
            if (!is_string($key) || !is_string($value)) {
                throw new InvalidArgumentException('Dashboard Widget empty-state bindings must be string values.');
            }
            if (
                strlen($value) < 1
                || strlen($value) > self::MAX_STRING_BYTES
                || trim($value, " \t\n\r\0\x0B") === ''
            ) {
                throw new InvalidArgumentException(
                    'Dashboard Widget empty-state binding strings must contain 1..2048 encoded bytes.',
                );
            }
            if (preg_match('/<\?(?:php|=)|<script\b|javascript:/i', $value) === 1) {
                throw new InvalidArgumentException(
                    'Executable authored channels are not accepted as Dashboard Widget empty-state bindings.',
                );
            }
            $normalizedBindings[$key] = ['source' => 'literal', 'value' => $value];
        }
        ksort($normalizedBindings, SORT_STRING);

        $encoded = json_encode([
            'kind' => 'component_blueprint',
            'blueprint_id' => $this->blueprintId,
            'blueprint_revision' => $this->blueprintRevision,
            'bindings' => $normalizedBindings,
        ], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
        if (!is_string($encoded) || strlen($encoded) > self::MAX_ENCODED_BYTES) {
            throw new InvalidArgumentException('Dashboard Widget empty-state object exceeds the bounded 4096-byte limit.');
        }
    }

    public function toRenderInput(): RenderInput
    {
        return new RenderInput(
            $this->blueprintId,
            $this->blueprintRevision,
            $this->bindings,
        );
    }
}
