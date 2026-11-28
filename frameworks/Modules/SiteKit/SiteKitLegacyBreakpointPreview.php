<?php

declare(strict_types=1);

namespace WPEssential\Modules\SiteKit;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Translate legacy selections and bridge numeric theme device keys to the Kit.
 * Original theme responsive values remain untouched.
 */
final class SiteKitLegacyBreakpointPreview {

	public const MAP_MOD = 'wpessential_site_kit_breakpoint_mobile_key';

	/** @var array<int|string,array{0:string,1:int}> */
	private const MAP = [
		1920 => [ 'widescreen', 1920 ],
		1280 => [ 'laptop', 1439 ],
		1024 => [ 'tablet_extra', 1279 ],
		768  => [ 'tablet', 1023 ],
		560  => [ 'mobile', 767 ],
		480  => [ 'mobile', 559 ],
		360  => [ 'mobile_small', 479 ],
		320  => [ 'mobile_tiny', 359 ],
	];

	/**
	 * The 480 slot needs the canonical Phone slot when selected. Freeze this
	 * mapping on explicit handoff so subsequent standalone edits cannot remap data.
	 *
	 * @return array<int|string,array{0:string,1:int}>
	 */
	private static function mapping( string $mobileKey ): array {
		$map = self::MAP;
		$map[560][0] = $mobileKey === '480' ? 'mobile_extra' : 'mobile';
		$map[480][0] = $mobileKey === '480' ? 'mobile' : 'mobile_extra';
		return [ 'desktop' => [ 'desktop', 0 ] ] + $map;
	}

	/**
	 * @param array<string,mixed> $legacy
	 * @return array{values:array<string,array{on:bool,w:int}>,mobile_key:string}|array{}
	 */
	private static function selection( array $legacy ): array {
		$raw = array_key_exists( 'breakpoints', $legacy ) ? $legacy['breakpoints'] : '';
		if ( ! is_string( $raw ) ) {
			return [];
		}
		$keys = $raw === '' ? [ '768', '560' ] : ( $raw === '-' ? [] : explode( '|', $raw ) );
		foreach ( $keys as $key ) {
			if ( $key !== 'desktop' && ! isset( self::MAP[ $key ] ) ) {
				return [];
			}
		}
		$mobileKey = in_array( '480', $keys, true ) ? '480' : '560';
		$values    = [];
		foreach ( self::mapping( $mobileKey ) as $legacyKey => [ $device, $width ] ) {
			if ( $device !== 'desktop' ) {
				$values[ $device ] = [
					'on' => in_array( (string) $legacyKey, $keys, true ),
					'w'  => $width,
				];
			}
		}
		return [
			'values'     => $values,
			'mobile_key' => $mobileKey,
		];
	}

	/**
	 * Historical read-only assessment; the bridge can retain numeric dependent
	 * values without translating them. Keep the diagnostic for older companions.
	 *
	 * @param array<string,mixed> $legacy
	 * @return array{status:string,values:array<string,array{on:bool,w:int}>}
	 */
	public static function assess( array $legacy ): array {
		$selection = self::selection( $legacy );
		if ( $selection === [] ) {
			return [
				'status' => 'unknown_format',
				'values' => [],
			];
		}
		foreach ( $legacy as $key => $value ) {
			if ( $key === 'breakpoints' ) {
				continue;
			}
			if ( is_string( $value ) && str_starts_with( $value, '{' ) ) {
				$value = json_decode( $value, true );
				if ( ! is_array( $value ) ) {
					return [
						'status' => 'unknown_format',
						'values' => [],
					];
				}
			}
			if ( is_array( $value ) && self::hasDeviceValues( $value ) ) {
				return [
					'status' => 'responsive_values',
					'values' => [],
				];
			}
		}
		return [
			'status' => 'ready',
			'values' => $selection['values'],
		];
	}

	public static function bridgeSupported(): bool {
		return SiteKitLegacyPreview::supported() && apply_filters( 'wpessential_site_kit_breakpoint_handoff', 0 ) === 1;
	}

