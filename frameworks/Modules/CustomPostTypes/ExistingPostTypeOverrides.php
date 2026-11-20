<?php

declare(strict_types=1);

namespace WPEssential\Modules\CustomPostTypes;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use InvalidArgumentException;
use Throwable;
use WPEssential\Contracts\AbilityHandlerInterface;
use WPEssential\Contracts\DefinitionRepositoryInterface;
use WPEssential\Platform\Abilities\AbilityRegistry;
use WPEssential\Platform\Auth\ExecutionChannel;
use WPEssential\Platform\Auth\ExecutionContext;
use WPEssential\Platform\Definitions\Definition;
use WPEssential\Platform\Definitions\DefinitionStatus;
use WPEssential\Platform\WordPress\Abilities\WordPressExecutionContextFactory;

/**
 * Edit, hide and revert post types that WordPress, a theme or another plugin already registers.
 *
 * Overrides are layered on through register_post_type_args, never a second registration, so
 * reverting (archiving the override) restores the original arguments on the next request.
 */
final class ExistingPostTypeOverrides implements AbilityHandlerInterface {

	public const DEFINITION_TYPE = 'cpt-existing-override';
	public const ABILITY         = 'wpessential/cpt/existing-override';
	private const PAGE_SLUG      = 'wpessential-existing-post-types';
	private const ACTION         = 'wpessential_existing_post_type';

	/** Core types that core itself depends on; they can be renamed but never hidden. */
	private const CORE_LOCKED = [ 'attachment', 'revision', 'nav_menu_item', 'custom_css', 'customize_changeset', 'oembed_cache', 'user_request', 'wp_block', 'wp_template', 'wp_template_part', 'wp_global_styles', 'wp_navigation', 'wp_font_family', 'wp_font_face' ];

	private const SUPPORTS = [ 'title', 'editor', 'author', 'thumbnail', 'excerpt', 'trackbacks', 'custom-fields', 'comments', 'revisions', 'page-attributes', 'post-formats' ];

	private const FLAGS = [ 'public', 'show_ui', 'show_in_menu', 'show_in_nav_menus', 'show_in_admin_bar', 'show_in_rest', 'exclude_from_search', 'has_archive' ];

	/** @var array<string,array<string,mixed>>|null Published overrides keyed by post type. */
	private ?array $cache = null;

	public function __construct(
		private readonly DefinitionRepositoryInterface $definitions,
		private ?AbilityRegistry $abilities = null,
		private ?WordPressExecutionContextFactory $contexts = null,
	) {}

	public function register(): void {
		add_filter( 'register_post_type_args', [ $this, 'filterArgs' ], 20, 2 );
		add_action( 'init', [ $this, 'applySupports' ], 100 );
	}

	public function registerAdmin( AbilityRegistry $abilities, WordPressExecutionContextFactory $contexts, string $parentSlug ): void {
		$this->abilities = $abilities;
		$this->contexts  = $contexts;
		add_action(
			'admin_menu',
			fn () => add_submenu_page( $parentSlug, __( 'Existing post types', 'wpessential' ), __( 'Existing post types', 'wpessential' ), 'manage_options', self::PAGE_SLUG, [ $this, 'renderPage' ] ),
			20,
		);
		add_action( 'admin_post_' . self::ACTION, [ $this, 'handleForm' ] );
	}

	/** @param array<string,mixed> $args */
	public function filterArgs( array $args, string $postType ): array {
		$override = $this->overrides()[ $postType ] ?? null;

		return $override === null ? $args : self::merge( $args, $override, $postType );
	}

	public function applySupports(): void {
		foreach ( $this->overrides() as $postType => $override ) {
			foreach ( (array) ( $override['supports_add'] ?? [] ) as $feature ) {
				add_post_type_support( $postType, $feature );
			}
			foreach ( (array) ( $override['supports_remove'] ?? [] ) as $feature ) {
				remove_post_type_support( $postType, $feature );
			}
		}
	}

