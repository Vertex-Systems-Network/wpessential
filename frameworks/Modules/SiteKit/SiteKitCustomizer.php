<?php

declare(strict_types=1);

namespace WPEssential\Modules\SiteKit;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Customizer projection of the canonical Site Tools Kit option.
 * Shared controls render the same schema in the admin and Customizer.
 */
final class SiteKitCustomizer {

	public const PANEL = 'wpessential_site_kit';

	private bool $dirty = false;

	public function __construct( private readonly SiteKitStore $store ) {}

	public function register(): void {
		add_action( 'customize_register', [ $this, 'controls' ] );
		add_action( 'customize_controls_enqueue_scripts', [ $this, 'enqueue' ] );
		add_action( 'customize_controls_print_footer_scripts', [ $this, 'config' ], 1 );
		add_action( 'customize_save_' . SiteKitStore::OPTION, [ $this, 'markDirty' ] );
		add_action( 'customize_save_after', [ $this, 'saved' ] );
	}

	public static function settingId( string $key ): string {
		return SiteKitStore::OPTION . '[values][' . $key . ']';
	}

	/** @param array<string,mixed> $field */
	public static function supports( array $field ): bool {
		return in_array(
			$field['type'] ?? '',
			[ 'color', 'toggle', 'range', 'select', 'choose', 'text', 'font', 'fonts', 'dimensions', 'typography', 'background', 'gradient', 'shadow', 'breakpoints', 'units', 'colour_tokens', 'typography_tokens', 'shadow_tokens' ],
			true
		);
	}

	/** @param array<string,mixed> $field */
	public static function sanitize( array $field, mixed $value ): mixed {
		if ( ! self::supports( $field ) || ( ! is_scalar( $value ) && ! is_array( $value ) ) ) {
			return null;
		}
		if ( $value === '' ) {
			return '';
		}
		$composite = ! empty( $field['responsive'] ) || in_array(
			$field['type'],
			[ 'range', 'dimensions', 'typography', 'background', 'gradient', 'shadow', 'breakpoints', 'units', 'colour_tokens', 'typography_tokens', 'shadow_tokens' ],
			true
		);
		if ( is_string( $value ) && str_starts_with( ltrim( $value ), '{' ) ) {
			$value = json_decode( $value, true );
			if ( ! is_array( $value ) ) {
				return null;
			}
		}
		if ( is_array( $value ) && ! $composite ) {
			return null;
		}
		$clean = SiteKitSanitizer::clean( $field, $value );
		if ( $clean === '' || ( $clean === [] && ! is_array( $value ) ) ) {
			return null;
		}
		return $clean;
	}

	public function controls( \WP_Customize_Manager $manager ): void {
		$manager->add_panel(
			self::PANEL,
			[
				'title'      => __( 'Site Tools Kit', 'wpessential' ),
				'capability' => SiteKitAbilities::CAPABILITY,
			]
		);
		foreach ( SiteKitSchema::sections() as $group => $section ) {
			$sectionId = self::PANEL . '_' . $group;
			$fields    = array_filter( $section['fields'], [ self::class, 'supports' ] );
			if ( $fields === [] ) {
				continue;
			}
			$manager->add_section(
				$sectionId,
				[
					'title'       => $section['title'],
					'description' => $section['description'],
					'panel'       => self::PANEL,
					'capability'  => SiteKitAbilities::CAPABILITY,
				]
			);
			foreach ( $fields as $key => $field ) {
				$id = self::settingId( $key );
				$manager->add_setting(
					$id,
					[
						'type'              => 'option',
						'default'           => '',
						'capability'        => SiteKitAbilities::CAPABILITY,
						'transport'         => 'refresh',
						'sanitize_callback' => static fn ( mixed $value ): mixed => self::sanitize( $field, $value ),
					]
				);
				$manager->add_control(
					new SiteKitCustomizerControl(
						$manager,
						$id,
						[
							'section'  => $sectionId,
							'settings' => $id,
							'fieldKey' => $key,
							'field'    => $field,
						]
					)
				);
			}
		}
	}

	/** Only the shared UI bundle; never the dashboard app. */
	public function enqueue(): void {
		$root = dirname( __DIR__, 3 );
		$assets = new \WPEssential\Platform\Admin\AdminAssetManifest( $root, plugins_url( '', $root . '/wpessential.php' ) );
		$entry = $assets->entry( 'ui' );
		if ( $entry === null ) {
			return;
		}
		foreach ( $entry['styles'] as $index => $style ) {
			wp_enqueue_style( 'wpessential-customizer-ui-' . $index, $style, [ 'dashicons' ], $entry['version'] );
		}
		wp_enqueue_script( 'wpessential-customizer-ui', $entry['script'], array_merge( $entry['dependencies'], [ 'customize-controls' ] ), $entry['version'], true );
		wp_enqueue_media();
	}

	public function config(): void {
		echo SiteKitAdmin::controlsConfig(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- JSON encoded by the shared renderer.
	}

	public function markDirty(): void {
		$this->dirty = true;
	}

	public function saved(): void {
		if ( ! $this->dirty || ! current_user_can( SiteKitAbilities::CAPABILITY ) ) {
			return;
		}
		$this->dirty = false;
		$this->store->normalizeCustomizerSave();
	}
}
