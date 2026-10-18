<?php

declare(strict_types=1);

namespace WPEssential\Modules\Backup\Verification;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use DateTimeImmutable;
use DateTimeZone;
use InvalidArgumentException;
use JsonException;
use Throwable;
use WPEssential\Modules\Backup\Manifest\BackupManifest;

/**
 * Read-only verification of an existing local Backup Set under one server-owned root.
 *
 * Layout: <root>/<backup-uuid>/manifest.json plus the artifacts it lists, relative to that directory.
 * Never creates, modifies, restores or deletes anything; every failure is fail-closed (null).
 */
final readonly class LocalBackupManifestVerifier {

	private const MAX_MANIFEST_BYTES = 1048576;

	public function __construct(
		private string $root,
		private int $siteId,
		private ?DateTimeImmutable $now = null,
	) {
		if ( $this->siteId < 1 ) {
			throw new InvalidArgumentException( 'Backup verifier site id must be positive.' );
		}
	}

	public function verify( string $backupId ): ?VerifiedBackup {
		if ( preg_match( '/^[0-9a-f]{8}-[0-9a-f]{4}-[1-5][0-9a-f]{3}-[89ab][0-9a-f]{3}-[0-9a-f]{12}$/', $backupId ) !== 1 ) {
			throw new InvalidArgumentException( 'Backup id must be a lowercase canonical UUID.' );
		}

		$root = realpath( $this->root );
		if ( $root === false || ! is_dir( $root ) ) {
			return null;
		}

		$directory = realpath( $root . DIRECTORY_SEPARATOR . $backupId );
		if ( $directory === false || ! is_dir( $directory ) || ! self::isInside( $root, $directory ) ) {
			return null;
		}

		$manifest = $this->manifest( $directory );
		if ( ! $manifest instanceof BackupManifest
			|| $manifest->backupId !== $backupId
			|| $manifest->siteId !== $this->siteId
			|| ! $manifest->claimsCompleteVerifiedCapture()
		) {
			return null;
		}

		foreach ( $manifest->artifacts as $artifact ) {
			$file = realpath( $directory . DIRECTORY_SEPARATOR . $artifact->path );
			if ( $file === false
				|| ! self::isInside( $directory, $file )
				|| ! is_file( $file )
				|| filesize( $file ) !== $artifact->size
				|| hash_file( 'sha256', $file ) !== $artifact->sha256
			) {
				return null;
			}
		}

		$now = $this->now ?? new DateTimeImmutable( 'now', new DateTimeZone( 'UTC' ) );
		$age = $now->getTimestamp() - $manifest->createdAt->getTimestamp();
		if ( $age < 0 ) {
			return null;
		}

		return new VerifiedBackup( $manifest, intdiv( $age, 3600 ) );
	}

	private function manifest( string $directory ): ?BackupManifest {
		$path = $directory . DIRECTORY_SEPARATOR . 'manifest.json';
		if ( ! is_file( $path ) || is_link( $path ) ) {
			return null;
		}
		$size = filesize( $path );
		if ( $size === false || $size < 2 || $size > self::MAX_MANIFEST_BYTES ) {
			return null;
		}

		try {
			$contents = file_get_contents( $path ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents -- local server-owned file.
			if ( ! is_string( $contents ) ) {
				return null;
			}
			$data = json_decode( $contents, true, 32, JSON_THROW_ON_ERROR );

			return is_array( $data ) ? BackupManifest::fromArray( $data ) : null;
		} catch ( JsonException | InvalidArgumentException ) {
			return null;
		} catch ( Throwable ) {
			return null;
		}
	}

	private static function isInside( string $parent, string $child ): bool {
		return str_starts_with( $child, rtrim( $parent, DIRECTORY_SEPARATOR ) . DIRECTORY_SEPARATOR );
	}
}
