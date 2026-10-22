<?php

declare(strict_types=1);

namespace WPEssential\Platform\Admin\Demos;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Remote demo manifest from wpessential.org (ported from hi-wpessential core `Demos`).
 * WPEssential ships no demos of its own; the manifest is cached for 12 hours, 15 minutes when offline.
 */
final class DemoCatalog {

	public const CACHE   = 'wpessential_demo_manifest';
	public const DEFAULT = 'https://wpessential.org/wp-json/wpessential-server/v1/demos';

	public static function url(): string {
		return function_exists( 'apply_filters' ) ? (string) apply_filters( 'wpessential_demo_manifest_url', self::DEFAULT ) : self::DEFAULT;
	}

	/** @return array{demos:list<array{id:string,title:string,tagline:string,niche:list<string>,type:string,thumbnail:string,preview:string,builders:list<string>}>,offline:bool} */
	public function all( bool $refresh = false ): array {
		$cached = ! $refresh && function_exists( 'get_transient' ) ? get_transient( self::CACHE ) : false;
		if ( is_array( $cached ) && isset( $cached['demos'], $cached['offline'] ) ) {
			return $cached;
		}

		$offline  = true;
		$demos    = [];
		$response = function_exists( 'wp_safe_remote_get' ) ? wp_safe_remote_get( self::url(), [ 'timeout' => 10 ] ) : null;
		if ( is_array( $response ) && (int) wp_remote_retrieve_response_code( $response ) === 200 ) {
			$body = json_decode( (string) wp_remote_retrieve_body( $response ), true );
			if ( is_array( $body ) && is_array( $body['demos'] ?? null ) ) {
				$offline = false;
				foreach ( $body['demos'] as $demo ) {
					$normalized = is_array( $demo ) ? self::normalize( $demo ) : null;
					if ( $normalized !== null ) {
						$demos[] = $normalized;
					}
				}
			}
		}

		$result = [
			'demos'   => $demos,
			'offline' => $offline,
		];
		if ( function_exists( 'set_transient' ) ) {
			set_transient( self::CACHE, $result, $offline ? 15 * 60 : 12 * 3600 );
		}

		return $result;
	}

	/**
	 * @param array<string,mixed> $demo
	 * @return array{id:string,title:string,tagline:string,niche:list<string>,type:string,thumbnail:string,preview:string,builders:list<string>}|null
	 */
	public static function normalize( array $demo ): ?array {
		$id = is_string( $demo['id'] ?? null ) ? strtolower( $demo['id'] ) : '';
		if ( preg_match( '/^[a-z0-9][a-z0-9-]*$/', $id ) !== 1 ) {
			return null;
		}
		$https   = static fn ( mixed $url ): string => is_string( $url ) && str_starts_with( $url, 'https://' ) ? $url : '';
		$preview = $demo['demo_url'] ?? ( is_array( $demo['preview'] ?? null ) ? ( $demo['preview']['desktop'] ?? '' ) : ( $demo['preview'] ?? '' ) );
		$niche   = [];
		foreach ( (array) ( $demo['niche'] ?? [] ) as $item ) {
			if ( is_string( $item ) && preg_match( '/^[a-z0-9-]+$/', $item ) === 1 ) {
				$niche[] = $item;
			}
		}

		return [
			'id'        => $id,
			'title'     => is_string( $demo['title'] ?? null ) ? $demo['title'] : $id,
			'tagline'   => is_string( $demo['tagline'] ?? null ) ? $demo['tagline'] : '',
			'niche'     => $niche,
			'type'      => ( $demo['type'] ?? 'free' ) === 'pro' ? 'pro' : 'free',
			'thumbnail' => $https( $demo['thumbnail'] ?? '' ),
			'preview'   => $https( $preview ),
			'builders'  => array_values( array_filter( array_map( 'strval', array_keys( (array) ( $demo['builders'] ?? [] ) ) ), static fn ( string $b ): bool => in_array( $b, [ 'gutenberg', 'elementor' ], true ) ) ),
		];
	}
}
