<?php

declare(strict_types=1);

namespace WPEssential\Modules\SiteKit;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use WPEssential\Platform\Admin\Core\CoreAjaxRoutes;
use WPEssential\Platform\Admin\PlatformAdminController;
use WPEssential\Platform\Admin\Ui\AdminUrl;
use WPEssential\Platform\Admin\Ui\Button;
use WPEssential\Platform\Admin\Ui\Icon;
use WPEssential\Platform\Admin\Ui\Notice;
use WPEssential\Platform\Admin\Ui\PageHeader;
use WPEssential\Platform\Admin\Ui\Screen;

/**
 * Site Kit admin screen: one tab per group, saved in place over AJAX (admin-post fallback).
 */
final class SiteKitAdmin {

	public const PAGE_SLUG       = 'wpessential-site-kit';
	public const SAVE_ACTION     = 'wpessential_site_kit_save';
	public const SNIPPETS_ACTION = 'wpessential_site_kit_snippets';
	public const AJAX_SAVE       = 'site-kit.save';
	public const AJAX_RESET      = 'site-kit.reset';
	public const AJAX_SNIPPETS   = 'site-kit.snippets';

	public function __construct( private readonly SiteKitStore $store, private readonly SiteKitSnippets $snippets ) {}

	public function register(): void {
		add_action( 'admin_menu', [ $this, 'menu' ], 20 );
		add_action( 'admin_enqueue_scripts', [ $this, 'enqueue' ] );
		add_action( 'admin_post_' . self::SAVE_ACTION, [ $this, 'handleSave' ] );
		add_action( 'admin_post_' . self::SNIPPETS_ACTION, [ $this, 'handleSnippets' ] );
		CoreAjaxRoutes::expose( self::AJAX_SAVE );
		CoreAjaxRoutes::expose( self::AJAX_RESET );
		CoreAjaxRoutes::expose( self::AJAX_SNIPPETS );
	}

	public function menu(): void {
		add_submenu_page( PlatformAdminController::PAGE_SLUG, __( 'Site Kit', 'wpessential' ), __( 'Site Kit', 'wpessential' ), SiteKitAbilities::CAPABILITY, self::PAGE_SLUG, [ $this, 'render' ] );
	}

	public function enqueue(): void {
		// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- asset routing only.
		if ( isset( $_GET['page'] ) && $_GET['page'] === self::PAGE_SLUG && function_exists( 'wp_enqueue_media' ) ) {
			wp_enqueue_media();
		}
	}

	public function handleSave(): void {
		if ( ! current_user_can( SiteKitAbilities::CAPABILITY ) ) {
			wp_die( esc_html__( 'You are not allowed to change Site Kit.', 'wpessential' ), 'WPEssential', [ 'response' => 403 ] );
		}
		check_admin_referer( self::SAVE_ACTION );
		$group  = isset( $_POST['group'] ) ? sanitize_key( wp_unslash( (string) $_POST['group'] ) ) : '';
		$values = isset( $_POST['values'] ) && is_array( $_POST['values'] ) ? wp_unslash( $_POST['values'] ) : []; // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized -- sanitized per field type by SiteKitSanitizer.
		if ( isset( $_POST['reset'] ) ) {
			$this->store->resetSection( $group );
		} else {
			$this->store->update( self::groupValues( $group, (array) $values ) );
		}
		wp_safe_redirect(
			AdminUrl::page(
				self::PAGE_SLUG,
				[
					'group'      => $group,
					'wpe-notice' => isset( $_POST['reset'] ) ? 'reset' : 'saved',
				]
			)
		);
		exit;
	}

	public function handleSnippets(): void {
		if ( ! current_user_can( SiteKitAbilities::CAPABILITY ) ) {
			wp_die( esc_html__( 'You are not allowed to change Site Kit.', 'wpessential' ), 'WPEssential', [ 'response' => 403 ] );
		}
		check_admin_referer( self::SNIPPETS_ACTION );
		$rows = isset( $_POST['snippets'] ) && is_array( $_POST['snippets'] ) ? wp_unslash( $_POST['snippets'] ) : []; // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized -- sanitized by SiteKitSnippets::clean().
		$this->snippets->save( array_values( array_filter( (array) $rows, 'is_array' ) ), current_user_can( 'unfiltered_html' ) );
		wp_safe_redirect(
			AdminUrl::page(
				self::PAGE_SLUG,
				[
					'group'      => 'snippets',
					'wpe-notice' => 'saved',
				]
			)
		);
		exit;
	}

