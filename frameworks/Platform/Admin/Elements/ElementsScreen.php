<?php

declare(strict_types=1);

namespace WPEssential\Platform\Admin\Elements;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use WPEssential\Platform\Admin\Core\AdminScreen;
use WPEssential\Platform\Admin\PlatformAdminController;
use WPEssential\Platform\Admin\Ui\AdminUrl;
use WPEssential\Platform\Admin\Ui\Badge;
use WPEssential\Platform\Admin\Ui\Button;
use WPEssential\Platform\Admin\Ui\Icon;
use WPEssential\Platform\Admin\Ui\Notice;
use WPEssential\Platform\Admin\Ui\PageHeader;
use WPEssential\Platform\Admin\Ui\Screen;
use WPEssential\Platform\Admin\Ui\Shell;
use WPEssential\Platform\Admin\Ui\Toggle;

/**
 * Elements screen: Gutenberg blocks and Elementor widgets with a site-wide on/off switch each.
 */
final class ElementsScreen implements AdminScreen {

	public const TOGGLE_ACTION = 'wpessential_element_toggle';

	public function __construct( private readonly ElementRegistry $elements ) {}

	public function slug(): string {
		return Shell::ELEMENTS_SLUG;
	}

	public function title(): string {
		return __( 'Elements', 'wpessential' );
	}

	public function register(): void {
		if ( function_exists( 'add_action' ) ) {
			add_action( 'admin_post_' . self::TOGGLE_ACTION, [ $this, 'handleToggle' ] );
		}
	}

	public function handleToggle(): void {
		if ( ! current_user_can( PlatformAdminController::CAPABILITY ) ) {
			wp_die( esc_html__( 'You are not allowed to manage WPEssential elements.', 'wpessential' ), 'WPEssential', [ 'response' => 403 ] );
		}
		check_admin_referer( self::TOGGLE_ACTION );

		$type    = isset( $_POST['type'] ) ? sanitize_key( wp_unslash( (string) $_POST['type'] ) ) : '';
		$id      = isset( $_POST['element'] ) ? sanitize_key( wp_unslash( (string) $_POST['element'] ) ) : '';
		$enabled = isset( $_POST['enabled'] ) && $_POST['enabled'] === '1';
		$type    = in_array( $type, ElementRegistry::TYPES, true ) ? $type : 'gutenberg';
		$saved   = $this->elements->set( $type, $id, $enabled );

		wp_safe_redirect(
			AdminUrl::page(
				Shell::ELEMENTS_SLUG,
				[
					'type'       => $type,
					'wpe-notice' => $saved ? ( $enabled ? 'element-on' : 'element-off' ) : 'element-invalid',
				]
			)
		);
		exit;
	}

