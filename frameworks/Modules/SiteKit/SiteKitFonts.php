<?php

declare(strict_types=1);

namespace WPEssential\Modules\SiteKit;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Google Fonts for Site Kit (ported from hi-wpessential inc/fonts.php).
 *
 * The full list ships with the module (data/google-fonts.json). A chosen family is downloaded to
 * uploads/wpessential-fonts on save, so visitors never contact Google, unless the site chooses
 * Google's servers. Chosen fonts become theme.json font families, so every block typography panel
 * and Elementor can use them.
 */
final class SiteKitFonts {

	public const LOCAL_OPTION = 'wpessential_site_kit_fonts';

	/** @var array<string,array{0:string,1:list<int>,2:bool,3:array{0:int,1:int}|null}>|null */
	private static ?array $list = null;

	/** @return array<string,array{0:string,1:list<int>,2:bool,3:array{0:int,1:int}|null}> family => [category, weights, italic, variable range] */
	public static function all(): array {
		if ( self::$list !== null ) {
			return self::$list;
		}
		self::$list = [];
		$file       = __DIR__ . '/data/google-fonts.json';
		$rows       = is_readable( $file ) ? json_decode( (string) file_get_contents( $file ), true ) : []; // phpcs:ignore WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents -- bundled data file.
		foreach ( is_array( $rows ) ? $rows : [] as $row ) {
			if ( is_array( $row ) && is_string( $row[0] ?? null ) ) {
				self::$list[ $row[0] ] = [
					(string) ( $row[1] ?? 'sans' ),
					array_map( 'intval', (array) ( $row[2] ?? [ 400 ] ) ),
					! empty( $row[3] ),
					is_array( $row[4] ?? null ) ? [ (int) $row[4][0], (int) $row[4][1] ] : null,
				];
			}
		}

		return self::$list;
	}

	public static function exists( string $family ): bool {
		return isset( self::all()[ $family ] );
	}

	/** CSS stack with a fallback for the font's category. */
	public static function stack( string $family ): string {
		$after = [
			'serif'   => 'Georgia, serif',
			'mono'    => 'ui-monospace, monospace',
			'hand'    => 'cursive',
			'display' => 'system-ui, sans-serif',
		][ self::all()[ $family ][0] ?? 'sans' ] ?? 'system-ui, sans-serif';

		return '"' . str_replace( '"', '', $family ) . '", ' . $after;
	}

	/** @param list<string> $families */
	public static function url( array $families ): string {
		$parts = [];
		foreach ( array_unique( $families ) as $family ) {
			$font = self::all()[ $family ] ?? null;
			if ( $font === null ) {
				continue;
			}
			if ( $font[3] !== null ) {
				$range = $font[3][0] . '..' . $font[3][1];
				$axis  = $font[2] ? 'ital,wght@0,' . $range . ';1,' . $range : 'wght@' . $range;
			} else {
				$weights = $font[1] !== [] ? $font[1] : [ 400 ];
				$axis    = $font[2]
					? 'ital,wght@' . implode( ';', array_merge( array_map( static fn ( int $w ): string => '0,' . $w, $weights ), array_map( static fn ( int $w ): string => '1,' . $w, $weights ) ) )
					: 'wght@' . implode( ';', $weights );
			}
			$parts[] = 'family=' . str_replace( '%20', '+', rawurlencode( $family ) ) . ':' . $axis;
		}

		return $parts !== [] ? 'https://fonts.googleapis.com/css2?' . implode( '&', $parts ) . '&display=swap' : '';
	}

	/**
	 * Google families the settings use (font pickers, typography sets and the extra list).
	 *
	 * @param array<string,mixed> $values
	 * @return list<string>
	 */
	public static function used( array $values ): array {
		$out = [];
		foreach ( [ 'font_body', 'font_heading', 'font_mono' ] as $key ) {
			$out[] = (string) ( $values[ $key ] ?? '' );
		}
		foreach ( explode( '|', (string) ( $values['fonts_extra'] ?? '' ) ) as $family ) {
			$out[] = 'google:' . trim( $family );
		}
		foreach ( $values as $key => $value ) {
			if ( str_starts_with( (string) $key, 'typo_' ) && is_array( $value ) ) {
				$out[] = (string) ( $value['family'] ?? '' );
			}
		}
		$families = [];
		foreach ( $out as $ref ) {
			if ( str_starts_with( $ref, 'google:' ) && self::exists( substr( $ref, 7 ) ) ) {
				$families[] = substr( $ref, 7 );
			}
		}

		return array_values( array_unique( $families ) );
	}

