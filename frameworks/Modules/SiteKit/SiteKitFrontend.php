<?php

declare(strict_types=1);

namespace WPEssential\Modules\SiteKit;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use WPEssential\Platform\Admin\Ui\Icon;

/**
 * Applies Site Kit on the front end and in the block editor: theme.json user layer, CSS, Google
 * Fonts, the light/dark scheme, the back-to-top button and the Elementor kit sync.
 */
final class SiteKitFrontend {

	public const STYLE_HANDLE = 'wpessential-site-kit';

	public function __construct( private readonly SiteKitStore $store ) {}

	public function register(): void {
		add_filter( 'wp_theme_json_data_user', [ $this, 'themeJson' ] );
		add_filter( 'wpessential_kit_global_palette', [ $this, 'kitPalette' ] );
		add_filter( 'wpessential_kit_typography_presets', [ $this, 'kitTypography' ] );
		add_filter( 'wpessential_kit_shadow_presets', [ $this, 'kitShadows' ] );
		add_action( 'wp_enqueue_scripts', [ $this, 'enqueue' ], 25 );
		add_action( 'enqueue_block_assets', [ $this, 'enqueueFonts' ] );
		add_action( 'enqueue_block_assets', [ $this, 'enqueueEditorTokens' ] );
		add_action( 'init', [ $this, 'registerTypographyStyles' ], 20 );
		add_filter( 'language_attributes', [ $this, 'schemeAttribute' ] );
		add_action( 'wp_head', [ $this, 'schemeScript' ], 1 );
		add_action( 'wp_footer', [ $this, 'floatingControls' ], 50 );
		add_action( 'wpessential_site_kit_saved', [ $this, 'saved' ] );
	}

	/** @param array<string,string> $palette @return array<string,string> */
	public function kitPalette( array $palette ): array {
		foreach ( SiteKitTokens::colours( $this->store->values()['custom_colours'] ?? [] ) as $token ) {
			$palette[ $token['slug'] ] = $token['name'];
		}
		return $palette;
	}

	/** @param array<mixed> $presets @return array<mixed> */
	public function kitTypography( array $presets ): array {
		$values = $this->store->values();
		foreach ( SiteKitTokens::typography( $values['custom_typography'] ?? [] ) as $token ) {
			$devices = array_keys( SiteKitBreakpoints::enabled( (array) ( $values['breakpoints'] ?? [] ) ) );
			$props   = [];
			foreach ( SiteKitCompiler::typography( $token['typography'], $devices ) as $declarations ) {
				$props = array_merge( $props, array_keys( $declarations ) );
			}
			$presets[] = [
				'slug'       => $token['slug'],
				'name'       => $token['name'],
				'properties' => array_values( array_unique( $props ) ),
			];
		}
		return $presets;
	}

	/** @param array<string,string> $presets @return array<string,string> */
	public function kitShadows( array $presets ): array {
		foreach ( SiteKitTokens::shadows( $this->store->values()['custom_shadows'] ?? [] ) as $token ) {
			$presets[ $token['slug'] ] = $token['name'];
		}
		return $presets;
	}

	/** @param \WP_Theme_JSON_Data $themeJson */
	public function themeJson( mixed $themeJson ): mixed {
		$values = $this->store->values();
		if ( $values === [] || ! is_object( $themeJson ) || ! method_exists( $themeJson, 'get_data' ) || ! method_exists( $themeJson, 'update_with' ) ) {
			return $themeJson;
		}
		$preset = static function ( array $path ): array {
			if ( ! class_exists( 'WP_Theme_JSON_Resolver' ) ) {
				return [];
			}
			$list = \WP_Theme_JSON_Resolver::get_theme_data()->get_settings();
			foreach ( $path as $step ) {
				$list = is_array( $list ) ? ( $list[ $step ] ?? [] ) : [];
			}
			$list = is_array( $list ) ? ( $list['theme'] ?? $list ) : [];

			return array_values( array_filter( (array) $list, 'is_array' ) );
		};

		return $themeJson->update_with( SiteKitCompiler::themeJson( (array) $themeJson->get_data(), $values, $preset ) );
	}

	public function enqueue(): void {
		$values = $this->store->values();
		$this->enqueueFonts();
		$css = SiteKitCompiler::css( $values );
		if ( $css === '' ) {
			return;
		}
		wp_register_style( self::STYLE_HANDLE, false, [], (string) ( defined( 'WPE_VERSION' ) ? WPE_VERSION : '1' ) );
		wp_enqueue_style( self::STYLE_HANDLE );
		wp_add_inline_style( self::STYLE_HANDLE, $css );
	}

	/** Native block style picker for text blocks; local block styles keep precedence. */
	public function registerTypographyStyles(): void {
		if ( ! function_exists( 'register_block_style' ) ) {
			return;
		}
		$values = $this->store->values();
		foreach ( SiteKitTokens::typography( $values['custom_typography'] ?? [] ) as $token ) {
			foreach ( [ 'core/paragraph', 'core/heading' ] as $block ) {
				register_block_style(
					$block,
					[
						'name'  => $token['slug'],
						'label' => $token['name'],
					]
				);
			}
		}
	}

