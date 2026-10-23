<?php

declare(strict_types=1);

namespace WPEssential\Platform\Admin\Demos;

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

/**
 * Demo Library screen (Figma: "Demo Library"). Browses the wpessential.org demo manifest.
 * The import wizard follows in a later change (ADR-0231).
 */
final class DemosScreen implements AdminScreen {

	public const REFRESH_ACTION = 'wpessential_demos_refresh';

	public function __construct( private readonly DemoCatalog $catalog ) {}

	public function slug(): string {
		return Shell::DEMOS_SLUG;
	}

	public function title(): string {
		return __( 'Demo Library', 'wpessential' );
	}

	public function register(): void {
		if ( function_exists( 'add_action' ) ) {
			add_action( 'admin_post_' . self::REFRESH_ACTION, [ $this, 'handleRefresh' ] );
		}
	}

	public function handleRefresh(): void {
		if ( ! current_user_can( PlatformAdminController::CAPABILITY ) ) {
			wp_die( esc_html__( 'You are not allowed to manage WPEssential demos.', 'wpessential' ), 'WPEssential', [ 'response' => 403 ] );
		}
		check_admin_referer( self::REFRESH_ACTION );
		$this->catalog->all( true );
		wp_safe_redirect( AdminUrl::page( Shell::DEMOS_SLUG ) );
		exit;
	}

	public function render(): void {
		// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- read-only filter.
		$niche = isset( $_GET['niche'] ) ? sanitize_key( wp_unslash( (string) $_GET['niche'] ) ) : '';
		$data  = $this->catalog->all();

		$refresh  = '<form method="post" action="' . esc_url( admin_url( 'admin-post.php' ) ) . '">' . wp_nonce_field( self::REFRESH_ACTION, '_wpnonce', true, false );
		$refresh .= '<input type="hidden" name="action" value="' . esc_attr( self::REFRESH_ACTION ) . '"><button type="submit" class="' . esc_attr( Button::classes( 'ghost', true ) ) . '">' . Icon::render( 'refresh', 16 ) . esc_html__( 'Refresh library', 'wpessential' ) . '</button></form>';

		$html = PageHeader::render( __( 'Demo Library', 'wpessential' ), __( 'Start from a ready-made site', 'wpessential' ), __( 'Browse complete websites built with WPEssential for Gutenberg and Elementor.', 'wpessential' ), $refresh );

		$niches = [];
		foreach ( $data['demos'] as $demo ) {
			foreach ( $demo['niche'] as $item ) {
				$niches[ $item ] = ( $niches[ $item ] ?? 0 ) + 1;
			}
		}
		if ( $niches !== [] ) {
			$html .= '<div class="wpe-ui-toolbar"><nav class="wpe-ui-tabs" aria-label="' . esc_attr__( 'Demo categories', 'wpessential' ) . '">';
			$html .= self::tab( '', __( 'All', 'wpessential' ), count( $data['demos'] ), $niche );
			foreach ( $niches as $item => $count ) {
				$html .= self::tab( $item, ucfirst( $item ), $count, $niche );
			}
			$html .= '</nav></div>';
		}

		$demos = array_values( array_filter( $data['demos'], static fn ( array $demo ): bool => $niche === '' || in_array( $niche, $demo['niche'], true ) ) );
		if ( $demos === [] ) {
			$html .= '<div class="wpe-ui-empty wpe-ui-empty--dashed"><span class="wpe-ui-icon wpe-ui-icon--lg">' . Icon::render( 'grid', 26 ) . '</span>';
			$html .= '<h2>' . esc_html( $data['offline'] ? __( 'The demo library is unreachable', 'wpessential' ) : __( 'No demos yet', 'wpessential' ) ) . '</h2>';
			$html .= '<p>' . esc_html( $data['offline'] ? __( 'WPEssential could not reach wpessential.org. Check the connection and refresh the library.', 'wpessential' ) : __( 'New demos appear here as they are published on wpessential.org.', 'wpessential' ) ) . '</p></div>';
		} else {
			$html .= '<div class="wpe-ui-demo-grid">';
			foreach ( $demos as $demo ) {
				$html .= self::card( $demo );
			}
			$html .= '</div>';
		}

		$notice = Notice::render( 'info', __( 'One-click demo import is coming in the next update. For now you can preview each demo.', 'wpessential' ) );
		Screen::render( Shell::DEMOS_SLUG, $html, $notice, 'core', $this->appData( $data, $refresh ) );
	}

