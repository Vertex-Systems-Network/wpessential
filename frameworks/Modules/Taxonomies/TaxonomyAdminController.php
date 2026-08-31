<?php

declare(strict_types=1);

namespace WPEssential\Modules\Taxonomies;

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

final class TaxonomyAdminController
{
    public const PAGE_SLUG = 'wpessential-taxonomy';
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
            throw new InvalidArgumentException('Taxonomy admin AJAX action cannot be blank.');
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
            __('Taxonomies', 'wpessential'),
            __('Taxonomies', 'wpessential'),
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
        $entry = $this->assets->entry('taxonomy', 'taxonomy-admin');
        if ($entry === null) {
            return;
        }
        $version = $entry['version'] ?? (defined('WPE_VERSION') ? (string) WPE_VERSION : null);
        foreach ($entry['styles'] as $index => $style) {
            wp_enqueue_style('wpessential-taxonomy-admin-' . $index, $style, [], $version);
        }
        wp_enqueue_script(
            'wpessential-taxonomy-admin',
            $entry['script'],
            $entry['dependencies'],
            $version,
            true,
        );
        if (function_exists('wp_script_add_data')) {
            wp_script_add_data('wpessential-taxonomy-admin', 'strategy', 'defer');
        }
    }

    public function render(): void
    {
        if (!function_exists('current_user_can') || !current_user_can(self::CAPABILITY)) {
            if (function_exists('wp_die')) {
                wp_die(
                    esc_html__('You are not allowed to manage WPEssential taxonomies.', 'wpessential'),
                    esc_html__('WPEssential', 'wpessential'),
                    ['response' => 403],
                );
            }
            return;
        }

        [$definitions, $readError] = $this->readDefinitions();
        $bootstrap = [
            'surface' => 'taxonomies',
            'ajaxUrl' => function_exists('admin_url') ? admin_url('admin-ajax.php') : '',
            'ajaxAction' => $this->ajaxAction,
            'routes' => [
                'list' => ['type' => 'taxonomy.list', 'nonce' => $this->ajax->createNonce('taxonomy.list')],
                'validate' => ['type' => 'taxonomy.validate', 'nonce' => $this->ajax->createNonce('taxonomy.validate')],
                'save' => ['type' => 'taxonomy.save', 'nonce' => $this->ajax->createNonce('taxonomy.save')],
                'status' => ['type' => 'taxonomy.status', 'nonce' => $this->ajax->createNonce('taxonomy.status')],
            ],
            'definitions' => $definitions,
        ];
        $json = function_exists('wp_json_encode')
            ? wp_json_encode($bootstrap, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT)
            : json_encode($bootstrap, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT);
        $json = is_string($json) ? $json : '{}';

        echo '<div class="wrap wpessential-admin-wrap">';
        echo '<section id="wpessential-taxonomy-root" data-wpessential-surface="taxonomies" aria-labelledby="wpessential-taxonomy-title">';
        echo '<h1 id="wpessential-taxonomy-title">' . esc_html__('Taxonomies', 'wpessential') . '</h1>';
        echo '<p>' . esc_html__('Create and manage canonical WPEssential taxonomy definitions. This builder uses the same revision-safe Policy and Ability path as runtime registration.', 'wpessential') . '</p>';
        if ($readError !== null) {
            echo '<div class="notice notice-error inline" role="alert"><p>' . esc_html($readError) . '</p></div>';
        }
        echo '<div id="wpessential-taxonomy-notice" class="notice inline wpessential-cpt-notice" role="status" aria-live="polite" hidden><p></p></div>';
        $this->renderEditor();
        $this->renderDefinitions($definitions);
        echo '<noscript><p class="notice notice-warning inline">' . esc_html__('JavaScript is required to create or change Taxonomy definitions. Existing definitions remain readable in the table.', 'wpessential') . '</p></noscript>';
        echo '</section>';
        echo '<script id="wpessential-taxonomy-bootstrap" type="application/json">' . $json . '</script>';
        echo '</div>';
    }

    private function renderEditor(): void
    {
        echo '<section class="wpessential-cpt-panel" aria-labelledby="wpessential-taxonomy-editor-title">';
        echo '<h2 id="wpessential-taxonomy-editor-title">' . esc_html__('Add taxonomy', 'wpessential') . '</h2>';
        echo '<form id="wpessential-taxonomy-form">';
        echo '<input type="hidden" id="wpessential-taxonomy-id" value="">';
        echo '<input type="hidden" id="wpessential-taxonomy-revision" value="">';
        echo '<div class="wpessential-cpt-grid">';
        $this->renderTextField('wpessential-taxonomy-key', __('Taxonomy key', 'wpessential'), 'taxonomy_key', 'book_genre', true, __('Lowercase; maximum 32 characters. Existing runtime keys are immutable.', 'wpessential'));
        $this->renderTextField('wpessential-taxonomy-name', __('Plural name', 'wpessential'), 'name', 'Genres', true);
        $this->renderTextField('wpessential-taxonomy-singular', __('Singular name', 'wpessential'), 'singular_name', 'Genre', true);
        $this->renderTextField('wpessential-taxonomy-description', __('Description', 'wpessential'), 'description', '', false);
        $this->renderTextField('wpessential-taxonomy-object-types', __('Object types', 'wpessential'), 'object_types', 'post, book', true, __('Comma-separated WordPress post type keys. Missing runtime object types are reported as compatibility warnings.', 'wpessential'));
        echo '</div>';

        echo '<fieldset class="wpessential-cpt-options"><legend>' . esc_html__('Behavior', 'wpessential') . '</legend>';
        $this->renderCheckbox('wpessential-taxonomy-public', __('Public', 'wpessential'), true);
        $this->renderCheckbox('wpessential-taxonomy-rest', __('Show in REST API', 'wpessential'), true);
        $this->renderCheckbox('wpessential-taxonomy-hierarchical', __('Hierarchical', 'wpessential'), false);
        $this->renderCheckbox('wpessential-taxonomy-admin-column', __('Show admin column', 'wpessential'), false);
        echo '</fieldset>';

        echo '<p><label for="wpessential-taxonomy-status"><strong>' . esc_html__('Lifecycle status', 'wpessential') . '</strong></label><br>';
        echo '<select id="wpessential-taxonomy-status">';
        foreach ([
            'draft' => __('Draft', 'wpessential'),
            'published' => __('Published', 'wpessential'),
            'disabled' => __('Disabled', 'wpessential'),
            'archived' => __('Archived', 'wpessential'),
        ] as $value => $label) {
            echo '<option value="' . esc_attr($value) . '">' . esc_html($label) . '</option>';
        }
        echo '</select></p>';

        echo '<div id="wpessential-taxonomy-validation" class="wpessential-cpt-validation" role="status" aria-live="polite" aria-labelledby="wpessential-taxonomy-validation-title" hidden>';
        echo '<h3 id="wpessential-taxonomy-validation-title">' . esc_html__('Validation report', 'wpessential') . '</h3>';
        echo '<p data-wpessential-taxonomy-validation-summary></p>';
        echo '<ul data-wpessential-taxonomy-validation-issues></ul>';
        echo '</div>';

        echo '<p class="submit">';
        echo '<button type="button" class="button" id="wpessential-taxonomy-validate">' . esc_html__('Validate', 'wpessential') . '</button> ';
        echo '<button type="submit" class="button button-primary" id="wpessential-taxonomy-save">' . esc_html__('Save taxonomy', 'wpessential') . '</button> ';
        echo '<button type="button" class="button" id="wpessential-taxonomy-cancel" hidden>' . esc_html__('Cancel edit', 'wpessential') . '</button>';
        echo '</p></form></section>';
    }

    /** @param list<array<string,mixed>> $definitions */
    private function renderDefinitions(array $definitions): void
    {
        echo '<section class="wpessential-cpt-panel" aria-labelledby="wpessential-taxonomy-list-title">';
        echo '<div class="wpessential-cpt-list-heading"><h2 id="wpessential-taxonomy-list-title">' . esc_html__('Saved taxonomies', 'wpessential') . '</h2>';
        echo '<button type="button" class="button" id="wpessential-taxonomy-refresh">' . esc_html__('Refresh', 'wpessential') . '</button></div>';
        echo '<table class="widefat striped" id="wpessential-taxonomy-table" aria-label="' . esc_attr__('Saved taxonomies', 'wpessential') . '">';
        echo '<thead><tr><th scope="col">' . esc_html__('Name', 'wpessential') . '</th><th scope="col">' . esc_html__('Key', 'wpessential') . '</th><th scope="col">' . esc_html__('Object types', 'wpessential') . '</th><th scope="col">' . esc_html__('Status', 'wpessential') . '</th><th scope="col">' . esc_html__('Revision', 'wpessential') . '</th><th scope="col">' . esc_html__('Actions', 'wpessential') . '</th></tr></thead>';
        echo '<tbody id="wpessential-taxonomy-rows">';
        foreach ($definitions as $definition) {
            $this->renderDefinitionRow($definition);
        }
        if ($definitions === []) {
            echo '<tr data-wpessential-taxonomy-empty><td colspan="6">' . esc_html__('No taxonomies have been created yet.', 'wpessential') . '</td></tr>';
        }
        echo '</tbody></table></section>';
    }

    /** @param array<string,mixed> $definition */
    private function renderDefinitionRow(array $definition): void
    {
        $payload = is_array($definition['payload'] ?? null) ? $definition['payload'] : [];
        $id = is_string($definition['id'] ?? null) ? $definition['id'] : '';
        $name = is_string($payload['name'] ?? null) ? $payload['name'] : '';
        $key = is_string($payload['taxonomy_key'] ?? null) ? $payload['taxonomy_key'] : '';
        $objectTypes = is_array($payload['object_types'] ?? null)
            ? implode(', ', array_filter($payload['object_types'], 'is_string'))
            : '';
        $status = is_string($definition['status'] ?? null) ? $definition['status'] : '';
        $revision = is_int($definition['revision'] ?? null) ? $definition['revision'] : 0;

        echo '<tr data-wpessential-taxonomy-row="' . esc_attr($id) . '">';
        echo '<td><strong>' . esc_html($name) . '</strong></td><td><code>' . esc_html($key) . '</code></td>';
        echo '<td>' . esc_html($objectTypes) . '</td><td>' . esc_html(ucfirst($status)) . '</td><td>' . esc_html((string) $revision) . '</td>';
        echo '<td><button type="button" class="button button-small" data-wpessential-taxonomy-edit="' . esc_attr($id) . '">' . esc_html__('Edit', 'wpessential') . '</button> ';
        if ($status === 'published') {
            echo '<button type="button" class="button button-small" data-wpessential-taxonomy-status="disabled" data-wpessential-taxonomy-id="' . esc_attr($id) . '">' . esc_html__('Disable', 'wpessential') . '</button> ';
        } else {
            echo '<button type="button" class="button button-small" data-wpessential-taxonomy-status="published" data-wpessential-taxonomy-id="' . esc_attr($id) . '">' . esc_html__('Publish', 'wpessential') . '</button> ';
        }
        if ($status !== 'archived') {
            echo '<button type="button" class="button button-small" data-wpessential-taxonomy-status="archived" data-wpessential-taxonomy-id="' . esc_attr($id) . '">' . esc_html__('Archive', 'wpessential') . '</button>';
        }
        echo '</td></tr>';
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
        echo '<input class="regular-text" type="text" id="' . esc_attr($id) . '" data-wpessential-taxonomy-field="' . esc_attr($field) . '" placeholder="' . esc_attr($placeholder) . '"' . ($required ? ' required' : '') . '>';
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
            $result = $this->abilities->execute('wpessential/taxonomy/list', [], $context);
            if (!is_array($result) || !is_array($result['definitions'] ?? null)) {
                return [[], __('The Taxonomy list returned an invalid response.', 'wpessential')];
            }
            $definitions = [];
            foreach ($result['definitions'] as $definition) {
                if (!is_array($definition)) {
                    return [[], __('The Taxonomy list returned an invalid response.', 'wpessential')];
                }
                $definitions[] = $definition;
            }
            return [$definitions, null];
        } catch (Throwable) {
            return [[], __('Taxonomy definitions could not be loaded.', 'wpessential')];
        }
    }
}
