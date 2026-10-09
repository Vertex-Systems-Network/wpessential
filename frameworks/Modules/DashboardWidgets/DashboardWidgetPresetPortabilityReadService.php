<?php

declare(strict_types=1);

namespace WPEssential\Modules\DashboardWidgets;

if (!defined('ABSPATH')) {
    exit;
}

final readonly class DashboardWidgetPresetPortabilityReadService
{
    public const FORMAT = 'wpessential-dashboard-preset';
    public const VERSION = 1;

    public function __construct(private DashboardWidgetPresetReadService $presets) {}

    /**
     * Read-only, bounded portability envelope; SHA-256 is a non-authenticating
     * content fingerprint, not a signature or permission bypass.
     *
     * @return array<string,mixed>|null
     */
    public function snapshot(string $presetId): ?array
    {
        $preset = $this->presets->get($presetId);
        if ($preset === null) {
            return null;
        }

        // Canonical Published Surface-10 preset compilation already checked
        // the widget definitions, normalized role slugs and bounded lengths.
        // Copy only contract-owned keys in deterministic order; never export
        // the raw Definition payload or site/user WordPress preferences.
        $payload = [
            'definition_id' => $preset['definition_id'],
            'revision' => $preset['revision'],
            'label' => $preset['label'],
            'widget_definition_ids' => $preset['widget_definition_ids'],
            'assignment' => [
                'roles' => $preset['assignment']['roles'],
                'network_default' => $preset['assignment']['network_default'],
            ],
        ];
        $canonicalJson = json_encode(
            $payload,
            JSON_THROW_ON_ERROR | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE,
        );

        return [
            'format' => self::FORMAT,
            'version' => self::VERSION,
            'payload' => $payload,
            'sha256' => hash('sha256', $canonicalJson),
        ];
    }
}
