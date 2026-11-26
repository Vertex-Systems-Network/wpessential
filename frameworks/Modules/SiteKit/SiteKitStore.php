<?php

declare(strict_types=1);

namespace WPEssential\Modules\SiteKit;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Site Kit values in one site option. Only values that differ from "theme default" are stored.
 */
final class SiteKitStore {

	public const OPTION = 'wpessential_site_kit';

	/** @var array<string,mixed>|null Used when WordPress options are unavailable (unit tests). */
	private ?array $memory = null;

	/** @return array<string,mixed> */
	public function values(): array {
		$stored = function_exists( 'get_option' ) ? get_option( self::OPTION, [] ) : ( $this->memory ?? [] );
		$values = is_array( $stored ) && is_array( $stored['values'] ?? null ) ? $stored['values'] : [];

		return array_filter( $values, static fn ( mixed $v ): bool => $v !== '' && $v !== null && $v !== false && $v !== [] );
	}

	/**
	 * Applies changes for known fields only. A value of "" (or null) resets that field.
	 *
	 * @param array<string,mixed> $changes
	 * @return array<string,array{from:mixed,to:mixed}> What actually changed.
	 */
	public function update( array $changes ): array {
		$fields  = SiteKitSchema::fields();
		$values  = $this->values();
		$changed = [];
		foreach ( $changes as $key => $raw ) {
			$key = (string) $key;
			if ( ! isset( $fields[ $key ] ) ) {
				continue;
			}
			$clean  = $raw === null || $raw === '' ? '' : SiteKitSanitizer::clean( $fields[ $key ], $raw );
			$before = $values[ $key ] ?? '';
			if ( $clean === '' || $clean === [] || $clean === false ) {
				unset( $values[ $key ] );
				$clean = '';
			} else {
				$values[ $key ] = $clean;
			}
			if ( $before !== $clean ) {
				$changed[ $key ] = [
					'from' => $before,
					'to'   => $clean,
				];
			}
		}
		if ( $changed !== [] ) {
			$this->write( $values );
		}

		return $changed;
	}

	/** @return list<string> Keys that were reset. */
	public function resetSection( string $section ): array {
		$sections = SiteKitSchema::sections();
		if ( ! isset( $sections[ $section ] ) ) {
			return [];
		}
		$values = $this->values();
		$reset  = array_values( array_intersect( array_keys( $values ), array_keys( $sections[ $section ]['fields'] ) ) );
		foreach ( $reset as $key ) {
			unset( $values[ $key ] );
		}
		if ( $reset !== [] ) {
			$this->write( $values );
		}

		return $reset;
	}

	/** @param array<string,mixed> $values */
	private function write( array $values ): void {
		$data = [
			'version' => 1,
			'values'  => $values,
		];
		if ( function_exists( 'update_option' ) ) {
			update_option( self::OPTION, $data );
		} else {
			$this->memory = $data;
		}
		if ( function_exists( 'do_action' ) ) {
			do_action( 'wpessential_site_kit_saved', $values );
		}
	}
}
