<?php
/**
 * Read-only Dashboard Widget definition service.
 *
 * @package WPEssential\Modules\DashboardWidgets
 */

declare(strict_types=1);

namespace WPEssential\Modules\DashboardWidgets;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use WPEssential\Contracts\DefinitionRepositoryInterface;
use WPEssential\Platform\Definitions\Definition;

/** Expose canonical Dashboard Widget definition projections. */
final readonly class DashboardWidgetsReadService {

	/**
	 * Construct the read-only Dashboard Widget service.
	 *
	 * @param DefinitionRepositoryInterface $definitions Canonical definitions repository.
	 */
	public function __construct( private DefinitionRepositoryInterface $definitions ) {
	}

	/**
	 * Fetch a Dashboard Widget by canonical definition ID.
	 *
	 * @param string $id Canonical definition ID.
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
	 * }|null
	 */
	public function get( string $id ): ?array {
		$definition = $this->definitions->get( $id );
		if ( ! $definition instanceof Definition ) {
			return null;
		}

		return DashboardWidgetDefinition::view( $definition );
	}

	/**
	 * List Dashboard Widget definitions sorted by slug and ID.
	 *
	 * @return list<array{
	 *   id:string,
	 *   slug:string,
	 *   type:string,
	 *   schema_version:int,
	 *   owner_surface_id:int,
	 *   status:string,
	 *   revision:int,
	 *   dependencies:list<string>,
	 *   payload:array<string,mixed>
	 * }>
	 */
	public function catalog(): array {
		$definitions = $this->definitions->byType( DashboardWidgetDefinition::TYPE );
		usort(
			$definitions,
			static function ( Definition $left, Definition $right ): int {
				$slug_order = $left->slug <=> $right->slug;
				return 0 !== $slug_order ? $slug_order : ( $left->id <=> $right->id );
			},
		);

		$catalog = array();
		foreach ( $definitions as $definition ) {
			$catalog[] = DashboardWidgetDefinition::view( $definition );
		}

		return $catalog;
	}
}
