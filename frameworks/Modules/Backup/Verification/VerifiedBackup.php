<?php

declare(strict_types=1);

namespace WPEssential\Modules\Backup\Verification;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use InvalidArgumentException;
use WPEssential\Modules\Backup\Manifest\BackupManifest;

/** A Backup Set whose manifest and every listed artifact were verified on disk. */
final readonly class VerifiedBackup {

	public function __construct(
		public BackupManifest $manifest,
		public int $ageHours,
	) {
		if ( $this->ageHours < 0 ) {
			throw new InvalidArgumentException( 'Verified backup age cannot be negative.' );
		}
	}
}
