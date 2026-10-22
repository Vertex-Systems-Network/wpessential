<?php

declare(strict_types=1);

namespace WPEssential\Platform\Admin\Elements;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Gutenberg blocks and Elementor widgets contributed by WPEssential modules.
 *
 * Nothing ships by default. Modules add elements through the `wpessential_elements` filter;
 * each element can be switched on or off for the whole site.
 */
final class ElementRegistry {

	public const TYPES  = [ 'gutenberg', 'elementor' ];
	public const OPTION = 'wpessential_elements_state';

	/** @var array<string,true>|null */
	private ?array $disabled = null;

	/**
	 * @return list<array{id:string,name:string,type:string,description:string,icon:string,edition:string}>
	 */
	public function all( ?string $type = null ): array {
		$raw      = function_exists( 'apply_filters' ) ? apply_filters( 'wpessential_elements', [] ) : [];
		$elements = [];
		foreach ( is_array( $raw ) ? $raw : [] as $element ) {
			$normalized = is_array( $element ) ? $this->normalize( $element ) : null;
			if ( $normalized !== null && ( $type === null || $normalized['type'] === $type ) ) {
				$elements[ $normalized['type'] . ':' . $normalized['id'] ] = $normalized;
			}
		}

		return array_values( $elements );
	}

	public function isEnabled( string $type, string $id ): bool {
		return ! isset( $this->disabled()[ $type . ':' . $id ] );
	}

	public function set( string $type, string $id, bool $enabled ): bool {
		$known = array_filter( $this->all( $type ), static fn ( array $e ): bool => $e['id'] === $id );
		if ( $known === [] ) {
			return false;
		}

		$disabled = $this->disabled();
		if ( $enabled ) {
			unset( $disabled[ $type . ':' . $id ] );
		} else {
			$disabled[ $type . ':' . $id ] = true;
		}
		$this->disabled = $disabled;
		if ( function_exists( 'update_option' ) ) {
			update_option( self::OPTION, array_keys( $disabled ), false );
		}

		return true;
	}

	/**
	 * @param array<mixed> $element
	 * @return array{id:string,name:string,type:string,description:string,icon:string,edition:string}|null
	 */
	private function normalize( array $element ): ?array {
		$id   = is_string( $element['id'] ?? null ) ? $element['id'] : '';
		$type = is_string( $element['type'] ?? null ) ? $element['type'] : '';
		$name = is_string( $element['name'] ?? null ) ? trim( $element['name'] ) : '';
		if ( preg_match( '/^[a-z0-9][a-z0-9-]*$/', $id ) !== 1 || ! in_array( $type, self::TYPES, true ) || $name === '' ) {
			return null;
		}

		return [
			'id'          => $id,
			'name'        => $name,
			'type'        => $type,
			'description' => is_string( $element['description'] ?? null ) ? $element['description'] : '',
			'icon'        => is_string( $element['icon'] ?? null ) ? $element['icon'] : 'layers',
			'edition'     => ( $element['edition'] ?? 'free' ) === 'pro' ? 'pro' : 'free',
		];
	}

	/** @return array<string,true> */
	private function disabled(): array {
		if ( $this->disabled === null ) {
			$stored         = function_exists( 'get_option' ) ? get_option( self::OPTION, [] ) : [];
			$this->disabled = [];
			foreach ( is_array( $stored ) ? $stored : [] as $key ) {
				if ( is_string( $key ) && preg_match( '/^(gutenberg|elementor):[a-z0-9][a-z0-9-]*$/', $key ) === 1 ) {
					$this->disabled[ $key ] = true;
				}
			}
		}

		return $this->disabled;
	}
}
