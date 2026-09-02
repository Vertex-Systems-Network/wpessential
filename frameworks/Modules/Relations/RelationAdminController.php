<?php

declare(strict_types=1);

namespace WPEssential\Modules\Relations;

if (!defined('ABSPATH')) {
    exit;
}

use RuntimeException;
use Throwable;
use WPEssential\Platform\Abilities\AbilityRegistry;
use WPEssential\Platform\Admin\PlatformAdminController;
use WPEssential\Platform\Auth\ExecutionChannel;
use WPEssential\Platform\Auth\ExecutionContext;
use WPEssential\Platform\WordPress\Abilities\WordPressExecutionContextFactory;

final class RelationAdminController
{
    public const PAGE_SLUG = 'wpessential-relations';
    public const SAVE_ACTION = 'wpessential_relations_save';
    public const STATUS_ACTION = 'wpessential_relations_status';
    public const EDGE_ACTION = 'wpessential_relations_edge';
    private const CAPABILITY = 'manage_options';

    public function __construct(
        private readonly AbilityRegistry $abilities,
        private readonly WordPressExecutionContextFactory $contexts,
        private readonly RelationAdminPayloadMapper $mapper = new RelationAdminPayloadMapper(),
    ) {}

    public function register(): void
    {
        if (!function_exists('add_action')) {
            return;
        }

        add_action('admin_menu', [$this, 'registerMenu']);
        add_action('admin_post_' . self::SAVE_ACTION, [$this, 'handleSave']);
        add_action('admin_post_' . self::STATUS_ACTION, [$this, 'handleStatus']);
        add_action('admin_post_' . self::EDGE_ACTION, [$this, 'handleEdge']);
    }

    public function registerMenu(): void
    {
        if (!function_exists('add_submenu_page')) {
            return;
        }

        add_submenu_page(
            PlatformAdminController::PAGE_SLUG,
            __('Relations', 'wpessential'),
            __('Relations', 'wpessential'),
            self::CAPABILITY,
            self::PAGE_SLUG,
            [$this, 'render'],
        );
    }

    public function render(): void
    {
        if (!$this->canManage()) {
            $this->deny();
            return;
        }

        [$definitions, $diagnostics, $readError] = $this->snapshot();
        $editingId = $this->requestString(INPUT_GET, 'relation_id');
        $editing = null;
        foreach ($definitions as $definition) {
            if (($definition['id'] ?? null) === $editingId) {
                $editing = $definition;
                break;
            }
        }

        echo '<div class="wrap">';
        echo '<h1>' . esc_html__('Relations', 'wpessential') . '</h1>';
        echo '<p>' . esc_html__('Create canonical Surface 4 relation definitions and manage individual edges through the shared Ability and Policy layer.', 'wpessential') . '</p>';
        $this->renderNotice($readError);
        $this->renderEditor($editing);
        $this->renderConnectionEditor($definitions);
        $this->renderDefinitions($definitions, $diagnostics);
        echo '</div>';
    }

    public function handleSave(): void
    {
        if (!$this->canManage()) {
            $this->deny();
            return;
        }
        if (!function_exists('check_admin_referer')) {
            throw new RuntimeException('WordPress nonce verification is unavailable.');
        }
        check_admin_referer(self::SAVE_ACTION);

        try {
            $values = $this->saveValues();
            $input = [
                'payload' => $this->mapper->map($values),
                'status' => $values['status'],
            ];
            $id = $values['id'];
            if ($id !== '') {
                $input['id'] = $id;
                $input['expected_revision'] = $this->positiveInteger(
                    $values['expected_revision'],
                    'Relation expected revision',
                );
            }

            $this->abilities->execute('wpessential/relations/save-definition', $input, $this->uiContext());
            $this->redirect('saved', __('Relation saved.', 'wpessential'));
        } catch (Throwable $error) {
            $this->redirect('error', $error->getMessage());
        }
    }

