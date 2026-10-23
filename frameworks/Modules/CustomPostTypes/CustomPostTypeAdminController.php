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
use WPEssential\Platform\Admin\Ui\AdminUrl;
use WPEssential\Platform\Admin\Ui\Button;
use WPEssential\Platform\Admin\Ui\Check;
use WPEssential\Platform\Admin\Ui\Field;
use WPEssential\Platform\Admin\Ui\Icon;
use WPEssential\Platform\Admin\Ui\Levels;
use WPEssential\Platform\Admin\Ui\Shell;
use WPEssential\Platform\Admin\Ui\StatusPill;
use WPEssential\Platform\Auth\ExecutionChannel;
use WPEssential\Platform\Auth\ExecutionContext;
use WPEssential\Platform\WordPress\Abilities\WordPressExecutionContextFactory;
use WPEssential\Platform\WordPress\Ajax\AjaxDispatcher;

final class CustomPostTypeAdminController {

	public const PAGE_SLUG   = 'wpessential-cpt';
	public const LIST_SLUG   = 'wpessential-post-types';
	private const CAPABILITY = 'manage_options';

	/** @var list<string> */
	private array $hookSuffixes = [];

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

		// The list is the sidebar entry; Add/Edit is its own page, reached from the list.
		$hooks = [
			add_submenu_page(
				PlatformAdminController::PAGE_SLUG,
				__( 'Post Types', 'wpessential' ),
				__( 'Post Types', 'wpessential' ),
				self::CAPABILITY,
				self::LIST_SLUG,
				[ $this, 'renderList' ],
			),
			add_submenu_page(
				'options.php',
				__( 'Edit custom post type', 'wpessential' ),
				__( 'Edit custom post type', 'wpessential' ),
				self::CAPABILITY,
				self::PAGE_SLUG,
				[ $this, 'render' ],
			),
		];