	/**
	 * Every field of the group, so cleared inputs (unchecked toggles, emptied colours) reset.
	 *
	 * @param array<string,mixed> $posted
	 * @return array<string,mixed>
	 */
	public static function groupValues( string $group, array $posted ): array {
		$section = SiteKitSchema::sections()[ $group ] ?? null;
		if ( $section === null ) {
			return [];
		}
		$out = [];
		foreach ( $section['fields'] as $key => $field ) {
			if ( $field['type'] !== 'heading' ) {
				$out[ $key ] = $posted[ $key ] ?? '';
			}
		}

		return $out;
	}

	public function render(): void {
		if ( ! current_user_can( SiteKitAbilities::CAPABILITY ) ) {
			wp_die( esc_html__( 'You are not allowed to change Site Kit.', 'wpessential' ), 'WPEssential', [ 'response' => 403 ] );
		}
		// phpcs:disable WordPress.Security.NonceVerification.Recommended -- read-only tab and notice routing.
		$current = isset( $_GET['group'] ) ? sanitize_key( wp_unslash( (string) $_GET['group'] ) ) : 'colors';
		$notice  = isset( $_GET['wpe-notice'] ) ? sanitize_key( wp_unslash( (string) $_GET['wpe-notice'] ) ) : '';
		// phpcs:enable
		$sections = SiteKitSchema::sections();
		$current  = isset( $sections[ $current ] ) || $current === 'snippets' ? $current : 'colors';
		$values   = $this->store->values();

		$html  = PageHeader::render( __( 'Site Kit', 'wpessential' ), __( 'Site settings', 'wpessential' ), __( 'Colours, fonts, buttons, forms, layout and motion for the whole site, in Gutenberg and Elementor. Empty fields keep your theme\'s value.', 'wpessential' ), Button::link( __( 'View site', 'wpessential' ), home_url( '/' ), 'ghost', true, true ) );
		$html .= '<div class="wpe-ui-sitekit" data-wpe-tabs>';
		$html .= '<nav class="wpe-ui-sitekit__nav" role="tablist" aria-label="' . esc_attr__( 'Site Kit groups', 'wpessential' ) . '">';
		foreach ( $sections as $id => $section ) {
			$html .= self::tab( $id, $section['title'], $section['icon'], $current === $id, self::count( $values, $section['fields'] ) );
		}
		$html .= self::tab( 'snippets', __( 'Custom code', 'wpessential' ), 'code', $current === 'snippets', count( $this->snippets->all() ) );
		$html .= '</nav><div class="wpe-ui-sitekit__panels">';
		foreach ( $sections as $id => $section ) {
			$html .= $this->panel( $id, $section, $values, $current === $id );
		}
		$html .= $this->snippetsPanel( $current === 'snippets' );
		$html .= '</div></div>';
		$html .= self::controlsConfig() . '<noscript>' . Notice::render( 'warning', __( 'Site Kit controls need JavaScript.', 'wpessential' ) ) . '</noscript>';

		$messages = [
			'saved' => [ 'success', __( 'Site Kit saved.', 'wpessential' ) ],
			'reset' => [ 'info', __( 'Group reset to the theme defaults.', 'wpessential' ) ],
		];
		Screen::render( self::PAGE_SLUG, $html, isset( $messages[ $notice ] ) ? Notice::render( ...$messages[ $notice ] ) : '', 'site-kit' );
	}

	private static function tab( string $id, string $title, string $icon, bool $active, int $count ): string {
		return '<button type="button" role="tab" id="wpe-sk-tab-' . esc_attr( $id ) . '" class="wpe-ui-sitekit__tab' . ( $active ? ' is-active' : '' ) . '" aria-selected="' . ( $active ? 'true' : 'false' ) . '" aria-controls="wpe-sk-' . esc_attr( $id ) . '" data-wpe-tab="' . esc_attr( $id ) . '"' . ( $active ? '' : ' tabindex="-1"' ) . '>'
			. Icon::render( $icon, 16 ) . '<span>' . esc_html( $title ) . '</span>' . ( $count > 0 ? '<span class="wpe-ui-sitekit__count">' . esc_html( (string) $count ) . '</span>' : '' ) . '</button>';
	}

	/**
	 * @param array<string,mixed>                $values
	 * @param array<string,array<string,mixed>>  $fields
	 */
	private static function count( array $values, array $fields ): int {
		return count( array_intersect_key( $values, $fields ) );
	}

