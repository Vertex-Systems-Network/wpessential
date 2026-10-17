<?php
/**
 * Validate Dashboard Widget Multisite policy definition ownership.
 *
 * @package WPEssential\Modules\DashboardWidgets
 */

declare(strict_types=1);

namespace WPEssential\Modules\DashboardWidgets;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use InvalidArgumentException;
use WPEssential\Platform\Definitions\Definition;

/** Validate canonical Dashboard Widgets definition ownership and type. */
final class DashboardWidgetMultisitePolicyDefinition {

	public const TYPE             = 'dashboard-widget-multisite-policy';
	public const OWNER_SURFACE_ID = 10;

	/**
	 * Reject unsupported Dashboard Widget definition ownership or type.
	 *
	 * @param Definition $definition Definition to validate.
	 * @return void
	 * @throws InvalidArgumentException If owner or type is wrong.
	 */
	public static function assertOwned(Definition $definition): void
	{
		if ($definition->ownerSurfaceId !== self::OWNER_SURFACE_ID) {
			throw new InvalidArgumentException( 'Dashboard Widget Multisite policy owner surface is invalid.' );
		}
		if ( self::TYPE !== $definition->type ) {
			throw new InvalidArgumentException( 'Dashboard Widget Multisite policy definition type is invalid.' );
		}
	}
}