    public function handleStatus(): void
    {
        if (!$this->canManage()) {
            $this->deny();
            return;
        }
        if (!function_exists('check_admin_referer')) {
            throw new RuntimeException('WordPress nonce verification is unavailable.');
        }
        check_admin_referer(self::STATUS_ACTION);

        try {
            $id = $this->requestString(INPUT_POST, 'id');
            $status = $this->requestString(INPUT_POST, 'status');
            $revision = $this->positiveInteger(
                $this->requestString(INPUT_POST, 'expected_revision'),
                'Relation expected revision',
            );
            $this->abilities->execute('wpessential/relations/status-definition', [
                'id' => $id,
                'status' => $status,
                'expected_revision' => $revision,
            ], $this->uiContext());
            $this->redirect('saved', __('Relation lifecycle status updated.', 'wpessential'));
        } catch (Throwable $error) {
            $this->redirect('error', $error->getMessage());
        }
    }

    public function handleEdge(): void
    {
        if (!$this->canManage()) {
            $this->deny();
            return;
        }
        if (!function_exists('check_admin_referer')) {
            throw new RuntimeException('WordPress nonce verification is unavailable.');
        }
        check_admin_referer(self::EDGE_ACTION);

        try {
            $operation = $this->requestString(INPUT_POST, 'operation');
            if (!in_array($operation, ['connect', 'disconnect'], true)) {
                throw new RuntimeException('Relation edge operation is not supported.');
            }
            $relationDefinitionId = $this->requestString(INPUT_POST, 'relation_definition_id');
            $fromObjectId = $this->positiveInteger(
                $this->requestString(INPUT_POST, 'from_object_id'),
                'Relation source object id',
            );
            $toObjectId = $this->positiveInteger(
                $this->requestString(INPUT_POST, 'to_object_id'),
                'Relation target object id',
            );

            $result = $this->abilities->execute(
                'wpessential/relations/' . $operation,
                [
                    'relation_definition_id' => $relationDefinitionId,
                    'from_object_id' => $fromObjectId,
                    'to_object_id' => $toObjectId,
                ],
                $this->uiContext(),
            );
            $changed = is_array($result)
                && is_array($result['mutation'] ?? null)
                && ($result['mutation']['changed'] ?? null) === true;
            $this->redirect(
                'saved',
                $changed
                    ? __('Relation edge updated.', 'wpessential')
                    : __('Relation edge was already in the requested state.', 'wpessential'),
            );
        } catch (Throwable $error) {
            $this->redirect('error', $error->getMessage());
        }
    }