	/**
	 * Demo Library view model for the React app (all demos; the niche tabs filter in the browser).
	 *
	 * @param array{demos:list<array<string,mixed>>,offline:bool} $data
	 * @return array<string,mixed>
	 */
	private function appData( array $data, string $refreshHtml ): array {
		$demos = [];
		foreach ( $data['demos'] as $demo ) {
			$demos[] = [
				'id'        => $demo['id'],
				'title'     => $demo['title'],
				'tagline'   => $demo['tagline'],
				'niche'     => $demo['niche'],
				'badgeHtml' => Badge::edition( $demo['type'] === 'pro' ? 'pro' : 'free' ),
				'thumbnail' => $demo['thumbnail'],
				'preview'   => $demo['preview'],
				'builders'  => array_map(
					static fn ( string $builder ): array => [
						'label'    => ucfirst( $builder ),
						'iconHtml' => Icon::render( $builder, 16 ),
					],
					$demo['builders']
				),
			];
		}

		return [
			'screen'      => 'demos',
			'title'       => __( 'Demo Library', 'wpessential' ),
			'text'        => [
				'eyebrow'    => __( 'Demo Library', 'wpessential' ),
				'heading'    => __( 'Start from a ready-made site', 'wpessential' ),
				'lead'       => __( 'Browse complete websites built with WPEssential for Gutenberg and Elementor.', 'wpessential' ),
				'all'        => __( 'All', 'wpessential' ),
				'categories' => __( 'Demo categories', 'wpessential' ),
				'preview'    => __( 'Preview', 'wpessential' ),
				'emptyTitle' => $data['offline'] ? __( 'The demo library is unreachable', 'wpessential' ) : __( 'No demos yet', 'wpessential' ),
				'emptyText'  => $data['offline'] ? __( 'WPEssential could not reach wpessential.org. Check the connection and refresh the library.', 'wpessential' ) : __( 'New demos appear here as they are published on wpessential.org.', 'wpessential' ),
				'newTab'     => __( '(opens in a new tab)', 'wpessential' ),
			],
			'refreshHtml' => $refreshHtml,
			'gridIcon'    => Icon::render( 'grid', 26 ),
			'imageIcon'   => Icon::render( 'image', 32 ),
			'demos'       => $demos,
		];
	}

	private static function tab( string $key, string $label, int $count, string $current ): string {
		$active = $key === $current;

		return '<a class="wpe-ui-tab' . ( $active ? ' is-active' : '' ) . '" href="' . esc_url( AdminUrl::page( Shell::DEMOS_SLUG, $key === '' ? [] : [ 'niche' => $key ] ) ) . '"' . ( $active ? ' aria-current="page"' : '' ) . '>'
			. esc_html( $label ) . ' ' . Badge::count( $count ) . '</a>';
	}

	/** @param array{id:string,title:string,tagline:string,niche:list<string>,type:string,thumbnail:string,preview:string,builders:list<string>} $demo */
	private static function card( array $demo ): string {
		$html  = '<article class="wpe-ui-demo">';
		$html .= $demo['thumbnail'] !== ''
			? '<img class="wpe-ui-demo__shot" src="' . esc_url( $demo['thumbnail'] ) . '" alt="" loading="lazy">'
			: '<div class="wpe-ui-demo__shot" aria-hidden="true">' . Icon::render( 'image', 32 ) . '</div>';
		$html .= '<div class="wpe-ui-demo__body"><div class="wpe-ui-demo__head"><h2>' . esc_html( $demo['title'] ) . '</h2>' . Badge::edition( $demo['type'] === 'pro' ? 'pro' : 'free' ) . '</div>';
		$html .= '<p class="wpe-ui-muted">' . esc_html( $demo['tagline'] ) . '</p><div class="wpe-ui-demo__foot"><span class="wpe-ui-demo__builders">';
		foreach ( $demo['builders'] as $builder ) {
			$html .= '<span title="' . esc_attr( ucfirst( $builder ) ) . '">' . Icon::render( $builder, 16 ) . '<span class="screen-reader-text">' . esc_html( ucfirst( $builder ) ) . '</span></span>';
		}
		$html .= '</span>';
		if ( $demo['preview'] !== '' ) {
			$html .= Button::link( __( 'Preview', 'wpessential' ), $demo['preview'], 'ghost', true, true );
		}

		return $html . '</div></div></article>';
	}
}
