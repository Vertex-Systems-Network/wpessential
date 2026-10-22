<?php

declare(strict_types=1);

namespace WPEssential\Platform\Admin\Core;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Reads the atomic option contracts shipped in config/product/option-contracts.
 */
final class OptionContractReader {

	public const TIERS = [ 'Essential', 'Advanced', 'Expert', 'System' ];

	/** @var array<string,array{status:string,coverage:array<string,int>,groups:list<array{label:string,options:list<array{id:string,label:string,tier:string,control:string,exclusive:bool}>}>}|null> */
	private array $cache = [];

	/** @var array<string,mixed>|null */
	private ?array $bundle = null;

	public function __construct( private readonly string $pluginRoot ) {}

	/**
	 * @return array{status:string,coverage:array<string,int>,groups:list<array{label:string,options:list<array{id:string,label:string,tier:string,control:string,exclusive:bool}>}>}|null
	 */
	public function read( string $contract ): ?array {
		if ( array_key_exists( $contract, $this->cache ) ) {
			return $this->cache[ $contract ];
		}
		if ( preg_match( '/^[a-z0-9][a-z0-9-]*$/', $contract ) !== 1 ) {
			return null;
		}

		$data = $this->bundle()[ $contract ] ?? $this->source( $contract );

		$this->cache[ $contract ] = is_array( $data ) ? $this->normalize( $data ) : null;

		return $this->cache[ $contract ];
	}

	/**
	 * Packaged builds ship a compact bundle built by the admin toolchain; development checkouts
	 * fall back to the canonical source contracts.
	 *
	 * @return array<string,mixed>
	 */
	private function bundle(): array {
		if ( $this->bundle === null ) {
			$data         = $this->decode( rtrim( $this->pluginRoot, '/\\' ) . '/assets/admin/option-contracts.json' );
			$this->bundle = is_array( $data ) ? $data : [];
		}

		return $this->bundle;
	}

	private function source( string $contract ): mixed {
		return $this->decode( rtrim( $this->pluginRoot, '/\\' ) . '/config/product/option-contracts/' . $contract . '.json' );
	}

	private function decode( string $path ): mixed {
		$raw = is_readable( $path ) ? file_get_contents( $path ) : false; // phpcs:ignore WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents -- trusted plugin file.

		return is_string( $raw ) ? json_decode( $raw, true ) : null;
	}

	/**
	 * @param array<mixed> $data
	 * @return array{status:string,coverage:array<string,int>,groups:list<array{label:string,options:list<array{id:string,label:string,tier:string,control:string,exclusive:bool}>}>}
	 */
	private function normalize( array $data ): array {
		$coverage = [];
		foreach ( (array) ( $data['coverage_summary'] ?? [] ) as $key => $value ) {
			if ( is_string( $key ) && is_int( $value ) ) {
				$coverage[ $key ] = $value;
			}
		}

		// Contracts with a single feature group are regrouped by their UI group.
		$groups   = [];
		$features = array_values( array_filter( (array) ( $data['feature_groups'] ?? [] ), 'is_array' ) );
		foreach ( $features as $feature ) {
			foreach ( (array) ( $feature['options'] ?? [] ) as $option ) {
				if ( ! is_array( $option ) ) {
					continue;
				}
				$ui    = is_array( $option['ui'] ?? null ) ? $option['ui'] : [];
				$label = count( $features ) === 1 && is_string( $ui['group'] ?? null ) && $ui['group'] !== ''
					? $ui['group']
					: (string) ( $feature['label'] ?? '' );
				$tier  = (string) ( $ui['tier'] ?? 'Advanced' );

				$groups[ $label ][] = [
					'id'        => (string) ( $option['id'] ?? '' ),
					'label'     => (string) ( $option['label'] ?? '' ),
					'tier'      => in_array( $tier, self::TIERS, true ) ? $tier : 'Advanced',
					'control'   => ucfirst( str_replace( '_', ' ', (string) ( $ui['control'] ?? 'policy' ) ) ),
					'exclusive' => ( $option['parity_status'] ?? '' ) === 'EXCEEDS',
				];
			}
		}

		$list = [];
		foreach ( $groups as $label => $options ) {
			$list[] = [
				'label'   => ucfirst( (string) $label ),
				'options' => $options,
			];
		}

		return [
			'status'   => (string) ( $data['status'] ?? '' ),
			'coverage' => $coverage,
			'groups'   => $list,
		];
	}
}
