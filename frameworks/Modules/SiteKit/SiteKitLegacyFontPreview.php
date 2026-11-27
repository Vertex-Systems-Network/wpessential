<?php

declare(strict_types=1);

namespace WPEssential\Modules\SiteKit;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Read-only font migration assessment. Bundled fonts require a verified companion
 * preset reference; never turn a bundled font into a remote font implicitly.
 */
final class SiteKitLegacyFontPreview {

	private const ROLES = [ 'font_body', 'font_heading', 'font_mono' ];

	/** @return array<string,string> */
	public static function bundledReferences(): array {
		$out = [];
		foreach ( SiteKitFonts::bundledPresets() as $family ) {
			$out[ (string) $family['name'] ] = 'theme:' . $family['slug'];
		}
		return $out;
	}

	/**
	 * Only locally bundled role references are eligible for this runtime slice.
	 * Google lists and delivery changes remain review-only.
	 *
	 * @param array<string,mixed> $legacy
	 * @return array<string,string>
	 */
	public static function bundledCandidates( array $legacy ): array {
		$refs = self::bundledReferences();
		$out  = [];
		foreach ( self::ROLES as $key ) {
			$raw = $legacy[ $key ] ?? '';
			if ( is_string( $raw ) && isset( $refs[ $raw ] ) ) {
				$out[ $key ] = $refs[ $raw ];
			}
		}
		return $out;
	}

	/** The extended contract explicitly supports suppressing legacy extra font lists. */
	public static function remoteSupported(): bool {
		return SiteKitLegacyPreview::supported() && apply_filters( 'wpessential_site_kit_remote_font_handoff', false ) === true;
	}

	/**
	 * Remote-only slice. Local cache ownership is handled separately:
	 * copying a local selection without its cached files can cause a remote request.
	 *
	 * @param array<string,mixed> $legacy
	 * @param array<string,mixed> $canonical
	 * @return array<string,string>
	 */
	public static function remoteCandidates( array $legacy, array $canonical ): array {
		if ( SiteKitFonts::local( $legacy ) || SiteKitFonts::local( $canonical ) ) {
			return [];
		}
		return self::assess( $legacy, [ 'fonts_delivery' => 'google' ], [] )['ready'];
	}

	/**
	 * Validate the complete cached face list before using its metadata. A single
	 * invalid face invalidates the family; the caller checks current upload files.
	 *
	 * @param mixed $faces Cached metadata.
	 * @param callable(string):bool $localFile Exact local source validator.
	 * @return list<array<string,mixed>>
	 */
	public static function validatedFaces( string $family, mixed $faces, callable $localFile ): array {
		if ( ! is_array( $faces ) || ! array_is_list( $faces ) || $faces === [] || ! SiteKitFonts::exists( $family ) ) {
			return [];
		}
		foreach ( $faces as $face ) {
			if ( ! is_array( $face ) || ( $face['fontFamily'] ?? null ) !== $family || ! in_array( $face['fontStyle'] ?? null, [ 'normal', 'italic' ], true ) || ! is_string( $face['fontWeight'] ?? null ) || preg_match( '/^[1-9][0-9]{0,3}(?: [1-9][0-9]{0,3})?$/D', $face['fontWeight'] ) !== 1 || ( $face['fontDisplay'] ?? null ) !== 'swap' || ! is_string( $face['unicodeRange'] ?? null ) || preg_match( '/^[Uu0-9A-Fa-f+?, -]*$/D', $face['unicodeRange'] ) !== 1 || ! is_array( $face['src'] ?? null ) || count( $face['src'] ) !== 1 || ! is_string( $face['src'][0] ?? null ) || ! $localFile( $face['src'][0] ) ) {
				return [];
			}
			if ( array_diff( array_keys( $face ), [ 'fontFamily', 'fontStyle', 'fontWeight', 'fontDisplay', 'src', 'unicodeRange' ] ) !== [] ) {
				return [];
			}
		}
		return $faces;
	}

