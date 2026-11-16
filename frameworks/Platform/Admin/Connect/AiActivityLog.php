<?php

declare(strict_types=1);

namespace WPEssential\Platform\Admin\Connect;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use Throwable;
use WPEssential\Platform\Admin\Connect\Mcp\SettingsAbilities;
use WPEssential\Platform\Audit\AuditTableNames;
use WPEssential\Platform\Database\DatabaseAdapterInterface;

/**
 * Read side of the AI Connect audit trail: every MCP tool call the AI made on this site.
 */
final class AiActivityLog {

	public const PAGE_SIZE    = 50;
	public const EXPORT_LIMIT = 5000;
	public const CSV_COLUMNS  = [ 'time_utc', 'tool', 'outcome', 'user_id', 'user', 'client', 'reason', 'details', 'event_id' ];

	public function __construct( private readonly ?DatabaseAdapterInterface $database ) {}

	public function available(): bool {
		return $this->database !== null;
	}

	/** @return list<array{id:string,time:string,tool:string,outcome:string,user_id:int,user:string,client:string,reason:string,details:array<string|int,mixed>}> */
	public function recent( int $limit = self::PAGE_SIZE, int $offset = 0 ): array {
		if ( $this->database === null ) {
			return [];
		}
		try {
			$table = ( new AuditTableNames( $this->database ) )->events;
			$rows  = $this->database->getResults(
				$this->database->prepare(
					"SELECT event_uuid, occurred_at, actor_user_id, outcome, resource_id, reason, metadata_json FROM `{$table}` WHERE site_id = %d AND owner_surface_id = %d ORDER BY occurred_at DESC, event_id DESC LIMIT %d OFFSET %d",
					self::siteId(),
					SettingsAbilities::OWNER_SURFACE_ID,
					max( 1, $limit ),
					max( 0, $offset )
				)
			);
		} catch ( Throwable ) {
			return [];
		}

		$events = [];
		foreach ( $rows as $row ) {
			$details  = json_decode( (string) ( $row['metadata_json'] ?? '' ), true );
			$details  = is_array( $details ) ? $details : [];
			$userId   = (int) ( $row['actor_user_id'] ?? 0 );
			$events[] = [
				'id'      => (string) ( $row['event_uuid'] ?? '' ),
				'time'    => (string) ( $row['occurred_at'] ?? '' ),
				'tool'    => (string) ( $row['resource_id'] ?? '' ),
				'outcome' => (string) ( $row['outcome'] ?? '' ),
				'user_id' => $userId,
				'user'    => self::userName( $userId ),
				'client'  => is_string( $details['client'] ?? null ) ? $details['client'] : '',
				'reason'  => (string) ( $row['reason'] ?? '' ),
				'details' => $details,
			];
		}

		return $events;
	}

	/** CSV of the newest events, header first. */
	public function csv(): string {
		$csv = self::csvLine( self::CSV_COLUMNS );
		foreach ( $this->recent( self::EXPORT_LIMIT ) as $event ) {
			$csv .= self::csvLine( [ $event['time'], $event['tool'], $event['outcome'], (string) $event['user_id'], $event['user'], $event['client'], $event['reason'], (string) wp_json_encode( $event['details'] ), $event['id'] ] );
		}

		return $csv;
	}

	/** @param list<string> $cells */
	public static function csvLine( array $cells ): string {
		$out = [];
		foreach ( $cells as $cell ) {
			// Neutralise spreadsheet formulas, then quote.
			$cell  = preg_match( '/^[=+\-@\t\r]/', $cell ) === 1 ? "'" . $cell : $cell;
			$out[] = '"' . str_replace( '"', '""', $cell ) . '"';
		}

		return implode( ',', $out ) . "\r\n";
	}

	private static function siteId(): int {
		return function_exists( 'get_current_blog_id' ) ? max( 1, (int) get_current_blog_id() ) : 1;
	}

	private static function userName( int $userId ): string {
		if ( $userId <= 0 || ! function_exists( 'get_userdata' ) ) {
			return '';
		}
		$user = get_userdata( $userId );

		return $user instanceof \WP_User ? (string) $user->user_login : '';
	}
}
