<?php

declare(strict_types=1);

// phpcs:disable WordPress.Security.EscapeOutput.ExceptionNotEscaped -- Exception messages are internal and never echoed raw: they reach admins as JSON validation issues (shown with textContent) or through escaped notices.

namespace WPEssential\Platform\Audit;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use RuntimeException;
use WPEssential\Contracts\AuditLoggerInterface;

final class InMemoryAuditLogger implements AuditLoggerInterface {

	/** @var array<string, AuditRecord> */
	private array $records = [];

	public function record( AuditRecord $record ): void {
		if ( isset( $this->records[ $record->id ] ) ) {
			throw new RuntimeException( sprintf( 'Audit record "%s" is already committed.', $record->id ) );
		}
		$this->records[ $record->id ] = $record;
	}

	/** @return list<AuditRecord> */
	public function all(): array {
		return array_values( $this->records );
	}
}