	public function enqueueEditorTokens(): void {
		if ( ! is_admin() ) {
			return;
		}
		$css = SiteKitCompiler::tokenCss( $this->store->values() );
		if ( $css === '' ) {
			return;
		}
		$handle = self::STYLE_HANDLE . '-editor-tokens';
		wp_register_style( $handle, false, [], (string) ( defined( 'WPE_VERSION' ) ? WPE_VERSION : '1' ) );
		wp_enqueue_style( $handle );
		wp_add_inline_style( $handle, $css );
	}

	public function enqueueFonts(): void {
		$url = SiteKitFonts::url( SiteKitFonts::remote( $this->store->values() ) );
		if ( $url !== '' ) {
			wp_enqueue_style( 'wpessential-site-kit-fonts', $url, [], null ); // phpcs:ignore WordPress.WP.EnqueuedResourceParameters.MissingVersion -- Google Fonts URL.
		}
	}

	/** Dark mode is active only when dark colours are set; the server-side default is light. */
	private function schemeEnabled(): bool {
		$values = $this->store->values();
		foreach ( SiteKitTokens::colours( $values['custom_colours'] ?? [] ) as $token ) {
			if ( $token['dark'] !== '' ) {
				return true;
			}
		}
		foreach ( array_keys( $values ) as $key ) {
			if ( str_starts_with( (string) $key, 'dark_' ) || $key === 'site_bg_dark' ) {
				return true;
			}
		}

		return false;
	}

	public function schemeAttribute( string $output ): string {
		return $this->schemeEnabled() && ! str_contains( $output, 'data-wpe-scheme' ) ? $output . ' data-wpe-scheme="light"' : $output;
	}

	/** Applies the stored or OS preference before first paint. */
	public function schemeScript(): void {
		if ( ! $this->schemeEnabled() ) {
			return;
		}
		$values = $this->store->values();
		$follow = ( $values['scheme_follow'] ?? '' ) !== 'fixed';
		$toggle = ( $values['scheme_toggle'] ?? '' ) !== 'off';
		wp_print_inline_script_tag(
			sprintf(
				'(function(){try{var d=document.documentElement,s=%1$s?localStorage.getItem("wpe-scheme"):null;if(!s&&%2$s){s=window.matchMedia("(prefers-color-scheme: dark)").matches?"dark":"light";}d.setAttribute("data-wpe-scheme",s||"light");}catch(e){}})();',
				$toggle ? 'true' : 'false',
				$follow ? 'true' : 'false'
			)
		);
	}

	/** Floating light/dark switch and back-to-top button, when enabled. */
	public function floatingControls(): void {
		$values = $this->store->values();
		$html   = '';
		if ( $this->schemeEnabled() && ( $values['scheme_toggle'] ?? '' ) !== 'off' ) {
			$html .= '<button type="button" class="wpe-site-scheme" aria-label="' . esc_attr__( 'Switch light or dark mode', 'wpessential' ) . '" onclick="(function(d){var n=d.getAttribute(\'data-wpe-scheme\')===\'dark\'?\'light\':\'dark\';d.setAttribute(\'data-wpe-scheme\',n);try{localStorage.setItem(\'wpe-scheme\',n);}catch(e){}})(document.documentElement)">' . Icon::render( 'contrast', 20 ) . '</button>';
		}
		if ( ( $values['back_to_top'] ?? '' ) === 'show' ) {
			$html .= '<a class="wpe-site-top" href="#" aria-label="' . esc_attr__( 'Back to top', 'wpessential' ) . '">' . Icon::render( 'arrow-up', 20 ) . '</a>';
		}
		if ( $html === '' ) {
			return;
		}
		echo '<div class="wpe-site-floating">' . $html . '</div>'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- built from escaped parts above.
		echo '<style>.wpe-site-floating{position:fixed;right:20px;bottom:20px;z-index:9990;display:flex;flex-direction:column;gap:8px}.wpe-site-floating>*{display:grid;place-items:center;width:44px;height:44px;border:0;border-radius:50%;background:var(--wp--preset--color--ink,#0b1a3d);color:var(--wp--preset--color--on-ink,#fff);text-decoration:none;cursor:pointer;box-shadow:0 8px 24px rgb(0 0 0/.2)}</style>';
	}

	/** After saving: download local fonts, drop the theme.json cache and sync Elementor. */
	public function saved(): void {
		$values = $this->store->values();
		if ( SiteKitFonts::local( $values ) ) {
			foreach ( SiteKitFonts::used( $values ) as $family ) {
				SiteKitFonts::download( $family );
			}
		}
		if ( function_exists( 'wp_clean_theme_json_cache' ) ) {
			wp_clean_theme_json_cache();
		}
		SiteKitElementor::sync();
	}
}
