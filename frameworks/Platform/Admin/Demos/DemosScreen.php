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
use WPEssential\Platform\Admin\Ui\PageHeader;
use WPEssential\Platform\Admin\Ui\Screen;
use WPEssential\Platform\Admin\Ui\Shell;
use WPEssential\Platform\Demos\Import\Demos as ImportDemos;
use WPEssential\Platform\Demos\Import\Importer;

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

		Screen::render( Shell::DEMOS_SLUG, $html, '', 'core', $this->appData( $data, $refresh ) );
	}

	/**
	 * Demo Library view model for the React app (all demos; the niche tabs filter in the browser).
	 *
	 * @param array{demos:list<array<string,mixed>>,offline:bool} $data
	 * @return array<string,mixed>
	 */
	private function appData( array $data, string $refreshHtml ): array {
		// What the importer can install: builders that have a package, and Pro lock state.
		$importable = [];
		foreach ( ImportDemos::all()['demos'] as $item ) {
			if ( is_array( $item ) && isset( $item['id'] ) ) {
				$importable[ (string) $item['id'] ] = $item;
			}
		}
		$imported = Importer::imported();
		$job      = Importer::current();
		$demos    = [];
		foreach ( $data['demos'] as $demo ) {
			$source = $importable[ $demo['id'] ] ?? null;
			$demos[] = [
				'id'        => $demo['id'],
				'title'     => $demo['title'],
				'tagline'   => $demo['tagline'],
				'niche'     => $demo['niche'],
				'badgeHtml' => Badge::edition( $demo['type'] === 'pro' ? 'pro' : 'free' ),
				'type'      => $demo['type'] === 'pro' ? 'pro' : 'free',
				'thumbnail' => $demo['thumbnail'],
				'preview'   => $demo['preview'],
				'builders'  => array_map(
					static fn ( string $builder ): array => [
						'label'    => ucfirst( $builder ),
						'iconHtml' => Icon::render( $builder, 16 ),
					],
					$demo['builders']
				),
				'locked'     => $demo['type'] === 'pro' && ! Shell::premiumAvailable(),
				'importable' => $source === null ? [] : array_values(
					array_map(
						static fn ( string $builder ): array => [
							'key'   => $builder,
							'label' => ucfirst( $builder ),
						],
						array_filter( array_map( 'strval', array_keys( (array) ( $source['builders'] ?? [] ) ) ), static fn ( string $b ): bool => in_array( $b, [ 'gutenberg', 'elementor' ], true ) )
					)
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
				'filters'    => __( 'Demo filters', 'wpessential' ),
				'search'     => __( 'Search demos…', 'wpessential' ),
				'type'       => __( 'Type', 'wpessential' ),
				'free'       => __( 'Free', 'wpessential' ),
				'premium'    => __( 'Premium', 'wpessential' ),
				'builder'    => __( 'Builder', 'wpessential' ),
				'reset'      => __( 'Clear filters', 'wpessential' ),
				'allTypes'   => __( 'All types', 'wpessential' ),
				'allBuilders' => __( 'All builders', 'wpessential' ),
				'sort'       => __( 'Sort', 'wpessential' ),
				'sortAsc'    => __( 'Name A–Z', 'wpessential' ),
				'sortDesc'   => __( 'Name Z–A', 'wpessential' ),
				/* translators: %d: number of demos. */
				'showing'    => __( '%d demos', 'wpessential' ),
				'noMatch'    => __( 'No demos match these filters.', 'wpessential' ),
			],
			'refreshHtml' => $refreshHtml,
			'gridIcon'    => Icon::render( 'grid', 26 ),
			'imageIcon'   => Icon::render( 'image', 32 ),
			'demos'       => $demos,
			'rest'        => [
				'root'  => esc_url_raw( rest_url() ),
				'nonce' => wp_create_nonce( 'wp_rest' ),
			],
			'upgradeUrl'  => Shell::UPGRADE_URL,
			'imported'    => is_array( $imported ) && isset( $imported['demo'] ) ? [
				'demo'    => (string) $imported['demo'],
				'title'   => (string) ( $imported['title'] ?? '' ),
				'builder' => (string) ( $imported['builder'] ?? '' ),
			] : null,
			'job'         => is_array( $job ) && ! empty( $job['id'] ) && in_array( $job['status'] ?? '', [ 'running', 'failed' ], true ) ? [
				'demo'    => (string) $job['demo'],
				'title'   => (string) $job['title'],
				'builder' => (string) $job['builder'],
				'status'  => (string) $job['status'],
			] : null,
			'autostart'   => ( static function () use ( $imported, $job ) {
				$auto = get_option( \WPEssential\Platform\Demos\Import\Rest::AUTOSTART );
				if ( ! is_array( $auto ) || empty( $auto['demo'] ) || $imported || ( is_array( $job ) && ! empty( $job['id'] ) && 'running' === ( $job['status'] ?? '' ) ) ) {
					return null;
				}
				return [
					'demo'    => (string) $auto['demo'],
					'builder' => (string) $auto['builder'],
				];
			} )(),
			'importText'  => [
				'import'         => __( 'Import', 'wpessential' ),
				/* translators: %s: Gutenberg or Elementor. */
				'importWith'     => __( 'Import for %s', 'wpessential' ),
				'unlock'         => __( 'Unlock with Pro', 'wpessential' ),
				'importing'      => __( 'Demo import', 'wpessential' ),
				'starting'       => __( 'Preparing the import…', 'wpessential' ),
				'progress'       => __( 'Import progress', 'wpessential' ),
				'done'           => __( 'Import complete', 'wpessential' ),
				'failed'         => __( 'The import stopped', 'wpessential' ),
				'saved'          => __( 'Your progress is saved; resume to continue where it stopped.', 'wpessential' ),
				'viewSite'       => __( 'View site', 'wpessential' ),
				'close'          => __( 'Close', 'wpessential' ),
				'resume'         => __( 'Resume import', 'wpessential' ),
				'cancel'         => __( 'Cancel import', 'wpessential' ),
				/* translators: %s: demo name. */
				'interrupted'    => __( 'The import of %s did not finish. Your progress is saved.', 'wpessential' ),
				/* translators: %s: demo name. */
				'installed'      => __( '%s is installed on this site. You can remove everything it added and restore your previous settings.', 'wpessential' ),
				'remove'         => __( 'Remove demo', 'wpessential' ),
				/* translators: %s: demo name. */
				'removeConfirm'  => __( 'Remove %s? Its pages, posts, products, images and menus are deleted and your previous settings restored. Content you created yourself is not touched.', 'wpessential' ),
				/* translators: %s: demo name. */
				'replaceConfirm' => __( '%s is already installed. It will be removed first, then the new demo is imported. Continue?', 'wpessential' ),
				'doneNotice'     => __( 'Demo imported. Open your site to see it.', 'wpessential' ),
				'active'         => __( 'Active', 'wpessential' ),
				'reimport'       => __( 'Import again', 'wpessential' ),
				'wizardSub'      => __( 'One-click demo import', 'wpessential' ),
				'stepBuilder'    => __( 'Builder', 'wpessential' ),
				'stepPlugins'    => __( 'Plugins', 'wpessential' ),
				'stepContent'    => __( 'Content', 'wpessential' ),
				'stepImport'     => __( 'Import', 'wpessential' ),
				'stepDone'       => __( 'Done', 'wpessential' ),
				'builderTitle'   => __( 'Choose your builder', 'wpessential' ),
				'builderText'    => __( 'Pages are built for the editor you pick. You can import the other version later.', 'wpessential' ),
				'pluginsTitle'   => __( 'Plugins this demo uses', 'wpessential' ),
				'pluginsText'    => __( 'We install, activate or switch off plugins so the demo works exactly like the preview.', 'wpessential' ),
				'noPlugins'      => __( 'This demo needs no extra plugins.', 'wpessential' ),
				'required'       => __( 'Required', 'wpessential' ),
				'recommended'    => __( 'Recommended', 'wpessential' ),
				'conflicts'      => __( 'Conflicts with this demo', 'wpessential' ),
				'plgActive'      => __( 'Active', 'wpessential' ),
				'plgInstall'     => __( 'Will install', 'wpessential' ),
				'plgActivate'    => __( 'Will activate', 'wpessential' ),
				'plgDeactivate'  => __( 'Will switch off', 'wpessential' ),
				'contentTitle'   => __( 'What to import', 'wpessential' ),
				'contentText'    => __( 'Untick anything you do not need. Everything imported can be removed later in one click.', 'wpessential' ),
				'optContent'     => __( 'Pages, posts and menus', 'wpessential' ),
				'optMedia'       => __( 'Images and media', 'wpessential' ),
				'optProducts'    => __( 'Shop products', 'wpessential' ),
				'optSettings'    => __( 'Site settings, logo and homepage', 'wpessential' ),
				/* translators: %s: demo name. */
				'replaceTitle'   => __( '%s will be replaced', 'wpessential' ),
				'replaceRemove'  => __( 'Its pages, posts, products, images and menus are deleted.', 'wpessential' ),
				'replaceRestore' => __( 'The settings it changed are restored to what you had before.', 'wpessential' ),
				'replaceKeep'    => __( 'Content you created yourself is not touched.', 'wpessential' ),
				/* translators: %s: demo name. */
				'replaceAgree'   => __( 'I understand. Remove %s and import the new demo.', 'wpessential' ),
				'importTitle'    => __( 'Importing your demo', 'wpessential' ),
				'doneTitle'      => __( 'Your site is ready', 'wpessential' ),
				'back'           => __( 'Back', 'wpessential' ),
				'next'           => __( 'Continue', 'wpessential' ),
				'startImport'    => __( 'Start import', 'wpessential' ),
				'loading'        => __( 'Loading…', 'wpessential' ),
				'safe'           => __( 'Safe import: every change can be undone.', 'wpessential' ),
			],
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
		if ( $demo['niche'] !== [] ) {
			$html .= '<span class="wpe-ui-demo__niches">';
			foreach ( $demo['niche'] as $item ) {
				$html .= '<span class="wpe-ui-chip">' . esc_html( ucfirst( $item ) ) . '</span>';
			}
			$html .= '</span>';
		}
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