	/**
	 * Preserve existing canonical selections and responsive values when their
	 * effective breakpoints differ. Only an explicit legacy selection is eligible.
	 *
	 * @param array<string,mixed> $legacy
	 * @param array<string,mixed> $canonical
	 * @return array{values:array<string,mixed>,mobile_key:string}|array{}
	 */
	public static function candidate( array $legacy, array $canonical ): array {
		if ( ! array_key_exists( 'breakpoints', $legacy ) ) {
			return [];
		}
		$selection = self::selection( $legacy );
		if ( $selection === [] ) {
			return [];
		}
		$existing = $canonical['breakpoints'] ?? [];
		if ( ! is_array( $existing ) ) {
			return [];
		}
		if ( array_key_exists( 'breakpoints', $canonical ) || self::hasCanonicalDeviceValues( $canonical ) ) {
			if ( SiteKitBreakpoints::enabled( $existing ) !== SiteKitBreakpoints::enabled( $selection['values'] ) ) {
				return [];
			}
		}
		if ( array_key_exists( 'breakpoints', $canonical ) ) {
			$selection['values'] = $existing;
		}
		return $selection;
	}

	/** @param array<mixed> $tree */
	private static function hasCanonicalDeviceValues( array $tree ): bool {
		foreach ( $tree as $key => $value ) {
			if ( $key === 'breakpoints' ) {
				continue;
			}
			if ( $key !== 'desktop' && isset( SiteKitBreakpoints::ALL[ $key ] ) && $value !== '' && $value !== null && $value !== [] ) {
				return true;
			}
			if ( is_array( $value ) && self::hasCanonicalDeviceValues( $value ) ) {
				return true;
			}
		}
		return false;
	}

	/**
	 * Build the compatibility rows from the canonical model. Legacy device keys
	 * remain stable; width, enabled state and media ordering come from the Kit.
	 *
	 * @param array<string,mixed> $stored
	 * @return array<int|string,array{key:string,label:string,width:int,media:string,dir:string,on:bool,defaultWidth:int,defaultOn:bool}>
	 */
	public static function rows( array $stored, string $mobileKey ): array {
		if ( ! in_array( $mobileKey, [ '480', '560' ], true ) ) {
			return [];
		}
		$labels = SiteKitBreakpoints::labels();
		$rows   = [];
		foreach ( self::mapping( $mobileKey ) as $legacy => [ $key ] ) {
			[ , $defaultWidth, $defaultOn, $dir ] = SiteKitBreakpoints::ALL[ $key ];
			$row   = is_array( $stored[ $key ] ?? null ) ? $stored[ $key ] : [];
			$width = $key === 'desktop' ? 0 : ( isset( $row['w'] ) && is_numeric( $row['w'] ) ? max( 240, min( 3840, (int) $row['w'] ) ) : $defaultWidth );
			$rows[ $legacy ] = [
				'key'          => $key,
				'label'        => $labels[ $key ],
				'width'        => $width,
				'media'        => $dir === '' ? '' : sprintf( '@media (%s-width: %dpx)', $dir, $width ),
				'dir'          => $dir,
				'on'           => $key === 'desktop' || (bool) ( $row['on'] ?? $defaultOn ),
				'defaultWidth' => $defaultWidth,
				'defaultOn'    => $defaultOn,
			];
		}
		uasort(
			$rows,
			static function ( array $a, array $b ): int {
				$rank = static fn ( array $row ): int => $row['dir'] === '' ? 0 : ( $row['dir'] === 'min' ? 1 : 2 );
				return [ $rank( $a ), -$a['width'] ] <=> [ $rank( $b ), -$b['width'] ];
			}
		);
		return $rows;
	}

	/** @return array<int|string,array<string,mixed>> */
	public static function model(): array {
		if ( ! self::bridgeSupported() ) {
			return [];
		}
		$handed = get_theme_mod( SiteKitLegacyPreview::HANDOFF_MOD, [] );
		$mobile = get_theme_mod( self::MAP_MOD, '' );
		if ( ! is_array( $handed ) || ! in_array( 'breakpoints', $handed, true ) || ! is_string( $mobile ) ) {
			return [];
		}
		$values = ( new SiteKitStore() )->values();
		return self::rows( (array) ( $values['breakpoints'] ?? [] ), $mobile );
	}

	/** @param array<mixed> $tree */
	private static function hasDeviceValues( array $tree ): bool {
		foreach ( $tree as $key => $value ) {
			if ( ( isset( self::MAP[ $key ] ) || $key === 'tablet' || $key === 'mobile' ) && $value !== '' && $value !== null && $value !== [] ) {
				return true;
			}
			if ( is_array( $value ) && self::hasDeviceValues( $value ) ) {
				return true;
			}
		}

		return false;
	}
}
