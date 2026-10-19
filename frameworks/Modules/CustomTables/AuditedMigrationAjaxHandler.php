<?php

declare(strict_types=1);

namespace WPEssential\Modules\CustomTables;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use WPEssential\Platform\Audit\AuditOutcome;
use WPEssential\Platform\Auth\ExecutionChannel;
use WPEssential\Platform\Auth\ExecutionContext;
use WPEssential\Platform\WordPress\Abilities\WordPressExecutionContextFactory;
use WPEssential\Platform\WordPress\Ajax\AbilityAjaxHandler;
use WPEssential\Platform\WordPress\Ajax\AjaxAuthorizationException;
use WPEssential\Platform\WordPress\Ajax\AjaxHandlerInterface;

/**
 * ADR-0228: records registry-level refusals on the admin-AJAX path.
 *
 * The registry refuses before the ability handler runs, so the handler cannot audit those refusals;
 * this wrapper records them and rethrows unchanged. Success and failure are audited by the handler.
 */
final readonly class AuditedMigrationAjaxHandler implements AjaxHandlerInterface {

	public function __construct(
		private AbilityAjaxHandler $inner,
		private string $action,
		private CustomTablesMigrationAudit $audit,
		private WordPressExecutionContextFactory $contexts,
	) {
	}

	/** @param array<string,mixed> $payload */
	public function handle( array $payload ): mixed {
		try {
			return $this->inner->handle( $payload );
		} catch ( AjaxAuthorizationException $exception ) {
			$current = $this->contexts->current();
			$this->audit->record(
				new ExecutionContext(
					principal: $current->principal,
					siteId: $current->siteId,
					channel: ExecutionChannel::Ui,
					networkId: $current->networkId,
					correlationId: $current->correlationId,
				),
				$this->action,
				AuditOutcome::Denied,
				$payload,
				$exception->reason,
			);

			throw $exception;
		}
	}
}
