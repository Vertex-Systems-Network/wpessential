<?php

declare(strict_types=1);

namespace WPEssential\Contracts;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * A service that records which module edition ("free" or "pro") registers into it.
 * The kernel enters the edition before a module's register()/boot() and leaves after.
 */
interface ModuleEditionScopeInterface {

	public function enterEdition( string $edition ): void;

	public function leaveEdition(): void;
}
