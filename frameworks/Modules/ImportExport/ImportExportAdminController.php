<?php

declare(strict_types=1);

namespace WPEssential\Modules\ImportExport;

if (!defined('ABSPATH')) {
    exit;
}

use InvalidArgumentException;
use WPEssential\Platform\Admin\AdminAssetManifest;
use WPEssential\Platform\Admin\PlatformAdminController;
use WPEssential\Platform\WordPress\Ajax\AjaxDispatcher;

final class ImportExportAdminController
{
    public const PAGE_SLUG = 'wpessential-configuration-packages';
    private const CAPABILITY = 'manage_options';

    private ?string $hookSuffix = null;

    public function __construct(
        private readonly AjaxDispatcher $ajax,
        private readonly AdminAssetManifest $assets,
        private readonly string $ajaxAction,
    ) {
        if (trim($this->ajaxAction) === '') {
            throw new InvalidArgumentException('Configuration Packages AJAX action cannot be blank.');
        }
    }

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
            __('Configuration Packages', 'wpessential'),
            __('Configuration Packages', 'wpessential'),
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
        $entry = $this->assets->entry('import-export');
        if ($entry === null) {
            return;
        }
        $version = $entry['version'] ?? (defined('WPE_VERSION') ? (string) WPE_VERSION : null);
        foreach ($entry['styles'] as $index => $style) {
            wp_enqueue_style('wpessential-import-export-' . $index, $style, [], $version);
        }
        wp_enqueue_script(
            'wpessential-import-export',
            $entry['script'],
            $entry['dependencies'],
            $version,
            true,
        );
        if (function_exists('wp_script_add_data')) {
            wp_script_add_data('wpessential-import-export', 'strategy', 'defer');
        }
    }

    public function render(): void
    {
        if (!function_exists('current_user_can') || !current_user_can(self::CAPABILITY)) {
            if (function_exists('wp_die')) {
                wp_die(
                    esc_html__('You are not allowed to manage WPEssential configuration packages.', 'wpessential'),
                    esc_html__('WPEssential', 'wpessential'),
                    ['response' => 403],
                );
            }
            return;
        }

        $bootstrap = [
            'surface' => 'configuration-packages',
            'ajaxUrl' => function_exists('admin_url') ? admin_url('admin-ajax.php') : '',
            'ajaxAction' => $this->ajaxAction,
            'maxBytes' => DefinitionPackageCodec::MAX_BYTES,
            'routes' => [
                'export' => [
                    'type' => 'import-export.export',
                    'nonce' => $this->ajax->createNonce('import-export.export'),
                ],
                'preflight' => [
                    'type' => 'import-export.preflight',
                    'nonce' => $this->ajax->createNonce('import-export.preflight'),
                ],
                'import' => [
                    'type' => 'import-export.import',
                    'nonce' => $this->ajax->createNonce('import-export.import'),
                ],
            ],
        ];
        $json = function_exists('wp_json_encode')
            ? wp_json_encode($bootstrap, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT)
            : json_encode($bootstrap, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT);
        $json = is_string($json) ? $json : '{}';

        echo '<div class="wrap wpessential-admin-wrap">';
        echo '<section id="wpessential-import-export-root" data-wpessential-surface="configuration-packages" aria-labelledby="wpessential-import-export-title">';
        echo '<h1 id="wpessential-import-export-title">' . esc_html__('Configuration Packages', 'wpessential') . '</h1>';
        echo '<p>' . esc_html__('Export and safely import portable WPEssential CPT and Taxonomy definitions. This WP122 package scope contains configuration only: no runtime content, credentials, executable code, or arbitrary remote files.', 'wpessential') . '</p>';
        echo '<div id="wpessential-import-export-notice" class="notice inline" role="status" aria-live="polite" hidden><p></p></div>';
        $this->renderExport();
        $this->renderImport();
        echo '<noscript><div class="notice notice-warning inline"><p>' . esc_html__('JavaScript is required to generate, inspect, or import configuration packages.', 'wpessential') . '</p></div></noscript>';
        echo '</section>';
        echo '<script id="wpessential-import-export-bootstrap" type="application/json">' . $json . '</script>';
        echo '</div>';
    }

    private function renderExport(): void
    {
        echo '<section class="wpessential-cpt-panel" aria-labelledby="wpessential-package-export-title">';
        echo '<h2 id="wpessential-package-export-title">' . esc_html__('Export definition package', 'wpessential') . '</h2>';
        echo '<p>' . esc_html__('The generated JSON preserves stable definition UUIDs, schema metadata, dependencies, and checksums. Secrets and runtime data are excluded.', 'wpessential') . '</p>';
        echo '<fieldset class="wpessential-cpt-options"><legend>' . esc_html__('Definition owners', 'wpessential') . '</legend>';
        echo '<label for="wpessential-package-export-cpt"><input type="checkbox" id="wpessential-package-export-cpt" checked> ' . esc_html__('Custom Post Types', 'wpessential') . '</label>';
        echo '<label for="wpessential-package-export-taxonomy"><input type="checkbox" id="wpessential-package-export-taxonomy" checked> ' . esc_html__('Taxonomies', 'wpessential') . '</label>';
        echo '</fieldset>';
        echo '<p><button type="button" class="button button-primary" id="wpessential-package-export-generate">' . esc_html__('Generate package', 'wpessential') . '</button> ';
        echo '<button type="button" class="button" id="wpessential-package-export-download" disabled>' . esc_html__('Download JSON', 'wpessential') . '</button></p>';
        echo '<p><label for="wpessential-package-export-json"><strong>' . esc_html__('Generated package JSON', 'wpessential') . '</strong></label></p>';
        echo '<textarea id="wpessential-package-export-json" class="large-text code" rows="12" readonly aria-describedby="wpessential-package-export-help"></textarea>';
        echo '<p id="wpessential-package-export-help" class="description">' . esc_html__('A package is portable configuration, not a database backup or content export.', 'wpessential') . '</p>';
        echo '</section>';
    }

    private function renderImport(): void
    {
        echo '<section class="wpessential-cpt-panel" aria-labelledby="wpessential-package-import-title">';
        echo '<h2 id="wpessential-package-import-title">' . esc_html__('Inspect and import definition package', 'wpessential') . '</h2>';
        echo '<p>' . esc_html__('Every import requires a fresh dry-run preflight. Create-only is the safest default; update-existing is explicit and revision-checked.', 'wpessential') . '</p>';
        echo '<p><label for="wpessential-package-file"><strong>' . esc_html__('Load JSON file', 'wpessential') . '</strong></label><br>';
        echo '<input type="file" id="wpessential-package-file" accept="application/json,.json"> ';
        echo '<span class="description">' . esc_html__('The browser reads this file locally; WPEssential does not accept ZIP, PHP, SQL, or remote URL imports in this WP122 slice.', 'wpessential') . '</span></p>';
        echo '<p><label for="wpessential-package-import-json"><strong>' . esc_html__('Package JSON', 'wpessential') . '</strong></label></p>';
        echo '<textarea id="wpessential-package-import-json" class="large-text code" rows="14" spellcheck="false"></textarea>';
        echo '<p><label for="wpessential-package-strategy"><strong>' . esc_html__('Import strategy', 'wpessential') . '</strong></label><br>';
        echo '<select id="wpessential-package-strategy">';
        echo '<option value="create_only">' . esc_html__('Create only — never modify an existing UUID', 'wpessential') . '</option>';
        echo '<option value="update_existing">' . esc_html__('Update same UUID — explicit revision-safe update', 'wpessential') . '</option>';
        echo '</select></p>';
        echo '<p><button type="button" class="button" id="wpessential-package-preflight">' . esc_html__('Inspect / dry run', 'wpessential') . '</button> ';
        echo '<button type="button" class="button button-primary" id="wpessential-package-import" disabled>' . esc_html__('Apply import', 'wpessential') . '</button></p>';
        echo '<div id="wpessential-package-report" class="wpessential-cpt-validation" role="status" aria-live="polite" aria-labelledby="wpessential-package-report-title" hidden>';
        echo '<h3 id="wpessential-package-report-title">' . esc_html__('Preflight report', 'wpessential') . '</h3>';
        echo '<p data-wpessential-package-report-summary></p>';
        echo '<ul data-wpessential-package-report-items></ul>';
        echo '</div>';
        echo '</section>';
    }
}
