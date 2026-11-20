<?php

declare(strict_types=1);

namespace WPEssential\Modules\Taxonomies;

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
 * Edit, hide, attach and revert taxonomies that WordPress, a theme or another plugin already registers.
 *
 * Overrides are layered on through register_taxonomy_args, never a second registration, so
 * reverting (archiving the override) restores the original arguments on the next request.
 */
final class ExistingTaxonomyOverrides implements AbilityHandlerInterface {

	public const DEFINITION_TYPE = 'taxonomy-existing-override';
	public const ABILITY         = 'wpessential/taxonomy/existing-override';
	private const PAGE_SLUG      = 'wpessential-existing-taxonomies';
	private const ACTION         = 'wpessential_existing_taxonomy';

	/** Taxonomies core itself depends on; they can be renamed but never hidden or moved. */
	private const CORE_LOCKED = [ 'nav_menu', 'link_category', 'post_format', 'wp_theme', 'wp_template_part_area', 'wp_pattern_category' ];

	private const FLAGS = [ 'public', 'show_ui', 'show_in_menu', 'show_in_nav_menus', 'show_tagcloud', 'show_in_quick_edit', 'show_admin_column', 'show_in_rest' ];

	/** @var array<string,array<string,mixed>>|null Published overrides keyed by post type. */
	private ?array $cache = null;

	public function __construct(
		private readonly DefinitionRepositoryInterface $definitions,
		private ?AbilityRegistry $abilities = null,
		private ?WordPressExecutionContextFactory $contexts = null,
	) {}

	public function register(): void {
		add_filter( 'register_taxonomy_args', [ $this, 'filterArgs' ], 20, 2 );
		add_action( 'init', [ $this, 'applyObjectTypes' ], 100 );
	}

	public function registerAdmin( AbilityRegistry $abilities, WordPressExecutionContextFactory $contexts, string $parentSlug ): void {
		$this->abilities = $abilities;
		$this->contexts  = $contexts;
		add_action(
			'admin_menu',
			fn () => add_submenu_page( $parentSlug, __( 'Existing taxonomies', 'wpessential' ), __( 'Existing taxonomies', 'wpessential' ), 'manage_options', self::PAGE_SLUG, [ $this, 'renderPage' ] ),
			20,
		);
		add_action( 'admin_post_' . self::ACTION, [ $this, 'handleForm' ] );
	}

	/** @param array<string,mixed> $args */
	public function filterArgs( array $args, string $taxonomy ): array {
		$override = $this->overrides()[ $taxonomy ] ?? null;

		return $override === null ? $args : self::merge( $args, $override, $taxonomy );
	}

	public function applyObjectTypes(): void {
		foreach ( $this->overrides() as $taxonomy => $override ) {
			foreach ( (array) ( $override['attach'] ?? [] ) as $postType ) {
				if ( post_type_exists( $postType ) ) {
					register_taxonomy_for_object_type( $taxonomy, $postType );
				}
			}
			foreach ( (array) ( $override['detach'] ?? [] ) as $postType ) {
				unregister_taxonomy_for_object_type( $taxonomy, $postType );
			}
		}
	}

	/**
	 * @param array<string,mixed> $args
	 * @param array<string,mixed> $override
	 * @return array<string,mixed>
	 */
	public static function merge( array $args, array $override, string $taxonomy ): array {
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
		if ( ( $override['disabled'] ?? false ) === true && ! in_array( $taxonomy, self::CORE_LOCKED, true ) ) {
			// Hidden everywhere, terms and relationships kept: nothing is deleted or unregistered.
			foreach ( [ 'public', 'publicly_queryable', 'show_ui', 'show_in_menu', 'show_in_nav_menus', 'show_tagcloud', 'show_in_quick_edit', 'show_admin_column', 'show_in_rest' ] as $flag ) {
				$args[ $flag ] = false;
			}
			$args['meta_box_cb'] = false;
		}

		return $args;
	}

