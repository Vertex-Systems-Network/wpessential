<?php

declare(strict_types=1);

namespace WPEssential\Modules\CustomPostTypes;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use InvalidArgumentException;
use Throwable;
use WPEssential\Platform\Abilities\AbilityRegistry;
use WPEssential\Platform\Admin\AdminAssetManifest;
use WPEssential\Platform\Admin\PlatformAdminController;
use WPEssential\Platform\Admin\Ui\Button;
use WPEssential\Platform\Admin\Ui\Check;
use WPEssential\Platform\Admin\Ui\Field;
use WPEssential\Platform\Admin\Ui\Icon;
use WPEssential\Platform\Admin\Ui\Shell;
use WPEssential\Platform\Admin\Ui\StatusPill;
use WPEssential\Platform\Auth\ExecutionChannel;
use WPEssential\Platform\Auth\ExecutionContext;
use WPEssential\Platform\WordPress\Abilities\WordPressExecutionContextFactory;
use WPEssential\Platform\WordPress\Ajax\AjaxDispatcher;

final class CustomPostTypeAdminController {

	public const PAGE_SLUG   = 'wpessential-cpt';
	private const CAPABILITY = 'manage_options';

	private ?string $hookSuffix = null;

	public function __construct(
		private readonly AbilityRegistry $abilities,
		private readonly WordPressExecutionContextFactory $contexts,
		private readonly AjaxDispatcher $ajax,
		private readonly AdminAssetManifest $assets,
		private readonly string $ajaxAction,
	) {
		if ( trim( $this->ajaxAction ) === '' ) {
			throw new InvalidArgumentException( 'CPT admin AJAX action cannot be blank.' );
		}
	}

	public function register(): void {
		if ( ! function_exists( 'add_action' ) ) {
			return;
		}

		add_action( 'admin_menu', [ $this, 'registerMenu' ] );
		add_action( 'admin_enqueue_scripts', [ $this, 'enqueueAssets' ] );
	}

	public function registerMenu(): void {
		if ( ! function_exists( 'add_submenu_page' ) ) {
			return;
		}

		$hook = add_submenu_page(
			PlatformAdminController::PAGE_SLUG,
			__( 'Custom Post Types', 'wpessential' ),
			__( 'Custom Post Types', 'wpessential' ),
			self::CAPABILITY,
			self::PAGE_SLUG,
			[ $this, 'render' ],
		);

		$this->hookSuffix = is_string( $hook ) && $hook !== '' ? $hook : null;
	}

	public function enqueueAssets( string $hookSuffix ): void {
		if ( $this->hookSuffix === null || $hookSuffix !== $this->hookSuffix ) {
			return;
		}

		$entry = $this->assets->entry();
		if ( $entry === null ) {
			return;
		}

		$version = $entry['version'] ?? ( defined( 'WPE_VERSION' ) ? (string) WPE_VERSION : null );
		foreach ( $entry['styles'] as $index => $style ) {
			wp_enqueue_style( 'wpessential-cpt-admin-' . $index, $style, [], $version );
		}

		wp_enqueue_script(
			'wpessential-cpt-admin',
			$entry['script'],
			$entry['dependencies'],
			$version,
			true,
		);
		if ( function_exists( 'wp_script_add_data' ) ) {
			wp_script_add_data( 'wpessential-cpt-admin', 'strategy', 'defer' );
		}
	}

