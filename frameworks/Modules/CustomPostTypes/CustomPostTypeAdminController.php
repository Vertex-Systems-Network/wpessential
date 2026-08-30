<?php

declare(strict_types=1);

namespace WPEssential\Modules\CustomPostTypes;

if (!defined('ABSPATH')) {
    exit;
}

use InvalidArgumentException;
use Throwable;
use WPEssential\Platform\Abilities\AbilityRegistry;
use WPEssential\Platform\Admin\AdminAssetManifest;
use WPEssential\Platform\Admin\PlatformAdminController;
use WPEssential\Platform\Auth\ExecutionChannel;
use WPEssential\Platform\Auth\ExecutionContext;
use WPEssential\Platform\WordPress\Abilities\WordPressExecutionContextFactory;
use WPEssential\Platform\WordPress\Ajax\AjaxDispatcher;

final class CustomPostTypeAdminController
{
    public const PAGE_SLUG = 'wpessential-cpt';
    private const CAPABILITY = 'manage_options';

    private ?string $hookSuffix = null;

    public function __construct(
        private readonly AbilityRegistry $abilities,
        private readonly WordPressExecutionContextFactory $contexts,
        private readonly AjaxDispatcher $ajax,
        private readonly AdminAssetManifest $assets,
        private readonly string $ajaxAction,
    ) {
        if (trim($this->ajaxAction) === '') {
            throw new InvalidArgumentException('CPT admin AJAX action cannot be blank.');
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
            __('Custom Post Types', 'wpessential'),
            __('Custom Post Types', 'wpessential'),
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

        $entry = $this->assets->entry();
        if ($entry === null) {
            return;
        }

        $version = $entry['version'] ?? (defined('WPE_VERSION') ? (string) WPE_VERSION : null);
        foreach ($entry['styles'] as $index => $style) {
            wp_enqueue_style('wpessential-cpt-admin-' . $index, $style, [], $version);
        }

        wp_enqueue_script(
            'wpessential-cpt-admin',
            $entry['script'],
            $entry['dependencies'],
            $version,
            true,
        );
        if (function_exists('wp_script_add_data')) {
            wp_script_add_data('wpessential-cpt-admin', 'strategy', 'defer');
        }
    }

    public function render(): void
    {
        if (!function_exists('current_user_can') || !current_user_can(self::CAPABILITY)) {
            if (function_exists('wp_die')) {
                wp_die(
                    esc_html__('You are not allowed to manage WPEssential custom post types.', 'wpessential'),
                    esc_html__('WPEssential', 'wpessential'),
                    ['response' => 403],
                );
            }
            return;
        }

        [$definitions, $readError] = $this->readDefinitions();
        $bootstrap = [
            'surface' => 'custom-post-types',
            'ajaxUrl' => function_exists('admin_url') ? admin_url('admin-ajax.php') : '',
            'ajaxAction' => $this->ajaxAction,
            'routes' => [
                'list' => ['type' => 'cpt.list', 'nonce' => $this->ajax->createNonce('cpt.list')],
                'save' => ['type' => 'cpt.save', 'nonce' => $this->ajax->createNonce('cpt.save')],
                'status' => ['type' => 'cpt.status', 'nonce' => $this->ajax->createNonce('cpt.status')],
            ],
            'definitions' => $definitions,
        ];
        $json = function_exists('wp_json_encode')
            ? wp_json_encode($bootstrap, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT)
            : json_encode($bootstrap, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT);
        $json = is_string($json) ? $json : '{}';

        echo '<div class="wrap wpessential-admin-wrap">';
        echo '<section id="wpessential-admin-root" data-wpessential-surface="custom-post-types" aria-labelledby="wpessential-cpt-title">';
        echo '<h1 id="wpessential-cpt-title">' . esc_html__('Custom Post Types', 'wpessential') . '</h1>';
        echo '<p>' . esc_html__('Create and manage canonical WPEssential post type definitions. Changes are revision-safe and pass through the shared Policy and Ability layer.', 'wpessential') . '</p>';

        if ($readError !== null) {
            echo '<div class="notice notice-error inline" role="alert"><p>' . esc_html($readError) . '</p></div>';
        }

        echo '<div id="wpessential-cpt-notice" class="notice inline wpessential-cpt-notice" role="status" aria-live="polite" hidden><p></p></div>';
        $this->renderEditor();
        $this->renderDefinitions($definitions);
        echo '<noscript><p class="notice notice-warning inline">' . esc_html__('JavaScript is required to create or change Custom Post Type definitions. Existing definitions remain readable in the table.', 'wpessential') . '</p></noscript>';
        echo '</section>';
        echo '<script id="wpessential-admin-bootstrap" type="application/json">' . $json . '</script>';
        echo '</div>';
    }

    private function renderEditor(): void
    {
        echo '<section class="wpessential-cpt-panel" aria-labelledby="wpessential-cpt-editor-title">';
        echo '<h2 id="wpessential-cpt-editor-title">' . esc_html__('Add custom post type', 'wpessential') . '</h2>';
        echo '<form id="wpessential-cpt-form">';
        echo '<input type="hidden" id="wpessential-cpt-id" value="">';
        echo '<input type="hidden" id="wpessential-cpt-revision" value="">';
        echo '<div class="wpessential-cpt-grid">';
        $this->renderTextField('wpessential-cpt-key', __('Post type key', 'wpessential'), 'post_type_key', 'book', true, __('Lowercase; maximum 20 characters. Existing runtime keys are immutable through this first UI slice.', 'wpessential'));
        $this->renderTextField('wpessential-cpt-name', __('Plural name', 'wpessential'), 'name', 'Books', true);
        $this->renderTextField('wpessential-cpt-singular', __('Singular name', 'wpessential'), 'singular_name', 'Book', true);
        $this->renderTextField('wpessential-cpt-description', __('Description', 'wpessential'), 'description', '', false);
        echo '</div>';

        echo '<fieldset class="wpessential-cpt-options"><legend>' . esc_html__('Behavior', 'wpessential') . '</legend>';
        $this->renderCheckbox('wpessential-cpt-public', __('Public', 'wpessential'), true);
        $this->renderCheckbox('wpessential-cpt-rest', __('Show in REST API', 'wpessential'), true);
        $this->renderCheckbox('wpessential-cpt-hierarchical', __('Hierarchical', 'wpessential'), false);
        $this->renderCheckbox('wpessential-cpt-archive', __('Has archive', 'wpessential'), false);
        echo '</fieldset>';

        echo '<fieldset class="wpessential-cpt-options"><legend>' . esc_html__('Editor supports', 'wpessential') . '</legend>';
        foreach ([
            'title' => __('Title', 'wpessential'),
            'editor' => __('Editor', 'wpessential'),
            'thumbnail' => __('Featured image', 'wpessential'),
            'excerpt' => __('Excerpt', 'wpessential'),
            'custom-fields' => __('Custom fields', 'wpessential'),
            'comments' => __('Comments', 'wpessential'),
            'revisions' => __('Revisions', 'wpessential'),
            'page-attributes' => __('Page attributes', 'wpessential'),
        ] as $support => $label) {
            $checked = in_array($support, ['title', 'editor'], true);
            echo '<label><input type="checkbox" data-wpessential-cpt-support="' . esc_attr($support) . '"' . checked($checked, true, false) . '> ' . esc_html($label) . '</label>';
        }
        echo '</fieldset>';

        echo '<p><label for="wpessential-cpt-status"><strong>' . esc_html__('Lifecycle status', 'wpessential') . '</strong></label><br>';
        echo '<select id="wpessential-cpt-status">';
        foreach ([
            'draft' => __('Draft', 'wpessential'),
            'published' => __('Published', 'wpessential'),
            'disabled' => __('Disabled', 'wpessential'),
            'archived' => __('Archived', 'wpessential'),
        ] as $value => $label) {
            echo '<option value="' . esc_attr($value) . '">' . esc_html($label) . '</option>';
        }
        echo '</select></p>';

        echo '<p class="submit">';
        echo '<button type="submit" class="button button-primary" id="wpessential-cpt-save">' . esc_html__('Save custom post type', 'wpessential') . '</button> ';
        echo '<button type="button" class="button" id="wpessential-cpt-cancel" hidden>' . esc_html__('Cancel edit', 'wpessential') . '</button>';
        echo '</p>';
        echo '</form>';
        echo '</section>';
    }

    /** @param list<array<string,mixed>> $definitions */
    private function renderDefinitions(array $definitions): void
    {
        echo '<section class="wpessential-cpt-panel" aria-labelledby="wpessential-cpt-list-title">';
        echo '<div class="wpessential-cpt-list-heading"><h2 id="wpessential-cpt-list-title">' . esc_html__('Saved custom post types', 'wpessential') . '</h2>';
        echo '<button type="button" class="button" id="wpessential-cpt-refresh">' . esc_html__('Refresh', 'wpessential') . '</button></div>';
        echo '<table class="widefat striped" id="wpessential-cpt-table" aria-label="' . esc_attr__('Saved custom post types', 'wpessential') . '">';
        echo '<thead><tr><th scope="col">' . esc_html__('Name', 'wpessential') . '</th><th scope="col">' . esc_html__('Key', 'wpessential') . '</th><th scope="col">' . esc_html__('Status', 'wpessential') . '</th><th scope="col">' . esc_html__('Revision', 'wpessential') . '</th><th scope="col">' . esc_html__('Actions', 'wpessential') . '</th></tr></thead>';
        echo '<tbody id="wpessential-cpt-rows">';
        foreach ($definitions as $definition) {
            $this->renderDefinitionRow($definition);
        }
        if ($definitions === []) {
            echo '<tr data-wpessential-cpt-empty><td colspan="5">' . esc_html__('No custom post types have been created yet.', 'wpessential') . '</td></tr>';
        }
        echo '</tbody></table>';
        echo '</section>';
    }

    /** @param array<string,mixed> $definition */
    private function renderDefinitionRow(array $definition): void
    {
        $payload = is_array($definition['payload'] ?? null) ? $definition['payload'] : [];
        $id = is_string($definition['id'] ?? null) ? $definition['id'] : '';
        $name = is_string($payload['name'] ?? null) ? $payload['name'] : '';
        $key = is_string($payload['post_type_key'] ?? null) ? $payload['post_type_key'] : '';
        $status = is_string($definition['status'] ?? null) ? $definition['status'] : '';
        $revision = is_int($definition['revision'] ?? null) ? $definition['revision'] : 0;

        echo '<tr data-wpessential-cpt-row="' . esc_attr($id) . '">';
        echo '<td><strong>' . esc_html($name) . '</strong></td>';
        echo '<td><code>' . esc_html($key) . '</code></td>';
        echo '<td>' . esc_html(ucfirst($status)) . '</td>';
        echo '<td>' . esc_html((string) $revision) . '</td>';
        echo '<td><button type="button" class="button button-small" data-wpessential-cpt-edit="' . esc_attr($id) . '">' . esc_html__('Edit', 'wpessential') . '</button> ';
        if ($status === 'published') {
            echo '<button type="button" class="button button-small" data-wpessential-cpt-status="disabled" data-wpessential-cpt-id="' . esc_attr($id) . '">' . esc_html__('Disable', 'wpessential') . '</button> ';
        } else {
            echo '<button type="button" class="button button-small" data-wpessential-cpt-status="published" data-wpessential-cpt-id="' . esc_attr($id) . '">' . esc_html__('Publish', 'wpessential') . '</button> ';
        }
        if ($status !== 'archived') {
            echo '<button type="button" class="button button-small" data-wpessential-cpt-status="archived" data-wpessential-cpt-id="' . esc_attr($id) . '">' . esc_html__('Archive', 'wpessential') . '</button>';
        }
        echo '</td>';
        echo '</tr>';
    }

    private function renderTextField(
        string $id,
        string $label,
        string $field,
        string $placeholder,
        bool $required,
        string $help = '',
    ): void {
        echo '<p><label for="' . esc_attr($id) . '"><strong>' . esc_html($label) . '</strong></label><br>';
        echo '<input class="regular-text" type="text" id="' . esc_attr($id) . '" data-wpessential-cpt-field="' . esc_attr($field) . '" placeholder="' . esc_attr($placeholder) . '"' . ($required ? ' required' : '') . '>';
        if ($help !== '') {
            echo '<span class="description">' . esc_html($help) . '</span>';
        }
        echo '</p>';
    }

    private function renderCheckbox(string $id, string $label, bool $checkedByDefault): void
    {
        echo '<label for="' . esc_attr($id) . '"><input type="checkbox" id="' . esc_attr($id) . '"' . checked($checkedByDefault, true, false) . '> ' . esc_html($label) . '</label>';
    }

    /** @return array{list<array<string,mixed>>,?string} */
    private function readDefinitions(): array
    {
        try {
            $current = $this->contexts->current();
            $context = new ExecutionContext(
                principal: $current->principal,
                siteId: $current->siteId,
                channel: ExecutionChannel::Ui,
                networkId: $current->networkId,
                correlationId: $current->correlationId,
            );
            $result = $this->abilities->execute('wpessential/cpt/list', [], $context);
            if (!is_array($result) || !is_array($result['definitions'] ?? null)) {
                return [[], __('The Custom Post Type list returned an invalid response.', 'wpessential')];
            }

            $definitions = [];
            foreach ($result['definitions'] as $definition) {
                if (!is_array($definition)) {
                    return [[], __('The Custom Post Type list returned an invalid response.', 'wpessential')];
                }
                $definitions[] = $definition;
            }

            return [$definitions, null];
        } catch (Throwable) {
            return [[], __('Custom Post Type definitions could not be loaded.', 'wpessential')];
        }
    }
}
