<?php

declare(strict_types=1);

namespace WPEssential\Platform\Tools;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use Closure;
use Throwable;

/**
 * Portable export and import of WPEssential definitions (post types, taxonomies) and settings.
 *
 * Definitions go through the same abilities the builders use, so imports are validated, audited and
 * compiled exactly like a save in the UI. Items are matched by slug: an existing slug is updated,
 * a new one is created. Settings are a fixed allow-list of WPEssential options.
 */
final class ImportExport {

	public const FORMAT  = 'wpessential-export';
	public const VERSION = 1;

	/** Definition kinds in import order (post types before the taxonomies that reference them). */
	public const KINDS = [
		'post_types' => 'wpessential/cpt',
		'taxonomies' => 'wpessential/taxonomy',
	];

	/** Option name => export key. Secrets, licences and AI sessions are never exported. */
	public const SETTINGS = [
		'wpessential_module_toggles' => 'module_toggles',
		'wpessential_elements_state' => 'elements',
	];

	/**
	 * @param Closure(string, array<string,mixed>): mixed $run     Runs an ability (name, input) as the current user.
	 * @param Closure(string): mixed                      $read    Reads an option.
	 * @param Closure(string, mixed): void                $write   Writes an option.
	 */
	public function __construct(
		private readonly Closure $run,
		private readonly Closure $read,
		private readonly Closure $write,
	) {}

	/**
	 * @param list<string> $parts Any of: post_types, taxonomies, settings.
	 * @return array<string,mixed>
	 */
	public function export( array $parts, string $site = '' ): array {
		$data = [
			'format'  => self::FORMAT,
			'version' => self::VERSION,
			'site'    => $site,
			'created' => gmdate( 'c' ),
		];
		foreach ( self::KINDS as $key => $ability ) {
			if ( ! in_array( $key, $parts, true ) ) {
				continue;
			}
			$data[ $key ] = [];
			foreach ( $this->definitions( $ability ) as $definition ) {
				$data[ $key ][] = [
					'slug'    => (string) $definition['slug'],
					'status'  => (string) $definition['status'],
					'payload' => $definition['payload'],
				];
			}
		}
		if ( in_array( 'settings', $parts, true ) ) {
			$data['settings'] = [];
			foreach ( self::SETTINGS as $option => $key ) {
				$data['settings'][ $key ] = ( $this->read )( $option );
			}
		}

		return $data;
	}

	/**
	 * Parses and checks an export file.
	 *
	 * @return array<string,mixed>|string The data, or an error message.
	 */
	public static function parse( string $json ): array|string {
		$data = json_decode( $json, true );
		if ( ! is_array( $data ) || ( $data['format'] ?? '' ) !== self::FORMAT ) {
			return 'This is not a WPEssential export file.';
		}
		if ( (int) ( $data['version'] ?? 0 ) > self::VERSION ) {
			return 'This file was made by a newer WPEssential. Update the plugin first.';
		}

		return $data;
	}

	/**
	 * What an import would do, without changing anything.
	 *
	 * @param array<string,mixed> $data
	 * @return list<array{kind:string,slug:string,action:string}> action: create, update, unchanged, invalid.
	 */
	public function preview( array $data ): array {
		$plan = [];
		foreach ( self::KINDS as $key => $ability ) {
			if ( ! is_array( $data[ $key ] ?? null ) ) {
				continue;
			}
			$existing = $this->bySlug( $ability );
			foreach ( $data[ $key ] as $item ) {
				$slug = is_array( $item ) && is_string( $item['slug'] ?? null ) ? $item['slug'] : '';
				if ( $slug === '' || ! is_array( $item['payload'] ?? null ) ) {
					$plan[] = [
						'kind'   => $key,
						'slug'   => $slug,
						'action' => 'invalid',
					];
					continue;
				}
				$current = $existing[ $slug ] ?? null;
				$action  = $current === null ? 'create' : ( $current['payload'] == $item['payload'] && $current['status'] === ( $item['status'] ?? $current['status'] ) ? 'unchanged' : 'update' ); // phpcs:ignore Universal.Operators.StrictComparisons.LooseEqual -- Order-insensitive array compare.
				$plan[]  = [
					'kind'   => $key,
					'slug'   => $slug,
					'action' => $action,
				];
			}
		}
		if ( is_array( $data['settings'] ?? null ) ) {
			foreach ( self::SETTINGS as $option => $key ) {
				if ( array_key_exists( $key, $data['settings'] ) ) {
					$plan[] = [
						'kind'   => 'settings',
						'slug'   => $key,
						'action' => ( $this->read )( $option ) == $data['settings'][ $key ] ? 'unchanged' : 'update',
					]; // phpcs:ignore Universal.Operators.StrictComparisons.LooseEqual -- Order-insensitive array compare.
				}
			}
		}

		return $plan;
	}

