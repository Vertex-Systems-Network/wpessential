<?php

declare(strict_types=1);

namespace WPEssential\Contracts;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/** Table gateway support for DefinitionDeletableRepositoryInterface. */
interface DefinitionDeletableTableGatewayInterface extends DefinitionTableGatewayInterface {

	/** Removes the row and its dependency edges when the revision still matches. */
	public function deleteIfCurrentRevision( string $id, int $expectedRevision ): bool;
}
