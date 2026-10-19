<?php

declare(strict_types=1);

namespace WPEssential\Modules\CustomTables;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use Throwable;
use WPEssential\Contracts\AuditLoggerInterface;
use WPEssential\Modules\CustomTables\Definition\TableDefinitionCompiler;
use WPEssential\Platform\Audit\AuditOutcome;
use WPEssential\Platform\Audit\AuditRecord;
use WPEssential\Platform\Auth\ExecutionContext;

/**
 * ADR-0228: audit trail for Custom Tables migration abilities.
 *
 * Returns whether the record was written instead of throwing, so an audit failure is reported to the
 * caller rather than turning an already-applied migration into an error response.
 */
final readonly class CustomTablesMigrationAudit {

	private const UUID_PATTERN = '/^[0-9a-f]{8}-[0-9a-f]{4}-[1-5][0-9a-f]{3}-[89ab][0-9a-f]{3}-[0-9a-f]{12}$/i';

	public function __construct( private AuditLoggerInterface $logger ) {
	}

	/**
	 * @param array<string,mixed> $input
	 * @param array<string,mixed> $metadata
	 */
	public function record(
		ExecutionContext $context,
		string $action,
		AuditOutcome $outcome,
		array $input,
		?string $reason = null,
		array $metadata = [],
	): bool {
		[ $resourceType, $resourceId ] = self::resource( $action, $input );

		try {
			$this->logger->record(
				new AuditRecord(
					id: self::uuid(),
					context: $context,
					ownerSurfaceId: TableDefinitionCompiler::OWNER_SURFACE_ID,
					action: 'custom-tables.migration.' . $action,
					outcome: $outcome,
					resourceType: $resourceType,
					resourceId: $resourceId,
					reason: $reason,
					metadata: $metadata,
				)
			);
		} catch ( Throwable ) {
			return false;
		}

		return true;
	}

	/**
	 * Only well-formed identifiers are recorded; malformed input is never echoed into the audit trail.
	 *
	 * @param array<string,mixed> $input
	 * @return array{0: ?string, 1: ?string}
	 */
	private static function resource( string $action, array $input ): array {
		$key   = $action === 'prepare' ? 'definition_id' : 'run_id';
		$value = $input[ $key ] ?? null;
		if ( ! is_string( $value ) || preg_match( self::UUID_PATTERN, $value ) !== 1 ) {
			return [ null, null ];
		}

		return [ $action === 'prepare' ? 'custom_table_definition' : 'custom_table_migration_run', strtolower( $value ) ];
	}

	private static function uuid(): string {
		if ( function_exists( 'wp_generate_uuid4' ) ) {
			$uuid = strtolower( (string) wp_generate_uuid4() );
			if ( preg_match( self::UUID_PATTERN, $uuid ) === 1 ) {
				return $uuid;
			}
		}
		$bytes    = random_bytes( 16 );
		$bytes[6] = chr( ( ord( $bytes[6] ) & 0x0f ) | 0x40 );
		$bytes[8] = chr( ( ord( $bytes[8] ) & 0x3f ) | 0x80 );
		$hex      = bin2hex( $bytes );

		return sprintf(
			'%s-%s-%s-%s-%s',
			substr( $hex, 0, 8 ),
			substr( $hex, 8, 4 ),
			substr( $hex, 12, 4 ),
			substr( $hex, 16, 4 ),
			substr( $hex, 20, 12 ),
		);
	}
}