	/**
	 * @param array{title:string,description:string,icon:string,fields:array<string,array<string,mixed>>} $section
	 * @param array<string,mixed> $values
	 */
	private function panel( string $id, array $section, array $values, bool $active ): string {
		$html  = '<section id="wpe-sk-' . esc_attr( $id ) . '" class="wpe-ui-panel wpe-ui-sitekit__panel" role="tabpanel" aria-labelledby="wpe-sk-tab-' . esc_attr( $id ) . '" data-wpe-panel' . ( $active ? '' : ' hidden' ) . '>';
		$html .= '<form method="post" action="' . esc_url( admin_url( 'admin-post.php' ) ) . '" data-wpe-ajax-form="' . esc_attr( self::AJAX_SAVE ) . '" data-wpe-reset-route="' . esc_attr( self::AJAX_RESET ) . '">';
		$html .= wp_nonce_field( self::SAVE_ACTION, '_wpnonce', true, false );
		$html .= '<input type="hidden" name="action" value="' . esc_attr( self::SAVE_ACTION ) . '"><input type="hidden" name="group" value="' . esc_attr( $id ) . '">';
		$html .= '<div class="wpe-ui-panel__head"><span class="wpe-ui-icon">' . Icon::render( $section['icon'], 20 ) . '</span><div><h2>' . esc_html( $section['title'] ) . '</h2>';
		$html .= $section['description'] !== '' ? '<p class="wpe-ui-muted">' . esc_html( $section['description'] ) . '</p>' : '';
		$html .= '</div></div><div class="wpe-ui-sitekit__fields">';
		foreach ( $section['fields'] as $key => $field ) {
			$html .= self::field( $key, $field, $values[ $key ] ?? '' );
		}
		$html .= '</div><div class="wpe-ui-builder__actions">';
		$html .= '<button type="submit" name="reset" value="1" class="wpe-ui-button wpe-ui-button--ghost" data-wpe-reset>' . esc_html__( 'Reset group', 'wpessential' ) . '</button>';
		$html .= '<button type="submit" class="wpe-ui-button wpe-ui-button--primary">' . esc_html__( 'Save changes', 'wpessential' ) . '</button>';

		return $html . '</div></form></section>';
	}

	/** @param array<string,mixed> $field */
	private static function field( string $key, array $field, mixed $value ): string {
		$type = (string) $field['type'];
		if ( $type === 'heading' ) {
			return '<h3 class="wpe-ui-builder__section wpe-ui-sitekit__wide">' . esc_html( (string) $field['label'] ) . '</h3>';
		}
		// Short selects read better as a button group (Elementor style).
		if ( $type === 'select' && count( (array) $field['choices'] ) <= 3 && max( array_map( 'strlen', array_map( 'strval', (array) $field['choices'] ) ) ) <= 22 ) {
			$type = 'buttons';
		}
		$choices = $field['choices'] ?? null;
		if ( is_array( $choices ) && isset( $choices['uppercase'] ) ) {
			// Text-transform choices show line icons instead of sample glyphs.
			$icons   = [
				''           => 'minus',
				'none'       => 'type',
				'uppercase'  => 'case-upper',
				'lowercase'  => 'case-lower',
				'capitalize' => 'case-sensitive',
			];
			$choices = array_combine( array_map( 'strval', array_keys( $choices ) ), array_map( static fn ( mixed $label, int|string $k ): array|string => isset( $icons[ (string) $k ] ) ? [ (string) $label, $icons[ (string) $k ] ] : (string) $label, $choices, array_keys( $choices ) ) );
		}
		$config     = array_filter(
			[
				'type'       => $type,
				'min'        => $field['min'] ?? null,
				'max'        => $field['max'] ?? null,
				'step'       => $field['step'] ?? null,
				'unit'       => $field['unit'] ?? null,
				'units'      => $field['units'] ?? null,
				'choices'    => $choices,
				'responsive' => ! empty( $field['responsive'] ) ? true : null,
				'tab'        => $field['tab'] ?? null,
			],
			static fn ( mixed $x ): bool => $x !== null
		);
			$wide   = in_array( $type, [ 'typography', 'background', 'gradient', 'shadow', 'dimensions', 'fonts' ], true );
			$string = is_array( $value ) ? (string) wp_json_encode( $value ) : ( $value === true ? '1' : ( is_scalar( $value ) ? (string) $value : '' ) );

			return '<div class="wpec wpec--' . esc_attr( $type ) . ( $wide ? ' wpe-ui-sitekit__wide' : '' ) . '" data-wpec="' . esc_attr( (string) wp_json_encode( $config ) ) . '"' . ( isset( $field['tab'] ) ? ' data-wpec-tab="' . esc_attr( (string) $field['tab'] ) . '"' : '' ) . '>'
			. '<div class="wpec__head"><span class="wpec__label">' . esc_html( (string) $field['label'] ) . '</span>'
			/* translators: %s: setting name. */
			. '<button type="button" class="wpec__reset" hidden aria-label="' . esc_attr( sprintf( __( 'Reset %s to the theme default', 'wpessential' ), (string) $field['label'] ) ) . '" title="' . esc_attr__( 'Back to the theme default', 'wpessential' ) . '">' . Icon::render( 'refresh', 14 ) . '</button></div>'
			. ( isset( $field['help'] ) ? '<p class="wpec__help">' . esc_html( (string) $field['help'] ) . '</p>' : '' )
			. '<div class="wpec__ui"></div>'
			. '<input type="hidden" class="wpec__value" name="' . esc_attr( 'values[' . $key . ']' ) . '" value="' . esc_attr( $string ) . '"></div>';
	}