	public function render(): void {
		if ( ! function_exists( 'current_user_can' ) || ! current_user_can( self::CAPABILITY ) ) {
			if ( function_exists( 'wp_die' ) ) {
				wp_die(
					esc_html__( 'You are not allowed to manage WPEssential custom post types.', 'wpessential' ),
					esc_html__( 'WPEssential', 'wpessential' ),
					[ 'response' => 403 ],
				);
			}
			return;
		}

		[$definitions, $readError] = $this->readDefinitions();
		$bootstrap                 = [
			'surface'     => 'custom-post-types',
			'ajaxUrl'     => function_exists( 'admin_url' ) ? admin_url( 'admin-ajax.php' ) : '',
			'ajaxAction'  => $this->ajaxAction,
			'routes'      => [
				'list'     => [
					'type'  => 'cpt.list',
					'nonce' => $this->ajax->createNonce( 'cpt.list' ),
				],
				'validate' => [
					'type'  => 'cpt.validate',
					'nonce' => $this->ajax->createNonce( 'cpt.validate' ),
				],
				'save'     => [
					'type'  => 'cpt.save',
					'nonce' => $this->ajax->createNonce( 'cpt.save' ),
				],
				'status'   => [
					'type'  => 'cpt.status',
					'nonce' => $this->ajax->createNonce( 'cpt.status' ),
				],
			],
			'definitions' => $definitions,
		];
		$json                      = function_exists( 'wp_json_encode' )
			? wp_json_encode( $bootstrap, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT )
			: json_encode( $bootstrap, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT );
		$json                      = is_string( $json ) ? $json : '{}';

		echo '<div class="wrap wpe-ui wpessential-admin-wrap">';
		echo Shell::topbar( self::PAGE_SLUG ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- toolkit components escape their own output.
		echo '<section id="wpessential-admin-root" class="wpe-ui-builder" data-wpessential-surface="custom-post-types" aria-labelledby="wpessential-cpt-title">';
		echo '<div class="wpe-ui-page-header"><div><p class="wpe-ui-eyebrow">' . esc_html__( 'Content modeling', 'wpessential' ) . '</p>';
		echo '<h1 id="wpessential-cpt-title">' . esc_html__( 'Custom Post Types', 'wpessential' ) . '</h1>';
		echo '<p class="wpe-ui-page-header__desc">' . esc_html__( 'Create and manage WPEssential post types. Every change is revision-safe and runs through the shared policy and ability layer.', 'wpessential' ) . '</p></div></div>';

		if ( $readError !== null ) {
			echo '<div class="notice notice-error inline" role="alert"><p>' . esc_html( $readError ) . '</p></div>';
		}

		echo '<div id="wpessential-cpt-notice" class="notice inline wpessential-cpt-notice" role="status" aria-live="polite" hidden><p></p></div>';
		echo '<div class="wpe-ui-builder__layout">';
		$this->renderDefinitions( $definitions );
		$this->renderEditor();
		echo '</div>';
		echo '<noscript><p class="notice notice-warning inline">' . esc_html__( 'JavaScript is required to create or change Custom Post Type definitions. Existing definitions remain readable in the table.', 'wpessential' ) . '</p></noscript>';
		echo '</section>';
		echo '<script id="wpessential-admin-bootstrap" type="application/json">' . $json . '</script>'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- JSON encoded with JSON_HEX_* flags.
		echo '</div>';
	}

	private function renderEditor(): void {
		echo '<section class="wpessential-cpt-panel wpe-ui-panel wpe-ui-builder__editor" aria-labelledby="wpessential-cpt-editor-title">';
		echo '<div class="wpe-ui-panel__head"><span class="wpe-ui-icon">' . Icon::render( 'file', 20 ) . '</span><h2 id="wpessential-cpt-editor-title">' . esc_html__( 'Add custom post type', 'wpessential' ) . '</h2></div>'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- toolkit components escape their own output.
		echo '<form id="wpessential-cpt-form">';
		echo '<input type="hidden" id="wpessential-cpt-id" value="">';
		echo '<input type="hidden" id="wpessential-cpt-revision" value="">';
		echo '<h3 class="wpe-ui-builder__section">' . esc_html__( 'Basics', 'wpessential' ) . '</h3>';
		echo '<div class="wpessential-cpt-grid wpe-ui-field-grid">';
		$this->renderTextField( 'wpessential-cpt-key', __( 'Post type key', 'wpessential' ), 'post_type_key', 'book', true, __( 'Lowercase; maximum 20 characters. Existing runtime keys are immutable through this first UI slice.', 'wpessential' ) );
		$this->renderTextField( 'wpessential-cpt-name', __( 'Plural name', 'wpessential' ), 'name', 'Books', true );
		$this->renderTextField( 'wpessential-cpt-singular', __( 'Singular name', 'wpessential' ), 'singular_name', 'Book', true );
		$this->renderTextField( 'wpessential-cpt-description', __( 'Description', 'wpessential' ), 'description', '', false );
		echo '</div>';

		echo '<fieldset class="wpessential-cpt-options wpe-ui-checks"><legend class="wpe-ui-builder__section">' . esc_html__( 'Behavior', 'wpessential' ) . '</legend>';
		$this->renderCheckbox( 'wpessential-cpt-public', __( 'Public', 'wpessential' ), true );
		$this->renderCheckbox( 'wpessential-cpt-rest', __( 'Show in REST API', 'wpessential' ), true );
		$this->renderCheckbox( 'wpessential-cpt-hierarchical', __( 'Hierarchical', 'wpessential' ), false );
		$this->renderCheckbox( 'wpessential-cpt-archive', __( 'Has archive', 'wpessential' ), false );
		echo '</fieldset>';

		echo '<fieldset class="wpessential-cpt-options wpe-ui-checks"><legend class="wpe-ui-builder__section">' . esc_html__( 'Editor supports', 'wpessential' ) . '</legend>';
		foreach ( [
			'title'           => __( 'Title', 'wpessential' ),
			'editor'          => __( 'Editor', 'wpessential' ),
			'thumbnail'       => __( 'Featured image', 'wpessential' ),
			'excerpt'         => __( 'Excerpt', 'wpessential' ),
			'custom-fields'   => __( 'Custom fields', 'wpessential' ),
			'comments'        => __( 'Comments', 'wpessential' ),
			'revisions'       => __( 'Revisions', 'wpessential' ),
			'page-attributes' => __( 'Page attributes', 'wpessential' ),
		] as $support => $label ) {
			$checked = in_array( $support, [ 'title', 'editor' ], true );
			echo Check::render( $label, [ 'data-wpessential-cpt-support' => $support ], $checked ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- toolkit components escape their own output.
		}
		echo '</fieldset>';

		echo '<p class="wpe-ui-field"><label for="wpessential-cpt-status">' . esc_html__( 'Lifecycle status', 'wpessential' ) . '</label>';
		echo '<select id="wpessential-cpt-status">';
		foreach ( [
			'draft'     => __( 'Draft', 'wpessential' ),
			'published' => __( 'Published', 'wpessential' ),
			'disabled'  => __( 'Disabled', 'wpessential' ),
			'archived'  => __( 'Archived', 'wpessential' ),
		] as $value => $label ) {
			echo '<option value="' . esc_attr( $value ) . '">' . esc_html( $label ) . '</option>';
		}
		echo '</select></p>';

		echo '<div id="wpessential-cpt-validation" class="wpessential-cpt-validation" role="status" aria-live="polite" aria-labelledby="wpessential-cpt-validation-title" hidden>';
		echo '<h3 id="wpessential-cpt-validation-title">' . esc_html__( 'Validation report', 'wpessential' ) . '</h3>';
		echo '<p data-wpessential-cpt-validation-summary></p>';
		echo '<ul data-wpessential-cpt-validation-issues></ul>';
		echo '</div>';

		echo '<div class="wpe-ui-builder__actions">';
		$html = Button::button(
			__( 'Cancel edit', 'wpessential' ),
			'ghost',
			[
				'id'     => 'wpessential-cpt-cancel',
				'hidden' => true,
			]
		);
		echo $html; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- toolkit components escape their own output.
		echo Button::button( __( 'Validate', 'wpessential' ), 'secondary', [ 'id' => 'wpessential-cpt-validate' ] ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- toolkit components escape their own output.
		$html = Button::button(
			__( 'Save custom post type', 'wpessential' ),
			'primary',
			[
				'type' => 'submit',
				'id'   => 'wpessential-cpt-save',
			]
		);
		echo $html; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- toolkit components escape their own output.
		echo '</div>';
		echo '</form>';
		echo '</section>';
	}

	/** @param list<array<string,mixed>> $definitions */
	private function renderDefinitions( array $definitions ): void {
		echo '<section class="wpessential-cpt-panel wpe-ui-panel wpe-ui-panel--flush wpe-ui-builder__list" aria-labelledby="wpessential-cpt-list-title">';
		echo '<div class="wpessential-cpt-list-heading wpe-ui-panel__head"><span class="wpe-ui-icon">' . Icon::render( 'layers', 20 ) . '</span><h2 id="wpessential-cpt-list-title">' . esc_html__( 'Saved custom post types', 'wpessential' ) . '</h2>'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- toolkit components escape their own output.
		echo Button::button( __( 'Refresh', 'wpessential' ), 'ghost', [ 'id' => 'wpessential-cpt-refresh' ], true, 'refresh' ) . '</div>'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- toolkit components escape their own output.
		echo '<table class="widefat striped" id="wpessential-cpt-table" aria-label="' . esc_attr__( 'Saved custom post types', 'wpessential' ) . '">';
		echo '<thead><tr><th scope="col">' . esc_html__( 'Name', 'wpessential' ) . '</th><th scope="col">' . esc_html__( 'Key', 'wpessential' ) . '</th><th scope="col">' . esc_html__( 'Status', 'wpessential' ) . '</th><th scope="col">' . esc_html__( 'Revision', 'wpessential' ) . '</th><th scope="col">' . esc_html__( 'Actions', 'wpessential' ) . '</th></tr></thead>';
		echo '<tbody id="wpessential-cpt-rows">';
		foreach ( $definitions as $definition ) {
			$this->renderDefinitionRow( $definition );
		}
		if ( $definitions === [] ) {
			echo '<tr data-wpessential-cpt-empty><td colspan="5">' . esc_html__( 'No custom post types have been created yet.', 'wpessential' ) . '</td></tr>';
		}
		echo '</tbody></table>';
		echo '</section>';
	}

	public static function statusPill( string $status ): string {
		$tones = [
			'published' => 'running',
			'draft'     => 'waiting',
			'disabled'  => 'off',
			'archived'  => 'locked',
		];

		return StatusPill::render( $tones[ $status ] ?? 'off', ucfirst( $status ) );
	}

	/** @param array<string,mixed> $definition */
	private function renderDefinitionRow( array $definition ): void {
		$payload  = is_array( $definition['payload'] ?? null ) ? $definition['payload'] : [];
		$id       = is_string( $definition['id'] ?? null ) ? $definition['id'] : '';
		$name     = is_string( $payload['name'] ?? null ) ? $payload['name'] : '';
		$key      = is_string( $payload['post_type_key'] ?? null ) ? $payload['post_type_key'] : '';
		$status   = is_string( $definition['status'] ?? null ) ? $definition['status'] : '';
		$revision = is_int( $definition['revision'] ?? null ) ? $definition['revision'] : 0;

		echo '<tr data-wpessential-cpt-row="' . esc_attr( $id ) . '">';
		echo '<td><strong>' . esc_html( $name ) . '</strong></td>';
		echo '<td><code>' . esc_html( $key ) . '</code></td>';
		echo '<td>' . self::statusPill( $status ) . '</td>'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- StatusPill escapes.
		echo '<td>' . esc_html( (string) $revision ) . '</td>';
		echo '<td><button type="button" class="button button-small" data-wpessential-cpt-edit="' . esc_attr( $id ) . '">' . esc_html__( 'Edit', 'wpessential' ) . '</button> ';
		if ( $status === 'published' ) {
			echo '<button type="button" class="button button-small" data-wpessential-cpt-status="disabled" data-wpessential-cpt-id="' . esc_attr( $id ) . '">' . esc_html__( 'Disable', 'wpessential' ) . '</button> ';
		} else {
			echo '<button type="button" class="button button-small" data-wpessential-cpt-status="published" data-wpessential-cpt-id="' . esc_attr( $id ) . '">' . esc_html__( 'Publish', 'wpessential' ) . '</button> ';
		}
		if ( $status !== 'archived' ) {
			echo '<button type="button" class="button button-small" data-wpessential-cpt-status="archived" data-wpessential-cpt-id="' . esc_attr( $id ) . '">' . esc_html__( 'Archive', 'wpessential' ) . '</button>';
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
		$html = Field::input(
			$id,
			$label,
			[
				'class'                      => 'regular-text',
				'data-wpessential-cpt-field' => $field,
				'placeholder'                => $placeholder,
				'required'                   => $required,
			],
			$help
		);
		echo $html; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- toolkit components escape their own output.
	}

	private function renderCheckbox( string $id, string $label, bool $checkedByDefault ): void {
		echo Check::render( $label, [ 'id' => $id ], $checkedByDefault ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- toolkit components escape their own output.
	}

	/** @return array{list<array<string,mixed>>,?string} */
	private function readDefinitions(): array {
		try {
			$current = $this->contexts->current();
			$context = new ExecutionContext(
				principal: $current->principal,
				siteId: $current->siteId,
				channel: ExecutionChannel::Ui,
				networkId: $current->networkId,
				correlationId: $current->correlationId,
			);
			$result  = $this->abilities->execute( 'wpessential/cpt/list', [], $context );
			if ( ! is_array( $result ) || ! is_array( $result['definitions'] ?? null ) ) {
				return [ [], __( 'The Custom Post Type list returned an invalid response.', 'wpessential' ) ];
			}

			$definitions = [];
			foreach ( $result['definitions'] as $definition ) {
				if ( ! is_array( $definition ) ) {
					return [ [], __( 'The Custom Post Type list returned an invalid response.', 'wpessential' ) ];
				}
				$definitions[] = $definition;
			}

			return [ $definitions, null ];
		} catch ( Throwable ) {
			return [ [], __( 'Custom Post Type definitions could not be loaded.', 'wpessential' ) ];
		}
	}
}
