<?php

declare(strict_types=1);

namespace WPEssential\Modules\CustomTables\Migration\Execution;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use WPEssential\Modules\CustomTables\Migration\Run\MigrationRunState;

final readonly class R1AdditiveExecutionResult {

	/** @param list<string> $reasons */
	private function __construct(
		public bool $applied,
		public bool $mutationAttempted,
		public ?MigrationRunState $finalState,
		public array $reasons,
	) {
	}

	/** @param list<string> $reasons */
	public static function rejected( array $reasons ): self {
		$reasons = array_values( array_unique( $reasons ) );
		sort( $reasons );

		return new self( false, false, null, $reasons );
	}

	public static function applied(): self {
		return new self( true, true, MigrationRunState::Applied, [] );
	}

	/** @param list<string> $reasons */
	public static function failedAfterMutation( MigrationRunState $state, array $reasons ): self {
		return new self( false, true, $state, $reasons );
	}
}
