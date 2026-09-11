<?php

declare(strict_types=1);

namespace WPEssential\Modules\Taxonomies;

if (!defined('ABSPATH')) {
    exit;
}

use WPEssential\Platform\WordPress\Ajax\AjaxDispatcher;

final readonly class TaxonomyCptUiImportPreviewAdminBridge
{
    private const CAPABILITY = 'manage_options';

    public function __construct(private AjaxDispatcher $ajax) {}

    public function register(): void
    {
        if (!function_exists('add_action')) {
            return;
        }
        add_action('admin_footer', [$this, 'renderBootstrap']);
    }

    public function renderBootstrap(): void
    {
        if (!$this->isTaxonomyPage()
            || !function_exists('current_user_can')
            || !current_user_can(self::CAPABILITY)
        ) {
            return;
        }

        $bootstrap = [
            'type' => 'taxonomy.import_preview',
            'nonce' => $this->ajax->createNonce('taxonomy.import_preview'),
        ];
        $json = function_exists('wp_json_encode')
            ? wp_json_encode($bootstrap, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT)
            : json_encode($bootstrap, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT);
        if (!is_string($json)) {
            return;
        }

        echo '<script id="wpessential-taxonomy-import-preview-bootstrap" type="application/json">' . $json . '</script>';
    }

    private function isTaxonomyPage(): bool
    {
        $page = $_GET['page'] ?? null; // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- read-only page routing check.
        if (!is_string($page)) {
            return false;
        }
        if (function_exists('wp_unslash')) {
            $page = wp_unslash($page);
        }
        if (function_exists('sanitize_key')) {
            $page = sanitize_key($page);
        }
        return $page === TaxonomyAdminController::PAGE_SLUG;
    }
}
