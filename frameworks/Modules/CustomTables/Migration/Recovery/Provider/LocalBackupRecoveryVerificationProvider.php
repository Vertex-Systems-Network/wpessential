<?php

declare(strict_types=1);

namespace WPEssential\Modules\CustomTables\Migration\Recovery\Provider;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use InvalidArgumentException;
use WPEssential\Modules\Backup\Verification\LocalBackupManifestVerifier;
use WPEssential\Modules\CustomTables\Migration\Recovery\Binding\BoundRecoveryEvidence;
use WPEssential\Modules\CustomTables\Migration\Recovery\RecoveryClass;
use WPEssential\Modules\CustomTables\Migration\Recovery\RecoveryEvidence;

/**
 * Server-owned real recovery verification source (audit blocker 2).
 *
 * The artifact id is a local Backup Set UUID. Evidence is returned only when the Backup Set is
 * verified on disk, captured the database scope, and its manifest is bound to the reviewed plan.
 */
final class LocalBackupRecoveryVerificationProvider implements TrustedRecoveryVerificationProviderInterface {

	public function __construct( private readonly LocalBackupManifestVerifier $verifier ) {
	}

	public function verify( string $artifactId, string $planFingerprint ): ?BoundRecoveryEvidence {
		if ( preg_match( '/^[a-z0-9][a-z0-9._:-]{2,127}$/', $artifactId ) !== 1 ) {
			throw new InvalidArgumentException( 'Custom Tables recovery artifact id is invalid.' );
		}
		if ( preg_match( '/^[a-f0-9]{64}$/', $planFingerprint ) !== 1 ) {
			throw new InvalidArgumentException( 'Custom Tables recovery verification plan fingerprint is invalid.' );
		}
		if ( preg_match( '/^[0-9a-f]{8}-[0-9a-f]{4}-[1-5][0-9a-f]{3}-[89ab][0-9a-f]{3}-[0-9a-f]{12}$/', $artifactId ) !== 1 ) {
			return null;
		}

		$backup = $this->verifier->verify( $artifactId );
		if ( $backup === null || ! $backup->manifest->captures( 'database' ) || ! $backup->manifest->isBoundToPlan( $planFingerprint ) ) {
			return null;
		}

		return new BoundRecoveryEvidence(
			evidence: new RecoveryEvidence(
				planFingerprint: $planFingerprint,
				class: RecoveryClass::VerifiedBackupRequired,
				verifiedBackupAvailable: true,
				backupAgeHours: $backup->ageHours,
			),
			provider: $backup->manifest->databaseProvider,
			providerVersion: $backup->manifest->databaseVersion,
			observedAgeHours: $backup->ageHours,
		);
	}
}
