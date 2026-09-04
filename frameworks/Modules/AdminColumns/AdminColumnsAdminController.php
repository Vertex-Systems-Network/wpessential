<?php

declare(strict_types=1);

namespace WPEssential\Modules\AdminColumns;

if (!defined('ABSPATH')) {
    exit;
}

use WPEssential\Platform\Admin\AdminAssetManifest;
use WPEssential\Platform\Admin\PlatformAdminController;
use WPEssential\Platform\WordPress\Ajax\AjaxDispatcher;

final class AdminColumnsAdminController
{
    public const PAGE_SLUG = 'wpessential-admin-columns';
    private const CAPABILITY = 'manage_options';
    private const LIST_ROUTE = 'admin-columns.list.views';
    private const GET_ROUTE = 'admin-columns.get.view';
    private const SAVE_ROUTE = 'admin-columns.save.view';

    private ?string $hookSuffix = null;

    public function __construct(
        private readonly AdminColumnsAdminBootstrapProjector $bootstrap,
        private readonly AdminAssetManifest $assets,
        private readonly ?AjaxDispatcher $ajax = null,
        private readonly ?string $ajaxAction = null,
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
            __('Admin Columns', 'wpessential'),
            __('Admin Columns', 'wpessential'),
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
        $entry = $this->assets->entry('columns-runtime');
        if ($entry === null) {
            return;
        }
        $version = $entry['version'] ?? (defined('WPE_VERSION') ? (string) WPE_VERSION : null);
        foreach ($entry['styles'] as $index => $style) {
            wp_enqueue_style('wpessential-columns-admin-' . $index, $style, [], $version);
        }
        wp_enqueue_script(
            'wpessential-columns-admin',
            $entry['script'],
            $entry['dependencies'],
            $version,
            true,
        );
        if (function_exists('wp_script_add_data')) {
            wp_script_add_data('wpessential-columns-admin', 'strategy', 'defer');
        }
    }

    public function render(): void
    {
        if (!function_exists('current_user_can') || !current_user_can(self::CAPABILITY)) {
            if (function_exists('wp_die')) {
                wp_die(
                    esc_html__('You are not allowed to manage WPEssential Admin Columns.', 'wpessential'),
                    esc_html__('WPEssential', 'wpessential'),
                    ['response' => 403],
                );
            }
            return;
        }

        $bootstrap = $this->bootstrap->project();
        if ($this->ajax instanceof AjaxDispatcher
            && is_string($this->ajaxAction)
            && trim($this->ajaxAction) !== ''
        ) {
            $bootstrap = array_merge(
                $bootstrap,
                [
                    'ajaxUrl' => function_exists('admin_url') ? admin_url('admin-ajax.php') : '',
                    'ajaxAction' => $this->ajaxAction,
                    'routes' => [
                        'list' => [
                            'type' => self::LIST_ROUTE,
                            'nonce' => $this->ajax->createNonce(self::LIST_ROUTE),
                        ],
                        'get' => [
                            'type' => self::GET_ROUTE,
                            'nonce' => $this->ajax->createNonce(self::GET_ROUTE),
                        ],
                        'save' => [
                            'type' => self::SAVE_ROUTE,
                            'nonce' => $this->ajax->createNonce(self::SAVE_ROUTE),
                        ],
                    ],
                ],
            );
        }

        $json = function_exists('wp_json_encode')
            ? wp_json_encode($bootstrap, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT)
            : json_encode($bootstrap, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT);
        $json = is_string($json) ? $json : '{}';

        echo '<div class="wrap wpessential-columns-wrap">';
        echo '<section id="wpessential-columns-root" data-wpessential-surface="columns" aria-labelledby="wpessential-columns-page-title">';
        echo '<h1 id="wpessential-columns-page-title">' . esc_html__('Admin Columns', 'wpessential') . '</h1>';
        echo '<p>' . esc_html__('Author and reopen revisioned shared Column Sets from canonical target and source metadata. Query execution, row mutation and export remain unavailable.', 'wpessential') . '</p>';
        echo '</section>';
        echo '<script id="wpessential-columns-bootstrap" type="application/json">' . $json . '</script>';
        echo '</div>';
    }
}
