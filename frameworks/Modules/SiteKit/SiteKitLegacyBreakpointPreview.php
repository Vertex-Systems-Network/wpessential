<?php

declare(strict_types=1);

namespace WPEssential\Modules\SiteKit;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Read-only translation of the companion theme's legacy breakpoint selection.
 * Never moves stored responsive values or delegates legacy ownership.
 */
final class SiteKitLegacyBreakpointPreview {

	/** @var array<int|string,array{0:string,1:int}> */
	private const MAP = [
		1920 => [ 'widescreen', 1920 ],
		1280 => [ 'laptop', 1439 ],
		1024 => [ 'tablet_extra', 1279 ],
		768  => [ 'tablet', 1023 ],
		560  => [ 'mobile', 767 ],
		480  => [ 'mobile', 559 ],
		360  => [ 'mobile_small', 479 ],
		320  => [ 'mobile_small', 359 ],
	];

	/**
	 * @param array<string,mixed> $legacy
	 * @return array{status:string,values:array<string,array{on:bool,w:int}>}
	 */
	public static function assess( array $legacy ): array {
		$raw = $legacy['breakpoints'] ?? '';
		if ( ! is_string( $raw ) ) {
			return [ 'status' => 'unknown_format', 'values' => [] ];
		}
		$keys = $raw === '' ? [ '768', '560' ] : ( $raw === '-' ? [] : explode( '|', $raw ) );
		foreach ( $keys as $key ) {
			if ( $key !== 'desktop' && ! isset( self::MAP[ $key ] ) ) {
				return [ 'status' => 'unknown_format', 'values' => [] ];
			}
		}
		if ( in_array( '360', $keys, true ) && in_array( '320', $keys, true ) ) {
			return [ 'status' => 'slot_conflict', 'values' => [] ];
		}
		// The old theme has numeric device keys and tablet/mobile aliases nested in
		// multiple controls. Until all those values move together, keep its ownership.
		foreach ( $legacy as $key => $value ) {
			if ( $key === 'breakpoints' ) {
				continue;
			}
			if ( is_string( $value ) && str_starts_with( $value, '{' ) ) {
				$value = json_decode( $value, true );
				if ( ! is_array( $value ) ) {
					return [ 'status' => 'unknown_format', 'values' => [] ];
				}
			}
			if ( is_array( $value ) && self::hasDeviceValues( $value ) ) {
				return [ 'status' => 'responsive_values', 'values' => [] ];
			}
		}
		$values = [];
		foreach ( SiteKitBreakpoints::ALL as $key => [ , $width ] ) {
			if ( $key !== 'desktop' ) {
				$values[ $key ] = [ 'on' => false, 'w' => $width ];
			}
		}
		foreach ( $keys as $key ) {
			if ( $key === 'desktop' ) {
				continue;
			}
			[ $device, $width ] = self::MAP[ $key ];
			if ( $key === '560' && in_array( '480', $keys, true ) ) {
				$device = 'mobile_extra';
			}
			$values[ $device ] = [ 'on' => true, 'w' => $width ];
		}

		return [ 'status' => 'ready', 'values' => $values ];
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
