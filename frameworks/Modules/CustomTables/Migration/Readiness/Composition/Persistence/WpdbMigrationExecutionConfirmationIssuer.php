<?php

declare(strict_types=1);

namespace WPEssential\Modules\CustomTables\Migration\Readiness\Composition\Persistence;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use InvalidArgumentException;
use RuntimeException;
use Throwable;
use WPEssential\Modules\CustomTables\CustomTablesCapabilities;
use WPEssential\Modules\CustomTables\Migration\Readiness\Authorization\ExecutionActorType;
use WPEssential\Modules\CustomTables\Migration\Readiness\Composition\BoundMigrationExecutionConfirmation;
use WPEssential\Platform\Auth\AuthorizationRequest;
use WPEssential\Platform\Auth\ExecutionContext;
use WPEssential\Platform\Auth\PolicyEngine;
use WPEssential\Platform\Database\DatabaseAdapterInterface;

/**
 * Internal-only issuance side of the execution confirmation store (audit blocker 3).
 *
 * Records one confirmation per site/run for the authenticated, authorized user who gives it,
 * then reads it back through the trusted provider. It never dispatches managed-table execution.
 */
final readonly class WpdbMigrationExecutionConfirmationIssuer {

	private string $table;

	public function __construct(
		private DatabaseAdapterInterface $database,
		private WpdbMigrationExecutionConfirmationProvider $confirmations,
		private PolicyEngine $policy,
		private int $siteId,
		private string $ability = 'custom-tables.migration.execute',
		private string $capability = CustomTablesCapabilities::APPLY_MIGRATIONS,
	) {
		if ( $this->siteId < 1 ) {
			throw new InvalidArgumentException( 'Custom Tables execution confirmation issuer site id must be positive.' );
		}

		$prefix = $database->networkTablePrefix();
		if ( preg_match( '/^[A-Za-z0-9_]+$/', $prefix ) !== 1 ) {
			throw new RuntimeException( 'Custom Tables execution confirmation issuer prefix is invalid.' );
		}

		$this->table = $prefix . 'wpe_custom_table_migration_confirmations';
	}

	public function issue(
		ExecutionContext $context,
		string $runId,
		string $planFingerprint,
		int $readinessStateRevision,
	): BoundMigrationExecutionConfirmation {
		if ( $context->siteId !== $this->siteId ) {
			throw new RuntimeException( 'Custom Tables execution confirmation context site does not match the issuer site.' );
		}
		if ( $context->principal->actorType !== 'user' || ! $context->principal->isAuthenticated() ) {
			throw new RuntimeException( 'Custom Tables execution confirmation requires an authenticated user actor.' );
		}

		$confirmation = new BoundMigrationExecutionConfirmation(
			runId: strtolower( $runId ),
			planFingerprint: $planFingerprint,
			readinessStateRevision: $readinessStateRevision,
			actorType: ExecutionActorType::User,
			actorUserId: $context->principal->userId,
		);

		$decision = $this->policy->authorize(
			new AuthorizationRequest(
				context: $context,
				ability: $this->ability,
				capability: $this->capability,
				resourceType: 'custom_table_migration_run',
				resourceId: $confirmation->runId,
			)
		);
		if ( ! $decision->allowed ) {
			throw new RuntimeException( 'Custom Tables execution confirmation is not authorized for this actor.' );
		}

		if ( $this->confirmations->confirmationFor( $confirmation->runId ) !== null ) {
			throw new RuntimeException( 'Custom Tables execution confirmation already exists for this run.' );
		}

		try {
			$this->database->insert(
				$this->table,
				[
					'site_id'                  => $this->siteId,
					'run_id'                   => $confirmation->runId,
					'plan_fingerprint'         => $confirmation->planFingerprint,
					'readiness_state_revision' => $confirmation->readinessStateRevision,
					'actor_type'               => $confirmation->actorType->value,
					'actor_user_id'            => $confirmation->actorUserId,
					'confirmed_at'             => gmdate( 'Y-m-d H:i:s' ),
				],
				[ '%d', '%s', '%s', '%d', '%s', '%d', '%s' ],
			);
		} catch ( Throwable ) {
			throw new RuntimeException( 'Custom Tables execution confirmation could not be recorded.' );
		}

		$stored = $this->confirmations->confirmationFor( $confirmation->runId );
		if ( $stored === null || $stored->canonical() !== $confirmation->canonical() ) {
			throw new RuntimeException( 'Custom Tables execution confirmation readback verification failed.' );
		}

		return $stored;
	}
}