		$this->hookSuffixes = array_values( array_filter( $hooks, static fn ( mixed $hook ): bool => is_string( $hook ) && $hook !== '' ) );
	}

	public function enqueueAssets( string $hookSuffix ): void {
		if ( ! in_array( $hookSuffix, $this->hookSuffixes, true ) ) {
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

	public function renderList(): void {
		$this->render( 'list' );
	}

	public function render( string $view = 'editor' ): void {
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
				'delete'   => [
					'type'  => 'cpt.delete',
					'nonce' => $this->ajax->createNonce( 'cpt.delete' ),
				],
				'trash'    => [
					'type'  => 'cpt.trash',
					'nonce' => $this->ajax->createNonce( 'cpt.trash' ),
				],
				'restore'  => [
					'type'  => 'cpt.restore',
					'nonce' => $this->ajax->createNonce( 'cpt.restore' ),
				],
				'lock'     => [
					'type'  => 'cpt.lock',
					'nonce' => $this->ajax->createNonce( 'cpt.lock' ),
				],
			],
			'definitions' => $definitions,
			'view'        => $view,
			'listUrl'     => AdminUrl::page( self::LIST_SLUG ),
			'editorUrl'   => AdminUrl::page( self::PAGE_SLUG ),
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
		echo '<p class="wpe-ui-page-header__desc">' . esc_html__( 'Create and manage WPEssential post types. Every change is revision-safe and runs through the shared policy and ability layer.', 'wpessential' ) . '</p></div>';
		$action = $view === 'list'
			? Button::link( __( 'Add new post type', 'wpessential' ), AdminUrl::page( self::PAGE_SLUG ) )
			: Button::link( __( '‹ All post types', 'wpessential' ), AdminUrl::page( self::LIST_SLUG ), 'ghost' );
		echo $action . '</div>'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- toolkit components escape their own output.

		if ( $readError !== null ) {
			echo '<div class="notice notice-error inline" role="alert"><p>' . esc_html( $readError ) . '</p></div>';
		}

		echo '<div id="wpessential-cpt-notice" class="notice inline wpessential-cpt-notice" role="status" aria-live="polite" hidden><p></p></div>';
		echo '<div class="wpe-ui-builder__layout wpe-ui-builder__layout--' . esc_attr( $view ) . '">';
		if ( $view === 'list' ) {
			$this->renderDefinitions( $definitions );
		} else {
			$this->renderEditor();
		}
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
		echo Levels::render( 'cpt', __( 'Essential controls are shown: the settings most sites need. Advanced and Expert values you saved stay as they are.', 'wpessential' ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- toolkit components escape their own output.
		echo '<h3 class="wpe-ui-builder__section">' . esc_html__( 'Basics', 'wpessential' ) . '</h3>';
		echo '<p class="wpe-ui-muted">' . esc_html__( 'What are you adding? For example Books, Events or Team members.', 'wpessential' ) . '</p>';
		echo '<div class="wpessential-cpt-grid wpe-ui-field-grid">';
		$this->renderTextField( 'wpessential-cpt-name', __( 'Plural name', 'wpessential' ), 'name', 'Books', true );
		$this->renderTextField( 'wpessential-cpt-singular', __( 'Singular name', 'wpessential' ), 'singular_name', 'Book', true );
		$this->renderTextField( 'wpessential-cpt-key', __( 'Post type key', 'wpessential' ), 'post_type_key', 'book', true, __( 'Filled in from the singular name. Lowercase, up to 20 characters; it cannot change after saving.', 'wpessential' ) );
		$this->renderTextField( 'wpessential-cpt-description', __( 'Description', 'wpessential' ), 'description', '', false );
		echo '</div>';

		echo '<fieldset class="wpessential-cpt-options wpe-ui-checks"><legend class="wpe-ui-builder__section">' . esc_html__( 'Behavior', 'wpessential' ) . '</legend>';
		echo '<p class="wpe-ui-muted wpe-ui-checks__hint">' . esc_html__( 'Public: visitors can see these on the website. Show in REST API: needed for the block editor. Hierarchical: items can have parents, like Pages. Has archive: a listing page such as /books/.', 'wpessential' ) . '</p>';
		$this->renderCheckbox( 'wpessential-cpt-public', __( 'Public', 'wpessential' ), true );
		$this->renderCheckbox( 'wpessential-cpt-rest', __( 'Show in REST API', 'wpessential' ), true );
		$this->renderCheckbox( 'wpessential-cpt-hierarchical', __( 'Hierarchical', 'wpessential' ), false );
		$this->renderCheckbox( 'wpessential-cpt-archive', __( 'Has archive', 'wpessential' ), false );
		echo '</fieldset>';

		echo '<fieldset class="wpessential-cpt-options wpe-ui-checks"><legend class="wpe-ui-builder__section">' . esc_html__( 'Editor supports', 'wpessential' ) . '</legend>';
		echo '<p class="wpe-ui-muted wpe-ui-checks__hint">' . esc_html__( 'The boxes shown on the edit screen.', 'wpessential' ) . '</p>';
		foreach ( [
			'title'           => __( 'Title', 'wpessential' ),
			'editor'          => __( 'Editor', 'wpessential' ),
			'thumbnail'       => __( 'Featured image', 'wpessential' ),
			'excerpt'         => __( 'Excerpt', 'wpessential' ),
			'custom-fields'   => __( 'Custom fields', 'wpessential' ),
			'comments'        => __( 'Comments', 'wpessential' ),
			'revisions'       => __( 'Revisions', 'wpessential' ),
			'page-attributes' => __( 'Page attributes', 'wpessential' ),
			'author'          => __( 'Author', 'wpessential' ),
			'trackbacks'      => __( 'Trackbacks', 'wpessential' ),
			'post-formats'    => __( 'Post formats', 'wpessential' ),
			'autosave'        => __( 'Autosave', 'wpessential' ),
		] as $support => $label ) {
			$checked = in_array( $support, [ 'title', 'editor' ], true );
			if ( $support === 'custom-fields' ) {
				echo '<span class="wpe-ui-checks__more" data-wpessential-cpt-tier-min="advanced" hidden>';
			}
			echo Check::render( $label, [ 'data-wpessential-cpt-support' => $support ], $checked ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- toolkit components escape their own output.
		}
		echo '</span></fieldset>';

		echo '<fieldset class="wpessential-cpt-options wpe-ui-checks"><legend class="wpe-ui-builder__section">' . esc_html__( 'Protection', 'wpessential' ) . '</legend>';
		echo '<p class="wpe-ui-muted wpe-ui-checks__hint">' . esc_html__( 'A locked entry cannot be deleted until the lock is turned off.', 'wpessential' ) . '</p>';
		echo Check::render( __( 'Lock this entry', 'wpessential' ), [ 'id' => 'wpessential-cpt-locked' ] ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- toolkit components escape their own output.
		echo '</fieldset>';

		echo $this->renderAdvanced(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- toolkit components escape their own output.

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

	private const DASHICONS = [ 'admin-post', 'admin-page', 'admin-media', 'admin-links', 'admin-comments', 'admin-users', 'admin-tools', 'admin-generic', 'admin-home', 'admin-site', 'book', 'book-alt', 'portfolio', 'products', 'cart', 'store', 'calendar', 'calendar-alt', 'clock', 'location', 'location-alt', 'building', 'groups', 'businessperson', 'id', 'awards', 'star-filled', 'heart', 'format-gallery', 'format-image', 'format-video', 'format-audio', 'format-quote', 'format-chat', 'media-document', 'clipboard', 'list-view', 'feedback', 'testimonial', 'megaphone', 'tickets-alt', 'welcome-learn-more', 'lightbulb', 'hammer', 'car', 'airplane', 'food', 'pets', 'palmtree', 'tag', 'category', 'archive', 'database', 'shield', 'lock', 'email', 'phone', 'money-alt', 'chart-bar' ];

	private const CAPABILITY_KEYS = [ 'edit_post', 'read_post', 'delete_post', 'edit_posts', 'edit_others_posts', 'delete_posts', 'publish_posts', 'read_private_posts', 'create_posts', 'delete_private_posts', 'delete_published_posts', 'delete_others_posts', 'edit_private_posts', 'edit_published_posts' ];

	/**
	 * Every remaining CustomPostTypeDefinitionProjector option, grouped like the option contract
	 * (cpt.json). Each control is read by admin-ui/src/cpt-options.ts through data-wpessential-cpt-opt
	 * and data-kind; "Default" leaves the key out so the projector default applies.
	 */
	private function renderAdvanced(): string {
		$tri      = static fn ( string $key, string $label, string $help = '' ): string => Field::select(
			'wpessential-cpt-opt-' . $key,
			$label,
			[
				''      => __( 'Default (WordPress)', 'wpessential' ),
				'true'  => __( 'Yes', 'wpessential' ),
				'false' => __( 'No', 'wpessential' ),
			],
			'',
			[
				'data-wpessential-cpt-opt' => $key,
				'data-kind'                => 'tri',
			],
			$help
		);
		$text     = static fn ( string $key, string $label, string $placeholder = '', string $help = '', string $kind = 'text' ): string => Field::input(
			'wpessential-cpt-opt-' . $key,
			$label,
			[
				'type'                     => $kind === 'int' ? 'number' : 'text',
				'min'                      => $kind === 'int' ? '0' : null,
				'max'                      => $kind === 'int' ? '999' : null,
				'placeholder'              => $placeholder,
				'data-wpessential-cpt-opt' => $key,
				'data-kind'                => $kind,
			],
			$help
		);
		$boolText = static fn ( string $key, string $label, string $customLabel, string $placeholder, string $help = '' ): string => Field::select(
			'wpessential-cpt-opt-' . $key,
			$label,
			[
				''       => __( 'Default (WordPress)', 'wpessential' ),
				'true'   => __( 'Yes', 'wpessential' ),
				'false'  => __( 'No', 'wpessential' ),
				'custom' => $customLabel,
			],
			'',
			[
				'data-wpessential-cpt-opt' => $key,
				'data-kind'                => 'bool-text',
			],
			$help
		) . Field::input(
			'wpessential-cpt-opt-' . $key . '-custom',
			$customLabel,
			[
				'placeholder'                     => $placeholder,
				'data-wpessential-cpt-opt-custom' => $key,
			]
		);
		$section  = static fn ( string $tier, string $title, string $desc, string $body ): string => '<section class="wpe-ui-builder__group" data-wpessential-cpt-tier-min="' . esc_attr( $tier ) . '" hidden><h3 class="wpe-ui-builder__section">' . esc_html( $title ) . '</h3>'
			. '<p class="wpe-ui-muted">' . esc_html( $desc ) . '</p><div class="wpe-ui-field-grid">' . $body . '</div></section>';

		$icons = [ '' => __( 'Default (Posts icon)', 'wpessential' ) ];
		foreach ( self::DASHICONS as $icon ) {
			$icons[ 'dashicons-' . $icon ] = ucwords( str_replace( '-', ' ', $icon ) );
		}
		$icons['none'] = __( 'No icon', 'wpessential' );
		$yesNo         = [
			''      => __( 'Default', 'wpessential' ),
			'true'  => __( 'Yes', 'wpessential' ),
			'false' => __( 'No', 'wpessential' ),
		];

		// Advanced: where it appears.
		$html = $section(
			'advanced',
			__( 'Where it appears', 'wpessential' ),
			__( 'The admin menu, navigation menus, the admin bar and site search.', 'wpessential' ),
			Field::select(
				'wpessential-cpt-opt-menu_icon',
				__( 'Menu icon', 'wpessential' ),
				$icons,
				'',
				[
					'data-wpessential-cpt-opt' => 'menu_icon',
					'data-kind'                => 'text',
				]
			)
			. $text( 'menu_position', __( 'Menu position', 'wpessential' ), '25', __( '0–999. 5 is below Posts, 20 below Pages.', 'wpessential' ), 'int' )
			. $tri( 'show_ui', __( 'Show admin screens', 'wpessential' ), __( 'The list and edit screens in the dashboard.', 'wpessential' ) )
			. $boolText( 'show_in_menu', __( 'Show in admin menu', 'wpessential' ), __( 'Under another menu', 'wpessential' ), 'tools.php' )
			. $tri( 'show_in_nav_menus', __( 'Available in navigation menus', 'wpessential' ) )
			. $tri( 'show_in_admin_bar', __( 'Show in the admin bar "New" menu', 'wpessential' ) )
			. $tri( 'exclude_from_search', __( 'Exclude from front-end search', 'wpessential' ) )
			. $tri( 'publicly_queryable', __( 'Publicly queryable', 'wpessential' ), __( 'Off hides single pages and archives from visitors while keeping the admin screens.', 'wpessential' ) )
		);

		// Advanced: web address.
		$html .= $section(
			'advanced',
			__( 'Web address', 'wpessential' ),
			__( 'How the archive and single pages are reached, for example /books/ and /books/my-book/.', 'wpessential' ),
			Field::input(
				'wpessential-cpt-archive-slug',
				__( 'Archive slug', 'wpessential' ),
				[ 'placeholder' => 'books' ],
				__( 'Used when "Has archive" is on; empty uses the post type key.', 'wpessential' )
			)
			. Field::select(
				'wpessential-cpt-opt-rewrite',
				__( 'Pretty permalinks', 'wpessential' ),
				[
					''       => __( 'Default (on, key as slug)', 'wpessential' ),
					'false'  => __( 'Off', 'wpessential' ),
					'custom' => __( 'Custom', 'wpessential' ),
				],
				'',
				[
					'data-wpessential-cpt-opt' => 'rewrite',
					'data-kind'                => 'rewrite',
				]
			)
			. Field::input(
				'wpessential-cpt-rewrite-slug',
				__( 'Permalink slug', 'wpessential' ),
				[
					'placeholder'                  => 'library/books',
					'data-wpessential-cpt-rewrite' => 'slug',
				],
				__( 'Used when Pretty permalinks is Custom.', 'wpessential' )
			)
			. Field::select(
				'wpessential-cpt-opt-permalink',
				__( 'URL format', 'wpessential' ),
				[
					''          => __( 'Default: /books/my-book/', 'wpessential' ),
					'hide_base' => __( 'Hide the base: /my-book/', 'wpessential' ),
					'custom'    => __( 'Custom pattern', 'wpessential' ),
				],
				'',
				[
					'data-wpessential-cpt-opt' => 'permalink',
					'data-kind'                => 'permalink',
				],
				__( 'Like Settings → Permalinks. With the base hidden, a page or post with the same name always wins.', 'wpessential' )
			)
			. Field::input(
				'wpessential-cpt-permalink-pattern',
				__( 'URL pattern', 'wpessential' ),
				[ 'placeholder' => 'books/%book_genre%/%postname%' ],
				__( 'End with the post name tag. Tags you can use:', 'wpessential' ) . ' %postname% %year% %monthnum% %day% %author% %your_taxonomy_key%'
			)
			. $boolText( 'query_var', __( 'Query variable', 'wpessential' ), __( 'Custom query variable', 'wpessential' ), 'book' )
		);

		$taxonomies = '';
		if ( function_exists( 'get_taxonomies' ) ) {
			foreach ( get_taxonomies( [ 'show_ui' => true ], 'objects' ) as $taxonomy ) {
				$taxonomies .= Check::render( (string) $taxonomy->label . ' (' . $taxonomy->name . ')', [ 'data-wpessential-cpt-taxonomy' => $taxonomy->name ] );
			}
		}
		$html .= '<section class="wpe-ui-builder__group" data-wpessential-cpt-tier-min="advanced" hidden><h3 class="wpe-ui-builder__section">' . esc_html__( 'Taxonomies', 'wpessential' ) . '</h3>'
			. '<p class="wpe-ui-muted">' . esc_html__( 'Categories, tags or your own taxonomies to use with this post type.', 'wpessential' ) . '</p>'
			. '<div class="wpe-ui-checks">' . ( $taxonomies !== '' ? $taxonomies : '<span class="wpe-ui-muted">' . esc_html__( 'No taxonomies found.', 'wpessential' ) . '</span>' ) . '</div></section>';

		$labels = $tri( 'automatic_labels', __( 'Generate missing labels automatically', 'wpessential' ) );
		foreach ( CustomPostTypeDefinitionProjector::LABEL_KEYS as $key ) {
			$labels .= Field::input(
				'wpessential-cpt-label-' . $key,
				ucfirst( str_replace( '_', ' ', $key ) ),
				[ 'data-wpessential-cpt-label' => $key ]
			);
		}
		$html .= $section(
			'advanced',
			__( 'Labels', 'wpessential' ),
			__( 'The words WordPress shows for this post type. Empty labels are written for you from the names.', 'wpessential' ),
			$labels
		);

		$html .= $section(
			'advanced',
			__( 'Permissions, export & REST', 'wpessential' ),
			__( 'Who can edit, what happens on export or when a user is deleted, and the REST address.', 'wpessential' ),
			Field::input(
				'wpessential-cpt-opt-capability_type',
				__( 'Capability type (singular)', 'wpessential' ),
				[
					'placeholder'              => 'book',
					'data-wpessential-cpt-opt' => 'capability_type',
					'data-kind'                => 'pair',
				],
				__( 'Empty uses the same permissions as Posts.', 'wpessential' )
			)
			. Field::input(
				'wpessential-cpt-capability-plural',
				__( 'Capability type (plural)', 'wpessential' ),
				[ 'placeholder' => 'books' ],
				__( 'Optional. Empty adds an "s".', 'wpessential' )
			)
			. $tri( 'can_export', __( 'Include in Tools → Export', 'wpessential' ) )
			. $tri( 'delete_with_user', __( 'Delete posts with their author', 'wpessential' ) )
			. $text( 'rest_base', __( 'REST base', 'wpessential' ), 'books', __( 'Used when "Show in REST API" is on.', 'wpessential' ) )
		);

		// Expert: developer settings.
		$developer = $text( 'rest_namespace', __( 'REST namespace', 'wpessential' ), 'wp/v2' );
		foreach ( [
			'with_front' => __( 'Prefix with the permalink front base', 'wpessential' ),
			'feeds'      => __( 'Feed permalinks', 'wpessential' ),
			'pages'      => __( 'Paged permalinks', 'wpessential' ),
		] as $key => $label ) {
			$developer .= Field::select( 'wpessential-cpt-rewrite-' . $key, $label, $yesNo, '', [ 'data-wpessential-cpt-rewrite' => $key ], __( 'Used when Pretty permalinks is Custom.', 'wpessential' ) );
		}
		$html .= $section(
			'expert',
			__( 'Developer settings', 'wpessential' ),
			__( 'REST namespace and permalink details. Leave on Default unless you know you need them.', 'wpessential' ),
			$developer
		);

		$caps = $tri( 'map_meta_cap', __( 'Map meta capabilities', 'wpessential' ), __( 'Lets WordPress check per-post permissions (editing others\' posts and so on).', 'wpessential' ) );
		foreach ( self::CAPABILITY_KEYS as $cap ) {
			$caps .= Field::input(
				'wpessential-cpt-cap-' . $cap,
				$cap,
				[ 'data-wpessential-cpt-cap' => $cap ]
			);
		}
		$html .= $section(
			'expert',
			__( 'Capability map', 'wpessential' ),
			__( 'Rename individual capabilities. Leave empty to use the ones made from the capability type.', 'wpessential' ),
			$caps
		);

		return $html;
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
