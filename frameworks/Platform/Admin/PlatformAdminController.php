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

        foreach ($entry['styles'] as $index => $style) {
            wp_enqueue_style('wpessential-admin-' . $index, $style, [], defined('WPE_VERSION') ? (string) WPE_VERSION : null);
        }

        if (function_exists('wp_enqueue_script_module')) {
            wp_enqueue_script_module(
                'wpessential-admin',
                $entry['script'],
                [],
                defined('WPE_VERSION') ? (string) WPE_VERSION : null,
            );
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

        echo '<div class="wrap wpessential-admin-wrap">';
        echo '<div id="wpessential-admin-root">';
        echo '<h1>' . esc_html__('WPEssential', 'wpessential') . '</h1>';
        echo '<p>' . esc_html__('Loading the Platform diagnostics shell…', 'wpessential') . '</p>';
        echo '</div>';
        echo '<script id="wpessential-admin-bootstrap" type="application/json">' . $json . '</script>';
        echo '</div>';
    }
}
