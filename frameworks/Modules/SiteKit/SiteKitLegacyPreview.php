<?php

declare(strict_types=1);

namespace WPEssential\Modules\SiteKit;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Read-only legacy theme migration assessment. No theme_mod or option writes.
 * Only a narrow set of semantically matching colour fields is import-ready.
 */
final class SiteKitLegacyPreview {

	/**
	 * @param array<string,mixed> $legacy
	 * @param array<string,mixed> $canonical
	 * @return array{ready:array<string,string>,conflicts:list<string>,deferred:list<string>}
	 */
	public static function assess( array $legacy, array $canonical ): array {
		$ready     = [];
		$conflicts = [];
		$deferred  = [];
		foreach ( $legacy as $key => $value ) {
			if ( $value === '' || $value === null || $value === false || $value === [] ) {
				continue;
			}
			$field = SiteKitSchema::field( $key );
			if ( $field === null || ( $field['type'] ?? '' ) !== 'color' || ( ! str_starts_with( $key, 'color_' ) && ! str_starts_with( $key, 'dark_' ) ) ) {
				$deferred[] = $key;
				continue;
			}
			$clean = SiteKitSanitizer::colour( $value );
			if ( $clean === '' ) {
				$deferred[] = $key;
				continue;
			}
			if ( array_key_exists( $key, $canonical ) ) {
				if ( $canonical[ $key ] !== $clean ) {
					$conflicts[] = $key;
				}
				continue;
			}
			$ready[ $key ] = $clean;
		}
		return [
			'ready'     => $ready,
			'conflicts' => $conflicts,
			'deferred'  => $deferred,
		];
	}
}