	/**
	 * @param array<string,mixed> $args
	 * @param array<string,mixed> $override
	 * @return array<string,mixed>
	 */
	public static function merge( array $args, array $override, string $postType ): array {
		$labels = is_array( $args['labels'] ?? null ) ? $args['labels'] : [];
		foreach ( [ 'name', 'singular_name', 'menu_name' ] as $key ) {
			if ( isset( $override['labels'][ $key ] ) ) {
				$labels[ $key ] = $override['labels'][ $key ];
			}
		}
		if ( isset( $override['labels']['name'] ) ) {
			$args['label'] = $override['labels']['name'];
		}
		$args['labels'] = $labels;
		foreach ( [ 'menu_icon', 'menu_position' ] as $key ) {
			if ( array_key_exists( $key, $override ) ) {
				$args[ $key ] = $override[ $key ];
			}
		}
		foreach ( self::FLAGS as $flag ) {
			if ( array_key_exists( $flag, $override ) ) {
				$args[ $flag ] = $override[ $flag ];
			}
		}
		if ( isset( $override['rewrite_slug'] ) ) {
			$rewrite         = is_array( $args['rewrite'] ?? null ) ? $args['rewrite'] : [];
			$args['rewrite'] = [
				...$rewrite,
				'slug' => $override['rewrite_slug'],
			];
		}
		if ( ( $override['disabled'] ?? false ) === true && ! in_array( $postType, self::CORE_LOCKED, true ) ) {
			// Hidden everywhere, content kept: nothing is deleted or unregistered.
			foreach ( [ 'public', 'publicly_queryable', 'show_ui', 'show_in_menu', 'show_in_nav_menus', 'show_in_admin_bar', 'show_in_rest', 'has_archive' ] as $flag ) {
				$args[ $flag ] = false;
			}
			$args['exclude_from_search'] = true;
		}

		return $args;
	}

	/**
	 * Keeps only the settings the screen offers, in the types WordPress expects.
	 *
	 * @param array<string,mixed> $input
	 * @return array<string,mixed>
	 */
	public static function sanitize( array $input, string $postType ): array {
		$out    = [];
		$labels = [];
		foreach ( [ 'name', 'singular_name', 'menu_name' ] as $key ) {
			$value = trim( (string) ( $input['labels'][ $key ] ?? '' ) );
			if ( $value !== '' ) {
				$labels[ $key ] = mb_substr( trim( wp_strip_all_tags( $value ) ), 0, 100 );
			}
		}
		if ( $labels !== [] ) {
			$out['labels'] = $labels;
		}
		$icon = trim( (string) ( $input['menu_icon'] ?? '' ) );
		if ( $icon !== '' ) {
			if ( preg_match( '/^dashicons-[a-z0-9-]+$/', $icon ) !== 1 ) {
				throw new InvalidArgumentException( 'Menu icon must be a Dashicons class such as dashicons-admin-post.' );
			}
			$out['menu_icon'] = $icon;
		}
		$position = trim( (string) ( $input['menu_position'] ?? '' ) );
		if ( $position !== '' ) {
			if ( ! ctype_digit( $position ) || (int) $position > 1000 ) {
				throw new InvalidArgumentException( 'Menu position must be a whole number from 0 to 1000.' );
			}
			$out['menu_position'] = (int) $position;
		}
		foreach ( self::FLAGS as $flag ) {
			$value = $input['flags'][ $flag ] ?? '';
			if ( $value === '1' || $value === '0' ) {
				$out[ $flag ] = $value === '1';
			}
		}
		$slug = trim( (string) ( $input['rewrite_slug'] ?? '' ) );
		if ( $slug !== '' ) {
			if ( preg_match( '#^[a-z0-9][a-z0-9_/-]{0,99}$#', $slug ) !== 1 ) {
				throw new InvalidArgumentException( 'URL slug may only use lowercase letters, numbers, dashes, underscores and slashes.' );
			}
			$out['rewrite_slug'] = $slug;
		}
		foreach ( [ 'supports_add', 'supports_remove' ] as $key ) {
			$list = array_values( array_intersect( self::SUPPORTS, array_map( 'strval', (array) ( $input[ $key ] ?? [] ) ) ) );
			if ( $list !== [] ) {
				$out[ $key ] = $list;
			}
		}
		if ( isset( $out['supports_add'], $out['supports_remove'] ) && array_intersect( $out['supports_add'], $out['supports_remove'] ) !== [] ) {
			throw new InvalidArgumentException( 'A feature cannot be both added and removed.' );
		}
		if ( ( $input['disabled'] ?? '' ) === '1' ) {
			if ( in_array( $postType, self::CORE_LOCKED, true ) ) {
				throw new InvalidArgumentException( 'This core post type cannot be hidden; WordPress depends on it.' );
			}
			$out['disabled'] = true;
		}

		return $out;
	}