	/**
	 * Keeps only the settings the screen offers, in the types WordPress expects.
	 *
	 * @param array<string,mixed> $input
	 * @return array<string,mixed>
	 */
	public static function sanitize( array $input, string $taxonomy ): array {
		$out    = [];
		$labels = [];
		foreach ( [ 'name', 'singular_name', 'menu_name' ] as $key ) {
			$value = trim( (string) ( $input['labels'][ $key ] ?? '' ) );
			if ( $value !== '' ) {
				$labels[ $key ] = mb_substr( trim( strip_tags( $value ) ), 0, 100 );
			}
		}
		if ( $labels !== [] ) {
			$out['labels'] = $labels;
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
		foreach ( [ 'attach', 'detach' ] as $key ) {
			$list = array_values( array_unique( array_filter( array_map( 'strval', (array) ( $input[ $key ] ?? [] ) ), static fn ( string $type ): bool => preg_match( '/^[a-z0-9_-]{1,20}$/', $type ) === 1 ) ) );
			if ( $list !== [] ) {
				if ( in_array( $taxonomy, self::CORE_LOCKED, true ) ) {
					throw new InvalidArgumentException( 'This core taxonomy cannot be moved to other post types.' );
				}
				$out[ $key ] = $list;
			}
		}
		if ( isset( $out['attach'], $out['detach'] ) && array_intersect( $out['attach'], $out['detach'] ) !== [] ) {
			throw new InvalidArgumentException( 'A post type cannot be both attached and detached.' );
		}
		if ( ( $input['disabled'] ?? '' ) === '1' ) {
			if ( in_array( $taxonomy, self::CORE_LOCKED, true ) ) {
				throw new InvalidArgumentException( 'This core taxonomy cannot be hidden; WordPress depends on it.' );
			}
			$out['disabled'] = true;
		}

		return $out;
	}

	/** Ability: {taxonomy, override?, revert?} saves or reverts one override. */
	public function handle( array $input, ExecutionContext $context ): mixed {
		$taxonomyKey = (string) ( $input['taxonomy'] ?? '' );
		if ( preg_match( '/^[a-z0-9_-]{1,32}$/', $taxonomyKey ) !== 1 ) {
			throw new InvalidArgumentException( 'Unknown taxonomy.' );
		}
		$existing = $this->find( $taxonomyKey );
		$revert   = ( $input['revert'] ?? false ) === true;
		$override = $revert ? [] : self::sanitize( is_array( $input['override'] ?? null ) ? $input['override'] : [], $taxonomyKey );
		$status   = $revert || $override === [] ? DefinitionStatus::Archived : DefinitionStatus::Published;
		$candidate = new Definition(
			id: $existing?->id ?? self::uuid(),
			slug: 'taxonomy-existing-' . str_replace( '_', '-', $taxonomyKey ),
			type: self::DEFINITION_TYPE,
			schemaVersion: 1,
			ownerSurfaceId: TaxonomyDefinitionProjector::OWNER_SURFACE_ID,
			status: $status,
			payload: [
				'taxonomy' => $taxonomyKey,
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
			'taxonomy' => $taxonomyKey,
			'status' => $status->value,
			'override' => $override,
		];
	}

	/** @return array<string,array<string,mixed>> */
	public function overrides(): array {
		if ( $this->cache === null ) {
			$this->cache = [];
			foreach ( $this->definitions->byType( self::DEFINITION_TYPE ) as $definition ) {
				if ( $definition->status === DefinitionStatus::Published && is_string( $definition->payload['taxonomy'] ?? null ) ) {
					$this->cache[ $definition->payload['taxonomy'] ] = (array) ( $definition->payload['override'] ?? [] );
				}
			}
		}

		return $this->cache;
	}

	private function find( string $taxonomyKey ): ?Definition {
		foreach ( $this->definitions->byType( self::DEFINITION_TYPE ) as $definition ) {
			if ( ( $definition->payload['taxonomy'] ?? null ) === $taxonomyKey ) {
				return $definition;
			}
		}

		return null;
	}

	public function handleForm(): void {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( esc_html__( 'You are not allowed to change taxonomies.', 'wpessential' ), 403 );
		}
		check_admin_referer( self::ACTION );
		$taxonomyKey = sanitize_key( wp_unslash( (string) ( $_POST['taxonomy'] ?? '' ) ) );
		$input    = [
			'taxonomy'  => $taxonomyKey,
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
			set_transient( 'wpessential_existing_tax_error_' . get_current_user_id(), $error->getMessage(), 60 );
		}
		$query = [
			'page' => self::PAGE_SLUG,
			'taxonomy' => $taxonomyKey,
			'wpe_notice' => $notice,
		];
		wp_safe_redirect( add_query_arg( $query, admin_url( 'admin.php' ) ) );
		exit;
	}

	public function renderPage(): void {
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}
		$taxonomies = get_taxonomies( [], 'objects' );
		$selected   = isset( $_GET['taxonomy'] ) ? sanitize_key( wp_unslash( (string) $_GET['taxonomy'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- read-only screen selection.
		$notice     = isset( $_GET['wpe_notice'] ) ? sanitize_key( wp_unslash( (string) $_GET['wpe_notice'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
		echo '<div class="wrap"><h1>' . esc_html__( 'Existing taxonomies', 'wpessential' ) . '</h1>';
		echo '<p class="description">' . esc_html__( 'Change taxonomies that WordPress, your theme or other plugins register. Changes are layered on top and can be reverted at any time; no terms are deleted.', 'wpessential' ) . '</p>';
		if ( $notice === 'saved' ) {
			echo '<div class="notice notice-success"><p>' . esc_html__( 'Saved.', 'wpessential' ) . '</p></div>';
		} elseif ( $notice === 'error' ) {
			$message = (string) get_transient( 'wpessential_existing_tax_error_' . get_current_user_id() );
			echo '<div class="notice notice-error"><p>' . esc_html( $message !== '' ? $message : __( 'Not saved.', 'wpessential' ) ) . '</p></div>';
		}
		$overrides = $this->overrides();
		echo '<table class="widefat striped"><thead><tr><th>' . esc_html__( 'Taxonomy', 'wpessential' ) . '</th><th>' . esc_html__( 'Key', 'wpessential' ) . '</th><th>' . esc_html__( 'Post types', 'wpessential' ) . '</th><th>' . esc_html__( 'Changed', 'wpessential' ) . '</th><th></th></tr></thead><tbody>';
		foreach ( $taxonomies as $taxonomy ) {
			if ( ! $taxonomy->show_ui && ! isset( $overrides[ $taxonomy->name ] ) ) {
				continue;
			}
			$query = [
				'page' => self::PAGE_SLUG,
				'taxonomy' => $taxonomy->name,
			];
			$url = add_query_arg( $query, admin_url( 'admin.php' ) );
			echo '<tr><td>' . esc_html( (string) $taxonomy->label ) . '</td><td><code>' . esc_html( $taxonomy->name ) . '</code></td><td>' . esc_html( implode( ', ', (array) $taxonomy->object_type ) ) . '</td><td>' . ( isset( $overrides[ $taxonomy->name ] ) ? esc_html__( 'Yes', 'wpessential' ) : '—' ) . '</td><td><a class="button" href="' . esc_url( $url ) . '">' . esc_html__( 'Edit', 'wpessential' ) . '</a></td></tr>';
		}
		echo '</tbody></table>';
		if ( isset( $taxonomies[ $selected ] ) ) {
			$this->renderForm( $taxonomies[ $selected ], $overrides[ $selected ] ?? [] );
		}
		echo '</div>';
	}

	/** @param array<string,mixed> $override */
	private function renderForm( object $taxonomy, array $override ): void {
		$field = static fn ( string $name, string $label, string $value, string $placeholder ): string => '<tr><th scope="row"><label for="wpe-' . esc_attr( $name ) . '">' . esc_html( $label ) . '</label></th><td><input class="regular-text" id="wpe-' . esc_attr( $name ) . '" name="override[' . esc_attr( str_replace( '.', '][', $name ) ) . ']" value="' . esc_attr( $value ) . '" placeholder="' . esc_attr( $placeholder ) . '"></td></tr>';
		$locked = in_array( $taxonomy->name, self::CORE_LOCKED, true );
		echo '<h2>' . esc_html( sprintf( /* translators: %s: taxonomy label */ __( 'Edit %s', 'wpessential' ), (string) $taxonomy->label ) ) . '</h2>';
		echo '<form method="post" action="' . esc_url( admin_url( 'admin-post.php' ) ) . '">';
		wp_nonce_field( self::ACTION );
		echo '<input type="hidden" name="action" value="' . esc_attr( self::ACTION ) . '"><input type="hidden" name="taxonomy" value="' . esc_attr( $taxonomy->name ) . '">';
		echo '<p class="description">' . esc_html__( 'Leave a box empty, or choose "Keep original", to keep what the taxonomy was registered with. The original value is shown as the placeholder.', 'wpessential' ) . '</p>';
		echo '<table class="form-table" role="presentation"><tbody>';
		echo $field( 'labels.name', __( 'Plural name', 'wpessential' ), (string) ( $override['labels']['name'] ?? '' ), (string) $taxonomy->labels->name ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped in $field.
		echo $field( 'labels.singular_name', __( 'Singular name', 'wpessential' ), (string) ( $override['labels']['singular_name'] ?? '' ), (string) $taxonomy->labels->singular_name ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		echo $field( 'labels.menu_name', __( 'Menu name', 'wpessential' ), (string) ( $override['labels']['menu_name'] ?? '' ), (string) $taxonomy->labels->menu_name ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		$slug = is_array( $taxonomy->rewrite ) ? (string) ( $taxonomy->rewrite['slug'] ?? '' ) : '';
		echo $field( 'rewrite_slug', __( 'URL slug', 'wpessential' ), (string) ( $override['rewrite_slug'] ?? '' ), $slug ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		foreach ( self::FLAGS as $flag ) {
			$current = array_key_exists( $flag, $override ) ? ( $override[ $flag ] ? '1' : '0' ) : '';
			echo '<tr><th scope="row"><label for="wpe-flag-' . esc_attr( $flag ) . '">' . esc_html( ucfirst( str_replace( '_', ' ', $flag ) ) ) . '</label></th><td><select id="wpe-flag-' . esc_attr( $flag ) . '" name="override[flags][' . esc_attr( $flag ) . ']">';
			$options = [
				'' => sprintf( /* translators: %s: Yes or No */ __( 'Keep original (%s)', 'wpessential' ), ! empty( $taxonomy->$flag ) ? __( 'Yes', 'wpessential' ) : __( 'No', 'wpessential' ) ),
				'1' => __( 'Yes', 'wpessential' ),
				'0' => __( 'No', 'wpessential' ),
			];
			foreach ( $options as $value => $text ) {
				echo '<option value="' . esc_attr( (string) $value ) . '"' . selected( $current, (string) $value, false ) . '>' . esc_html( $text ) . '</option>';
			}
			echo '</select></td></tr>';
		}
		if ( ! $locked ) {
			$attached = (array) $taxonomy->object_type;
			$options = [
				'attach' => __( 'Also use on', 'wpessential' ),
				'detach' => __( 'Remove from', 'wpessential' ),
			];
			foreach ( $options as $key => $legend ) {
				echo '<tr><th scope="row">' . esc_html( $legend ) . '</th><td><fieldset>';
				foreach ( get_post_types( [ 'show_ui' => true ], 'objects' ) as $type ) {
					$isOn = in_array( $type->name, $attached, true );
					if ( ( $key === 'attach' ) === $isOn && ! in_array( $type->name, (array) ( $override[ $key ] ?? [] ), true ) ) {
						continue; // Offer to attach only where missing and to detach only where attached.
					}
					echo '<label><input type="checkbox" name="override[' . esc_attr( $key ) . '][]" value="' . esc_attr( $type->name ) . '"' . checked( in_array( $type->name, (array) ( $override[ $key ] ?? [] ), true ), true, false ) . '> ' . esc_html( (string) $type->label ) . '</label><br>';
				}
				echo '</fieldset></td></tr>';
			}
			echo '<tr><th scope="row">' . esc_html__( 'Hide this taxonomy', 'wpessential' ) . '</th><td><label><input type="checkbox" name="override[disabled]" value="1"' . checked( ( $override['disabled'] ?? false ) === true, true, false ) . '> ' . esc_html__( 'Hide from menus, the editor, REST and admin columns. Terms are kept.', 'wpessential' ) . '</label></td></tr>';
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
