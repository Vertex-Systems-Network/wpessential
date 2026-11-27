<?php

declare(strict_types=1);

namespace WPEssential\Modules\SiteKit;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Customizer projection of the canonical Site Tools Kit option.
 * V1 first slice supports scalar settings; composite controls follow separately.
 */
final class SiteKitCustomizer {

	public const PANEL = 'wpessential_site_kit';

	private bool $dirty = false;

	public function __construct( private readonly SiteKitStore $store ) {}

	public function register(): void {
		add_action( 'customize_register', [ $this, 'controls' ] );
		add_action( 'customize_save_' . SiteKitStore::OPTION, [ $this, 'markDirty' ] );
		add_action( 'customize_save_after', [ $this, 'saved' ] );
	}

	public static function settingId( string $key ): string {
		return SiteKitStore::OPTION . '[values][' . $key . ']';
	}

	/** @param array<string,mixed> $field */
	public static function supports( array $field ): bool {
		return empty( $field['responsive'] )
			&& in_array( $field['type'] ?? '', [ 'color', 'toggle', 'range', 'select', 'choose', 'text', 'font', 'fonts' ], true );
	}

	/** @param array<string,mixed> $field */
	public static function sanitize( array $field, mixed $value ): mixed {
		if ( ! self::supports( $field ) || ! is_scalar( $value ) ) {
			return null;
		}
		$clean = SiteKitSanitizer::clean( $field, $value );
		if ( is_array( $clean ) ) {
			return null;
		}
		// Null is rejected by the Customizer; an explicit blank resets a field.
		if ( $value !== '' && $clean === '' ) {
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
		$values = $this->store->values();
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
				// Do not replace legacy composite values with scalar input.
				if ( is_array( $values[ $key ] ?? null ) ) {
					continue;
				}
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
				$control = [
					'label'       => (string) $field['label'],
					'description' => (string) ( $field['help'] ?? '' ),
					'section'     => $sectionId,
					'settings'    => $id,
					'type'        => 'text',
				];
				if ( in_array( $field['type'], [ 'select', 'choose' ], true ) ) {
					$control['type']    = 'radio';
					$control['choices'] = (array) $field['choices'];
				} elseif ( $field['type'] === 'toggle' ) {
					$control['type'] = 'checkbox';
				} elseif ( $field['type'] === 'range' ) {
					$control['type']        = 'number';
					$control['input_attrs'] = array_intersect_key( $field, array_flip( [ 'min', 'max', 'step' ] ) );
					$control['description'] = trim( $control['description'] . ' ' . (string) ( $field['unit'] ?? '' ) );
				}
				$manager->add_control( $id, $control );
			}
		}
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