	public function render(): void {
		// phpcs:disable WordPress.Security.NonceVerification.Recommended -- read-only tab and notice routing.
		$type   = isset( $_GET['type'] ) ? sanitize_key( wp_unslash( (string) $_GET['type'] ) ) : 'gutenberg';
		$notice = isset( $_GET['wpe-notice'] ) ? sanitize_key( wp_unslash( (string) $_GET['wpe-notice'] ) ) : '';
		// phpcs:enable
		$type = in_array( $type, ElementRegistry::TYPES, true ) ? $type : 'gutenberg';

		$labels = [
			'gutenberg' => __( 'Gutenberg', 'wpessential' ),
			'elementor' => __( 'Elementor', 'wpessential' ),
		];

		$html  = PageHeader::render( __( 'Elements', 'wpessential' ), __( 'Blocks & widgets', 'wpessential' ), __( 'Turn WPEssential elements on or off for each page builder. Disabled elements load no code.', 'wpessential' ) );
		$html .= '<div class="wpe-ui-toolbar"><nav class="wpe-ui-tabs" aria-label="' . esc_attr__( 'Page builder', 'wpessential' ) . '">';
		foreach ( $labels as $key => $label ) {
			$active = $key === $type;
			$html  .= '<a class="wpe-ui-tab' . ( $active ? ' is-active' : '' ) . '" href="' . esc_url( AdminUrl::page( Shell::ELEMENTS_SLUG, [ 'type' => $key ] ) ) . '"' . ( $active ? ' aria-current="page"' : '' ) . '>'
				. Icon::render( $key, 16 ) . esc_html( $label ) . ' ' . Badge::count( count( $this->elements->all( $key ) ) ) . '</a>';
		}
		$html .= '</nav></div>';

		if ( $type === 'elementor' && ! did_action( 'elementor/loaded' ) ) {
			$html .= Notice::render( 'warning', __( 'Elementor is not active. Install and activate Elementor to use WPEssential Elementor widgets.', 'wpessential' ), true );
		}

		$elements = $this->elements->all( $type );
		if ( $elements === [] ) {
			$html .= '<div class="wpe-ui-empty wpe-ui-empty--dashed"><span class="wpe-ui-icon wpe-ui-icon--lg">' . Icon::render( $type, 26 ) . '</span>';
			$html .= '<h2>' . esc_html( $type === 'gutenberg' ? __( 'No Gutenberg blocks yet', 'wpessential' ) : __( 'No Elementor widgets yet', 'wpessential' ) ) . '</h2>';
			$html .= '<p>' . esc_html__( 'Elements will appear here as WPEssential modules add them. Each one can be switched on or off for the whole site.', 'wpessential' ) . '</p>';
			$html .= Button::link( __( 'Read the Elements guide', 'wpessential' ), AdminUrl::page( Shell::DOCS_SLUG ), 'ghost' ) . '</div>';
		} else {
			$html .= '<section class="wpe-ui-panel wpe-ui-panel--flush" aria-label="' . esc_attr( $labels[ $type ] ) . '">';
			foreach ( $elements as $element ) {
				$html .= '<div class="wpe-ui-option"><div class="wpe-ui-option__copy wpe-ui-option__copy--row"><span class="wpe-ui-icon">' . Icon::render( $element['icon'], 20 ) . '</span>';
				$html .= '<span><span class="wpe-ui-option__label">' . esc_html( $element['name'] ) . '</span><br><span class="wpe-ui-muted">' . esc_html( $element['description'] ) . '</span></span></div>';
				$html .= '<div class="wpe-ui-option__meta">' . Badge::edition( $element['edition'] );
				$html .= Toggle::form(
					self::TOGGLE_ACTION,
					self::TOGGLE_ACTION,
					$this->elements->isEnabled( $type, $element['id'] ),
					/* translators: %s: element name. */
					sprintf( __( 'Turn %s on or off', 'wpessential' ), $element['name'] ),
					[
						'type'    => $type,
						'element' => $element['id'],
					]
				) . '</div></div>';
			}
			$html .= '</section>';
		}

		$messages = [
			'element-on'      => [ 'success', __( 'Element switched on.', 'wpessential' ) ],
			'element-off'     => [ 'info', __( 'Element switched off.', 'wpessential' ) ],
			'element-invalid' => [ 'error', __( 'That element is not available.', 'wpessential' ) ],
		];

		Screen::render( Shell::ELEMENTS_SLUG, $html, isset( $messages[ $notice ] ) ? Notice::render( ...$messages[ $notice ] ) : '', 'core', $this->appData( $labels ) );
	}

	/**
	 * Elements view model for the React app: both builders, so the tabs switch in the browser.
	 *
	 * @param array<string,string> $labels
	 * @return array<string,mixed>
	 */
	private function appData( array $labels ): array {
		$types = [];
		foreach ( $labels as $key => $label ) {
			$items = [];
			foreach ( $this->elements->all( $key ) as $element ) {
				$items[] = [
					'id'          => $element['id'],
					'name'        => $element['name'],
					'description' => $element['description'],
					'iconHtml'    => Icon::render( $element['icon'], 20 ),
					'badgeHtml'   => Badge::edition( $element['edition'] ),
					'toggleHtml'  => Toggle::form(
						self::TOGGLE_ACTION,
						self::TOGGLE_ACTION,
						$this->elements->isEnabled( $key, $element['id'] ),
						/* translators: %s: element name. */
						sprintf( __( 'Turn %s on or off', 'wpessential' ), $element['name'] ),
						[
							'type'    => $key,
							'element' => $element['id'],
						]
					),
				];
			}
			$types[] = [
				'key'        => $key,
				'label'      => $label,
				'iconHtml'   => Icon::render( $key, 16 ),
				'emptyIcon'  => Icon::render( $key, 26 ),
				'emptyTitle' => $key === 'gutenberg' ? __( 'No Gutenberg blocks yet', 'wpessential' ) : __( 'No Elementor widgets yet', 'wpessential' ),
				'noticeHtml' => $key === 'elementor' && ! did_action( 'elementor/loaded' ) ? Notice::render( 'warning', __( 'Elementor is not active. Install and activate Elementor to use WPEssential Elementor widgets.', 'wpessential' ), true ) : '',
				'elements'   => $items,
			];
		}

		return [
			'screen'  => 'elements',
			'title'   => __( 'Elements', 'wpessential' ),
			'text'    => [
				'eyebrow'   => __( 'Elements', 'wpessential' ),
				'heading'   => __( 'Blocks & widgets', 'wpessential' ),
				'lead'      => __( 'Turn WPEssential elements on or off for each page builder. Disabled elements load no code.', 'wpessential' ),
				'builder'   => __( 'Page builder', 'wpessential' ),
				'emptyText' => __( 'Elements will appear here as WPEssential modules add them. Each one can be switched on or off for the whole site.', 'wpessential' ),
				'guide'     => __( 'Read the Elements guide', 'wpessential' ),
			],
			'docsUrl' => AdminUrl::page( Shell::DOCS_SLUG ),
			'types'   => $types,
		];
	}
}