	/** @param array<string,mixed> $values */
	public static function local( array $values ): bool {
		return ( $values['fonts_delivery'] ?? '' ) !== 'google';
	}

	/**
	 * Downloads a family's latin + latin-ext woff2 files and returns its theme.json fontFace list.
	 *
	 * @return list<array<string,mixed>>
	 */
	public static function download( string $family ): array {
		$saved = (array) get_option( self::LOCAL_OPTION, [] );
		if ( isset( $saved[ $family ] ) && is_array( $saved[ $family ] ) ) {
			return $saved[ $family ];
		}
		$url = self::url( [ $family ] );
		if ( $url === '' ) {
			return [];
		}
		// A modern browser user agent makes Google return woff2.
		$response = wp_safe_remote_get(
			$url,
			[
				'timeout'    => 20,
				'user-agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/124.0 Safari/537.36',
			]
		);
		if ( is_wp_error( $response ) || (int) wp_remote_retrieve_response_code( $response ) !== 200 ) {
			return [];
		}
		$css     = (string) wp_remote_retrieve_body( $response );
		$uploads = wp_upload_dir();
		$slug    = sanitize_title( $family );
		$dir     = trailingslashit( $uploads['basedir'] ) . 'wpessential-fonts/' . $slug;
		$base    = trailingslashit( $uploads['baseurl'] ) . 'wpessential-fonts/' . $slug;
		if ( ! wp_mkdir_p( $dir ) ) {
			return [];
		}
		$faces = [];
		if ( preg_match_all( '#/\*\s*([a-z-]+)\s*\*/\s*@font-face\s*\{([^}]+)\}#i', $css, $blocks, PREG_SET_ORDER ) ) {
			foreach ( $blocks as $block ) {
				if ( ! in_array( $block[1], [ 'latin', 'latin-ext' ], true ) ) {
					continue;
				}
				preg_match( '/font-style:\s*([a-z]+)/', $block[2], $style );
				preg_match( '/font-weight:\s*([0-9 ]+)/', $block[2], $weight );
				preg_match( '/src:\s*url\(([^)]+)\)/', $block[2], $src );
				preg_match( '/unicode-range:\s*([^;]+)/', $block[2], $range );
				if ( empty( $src[1] ) || ! str_starts_with( $src[1], 'https://fonts.gstatic.com/' ) ) {
					continue;
				}
				$file = sanitize_file_name( $slug . '-' . ( $style[1] ?? 'normal' ) . '-' . str_replace( ' ', '-', trim( $weight[1] ?? '400' ) ) . '-' . $block[1] . '.woff2' );
				if ( ! file_exists( $dir . '/' . $file ) ) {
					$font = wp_safe_remote_get( $src[1], [ 'timeout' => 20 ] );
					if ( is_wp_error( $font ) || (int) wp_remote_retrieve_response_code( $font ) !== 200 ) {
						continue;
					}
					file_put_contents( $dir . '/' . $file, wp_remote_retrieve_body( $font ) ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_file_put_contents -- font file in uploads.
				}
				$faces[] = [
					'fontFamily'   => $family,
					'fontStyle'    => $style[1] ?? 'normal',
					'fontWeight'   => trim( $weight[1] ?? '400' ),
					'fontDisplay'  => 'swap',
					'src'          => [ $base . '/' . $file ],
					'unicodeRange' => trim( $range[1] ?? '' ),
				];
			}
		}
		if ( $faces !== [] ) {
			$saved[ $family ] = $faces;
			update_option( self::LOCAL_OPTION, $saved, false );
		}

		return $faces;
	}

	/**
	 * A theme.json font family entry for a Google family.
	 *
	 * @param array<string,mixed> $values
	 * @return array<string,mixed>
	 */
	public static function entry( string $slug, string $family, array $values ): array {
		$entry = [
			'slug'       => $slug,
			'name'       => $family,
			'fontFamily' => self::stack( $family ),
		];
		if ( self::local( $values ) ) {
			$saved = (array) get_option( self::LOCAL_OPTION, [] );
			if ( ! empty( $saved[ $family ] ) ) {
				$entry['fontFace'] = $saved[ $family ];
			}
		}

		return $entry;
	}

	/**
	 * Families still loaded from Google (Google mode, or the download failed).
	 *
	 * @param array<string,mixed> $values
	 * @return list<string>
	 */
	public static function remote( array $values ): array {
		$saved = self::local( $values ) ? (array) get_option( self::LOCAL_OPTION, [] ) : [];

		return array_values( array_filter( self::used( $values ), static fn ( string $family ): bool => empty( $saved[ $family ] ) ) );
	}
}
