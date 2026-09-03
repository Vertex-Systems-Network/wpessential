<?php

declare(strict_types=1);

namespace WPEssential\Modules\Query;

if (!defined('ABSPATH')) {
    exit;
}

use WPEssential\Platform\Admin\AdminAssetManifest;
use WPEssential\Platform\Admin\PlatformAdminController;

final class QueryAdminController
{
    public const PAGE_SLUG = 'wpessential-query';
    private const CAPABILITY = 'manage_options';

    private ?string $hookSuffix = null;

    public function __construct(
        private readonly QueryAdminBootstrapProjector $bootstrap,
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
        if (!function_exists('add_submenu_page')) {
            return;
        }

        $hook = add_submenu_page(
            PlatformAdminController::PAGE_SLUG,
            __('Query Builder', 'wpessential'),
            __('Query Builder', 'wpessential'),
            self::CAPABILITY,
            self::PAGE_SLUG,
            [$this, 'render'],
        );
        $this->hookSuffix = is_string($hook) && $hook !== '' ? $hook : null;
    }

    public function enqueueAssets(string $hookSuffix): void
    {
        if ($this->hookSuffix === null || $hookSuffix !== $this->hookSuffix) {
            return;
        }

        $entry = $this->assets->entry('query');
        if ($entry === null) {
            return;
        }
        $version = $entry['version'] ?? (defined('WPE_VERSION') ? (string) WPE_VERSION : null);
        foreach ($entry['styles'] as $index => $style) {
            wp_enqueue_style('wpessential-query-admin-' . $index, $style, [], $version);
        }
        wp_enqueue_script(
            'wpessential-query-admin',
            $entry['script'],
            $entry['dependencies'],
            $version,
            true,
        );
        if (function_exists('wp_script_add_data')) {
            wp_script_add_data('wpessential-query-admin', 'strategy', 'defer');
        }
    }

    public function render(): void
    {
        if (!function_exists('current_user_can') || !current_user_can(self::CAPABILITY)) {
            if (function_exists('wp_die')) {
                wp_die(
                    esc_html__('You are not allowed to manage WPEssential Queries.', 'wpessential'),
                    esc_html__('WPEssential', 'wpessential'),
                    ['response' => 403],
                );
            }
            return;
        }

        $bootstrap = $this->bootstrap->project();
        $json = function_exists('wp_json_encode')
            ? wp_json_encode($bootstrap, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT)
            : json_encode($bootstrap, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT);
        $json = is_string($json) ? $json : '{}';

        echo '<div class="wrap wpessential-query-wrap">';
        echo '<section id="wpessential-query-root" data-wpessential-surface="query" aria-labelledby="wpessential-query-page-title">';
        echo '<h1 id="wpessential-query-page-title">' . esc_html__('Query Builder', 'wpessential') . '</h1>';
        echo '<p>' . esc_html__('Author a bounded Query definition from registered data-source metadata. Execution preview remains unavailable.', 'wpessential') . '</p>';
        echo '</section>';
        echo '<script id="wpessential-query-bootstrap" type="application/json">' . $json . '</script>';
        echo '</div>';
    }
}