    /** @param null|array<string,mixed> $definition */
    private function renderEditor(?array $definition): void
    {
        $payload = is_array($definition['payload'] ?? null) ? $definition['payload'] : [];
        $direction = is_array($payload['direction'] ?? null) ? $payload['direction'] : [];
        $from = is_array($payload['from'] ?? null) ? $payload['from'] : [];
        $to = is_array($payload['to'] ?? null) ? $payload['to'] : [];
        $bounds = is_array($payload['bounds'] ?? null) ? $payload['bounds'] : [];
        $editing = $definition !== null;

        echo '<section aria-labelledby="wpessential-relations-editor-title">';
        echo '<h2 id="wpessential-relations-editor-title">' . esc_html($editing ? __('Edit relation', 'wpessential') : __('Add relation', 'wpessential')) . '</h2>';
        echo '<form method="post" action="' . esc_url(admin_url('admin-post.php')) . '">';
        echo '<input type="hidden" name="action" value="' . esc_attr(self::SAVE_ACTION) . '">';
        if (function_exists('wp_nonce_field')) {
            wp_nonce_field(self::SAVE_ACTION);
        }
        echo '<input type="hidden" name="id" value="' . esc_attr((string) ($definition['id'] ?? '')) . '">';
        echo '<input type="hidden" name="expected_revision" value="' . esc_attr((string) ($definition['revision'] ?? '')) . '">';

        echo '<table class="form-table" role="presentation"><tbody>';
        $this->textRow('relation_key', __('Relation key', 'wpessential'), (string) ($payload['relation_key'] ?? ''), true, $editing);
        $this->textRow('title', __('Title', 'wpessential'), (string) ($payload['title'] ?? ''), true);
        $this->textareaRow('description', __('Description', 'wpessential'), (string) ($payload['description'] ?? ''));
        $this->selectRow('cardinality', __('Cardinality', 'wpessential'), (string) ($payload['cardinality'] ?? 'many_to_many'), [
            'one_to_one' => __('One to one', 'wpessential'),
            'one_to_many' => __('One to many', 'wpessential'),
            'many_to_one' => __('Many to one', 'wpessential'),
            'many_to_many' => __('Many to many', 'wpessential'),
        ]);
        $this->endpointRows('from', __('Source', 'wpessential'), $from);
        $this->endpointRows('to', __('Target', 'wpessential'), $to);
        $this->numberRow('from_min', __('Source minimum', 'wpessential'), $bounds['from_min'] ?? 0, 0);
        $this->numberRow('from_max', __('Source maximum', 'wpessential'), $bounds['from_max'] ?? null, 1, true);
        $this->numberRow('to_min', __('Target minimum', 'wpessential'), $bounds['to_min'] ?? 0, 0);
        $this->numberRow('to_max', __('Target maximum', 'wpessential'), $bounds['to_max'] ?? null, 1, true);
        $this->checkboxRow('reciprocal', __('Reciprocal composition', 'wpessential'), ($direction['reciprocal'] ?? false) === true);
        $this->checkboxRow('bidirectional_traversal', __('Bidirectional traversal', 'wpessential'), ($direction['bidirectional_traversal'] ?? true) === true);
        $this->checkboxRow('unique_edge', __('Unique source/target tuple', 'wpessential'), ($payload['unique_edge'] ?? true) === true);
        $this->selectRow('status', __('Lifecycle status', 'wpessential'), (string) ($definition['status'] ?? 'draft'), [
            'draft' => __('Draft', 'wpessential'),
            'published' => __('Published', 'wpessential'),
            'disabled' => __('Disabled', 'wpessential'),
            'archived' => __('Archived', 'wpessential'),
        ]);
        echo '</tbody></table>';
        echo '<p class="description">' . esc_html__('Native post, taxonomy, user, comment, and media endpoints are supported. Custom-table and registered-entity endpoints remain unavailable until their owner adapters are certified.', 'wpessential') . '</p>';
        echo '<p class="submit"><button type="submit" class="button button-primary">' . esc_html__('Save relation', 'wpessential') . '</button>';
        if ($editing) {
            echo ' <a class="button" href="' . esc_url($this->pageUrl()) . '">' . esc_html__('Cancel edit', 'wpessential') . '</a>';
        }
        echo '</p></form></section>';
    }

    /** @param list<array<string,mixed>> $definitions */
    private function renderConnectionEditor(array $definitions): void
    {
        echo '<section aria-labelledby="wpessential-relations-edge-title">';
        echo '<h2 id="wpessential-relations-edge-title">' . esc_html__('Manage connection', 'wpessential') . '</h2>';
        echo '<p>' . esc_html__('Connect or disconnect one source/target pair. Endpoint existence, resource authorization, cardinality and transactional integrity are rechecked server-side.', 'wpessential') . '</p>';
        if ($definitions === []) {
            echo '<p><em>' . esc_html__('Create a relation definition first.', 'wpessential') . '</em></p></section>';
            return;
        }

        echo '<form method="post" action="' . esc_url(admin_url('admin-post.php')) . '">';
        echo '<input type="hidden" name="action" value="' . esc_attr(self::EDGE_ACTION) . '">';
        if (function_exists('wp_nonce_field')) {
            wp_nonce_field(self::EDGE_ACTION);
        }
        echo '<table class="form-table" role="presentation"><tbody>';
        echo '<tr><th scope="row"><label for="wpe-rel-edge-definition">' . esc_html__('Relation', 'wpessential') . '</label></th><td>';
        echo '<select id="wpe-rel-edge-definition" name="relation_definition_id" required>';
        foreach ($definitions as $definition) {
            if (($definition['status'] ?? null) !== 'published') {
                continue;
            }
            $payload = is_array($definition['payload'] ?? null) ? $definition['payload'] : [];
            $id = is_string($definition['id'] ?? null) ? $definition['id'] : '';
            $title = is_string($payload['title'] ?? null) ? $payload['title'] : $id;
            echo '<option value="' . esc_attr($id) . '">' . esc_html($title) . '</option>';
        }
        echo '</select></td></tr>';
        $this->numberRow('from_object_id', __('Source object ID', 'wpessential'), null, 1);
        $this->numberRow('to_object_id', __('Target object ID', 'wpessential'), null, 1);
        $this->selectRow('operation', __('Operation', 'wpessential'), 'connect', [
            'connect' => __('Connect', 'wpessential'),
            'disconnect' => __('Disconnect', 'wpessential'),
        ]);
        echo '</tbody></table><p class="submit"><button type="submit" class="button">' . esc_html__('Apply connection change', 'wpessential') . '</button></p>';
        echo '</form></section>';
    }