	/** @param array<string,string> $choices */
	private static function select( string $id, string $name, array $choices, string $value ): string {
		$html = '<select id="' . esc_attr( $id ) . '" name="' . esc_attr( $name ) . '">';
		foreach ( $choices as $key => $label ) {
			$html .= '<option value="' . esc_attr( (string) $key ) . '"' . ( (string) $key === $value ? ' selected' : '' ) . '>' . esc_html( (string) $label ) . '</option>';
		}

		return $html . '</select>';
	}

	/** Palette, theme fonts and the Google Fonts list for the controls. */
	private static function controlsConfig(): string {
		$palette  = [];
		$settings = function_exists( 'wp_get_global_settings' ) ? wp_get_global_settings( [ 'color', 'palette' ] ) : [];
		foreach ( array_merge( (array) ( $settings['theme'] ?? [] ), (array) ( $settings['custom'] ?? [] ) ) as $colour ) {
			if ( isset( $colour['slug'], $colour['color'] ) && preg_match( '/^#[0-9a-f]{3,8}$/i', (string) $colour['color'] ) === 1 ) {
				$palette[ (string) $colour['slug'] ] = [ (string) ( $colour['name'] ?? $colour['slug'] ), (string) $colour['color'] ];
			}
		}
		$fonts    = [];
		$families = function_exists( 'wp_get_global_settings' ) ? wp_get_global_settings( [ 'typography', 'fontFamilies' ] ) : [];
		foreach ( (array) ( $families['theme'] ?? [] ) as $family ) {
			if ( isset( $family['slug'], $family['fontFamily'] ) ) {
				$fonts[] = [
					'value' => 'theme:' . $family['slug'],
					'name'  => (string) ( $family['name'] ?? $family['slug'] ),
					'stack' => (string) $family['fontFamily'],
				];
			}
		}
		$config = [
			'palette'    => (object) $palette,
			'themeFonts' => $fonts,
			'googleList' => plugins_url( 'data/google-fonts.json', __FILE__ ),
		];

		return '<script type="application/json" id="wpe-controls-config">' . (string) wp_json_encode( $config, JSON_HEX_TAG | JSON_HEX_AMP | JSON_UNESCAPED_SLASHES ) . '</script>';
	}