	/** Ability: {post_type, override?, revert?} saves or reverts one override. */
	public function handle( array $input, ExecutionContext $context ): mixed {
		$postType = (string) ( $input['post_type'] ?? '' );
		if ( preg_match( '/^[a-z0-9_-]{1,20}$/', $postType ) !== 1 ) {
			throw new InvalidArgumentException( 'Unknown post type.' );
		}
		$existing = $this->find( $postType );
		$revert   = ( $input['revert'] ?? false ) === true;
		$override = $revert ? [] : self::sanitize( is_array( $input['override'] ?? null ) ? $input['override'] : [], $postType );
		$status   = $revert || $override === [] ? DefinitionStatus::Archived : DefinitionStatus::Published;
		$candidate = new Definition(
			id: $existing?->id ?? self::uuid(),
			slug: 'cpt-existing-' . str_replace( '_', '-', $postType ),
			type: self::DEFINITION_TYPE,
			schemaVersion: 1,
			ownerSurfaceId: CustomPostTypeDefinitionProjector::OWNER_SURFACE_ID,
			status: $status,
			payload: [
				'post_type' => $postType,
				'override' => $override,
			],
			revision: ( $existing?->revision ?? 0 ) + 1,
		);
		$this->definitions->save( new Definition( $candidate->id, $candidate->slug, $candidate->type, 1, $candidate->ownerSurfaceId, $status, $candidate->payload, $candidate->revision, [], $candidate->computedChecksum() ) );
		$this->cache = null;
		if ( function_exists( 'delete_option' ) && array_key_exists( 'rewrite_slug', $override + (array) ( $existing?->payload['override'] ?? [] ) ) ) {
			delete_option( 'rewrite_rules' ); // WordPress rebuilds the rules on the next request, after the new args apply.
		}

		return [
			'post_type' => $postType,
			'status' => $status->value,
			'override' => $override,
		];
	}

	/** @return array<string,array<string,mixed>> */
	public function overrides(): array {
		if ( $this->cache === null ) {
			$this->cache = [];
			foreach ( $this->definitions->byType( self::DEFINITION_TYPE ) as $definition ) {
				if ( $definition->status === DefinitionStatus::Published && is_string( $definition->payload['post_type'] ?? null ) ) {
					$this->cache[ $definition->payload['post_type'] ] = (array) ( $definition->payload['override'] ?? [] );
				}
			}
		}

		return $this->cache;
	}

	private function find( string $postType ): ?Definition {
		foreach ( $this->definitions->byType( self::DEFINITION_TYPE ) as $definition ) {
			if ( ( $definition->payload['post_type'] ?? null ) === $postType ) {
				return $definition;
			}
		}

		return null;
	}

