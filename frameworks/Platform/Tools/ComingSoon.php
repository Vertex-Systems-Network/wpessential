<?php

declare(strict_types=1);

namespace WPEssential\Platform\Tools;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use Closure;
use WP_Admin_Bar;

/**
 * Coming soon / maintenance mode. While the module is on, visitors see the WPEssential template or a
 * page/post the owner picked; staff who can edit posts browse the real site and get an admin bar alert.
 */
final class ComingSoon {

	public const OPTION = 'wpessential_coming_soon';
	public const SLUG   = 'wpessential-coming-soon';

	public const DEFAULTS = [
		'mode'     => 'coming_soon',
		'template' => 'default',
		'page_id'  => 0,
		'heading'  => '',
		'message'  => '',
	];

	/** @param Closure(): bool $enabled */
	public function __construct( private readonly Closure $enabled ) {}

	/** @return array{mode:string,template:string,page_id:int,heading:string,message:string} */
	public static function settings(): array {
		$saved = function_exists( 'get_option' ) ? get_option( self::OPTION, [] ) : [];

		return self::normalize( is_array( $saved ) ? $saved : [] );
	}

	/**
	 * @param array<string,mixed> $input
	 * @return array{mode:string,template:string,page_id:int,heading:string,message:string}
	 */
	public static function normalize( array $input ): array {
		return [
			'mode'     => ( $input['mode'] ?? '' ) === 'maintenance' ? 'maintenance' : 'coming_soon',
			'template' => ( $input['template'] ?? '' ) === 'custom' ? 'custom' : 'default',
			'page_id'  => max( 0, (int) ( $input['page_id'] ?? 0 ) ),
			'heading'  => trim( (string) ( $input['heading'] ?? '' ) ),
			'message'  => trim( (string) ( $input['message'] ?? '' ) ),
		];
	}

	/** Staff keep the real site. */
	public static function bypass(): bool {
		return function_exists( 'current_user_can' ) && current_user_can( 'edit_posts' );
	}

	public function register(): void {
		if ( ! function_exists( 'add_action' ) ) {
			return;
		}
		add_action( 'template_redirect', [ $this, 'gate' ], 0 );
		add_action( 'admin_bar_menu', [ $this, 'adminBar' ], 100 );
		add_action( 'admin_head', [ $this, 'adminBarStyle' ] );
		add_action( 'wp_head', [ $this, 'adminBarStyle' ] );
	}

