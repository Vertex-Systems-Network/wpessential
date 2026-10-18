<?php

declare(strict_types=1);

namespace WPEssential\Modules\Backup\Manifest;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use InvalidArgumentException;

/** One physical archive/chunk/database stream listed in a Backup Set manifest. */
final readonly class BackupArtifact {

	private function __construct(
		public string $path,
		public string $sha256,
		public int $size,
	) {
	}

	/** @param array<mixed> $data */
	public static function fromArray( array $data ): self {
		$path   = $data['path'] ?? null;
		$sha256 = $data['sha256'] ?? null;
		$size   = $data['size'] ?? null;

		// Relative, forward-slash path made of safe segments only: no absolute paths, no traversal.
		if ( ! is_string( $path ) || preg_match( '#^[A-Za-z0-9_-][A-Za-z0-9._-]*(/[A-Za-z0-9_-][A-Za-z0-9._-]*)*$#', $path ) !== 1 ) {
			throw new InvalidArgumentException( 'Backup manifest artifact path must be a safe relative path.' );
		}
		if ( ! is_string( $sha256 ) || preg_match( '/^[a-f0-9]{64}$/', $sha256 ) !== 1 ) {
			throw new InvalidArgumentException( 'Backup manifest artifact checksum must be a lowercase SHA-256.' );
		}
		if ( ! is_int( $size ) || $size < 1 ) {
			throw new InvalidArgumentException( 'Backup manifest artifact size must be a positive integer.' );
		}

		return new self( $path, $sha256, $size );
	}
}