	private function snippetsPanel( bool $active ): string {
		$canJs  = current_user_can( 'unfiltered_html' );
		$rows   = $this->snippets->all();
		$rows[] = [
			'id'       => '',
			'title'    => '',
			'type'     => 'css',
			'scope'    => 'all',
			'location' => 'footer',
			'enabled'  => true,
			'code'     => '',
		];
		$html   = '<section id="wpe-sk-snippets" class="wpe-ui-panel wpe-ui-sitekit__panel" role="tabpanel" aria-labelledby="wpe-sk-tab-snippets" data-wpe-panel' . ( $active ? '' : ' hidden' ) . '>';
		$html  .= '<form method="post" action="' . esc_url( admin_url( 'admin-post.php' ) ) . '" data-wpe-ajax-form="' . esc_attr( self::AJAX_SNIPPETS ) . '">';
		$html  .= wp_nonce_field( self::SNIPPETS_ACTION, '_wpnonce', true, false ) . '<input type="hidden" name="action" value="' . esc_attr( self::SNIPPETS_ACTION ) . '">';
		$html  .= '<div class="wpe-ui-panel__head"><span class="wpe-ui-icon">' . Icon::render( 'code', 20 ) . '</span><div><h2>' . esc_html__( 'Custom code', 'wpessential' ) . '</h2>';
		$html  .= '<p class="wpe-ui-muted">' . esc_html__( 'CSS and JavaScript that survive theme changes. Open any page with ?wpe-safe-mode=1 to load it without snippets.', 'wpessential' ) . ( $canJs ? '' : ' ' . esc_html__( 'Your role can add CSS only.', 'wpessential' ) ) . '</p></div></div>';
		foreach ( $rows as $index => $row ) {
			$prefix = 'snippets[' . $index . ']';
			$isNew  = $row['id'] === '';
			$html  .= '<fieldset class="wpe-ui-sitekit__snippet"><legend>' . esc_html( $isNew ? __( 'New snippet', 'wpessential' ) : (string) $row['title'] ) . '</legend>';
			$html  .= '<input type="hidden" name="' . esc_attr( $prefix . '[id]' ) . '" value="' . esc_attr( (string) $row['id'] ) . '">';
			$html  .= '<div class="wpe-ui-sitekit__row">';
			$html  .= '<span class="wpe-ui-sitekit__mini wpe-ui-sitekit__grow"><span class="wpe-ui-sitekit__unit">' . esc_html__( 'Title', 'wpessential' ) . '</span><input type="text" name="' . esc_attr( $prefix . '[title]' ) . '" value="' . esc_attr( (string) $row['title'] ) . '" aria-label="' . esc_attr__( 'Snippet title', 'wpessential' ) . '"></span>';
			$html  .= '<span class="wpe-ui-sitekit__mini"><span class="wpe-ui-sitekit__unit">' . esc_html__( 'Type', 'wpessential' ) . '</span>' . self::select(
				'wpe-sk-snippet-' . $index . '-type',
				$prefix . '[type]',
				$canJs ? [
					'css' => 'CSS',
					'js'  => 'JavaScript',
				] : [ 'css' => 'CSS' ],
				(string) $row['type']
			) . '</span>';
			$html  .= '<span class="wpe-ui-sitekit__mini"><span class="wpe-ui-sitekit__unit">' . esc_html__( 'Devices', 'wpessential' ) . '</span>' . self::select(
				'wpe-sk-snippet-' . $index . '-scope',
				$prefix . '[scope]',
				[
					'all'     => __( 'All', 'wpessential' ),
					'desktop' => __( 'Desktop', 'wpessential' ),
					'tablet'  => __( 'Tablet', 'wpessential' ),
					'mobile'  => __( 'Phone', 'wpessential' ),
				],
				(string) $row['scope']
			) . '</span>';
			$html  .= '<span class="wpe-ui-sitekit__mini"><span class="wpe-ui-sitekit__unit">' . esc_html__( 'JS location', 'wpessential' ) . '</span>' . self::select(
				'wpe-sk-snippet-' . $index . '-location',
				$prefix . '[location]',
				[
					'footer' => __( 'Footer', 'wpessential' ),
					'head'   => __( 'Head', 'wpessential' ),
				],
				(string) $row['location']
			) . '</span>';
			$html  .= '<input type="hidden" name="' . esc_attr( $prefix . '[enabled]' ) . '" value=""><label class="wpe-ui-check"><input type="checkbox" name="' . esc_attr( $prefix . '[enabled]' ) . '" value="1"' . ( $row['enabled'] ? ' checked' : '' ) . '> ' . esc_html__( 'Enabled', 'wpessential' ) . '</label>';
			$html  .= '</div><textarea class="wpe-ui-sitekit__code" name="' . esc_attr( $prefix . '[code]' ) . '" rows="6" spellcheck="false" aria-label="' . esc_attr__( 'Code', 'wpessential' ) . '">' . esc_textarea( (string) $row['code'] ) . '</textarea></fieldset>';
		}
		$html .= '<div class="wpe-ui-builder__actions"><button type="submit" class="wpe-ui-button wpe-ui-button--primary">' . esc_html__( 'Save snippets', 'wpessential' ) . '</button></div></form></section>';

		return $html;
	}
}
