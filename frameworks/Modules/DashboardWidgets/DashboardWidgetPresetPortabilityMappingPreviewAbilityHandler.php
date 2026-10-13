<?php
/**
 * Dashboard preset portability mapping preview validation.
 *
 * @package WPEssential\Modules\DashboardWidgets
 */

declare(strict_types=1);

namespace WPEssential\Modules\DashboardWidgets;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use InvalidArgumentException;
use WPEssential\Contracts\AbilityHandlerInterface;
use WPEssential\Platform\Auth\ExecutionContext;

/** Read-only validation adapter for Dashboard preset portability. */
final readonly class DashboardWidgetPresetPortabilityMappingPreviewAbilityHandler implements AbilityHandlerInterface {

	/**
	 * Construct the handler with the canonical service.
	 *
	 * @param DashboardWidgetPresetPortabilityMappingPreviewService $preview Portability validation service.
	 */
	public function __construct( private DashboardWidgetPresetPortabilityMappingPreviewService $preview ) {}

	/**
	 * Validate and preview Dashboard preset widget mappings.
	 *
	 * @param array            $input Canonical preset portability input.
	 * @param ExecutionContext $context Authorized invocation context.
	 * @return mixed
	 * @throws InvalidArgumentException On invalid canonical input.
	 */
	public function handle( array $input, ExecutionContext $context ): mixed {
		$keys = array_keys( $input );
		sort( $keys, SORT_STRING );
		if (
			array( 'snapshot', 'target_id', 'widget_id_map' ) !== $keys
			|| ! is_array( $input['snapshot'] )
			|| ! is_string( $input['target_id'] )
			|| ! is_array( $input['widget_id_map'] )
		) {
			throw new InvalidArgumentException(
				'Preset mapping preview requires exactly snapshot, target_id and widget_id_map.'
			);
		}

		return $this->preview->preview( $input['snapshot'], $input['target_id'], $input['widget_id_map'] );
	}
}
