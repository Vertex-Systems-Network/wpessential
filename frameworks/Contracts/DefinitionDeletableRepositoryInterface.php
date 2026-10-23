<?php

declare(strict_types=1);

namespace WPEssential\Contracts;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Opt-in permanent removal of a Definition. The delete happens only when the stored revision is
 * still the one the caller saw (optimistic concurrency), so a concurrent edit is never lost.
 * Business rules (lock, status, dependents) belong to the caller, not the repository.
 */
interface DefinitionDeletableRepositoryInterface extends DefinitionRepositoryInterface {

	/** @return bool false when the definition is gone or its revision moved on. */
	public function deleteIfCurrentRevision( string $id, int $expectedRevision ): bool;
}