	/** @return list<array<string,mixed>> */
	private static function cachedFaces( string $family ): array {
		if ( ! function_exists( 'wp_get_upload_dir' ) || ! function_exists( 'get_option' ) ) {
			return [];
		}
		$uploads = wp_get_upload_dir();
		if ( ! empty( $uploads['error'] ) ) {
			return [];
		}
		$slug = sanitize_title( $family );
		$base = trailingslashit( $uploads['baseurl'] ) . 'wpessential-fonts/' . $slug . '/';
		$dir  = trailingslashit( $uploads['basedir'] ) . 'wpessential-fonts/' . $slug . '/';
		$cache = (array) get_option( SiteKitFonts::LOCAL_OPTION, [] );
		$legacy = (array) get_option( 'wpessential_fonts_local', [] );
		return self::validatedFaces( $family, array_key_exists( $family, $cache ) ? $cache[ $family ] : ( $legacy[ $family ] ?? [] ), static function ( string $src ) use ( $base, $dir, $slug ): bool {
			if ( ! str_starts_with( $src, $base ) ) {
				return false;
			}
			$file = substr( $src, strlen( $base ) );
			if ( preg_match( '/^' . preg_quote( $slug, '/' ) . '-(?:normal|italic)-[0-9-]+-latin(?:-ext)?\\.woff2$/D', $file ) !== 1 ) {
				return false;
			}
			$path = $dir . $file;
			$real = realpath( $path );
			$root = realpath( $dir );
			if ( $real === false || $root === false || dirname( $real ) !== $root || ! is_file( $path ) || is_link( $path ) || ! is_readable( $path ) || filesize( $path ) <= 4 ) {
				return false;
			}
			return file_get_contents( $path, false, null, 0, 4 ) === 'wOF2'; // phpcs:ignore WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents -- local font signature only.
		} );
	}

	/**
	 * @param array<string,string> $candidates
	 * @param array<string,mixed> $canonical
	 * @param callable(string):bool $cached
	 * @return array<string,string>
	 */
	public static function cachedCandidates( array $candidates, array $canonical, callable $cached ): array {
		foreach ( $candidates as $key => $value ) {
			$winner = $canonical[ $key ] ?? $value;
			$families = SiteKitFonts::used( [ $key => $winner ] );
			foreach ( $families as $family ) {
				if ( ! $cached( $family ) ) {
					unset( $candidates[ $key ] );
					break;
				}
			}
		}
		return $candidates;
	}

	/**
	 * Called only from the explicit, privileged transfer action. Reuse already
	 * verified files, without network requests or modifying the legacy option.
	 * Confirm canonical metadata readback before suppressing any legacy field.
	 *
	 * @param array<string,string> $candidates
	 * @param array<string,mixed> $canonical
	 * @return array<string,string>
	 */
	public static function adoptLocalCache( array $candidates, array $canonical ): array {
		if ( ! SiteKitFonts::local( $canonical ) ) {
			return $candidates;
		}
		$cache = (array) get_option( SiteKitFonts::LOCAL_OPTION, [] );
		$next  = $cache;
		foreach ( $candidates as $key => $value ) {
			foreach ( SiteKitFonts::used( [ $key => $canonical[ $key ] ?? $value ] ) as $family ) {
				$faces = self::cachedFaces( $family );
				if ( $faces !== [] && ! array_key_exists( $family, $cache ) ) {
					$next[ $family ] = $faces;
				}
			}
		}
		if ( $next !== $cache ) {
			update_option( SiteKitFonts::LOCAL_OPTION, $next, false );
		}
		$stored = (array) get_option( SiteKitFonts::LOCAL_OPTION, [] );
		return self::cachedCandidates( $candidates, $canonical, static fn ( string $family ): bool => isset( $next[ $family ], $stored[ $family ] ) && $stored[ $family ] === $next[ $family ] && self::cachedFaces( $family ) !== [] );
	}

	/**
	 * @param array<string,mixed> $legacy
	 * @param array<string,mixed> $canonical
	 * @return array<string,string>
	 */
	public static function candidates( array $legacy, array $canonical ): array {
		$bundled    = self::bundledCandidates( $legacy );
		$candidates = self::remoteSupported() ? $bundled + self::remoteCandidates( $legacy, $canonical ) : $bundled;
		$slugs      = array_column( SiteKitFonts::bundledPresets(), 'slug' );
		if ( function_exists( 'wp_get_global_settings' ) ) {
			$origins = wp_get_global_settings( [ 'typography', 'fontFamilies' ] );
			foreach ( (array) $origins as $families ) {
				foreach ( (array) $families as $family ) {
					if ( is_array( $family ) && is_string( $family['slug'] ?? null ) ) {
						$slugs[] = $family['slug'];
					}
				}
			}
		}
		if ( self::remoteSupported() && SiteKitFonts::local( $legacy ) && SiteKitFonts::local( $canonical ) ) {
			$candidates += self::assess( $legacy, [], [] )['ready'];
		}
		$candidates = self::resolvableCandidates( $candidates, $canonical, $slugs );
		return SiteKitFonts::local( $canonical ) ? self::cachedCandidates( $candidates, $canonical, static fn ( string $family ): bool => self::cachedFaces( $family ) !== [] ) : $candidates;
	}

