<?php

declare(strict_types=1);

namespace WPEssential\Modules\Backup\Manifest;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use DateTimeImmutable;
use DateTimeZone;
use InvalidArgumentException;

/**
 * Backup Set manifest V1 (docs/MODULES/BACKUP-RESTORE-SEMANTICS.md "Mandatory manifest").
 *
 * Strictly parsed: any missing or malformed field rejects the whole manifest.
 */
final readonly class BackupManifest {

	public const FORMAT_VERSION   = 1;
	public const STATE_VERIFIED   = 'completed_verified';
	private const SCOPES          = [ 'database', 'files', 'wpe_configuration' ];
	private const DB_PROVIDERS    = [ 'mysql', 'mariadb' ];
	private const UUID_PATTERN    = '/^[0-9a-f]{8}-[0-9a-f]{4}-[1-5][0-9a-f]{3}-[89ab][0-9a-f]{3}-[0-9a-f]{12}$/';
	private const SHA256_PATTERN  = '/^[a-f0-9]{64}$/';
	private const VERSION_PATTERN = '/^\d+\.\d+\.\d+$/';

	/**
	 * @param list<string>          $scopeRequested
	 * @param list<string>          $scopeCaptured
	 * @param list<BackupArtifact>  $artifacts
	 * @param list<string>          $failedItems
	 * @param list<string>          $boundPlanFingerprints
	 */
	private function __construct(
		public string $backupId,
		public int $siteId,
		public DateTimeImmutable $createdAt,
		public string $state,
		public array $scopeRequested,
		public array $scopeCaptured,
		public string $tablePrefix,
		public string $databaseProvider,
		public string $databaseVersion,
		public array $artifacts,
		public array $failedItems,
		public array $boundPlanFingerprints,
	) {
	}

	/** @param array<string,mixed> $data */
	public static function fromArray( array $data ): self {
		if ( ( $data['manifest_version'] ?? null ) !== self::FORMAT_VERSION ) {
			throw new InvalidArgumentException( 'Backup manifest format version is unsupported.' );
		}

		$backupId = self::string( $data, 'backup_id' );
		if ( preg_match( self::UUID_PATTERN, $backupId ) !== 1 ) {
			throw new InvalidArgumentException( 'Backup manifest id must be a lowercase canonical UUID.' );
		}

		$siteId = $data['site_id'] ?? null;
		if ( ! is_int( $siteId ) || $siteId < 1 ) {
			throw new InvalidArgumentException( 'Backup manifest site id must be a positive integer.' );
		}

		$createdAt = DateTimeImmutable::createFromFormat( 'Y-m-d\TH:i:s\Z', self::string( $data, 'created_at' ), new DateTimeZone( 'UTC' ) );
		if ( ! $createdAt instanceof DateTimeImmutable ) {
			throw new InvalidArgumentException( 'Backup manifest created_at must be a UTC timestamp (Y-m-d\TH:i:s\Z).' );
		}

		$tablePrefix = self::string( $data, 'table_prefix' );
		if ( preg_match( '/^[A-Za-z0-9_]+$/', $tablePrefix ) !== 1 ) {
			throw new InvalidArgumentException( 'Backup manifest table prefix is invalid.' );
		}

		$database = $data['database'] ?? null;
		if ( ! is_array( $database ) ) {
			throw new InvalidArgumentException( 'Backup manifest database metadata is missing.' );
		}
		$provider = self::string( $database, 'provider' );
		$version  = self::string( $database, 'version' );
		if ( ! in_array( $provider, self::DB_PROVIDERS, true ) || preg_match( self::VERSION_PATTERN, $version ) !== 1 ) {
			throw new InvalidArgumentException( 'Backup manifest database provider metadata is invalid.' );
		}

		$artifacts = [];
		foreach ( self::list( $data, 'artifacts' ) as $artifact ) {
			if ( ! is_array( $artifact ) ) {
				throw new InvalidArgumentException( 'Backup manifest artifact entry is invalid.' );
			}
			$artifacts[] = BackupArtifact::fromArray( $artifact );
		}
		if ( $artifacts === [] ) {
			throw new InvalidArgumentException( 'Backup manifest must list at least one artifact.' );
		}

		return new self(
			backupId: $backupId,
			siteId: $siteId,
			createdAt: $createdAt,
			state: self::string( $data, 'state' ),
			scopeRequested: self::scopes( $data, 'scope_requested' ),
			scopeCaptured: self::scopes( $data, 'scope_captured' ),
			tablePrefix: $tablePrefix,
			databaseProvider: $provider,
			databaseVersion: $version,
			artifacts: $artifacts,
			failedItems: self::strings( $data, 'failed_items', null ),
			boundPlanFingerprints: self::strings( $data, 'bound_plan_fingerprints', self::SHA256_PATTERN ),
		);
	}

	/** A manifest only counts as verified when it says so and nothing requested is missing or failed. */
	public function claimsCompleteVerifiedCapture(): bool {
		return $this->state === self::STATE_VERIFIED
			&& $this->failedItems === []
			&& array_diff( $this->scopeRequested, $this->scopeCaptured ) === [];
	}

	public function captures( string $scope ): bool {
		return in_array( $scope, $this->scopeCaptured, true );
	}

	public function isBoundToPlan( string $planFingerprint ): bool {
		return in_array( $planFingerprint, $this->boundPlanFingerprints, true );
	}

	/** @param array<string,mixed> $data */
	private static function string( array $data, string $key ): string {
		$value = $data[ $key ] ?? null;
		if ( ! is_string( $value ) || $value === '' ) {
			throw new InvalidArgumentException( "Backup manifest field {$key} must be a non-empty string." );
		}

		return $value;
	}

	/**
	 * @param array<string,mixed> $data
	 * @return list<mixed>
	 */
	private static function list( array $data, string $key ): array {
		$value = $data[ $key ] ?? null;
		if ( ! is_array( $value ) || ! array_is_list( $value ) ) {
			throw new InvalidArgumentException( "Backup manifest field {$key} must be a list." );
		}

		return $value;
	}

	/**
	 * @param array<string,mixed> $data
	 * @return list<string>
	 */
	private static function strings( array $data, string $key, ?string $pattern ): array {
		$values = [];
		foreach ( self::list( $data, $key ) as $value ) {
			if ( ! is_string( $value ) || $value === '' || ( $pattern !== null && preg_match( $pattern, $value ) !== 1 ) ) {
				throw new InvalidArgumentException( "Backup manifest field {$key} contains an invalid entry." );
			}
			$values[] = $value;
		}

		return $values;
	}

	/**
	 * @param array<string,mixed> $data
	 * @return list<string>
	 */
	private static function scopes( array $data, string $key ): array {
		$scopes = self::strings( $data, $key, null );
		if ( $scopes === [] || array_diff( $scopes, self::SCOPES ) !== [] || count( array_unique( $scopes ) ) !== count( $scopes ) ) {
			throw new InvalidArgumentException( "Backup manifest field {$key} must list known, unique scopes." );
		}

		return $scopes;
	}
}
