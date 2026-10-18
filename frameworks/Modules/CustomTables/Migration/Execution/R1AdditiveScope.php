<?php

declare(strict_types=1);

namespace WPEssential\Modules\CustomTables\Migration\Execution;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use WPEssential\Modules\CustomTables\Migration\MigrationPlan;
use WPEssential\Modules\CustomTables\Migration\MigrationRisk;

/** ADR-0224/0225: the single R1 additive scope rule shared by preparation and execution. */
final class R1AdditiveScope {

	private const ADDITIVE_OPERATIONS = [ 'create_table', 'add_column', 'add_index' ];

	/** @return list<string> */
	public static function planReasons( MigrationPlan $plan ): array {
		$reasons = [];
		if ( $plan->operations === [] ) {
			$reasons[] = 'plan_empty';
		}
		if ( $plan->blocked || $plan->recoveryRequired || $plan->findings !== [] ) {
			$reasons[] = 'plan_not_clean';
		}
		if ( $plan->risk->value > MigrationRisk::R1->value ) {
			$reasons[] = 'plan_risk_above_r1';
		}
		foreach ( $plan->operations as $operation ) {
			if ( ! in_array( $operation->type, self::ADDITIVE_OPERATIONS, true ) ) {
				$reasons[] = 'operation_not_additive';
			}
			if ( $operation->risk->value > MigrationRisk::R1->value || $operation->blocked || $operation->recoveryRequired ) {
				$reasons[] = 'operation_risk_above_r1';
			}
		}

		return $reasons;
	}
}
