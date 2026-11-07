<?php

declare(strict_types=1);

// phpcs:disable WordPress.Security.EscapeOutput.ExceptionNotEscaped -- Exception messages are internal and never echoed raw: they reach admins as JSON validation issues (shown with textContent) or through escaped notices.

namespace WPEssential\Platform\Database\Migrations;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use RuntimeException;
use WPEssential\Contracts\MigrationInterface;
use WPEssential\Contracts\MigrationStateStoreInterface;

final class MigrationRunner {

	public function __construct(
		private readonly MigrationRegistry $registry,
		private readonly MigrationStateStoreInterface $stateStore,
	) {
	}

	/** @return list<string> applied migration ids */
	public function runPending(): array {
		$alreadyApplied = array_fill_keys( $this->stateStore->appliedIds(), true );
		$appliedNow     = [];

		foreach ( $this->registry->ordered() as $migration ) {
			if ( isset( $alreadyApplied[ $migration->id() ] ) ) {
				continue;
			}

			$this->assertSafe( $migration );
			$migration->apply();
			$this->stateStore->markApplied( $migration->id() );
			$alreadyApplied[ $migration->id() ] = true;
			$appliedNow[]                       = $migration->id();
		}

		return $appliedNow;
	}

	private function assertSafe( MigrationInterface $migration ): void {
		if ( ! $migration->isDestructive() ) {
			return;
		}

		$recoveryPlan = trim( (string) $migration->recoveryPlan() );
		if ( $recoveryPlan === '' ) {
			throw new RuntimeException(
				sprintf(
					'Destructive migration "%s" requires an explicit recovery plan.',
					$migration->id(),
				)
			);
		}
	}
}