	public function handleForm(): void {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( esc_html__( 'You are not allowed to change post types.', 'wpessential' ), 403 );
		}
		check_admin_referer( self::ACTION );
		$postType = sanitize_key( wp_unslash( (string) ( $_POST['post_type'] ?? '' ) ) );
		$input    = [
			'post_type' => $postType,
			'revert'    => isset( $_POST['revert'] ),
			'override'  => is_array( $_POST['override'] ?? null ) ? wp_unslash( $_POST['override'] ) : [], // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized -- sanitize() allowlists every key.
		];
		$notice = 'saved';
		try {
			$current = $this->contexts?->current();
			if ( $this->abilities === null || $current === null ) {
				throw new InvalidArgumentException( 'Abilities are unavailable.' );
			}
			$this->abilities->execute( self::ABILITY, $input, new ExecutionContext( principal: $current->principal, siteId: $current->siteId, channel: ExecutionChannel::Ui, networkId: $current->networkId, correlationId: $current->correlationId ) );
		} catch ( Throwable $error ) {
			$notice = 'error';
			set_transient( 'wpessential_existing_cpt_error_' . get_current_user_id(), $error->getMessage(), 60 );
		}
		$query = [
			'page' => self::PAGE_SLUG,
			'post_type' => $postType,
			'wpe_notice' => $notice,
		];
		wp_safe_redirect( add_query_arg( $query, admin_url( 'admin.php' ) ) );
		exit;
	}

	public function renderPage(): void {
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}
		$types    = get_post_types( [], 'objects' );
		$selected = isset( $_GET['post_type'] ) ? sanitize_key( wp_unslash( (string) $_GET['post_type'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- read-only screen selection.
		$notice   = isset( $_GET['wpe_notice'] ) ? sanitize_key( wp_unslash( (string) $_GET['wpe_notice'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
		echo '<div class="wrap"><h1>' . esc_html__( 'Existing post types', 'wpessential' ) . '</h1>';
		echo '<p class="description">' . esc_html__( 'Change post types that WordPress, your theme or other plugins register. Changes are layered on top and can be reverted at any time; no content is deleted.', 'wpessential' ) . '</p>';
		if ( $notice === 'saved' ) {
			echo '<div class="notice notice-success"><p>' . esc_html__( 'Saved.', 'wpessential' ) . '</p></div>';
		} elseif ( $notice === 'error' ) {
			$message = (string) get_transient( 'wpessential_existing_cpt_error_' . get_current_user_id() );
			echo '<div class="notice notice-error"><p>' . esc_html( $message !== '' ? $message : __( 'Not saved.', 'wpessential' ) ) . '</p></div>';
		}
		$overrides = $this->overrides();
		echo '<table class="widefat striped"><thead><tr><th>' . esc_html__( 'Post type', 'wpessential' ) . '</th><th>' . esc_html__( 'Key', 'wpessential' ) . '</th><th>' . esc_html__( 'Changed', 'wpessential' ) . '</th><th></th></tr></thead><tbody>';
		foreach ( $types as $type ) {
			if ( ! $type->show_ui && ! isset( $overrides[ $type->name ] ) ) {
				continue;
			}
			$query = [
				'page' => self::PAGE_SLUG,
				'post_type' => $type->name,
			];
			$url = add_query_arg( $query, admin_url( 'admin.php' ) );
			echo '<tr><td>' . esc_html( (string) $type->label ) . '</td><td><code>' . esc_html( $type->name ) . '</code></td><td>' . ( isset( $overrides[ $type->name ] ) ? esc_html__( 'Yes', 'wpessential' ) : '—' ) . '</td><td><a class="button" href="' . esc_url( $url ) . '">' . esc_html__( 'Edit', 'wpessential' ) . '</a></td></tr>';
		}
		echo '</tbody></table>';
		if ( isset( $types[ $selected ] ) ) {
			$this->renderForm( $types[ $selected ], $overrides[ $selected ] ?? [] );
		}
		echo '</div>';
	}

	/** @param array<string,mixed> $override */
	private function renderForm( object $type, array $override ): void {
		$field = static fn ( string $name, string $label, string $value, string $placeholder ): string => '<tr><th scope="row"><label for="wpe-' . esc_attr( $name ) . '">' . esc_html( $label ) . '</label></th><td><input class="regular-text" id="wpe-' . esc_attr( $name ) . '" name="override[' . esc_attr( str_replace( '.', '][', $name ) ) . ']" value="' . esc_attr( $value ) . '" placeholder="' . esc_attr( $placeholder ) . '"></td></tr>';
		echo '<h2>' . esc_html( sprintf( /* translators: %s: post type label */ __( 'Edit %s', 'wpessential' ), (string) $type->label ) ) . '</h2>';
		echo '<form method="post" action="' . esc_url( admin_url( 'admin-post.php' ) ) . '">';
		wp_nonce_field( self::ACTION );
		echo '<input type="hidden" name="action" value="' . esc_attr( self::ACTION ) . '"><input type="hidden" name="post_type" value="' . esc_attr( $type->name ) . '">';
		echo '<p class="description">' . esc_html__( 'Leave a box empty, or choose "Keep original", to keep what the post type was registered with. The original value is shown as the placeholder.', 'wpessential' ) . '</p>';
		echo '<table class="form-table" role="presentation"><tbody>';
		echo $field( 'labels.name', __( 'Plural name', 'wpessential' ), (string) ( $override['labels']['name'] ?? '' ), (string) $type->labels->name ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped in $field.
		echo $field( 'labels.singular_name', __( 'Singular name', 'wpessential' ), (string) ( $override['labels']['singular_name'] ?? '' ), (string) $type->labels->singular_name ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		echo $field( 'labels.menu_name', __( 'Menu name', 'wpessential' ), (string) ( $override['labels']['menu_name'] ?? '' ), (string) $type->labels->menu_name ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		echo $field( 'menu_icon', __( 'Menu icon (Dashicons class)', 'wpessential' ), (string) ( $override['menu_icon'] ?? '' ), is_string( $type->menu_icon ) ? $type->menu_icon : 'dashicons-admin-post' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		echo $field( 'menu_position', __( 'Menu position', 'wpessential' ), isset( $override['menu_position'] ) ? (string) $override['menu_position'] : '', (string) ( $type->menu_position ?? '' ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		$slug = is_array( $type->rewrite ) ? (string) ( $type->rewrite['slug'] ?? '' ) : '';
		echo $field( 'rewrite_slug', __( 'URL slug', 'wpessential' ), (string) ( $override['rewrite_slug'] ?? '' ), $slug ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		foreach ( self::FLAGS as $flag ) {
			$current = array_key_exists( $flag, $override ) ? ( $override[ $flag ] ? '1' : '0' ) : '';
			echo '<tr><th scope="row"><label for="wpe-flag-' . esc_attr( $flag ) . '">' . esc_html( ucfirst( str_replace( '_', ' ', $flag ) ) ) . '</label></th><td><select id="wpe-flag-' . esc_attr( $flag ) . '" name="override[flags][' . esc_attr( $flag ) . ']">';
			$options = [
				'' => sprintf( /* translators: %s: Yes or No */ __( 'Keep original (%s)', 'wpessential' ), ! empty( $type->$flag ) ? __( 'Yes', 'wpessential' ) : __( 'No', 'wpessential' ) ),
				'1' => __( 'Yes', 'wpessential' ),
				'0' => __( 'No', 'wpessential' ),
			];
			foreach ( $options as $value => $text ) {
				echo '<option value="' . esc_attr( (string) $value ) . '"' . selected( $current, (string) $value, false ) . '>' . esc_html( $text ) . '</option>';
			}
			echo '</select></td></tr>';
		}
		$options = [
			'supports_add' => __( 'Add features', 'wpessential' ),
			'supports_remove' => __( 'Remove features', 'wpessential' ),
		];
		foreach ( $options as $key => $legend ) {
			echo '<tr><th scope="row">' . esc_html( $legend ) . '</th><td><fieldset>';
			foreach ( self::SUPPORTS as $feature ) {
				$has = post_type_supports( $type->name, $feature );
				if ( ( $key === 'supports_add' ) === $has && ! in_array( $feature, (array) ( $override[ $key ] ?? [] ), true ) ) {
					continue; // Offer to add only what is missing and to remove only what is there.
				}
				echo '<label><input type="checkbox" name="override[' . esc_attr( $key ) . '][]" value="' . esc_attr( $feature ) . '"' . checked( in_array( $feature, (array) ( $override[ $key ] ?? [] ), true ), true, false ) . '> ' . esc_html( $feature ) . '</label><br>';
			}
			echo '</fieldset></td></tr>';
		}
		if ( ! in_array( $type->name, self::CORE_LOCKED, true ) ) {
			echo '<tr><th scope="row">' . esc_html__( 'Hide this post type', 'wpessential' ) . '</th><td><label><input type="checkbox" name="override[disabled]" value="1"' . checked( ( $override['disabled'] ?? false ) === true, true, false ) . '> ' . esc_html__( 'Hide from menus, editor, REST and search. Existing content is kept.', 'wpessential' ) . '</label></td></tr>';
		}
		echo '</tbody></table>';
		submit_button( __( 'Save changes', 'wpessential' ), 'primary', 'submit', false );
		echo ' ';
		submit_button( __( 'Revert to original', 'wpessential' ), 'secondary', 'revert', false );
		echo '</form>';
	}

	private static function uuid(): string {
		$bytes    = random_bytes( 16 );
		$bytes[6] = chr( ( ord( $bytes[6] ) & 0x0f ) | 0x40 );
		$bytes[8] = chr( ( ord( $bytes[8] ) & 0x3f ) | 0x80 );

		return vsprintf( '%s%s-%s-%s-%s-%s%s%s', str_split( bin2hex( $bytes ), 4 ) );
	}
}