    /** @param list<array<string,mixed>> $definitions @param array<string,array<string,mixed>> $diagnostics */
    private function renderDefinitions(array $definitions, array $diagnostics): void
    {
        echo '<section aria-labelledby="wpessential-relations-list-title">';
        echo '<h2 id="wpessential-relations-list-title">' . esc_html__('Saved relations', 'wpessential') . '</h2>';
        echo '<table class="widefat striped"><thead><tr>';
        foreach ([__('Title', 'wpessential'), __('Key', 'wpessential'), __('Cardinality', 'wpessential'), __('Status', 'wpessential'), __('Revision', 'wpessential'), __('Health', 'wpessential'), __('Actions', 'wpessential')] as $heading) {
            echo '<th scope="col">' . esc_html($heading) . '</th>';
        }
        echo '</tr></thead><tbody>';

        foreach ($definitions as $definition) {
            $payload = is_array($definition['payload'] ?? null) ? $definition['payload'] : [];
            $id = is_string($definition['id'] ?? null) ? $definition['id'] : '';
            $revision = is_int($definition['revision'] ?? null) ? $definition['revision'] : 0;
            $status = is_string($definition['status'] ?? null) ? $definition['status'] : '';
            $diagnostic = $diagnostics[$id] ?? [];
            $issues = is_array($diagnostic['issues'] ?? null) ? $diagnostic['issues'] : [];
            $health = $issues === [] ? __('Healthy', 'wpessential') : sprintf(__('Issues: %d', 'wpessential'), count($issues));

            echo '<tr>';
            echo '<td><strong>' . esc_html((string) ($payload['title'] ?? '')) . '</strong></td>';
            echo '<td><code>' . esc_html((string) ($payload['relation_key'] ?? '')) . '</code></td>';
            echo '<td>' . esc_html((string) ($payload['cardinality'] ?? '')) . '</td>';
            echo '<td>' . esc_html($status) . '</td>';
            echo '<td>' . esc_html((string) $revision) . '</td>';
            echo '<td>' . esc_html($health) . '</td><td>';
            echo '<a class="button button-small" href="' . esc_url($this->pageUrl(['relation_id' => $id])) . '">' . esc_html__('Edit', 'wpessential') . '</a> ';
            if ($status !== 'published') {
                $this->statusButton($id, $revision, 'published', __('Publish', 'wpessential'));
            } else {
                $this->statusButton($id, $revision, 'disabled', __('Disable', 'wpessential'));
            }
            if ($status !== 'archived') {
                echo ' ';
                $this->statusButton($id, $revision, 'archived', __('Archive', 'wpessential'));
            }
            echo '</td></tr>';
        }

        if ($definitions === []) {
            echo '<tr><td colspan="7">' . esc_html__('No Relation definitions have been created yet.', 'wpessential') . '</td></tr>';
        }
        echo '</tbody></table></section>';
    }

    private function statusButton(string $id, int $revision, string $status, string $label): void
    {
        echo '<form method="post" action="' . esc_url(admin_url('admin-post.php')) . '" style="display:inline">';
        echo '<input type="hidden" name="action" value="' . esc_attr(self::STATUS_ACTION) . '">';
        echo '<input type="hidden" name="id" value="' . esc_attr($id) . '">';
        echo '<input type="hidden" name="expected_revision" value="' . esc_attr((string) $revision) . '">';
        echo '<input type="hidden" name="status" value="' . esc_attr($status) . '">';
        if (function_exists('wp_nonce_field')) {
            wp_nonce_field(self::STATUS_ACTION);
        }
        echo '<button type="submit" class="button button-small">' . esc_html($label) . '</button></form>';
    }