	/**
	 * Applies an import. Each item succeeds or fails on its own.
	 *
	 * @param array<string,mixed> $data
	 * @return list<array{kind:string,slug:string,action:string,error:string}>
	 */
	public function import( array $data ): array {
		$results = [];
		foreach ( self::KINDS as $key => $ability ) {
			if ( ! is_array( $data[ $key ] ?? null ) ) {
				continue;
			}
			$existing = $this->bySlug( $ability );
			foreach ( $data[ $key ] as $item ) {
				$slug = is_array( $item ) && is_string( $item['slug'] ?? null ) ? $item['slug'] : '';
				if ( $slug === '' || ! is_array( $item['payload'] ?? null ) ) {
					$results[] = [
						'kind'   => $key,
						'slug'   => $slug,
						'action' => 'skipped',
						'error'  => 'Missing slug or payload.',
					];
					continue;
				}
				$input   = [ 'payload' => $item['payload'] ];
				$status  = is_string( $item['status'] ?? null ) && in_array( $item['status'], [ 'draft', 'published', 'disabled', 'archived' ], true ) ? $item['status'] : null;
				$current = $existing[ $slug ] ?? null;
				if ( $current !== null ) {
					$input['id']                = (string) $current['id'];
					$input['expected_revision'] = (int) $current['revision'];
				}
				if ( $status !== null ) {
					$input['status'] = $status;
				}
				try {
					( $this->run )( $ability . '/save', $input );
					$results[] = [
						'kind'   => $key,
						'slug'   => $slug,
						'action' => $current === null ? 'created' : 'updated',
						'error'  => '',
					];
				} catch ( Throwable $error ) {
					$results[] = [
						'kind'   => $key,
						'slug'   => $slug,
						'action' => 'failed',
						'error'  => $error->getMessage(),
					];
				}
			}
		}
		if ( is_array( $data['settings'] ?? null ) ) {
			foreach ( self::SETTINGS as $option => $key ) {
				if ( array_key_exists( $key, $data['settings'] ) && is_array( $data['settings'][ $key ] ) ) {
					( $this->write )( $option, $data['settings'][ $key ] );
					$results[] = [
						'kind'   => 'settings',
						'slug'   => $key,
						'action' => 'updated',
						'error'  => '',
					];
				}
			}
		}

		return $results;
	}

	/** @return list<array<string,mixed>> */
	private function definitions( string $ability ): array {
		$list = ( $this->run )( $ability . '/list', [] );
		$list = is_array( $list ) && is_array( $list['definitions'] ?? null ) ? $list['definitions'] : [];

		return array_values( array_filter( $list, static fn ( mixed $item ): bool => is_array( $item ) && is_string( $item['slug'] ?? null ) && ( $item['payload']['trashed'] ?? false ) !== true ) );
	}

	/** @return array<string,array<string,mixed>> */
	private function bySlug( string $ability ): array {
		$map = [];
		foreach ( $this->definitions( $ability ) as $definition ) {
			$map[ (string) $definition['slug'] ] = $definition;
		}

		return $map;
	}
}
