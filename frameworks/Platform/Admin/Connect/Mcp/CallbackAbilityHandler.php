<?php

declare(strict_types=1);

namespace WPEssential\Platform\Admin\Connect\Mcp;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use Closure;
use WPEssential\Contracts\AbilityHandlerInterface;
use WPEssential\Platform\Auth\ExecutionContext;

final class CallbackAbilityHandler implements AbilityHandlerInterface {

	/** @param Closure(array<string,mixed>):array<string,mixed> $callback */
	public function __construct( private readonly Closure $callback ) {}

	/** @param array<string, mixed> $input */
	public function handle( array $input, ExecutionContext $context ): mixed {
		return ( $this->callback )( $input );
	}
}
