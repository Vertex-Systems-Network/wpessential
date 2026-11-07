<?php

declare(strict_types=1);

// phpcs:disable WordPress.Security.EscapeOutput.ExceptionNotEscaped -- Exception messages are internal and never echoed raw: they reach admins as JSON validation issues (shown with textContent) or through escaped notices.

namespace WPEssential\Platform\Audit;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use RuntimeException;
use Throwable;
use WPEssential\Contracts\AuditLoggerInterface;
use WPEssential\Platform\Database\DatabaseAdapterInterface;

final class PersistentAuditLogger implements AuditLoggerInterface {

	private readonly AuditTableNames $tables;

	public function __construct(
		private readonly DatabaseAdapterInterface $database,
		private readonly AuditRowCodec $codec = new AuditRowCodec(),
	) {
		$this->tables = new AuditTableNames( $database );
	}

	public function record( AuditRecord $record ): void {
		try {
			$row = $this->codec->encode( $record );
			$this->database->insert(
				$this->tables->events,
				$row,
				[
					'%s',
					'%d',
					'%d',
					'%s',
					'%s',
					'%d',
					'%s',
					'%s',
					'%d',
					'%s',
					'%s',
					'%s',
					'%s',
					'%s',
					'%s',
					'%s',
					'%s',
					'%d',
					'%s',
					'%s',
				]
			);
		} catch ( Throwable $exception ) {
			throw new RuntimeException( 'Persistent audit append failed.', 0, $exception );
		}
	}
}