	/**
	 * A conflicting canonical winner must resolve before suppressing the legacy source.
	 * Existing invalid values are never overwritten by this operation.
	 *
	 * @param array<string,string> $candidates
	 * @param array<string,mixed> $canonical
	 * @param list<string> $themeSlugs
	 * @return array<string,string>
	 */
	public static function resolvableCandidates( array $candidates, array $canonical, array $themeSlugs ): array {
		foreach ( array_keys( $candidates ) as $key ) {
			if ( ! array_key_exists( $key, $canonical ) ) {
				continue;
			}
			$existing = $canonical[ $key ];
			$valid    = false;
			if ( is_string( $existing ) ) {
				if ( $key === 'fonts_extra' ) {
					$names = array_map( static fn ( string $name ): string => str_starts_with( trim( $name ), 'google:' ) ? substr( trim( $name ), 7 ) : trim( $name ), explode( '|', $existing ) );
					$valid = array_values( array_unique( $names ) ) === SiteKitFonts::extraFamilies( $existing );
				} elseif ( str_starts_with( $existing, 'theme:' ) ) {
					$valid = in_array( substr( $existing, 6 ), $themeSlugs, true );
				} elseif ( str_starts_with( $existing, 'google:' ) ) {
					$valid = SiteKitFonts::exists( substr( $existing, 7 ) );
				}
			}
			if ( ! $valid ) {
				unset( $candidates[ $key ] );
			}
		}
		return $candidates;
	}

	/**
	 * @param list<string> $handed
	 * @return list<string>
	 */
	public static function ownedKeys( array $handed ): array {
		$eligible = self::bundledReferences() === [] ? [] : self::ROLES;
		if ( self::remoteSupported() ) {
			$eligible = array_merge( $eligible, [ 'fonts_extra' ] );
		}
		return array_values( array_intersect( $handed, $eligible ) );
	}

	/**
	 * @param array<string,mixed> $legacy
	 * @param array<string,mixed> $canonical
	 * @param array<string,string> $bundled Exact theme family name => verified theme:slug reference.
	 * @return array{ready:array<string,string>,conflicts:list<string>,unchanged:list<string>,deferred:list<string>}
	 */
	public static function assess( array $legacy, array $canonical, array $bundled ): array {
		$ready     = [];
		$conflicts = [];
		$unchanged = [];
		$deferred  = [];
		$sameDelivery = SiteKitFonts::local( $legacy ) === SiteKitFonts::local( $canonical );
		foreach ( [ 'font_body', 'font_heading', 'font_mono', 'fonts_extra' ] as $key ) {
			$raw = $legacy[ $key ] ?? '';
			if ( $raw === '' || $raw === false ) {
				continue;
			}
			$clean = '';
			if ( is_string( $raw ) ) {
				if ( $key === 'fonts_extra' ) {
					$families = array_map( 'trim', explode( '|', $raw ) );
					$valid    = SiteKitFonts::extraFamilies( $raw );
					// Never silently drop unknown entries or change the delivery mode.
					if ( $sameDelivery && array_values( array_unique( $families ) ) === $valid ) {
						$clean = implode( '|', $valid );
					}
				} elseif ( str_starts_with( $raw, 'google:' ) ) {
					if ( $sameDelivery && SiteKitFonts::exists( substr( $raw, 7 ) ) ) {
						$clean = $raw;
					}
				} elseif ( isset( $bundled[ $raw ] ) && preg_match( '/^theme:[a-z0-9-]+$/', $bundled[ $raw ] ) === 1 ) {
					$clean = $bundled[ $raw ];
				}
			}
			if ( $clean === '' ) {
				$deferred[] = $key;
				continue;
			}
			if ( array_key_exists( $key, $canonical ) ) {
				$existing = $key === 'fonts_extra' ? implode( '|', SiteKitFonts::extraFamilies( $canonical[ $key ] ) ) : $canonical[ $key ];
				if ( $existing === $clean ) {
					$unchanged[] = $key;
				} else {
					$conflicts[] = $key;
				}
				continue;
			}
			$ready[ $key ] = $clean;
		}

		return [
			'ready'     => $ready,
			'conflicts' => $conflicts,
			'unchanged' => $unchanged,
			'deferred'  => $deferred,
		];
	}
}
