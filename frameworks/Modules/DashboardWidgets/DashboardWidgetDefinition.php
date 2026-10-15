<?php
/**
 * Canonical Dashboard widget definition ownership and public projections.
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

/** Validate Dashboard widget ownership and project its public shape. */
final class DashboardWidgetDefinition {

	public const TYPE             = 'dashboard-widget';
	public const OWNER_SURFACE_ID = 10;

	/**
	 * Verify that the definition belongs to Dashboard Widgets.
	 *
	 * @param Definition $definition Candidate definition.
	 * @return void
	 * @throws InvalidArgumentException If owner or definition type is wrong.
	 */
	public static function assertOwned(Definition $definition): void
	{
		if ( $definition->ownerSurfaceId !== self::OWNER_SURFACE_ID ) {
			throw new InvalidArgumentException( 'Dashboard Widget definition owner surface is invalid.' );
		}

		if ( $definition->type !== self::TYPE ) {
			throw new InvalidArgumentException( 'Dashboard Widget definition type is invalid.' );
		}
	}

	/**
	 * Project a canonical Dashboard widget definition for public reads.
	 *
	 * @param Definition $definition Owned Dashboard widget definition.
	 * @return array{
	 *   id:string,
	 *   slug:string,
	 *   type:string,
	 *   schema_version:int,
	 *   owner_surface_id:int,
	 *   status:string,
	 *   revision:int,
	 *   dependencies:list<string>,
	 *   payload:array<string,mixed>
	 * }
	 */
	public static function view( Definition $definition ): array {
		self::assertOwned( $definition );

		return array(
			'id'               => $definition->id,
			'slug'             => $definition->slug,
			'type'             => $definition->type,
			'schema_version' => $definition->schemaVersion,
			'owner_surface_id' => $definition->ownerSurfaceId,
			'status'           => $definition->status->value,
			'revision'         => $definition->revision,
			'dependencies'     => $definition->dependencies,
			'payload'          => $definition->payload,
		);
	}
}
