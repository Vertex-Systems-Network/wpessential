<?php

declare(strict_types=1);

namespace WPEssential\Modules\CustomTables\Migration\Review;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use WPEssential\Modules\CustomTables\Migration\Execution\R1AdditiveExecutionRequest;

final readonly class R1ExecutionRequestResolution {

	/** @param list<string> $reasons */
	private function __construct(
		public ?R1AdditiveExecutionRequest $request,
		public bool $superseded,
		public array $reasons,
	) {
	}

	public static function resolved( R1AdditiveExecutionRequest $request ): self {
		return new self( $request, false, [] );
	}

	public static function superseded(): self {
		return new self( null, true, [ 'definition_revision_changed' ] );
	}

	/** @param list<string> $reasons */
	public static function rejected( array $reasons ): self {
		$reasons = array_values( array_unique( $reasons ) );
		sort( $reasons );

		return new self( null, false, $reasons );
	}
}