    /** @param array<string,mixed> $endpoint */
    private function endpointRows(string $side, string $label, array $endpoint): void
    {
        $this->selectRow($side . '_type', $label . ' ' . __('type', 'wpessential'), (string) ($endpoint['object_type'] ?? ($side === 'from' ? 'post' : 'user')), [
            'post' => __('Post', 'wpessential'),
            'term' => __('Term', 'wpessential'),
            'user' => __('User', 'wpessential'),
            'comment' => __('Comment', 'wpessential'),
            'media' => __('Media', 'wpessential'),
        ]);
        $this->textRow($side . '_subtype', $label . ' ' . __('subtype', 'wpessential'), (string) ($endpoint['object_subtype'] ?? ''), false);
        $this->textRow($side . '_label', $label . ' ' . __('label', 'wpessential'), (string) ($endpoint['label'] ?? $label), true);
    }

    private function textRow(string $name, string $label, string $value, bool $required, bool $readonly = false): void
    {
        echo '<tr><th scope="row"><label for="' . esc_attr('wpe-rel-' . $name) . '">' . esc_html($label) . '</label></th><td>';
        echo '<input class="regular-text" id="' . esc_attr('wpe-rel-' . $name) . '" name="' . esc_attr($name) . '" type="text" value="' . esc_attr($value) . '"';
        echo $required ? ' required' : '';
        echo $readonly ? ' readonly aria-readonly="true"' : '';
        echo '></td></tr>';
    }

    private function textareaRow(string $name, string $label, string $value): void
    {
        echo '<tr><th scope="row"><label for="' . esc_attr('wpe-rel-' . $name) . '">' . esc_html($label) . '</label></th><td>';
        echo '<textarea class="large-text" rows="3" id="' . esc_attr('wpe-rel-' . $name) . '" name="' . esc_attr($name) . '">' . esc_textarea($value) . '</textarea></td></tr>';
    }

    /** @param array<string,string> $options */
    private function selectRow(string $name, string $label, string $value, array $options): void
    {
        echo '<tr><th scope="row"><label for="' . esc_attr('wpe-rel-' . $name) . '">' . esc_html($label) . '</label></th><td>';
        echo '<select id="' . esc_attr('wpe-rel-' . $name) . '" name="' . esc_attr($name) . '">';
        foreach ($options as $optionValue => $optionLabel) {
            echo '<option value="' . esc_attr($optionValue) . '"' . ($optionValue === $value ? ' selected' : '') . '>' . esc_html($optionLabel) . '</option>';
        }
        echo '</select></td></tr>';
    }

    private function numberRow(string $name, string $label, mixed $value, int $minimum, bool $blankAllowed = false): void
    {
        $display = is_int($value) ? (string) $value : '';
        echo '<tr><th scope="row"><label for="' . esc_attr('wpe-rel-' . $name) . '">' . esc_html($label) . '</label></th><td>';
        echo '<input class="small-text" id="' . esc_attr('wpe-rel-' . $name) . '" name="' . esc_attr($name) . '" type="number" min="' . esc_attr((string) $minimum) . '" value="' . esc_attr($display) . '"';
        echo $blankAllowed ? '' : ' required';
        echo '></td></tr>';
    }

    private function checkboxRow(string $name, string $label, bool $checked): void
    {
        echo '<tr><th scope="row">' . esc_html($label) . '</th><td><label>';
        echo '<input type="hidden" name="' . esc_attr($name) . '" value="0">';
        echo '<input type="checkbox" name="' . esc_attr($name) . '" value="1"' . ($checked ? ' checked' : '') . '> ';
        echo esc_html__('Enabled', 'wpessential') . '</label></td></tr>';
    }

