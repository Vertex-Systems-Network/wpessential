<?php

declare(strict_types=1);

namespace WPEssential\Platform\Admin;

if (!defined('ABSPATH')) {
    exit;
}

final class PlatformAdminController
{
    public const PAGE_SLUG = 'wpessential';
    public const CAPABILITY = 'manage_options';

    private ?string $hookSuffix = null;

    public function __construct(
        private readonly RuntimeDiagnosticsSnapshot $snapshot,
        private readonly AdminAssetManifest $assets,
    ) {}

    public function register(): void
    {
        if (!function_exists('add_action')) {
            return;
        }

        add_action('admin_menu', [$this, 'registerMenu']);
        add_action('admin_enqueue_scripts', [$this, 'enqueueAssets']);
    }

    public function registerMenu(): void
    {
        if (!function_exists('add_menu_page')) {
            return;
        }

        $hook = add_menu_page(
            'WPEssential',
            'WPEssential',
            self::CAPABILITY,
            self::PAGE_SLUG,
            [$this, 'render'],
            'dashicons-admin-generic',
            58,
        );

        $this->hookSuffix = is_string($hook) && $hook !== '' ? $hook : null;
    }

    public function enqueueAssets(string $hookSuffix): void
    {
        if ($this->hookSuffix === null || $hookSuffix !== $this->hookSuffix) {
            return;
        }

        $entry = $this->assets->entry();
        if ($entry === null) {
            return;
        }

        $version = $entry['version'] ?? (defined('WPE_VERSION') ? (string) WPE_VERSION : null);

        foreach ($entry['styles'] as $index => $style) {
            wp_enqueue_style('wpessential-admin-' . $index, $style, [], $version);
        }

        wp_enqueue_script(
            'wpessential-admin',
            $entry['script'],
            $entry['dependencies'],
            $version,
            true,
        );

        if (function_exists('wp_script_add_data')) {
            wp_script_add_data('wpessential-admin', 'strategy', 'defer');
        }
    }

    public function render(): void
    {
        if (!function_exists('current_user_can') || !current_user_can(self::CAPABILITY)) {
            if (function_exists('wp_die')) {
                wp_die('You are not allowed to view WPEssential diagnostics.', 'WPEssential', ['response' => 403]);
            }
            return;
        }

        $snapshot = $this->snapshot->build();
        $json = function_exists('wp_json_encode')
            ? wp_json_encode($snapshot, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT)
            : json_encode($snapshot, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT);
        $json = is_string($json) ? $json : '{}';

        $app = is_array($snapshot['app'] ?? null) ? $snapshot['app'] : [];
        $runtime = is_array($snapshot['runtime'] ?? null) ? $snapshot['runtime'] : [];
        $observability = is_array($snapshot['observability'] ?? null) ? $snapshot['observability'] : [];
        $context = is_array($snapshot['context'] ?? null) ? $snapshot['context'] : [];

        echo '<div class="wrap wpessential-admin-wrap">';
        echo '<main id="wpessential-admin-root" data-wpessential-surface="runtime-observatory">';
        echo '<h1>' . esc_html__('WPEssential', 'wpessential') . '</h1>';
        echo '<h2>' . esc_html__('Runtime Observatory', 'wpessential') . '</h2>';
        echo '<p>' . esc_html__('Read-only bounded diagnostics for the shared WPEssential Platform runtime. No mutation controls are exposed on this surface.', 'wpessential') . '</p>';
        echo '<table class="widefat striped" aria-label="' . esc_html__('WPEssential runtime diagnostics', 'wpessential') . '"><tbody>';
        $this->renderDiagnosticRow('Platform version', (string) ($app['version'] ?? 'unknown'));
        $this->renderDiagnosticRow('WordPress', (string) ($runtime['wordpress'] ?? 'unknown'));
        $this->renderDiagnosticRow('PHP', (string) ($runtime['php'] ?? PHP_VERSION));
        $this->renderDiagnosticRow('Kernel booted', !empty($runtime['kernel_booted']) ? 'yes' : 'no');
        $this->renderDiagnosticRow('Debug enabled', !empty($runtime['debug_enabled']) ? 'yes' : 'no');
        $this->renderDiagnosticRow('Trace capture', (string) ($runtime['trace_capture'] ?? 'disabled'));
        $this->renderDiagnosticRow('Captured traces', (string) ((int) ($observability['captured_trace_count'] ?? 0)));
        $this->renderDiagnosticRow('Visible traces', (string) ((int) ($observability['visible_trace_count'] ?? 0)));
        $this->renderDiagnosticRow('Site ID', (string) ((int) ($context['site_id'] ?? 1)));
        $this->renderDiagnosticRow('Network ID', (string) ((int) ($context['network_id'] ?? 1)));
        $this->renderDiagnosticRow('Multisite', !empty($context['multisite']) ? 'yes' : 'no');
        echo '</tbody></table>';
        echo '<p><em>' . esc_html__('Trace data is request-bounded and non-authoritative; use canonical persistent evidence for release or incident decisions.', 'wpessential') . '</em></p>';
        echo '</main>';
        echo '<script id="wpessential-admin-bootstrap" type="application/json">' . $json . '</script>';
        echo '</div>';
    }

    private function renderDiagnosticRow(string $label, string $value): void
    {
        echo '<tr><th scope="row">' . esc_html__($label, 'wpessential') . '</th><td><code>' . esc_html($value) . '</code></td></tr>';
    }
}