	public function gate(): void {
		// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Read-only preview for staff.
		if ( isset( $_GET['wpe-coming-soon-preview'] ) && self::bypass() ) {
			nocache_headers();
			echo self::page( self::settings() ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Escaped in page().
			exit;
		}
		if ( ! ( $this->enabled )() || self::bypass() || is_robots() || is_favicon() ) {
			return;
		}
		$settings = self::settings();
		header( 'X-Robots-Tag: noindex, nofollow', true );
		$page = $settings['template'] === 'custom' ? get_post( $settings['page_id'] ) : null;
		if ( $page !== null && $page->post_status === 'publish' ) {
			if ( ! is_singular() || (int) get_queried_object_id() !== (int) $page->ID ) {
				wp_safe_redirect( (string) get_permalink( $page ), 302 );
				exit;
			}
			if ( $settings['mode'] === 'maintenance' ) {
				status_header( 503 );
				header( 'Retry-After: 3600' );
			}
			return;
		}
		nocache_headers();
		status_header( $settings['mode'] === 'maintenance' ? 503 : 200 );
		if ( $settings['mode'] === 'maintenance' ) {
			header( 'Retry-After: 3600' );
		}
		echo self::page( $settings ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Escaped in page().
		exit;
	}

	/** @param array{mode:string,template:string,page_id:int,heading:string,message:string} $settings */
	public static function page( array $settings ): string {
		$maintenance = $settings['mode'] === 'maintenance';
		$heading     = $settings['heading'] !== '' ? $settings['heading'] : ( $maintenance ? __( 'We will be back soon', 'wpessential' ) : __( 'Something new is coming', 'wpessential' ) );
		$message     = $settings['message'] !== '' ? $settings['message'] : ( $maintenance ? __( 'We are making a few improvements. Please check back in a little while.', 'wpessential' ) : __( 'Our new website is almost ready. Check back soon.', 'wpessential' ) );
		$name        = get_bloginfo( 'name' );
		$icon        = get_site_icon_url( 96 );
		$eyebrow     = $maintenance ? __( 'Maintenance', 'wpessential' ) : __( 'Coming soon', 'wpessential' );

		return '<!doctype html><html ' . get_language_attributes() . '><head><meta charset="' . esc_attr( get_bloginfo( 'charset' ) ) . '">'
			. '<meta name="viewport" content="width=device-width, initial-scale=1"><meta name="robots" content="noindex, nofollow">'
			. '<title>' . esc_html( $heading . ' – ' . $name ) . '</title>'
			. ( $icon !== '' ? '<link rel="icon" href="' . esc_url( $icon ) . '">' : '' )
			. '<style>'
			. ':root{--ink:#0b1a3d;--muted:#a9b6d3;--orange:#ec7d1a;--indigo:#1f48a6}'
			. '*{box-sizing:border-box}html,body{height:100%;margin:0}'
			. 'body{display:grid;place-items:center;padding:24px;background:radial-gradient(120% 80% at 50% 110%,var(--indigo) 0%,var(--ink) 65%);color:#fff;font:16px/1.6 "Plus Jakarta Sans",-apple-system,BlinkMacSystemFont,"Segoe UI",Roboto,sans-serif;text-align:center}'
			. 'main{max-width:640px}img{width:64px;height:64px;border-radius:14px;margin-bottom:24px}'
			. '.e{display:inline-block;margin-bottom:16px;padding:4px 12px;border-radius:999px;background:rgb(236 125 26/18%);color:var(--orange);font:500 12px/1.6 ui-monospace,SFMono-Regular,Menlo,monospace;letter-spacing:.08em;text-transform:uppercase}'
			. 'h1{margin:0 0 16px;font-family:"Bricolage Grotesque","Plus Jakarta Sans",sans-serif;font-size:clamp(32px,6vw,56px);line-height:1.1;letter-spacing:-.02em}'
			. 'p{margin:0;color:var(--muted);font-size:18px}footer{margin-top:40px;color:var(--muted);font-size:14px}'
			. '</style></head><body><main>'
			. ( $icon !== '' ? '<img src="' . esc_url( $icon ) . '" alt="">' : '' )
			. '<span class="e">' . esc_html( $eyebrow ) . '</span>'
			. '<h1>' . esc_html( $heading ) . '</h1>'
			. '<p>' . nl2br( esc_html( $message ) ) . '</p>'
			. '<footer>' . esc_html( $name ) . '</footer>'
			. '</main></body></html>';
	}

	public function adminBar( WP_Admin_Bar $bar ): void {
		if ( ! ( $this->enabled )() || ! self::bypass() ) {
			return;
		}
		$bar->add_node(
			[
				'id'    => 'wpessential-coming-soon',
				'title' => esc_html( self::settings()['mode'] === 'maintenance' ? __( 'Maintenance mode is on', 'wpessential' ) : __( 'Coming soon mode is on', 'wpessential' ) ),
				'href'  => admin_url( 'admin.php?page=' . self::SLUG ),
				'meta'  => [ 'title' => __( 'Visitors see the coming soon page. Open the settings to change or switch it off.', 'wpessential' ) ],
			]
		);
	}

	public function adminBarStyle(): void {
		if ( ! ( $this->enabled )() || ! self::bypass() || ! is_admin_bar_showing() ) {
			return;
		}
		echo '<style>#wpadminbar #wp-admin-bar-wpessential-coming-soon>.ab-item{background:#c0362c;color:#fff;font-weight:600}#wpadminbar #wp-admin-bar-wpessential-coming-soon>.ab-item:hover{background:#a12b22;color:#fff}</style>';
	}
}