    /** @return array{list<array<string,mixed>>,array<string,array<string,mixed>>,?string} */
    private function snapshot(): array
    {
        try {
            $context = $this->uiContext();
            $listed = $this->abilities->execute('wpessential/relations/list-definitions', [], $context);
            $diagnosticResult = $this->abilities->execute('wpessential/relations/diagnostics', [], $context);
            if (!is_array($listed) || !is_array($listed['definitions'] ?? null)
                || !is_array($diagnosticResult) || !is_array($diagnosticResult['relations'] ?? null)
            ) {
                throw new RuntimeException('Relations returned an invalid admin snapshot.');
            }

            $definitions = [];
            foreach ($listed['definitions'] as $definition) {
                if (!is_array($definition)) {
                    throw new RuntimeException('Relations list returned an invalid definition.');
                }
                $definitions[] = $definition;
            }

            $diagnostics = [];
            foreach ($diagnosticResult['relations'] as $diagnostic) {
                if (!is_array($diagnostic) || !is_string($diagnostic['id'] ?? null)) {
                    continue;
                }
                $diagnostics[$diagnostic['id']] = $diagnostic;
            }

            return [$definitions, $diagnostics, null];
        } catch (Throwable $error) {
            return [[], [], $error->getMessage()];
        }
    }

    private function renderNotice(?string $readError): void
    {
        $notice = $this->requestString(INPUT_GET, 'notice');
        $message = $this->requestString(INPUT_GET, 'message');
        if ($readError !== null) {
            echo '<div class="notice notice-error inline" role="alert"><p>' . esc_html($readError) . '</p></div>';
        }
        if ($notice === '' || $message === '') {
            return;
        }
        $class = $notice === 'saved' ? 'notice-success' : 'notice-error';
        echo '<div class="notice ' . esc_attr($class) . ' is-dismissible" role="status"><p>' . esc_html($message) . '</p></div>';
    }

    /** @return array<string,string> */
    private function saveValues(): array
    {
        $keys = [
            'id', 'expected_revision', 'relation_key', 'title', 'description', 'cardinality',
            'from_type', 'from_subtype', 'from_label', 'to_type', 'to_subtype', 'to_label',
            'from_min', 'from_max', 'to_min', 'to_max', 'reciprocal', 'bidirectional_traversal',
            'unique_edge', 'status',
        ];
        $values = [];
        foreach ($keys as $key) {
            $values[$key] = $this->requestString(INPUT_POST, $key, $key === 'description');
        }
        return $values;
    }

    private function requestString(int $inputType, string $key, bool $textarea = false): string
    {
        $value = filter_input($inputType, $key, FILTER_UNSAFE_RAW);
        if (!is_string($value)) {
            return '';
        }
        $value = function_exists('wp_unslash') ? wp_unslash($value) : $value;
        if ($textarea && function_exists('sanitize_textarea_field')) {
            return sanitize_textarea_field($value);
        }
        return function_exists('sanitize_text_field') ? sanitize_text_field($value) : trim(strip_tags($value));
    }

    private function positiveInteger(string $value, string $label): int
    {
        if (preg_match('/^[1-9]\d*$/', $value) !== 1) {
            throw new RuntimeException($label . ' must be a positive integer.');
        }
        return (int) $value;
    }

    private function canManage(): bool
    {
        return function_exists('current_user_can') && current_user_can(self::CAPABILITY);
    }

    private function deny(): void
    {
        if (function_exists('wp_die')) {
            wp_die(
                esc_html__('You are not allowed to manage WPEssential Relations.', 'wpessential'),
                esc_html__('WPEssential', 'wpessential'),
                ['response' => 403],
            );
        }
    }

    private function redirect(string $notice, string $message): never
    {
        $url = $this->pageUrl(['notice' => $notice, 'message' => $message]);
        if (function_exists('wp_safe_redirect')) {
            wp_safe_redirect($url);
        }
        exit;
    }

    /** @param array<string,string> $args */
    private function pageUrl(array $args = []): string
    {
        $base = function_exists('admin_url') ? admin_url('admin.php') : 'admin.php';
        $query = array_merge(['page' => self::PAGE_SLUG], $args);
        return function_exists('add_query_arg') ? add_query_arg($query, $base) : $base . '?' . http_build_query($query);
    }

    private function uiContext(): ExecutionContext
    {
        $current = $this->contexts->current();
        return new ExecutionContext(
            principal: $current->principal,
            siteId: $current->siteId,
            channel: ExecutionChannel::Ui,
            networkId: $current->networkId,
            correlationId: $current->correlationId,
        );
    }
}
