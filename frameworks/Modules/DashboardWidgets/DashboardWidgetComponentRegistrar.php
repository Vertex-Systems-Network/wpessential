<?php
/**
 * Register Dashboard widget component blueprints with the renderer.
 *
 * @package WPEssential\Modules\DashboardWidgets
 */

declare(strict_types=1);

namespace WPEssential\Modules\DashboardWidgets;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use RuntimeException;
use WPEssential\Platform\Components\ComponentBlueprintRegistry;
use WPEssential\Platform\Rendering\BlueprintRendererDispatcher;

/** Register trusted Dashboard widget renderers and component blueprints. */
final readonly class DashboardWidgetComponentRegistrar {

	/**
	 * Trusted component blueprint catalog.
	 *
	 * @var DashboardWidgetComponentBlueprintCatalog
	 */
	private DashboardWidgetComponentBlueprintCatalog $catalog;
	/**
	 * Trusted renderer implementation.
	 *
	 * @var DashboardWidgetTrustedComponentRenderer
	 */
	private DashboardWidgetTrustedComponentRenderer $renderer;

	/**
	 * Construct the component registrar.
	 *
	 * @param ComponentBlueprintRegistry                    $blueprints Component registry.
	 * @param BlueprintRendererDispatcher                   $dispatcher Renderer dispatcher.
	 * @param DashboardWidgetComponentBlueprintCatalog|null $catalog Optional catalog.
	 * @param DashboardWidgetTrustedComponentRenderer|null  $renderer Optional renderer.
	 */
	public function __construct(
		private ComponentBlueprintRegistry $blueprints,
		private BlueprintRendererDispatcher $dispatcher,
		?DashboardWidgetComponentBlueprintCatalog $catalog = null,
		?DashboardWidgetTrustedComponentRenderer $renderer = null,
	) {
		$this->catalog  = $catalog ?? new DashboardWidgetComponentBlueprintCatalog();
		$this->renderer = $renderer ?? new DashboardWidgetTrustedComponentRenderer( $this->catalog );
	}

	/**
	 * Register the available trusted components and validate their graph.
	 *
	 * @return void
	 * @throws RuntimeException If a component is already registered.
	 */
	public function register(): void {
		$blueprints = $this->catalog->all();

		foreach ( $blueprints as $blueprint ) {
			if ( $this->blueprints->get( $blueprint->id, $blueprint->revision ) !== null ) {
				throw new RuntimeException( 'Dashboard Widget Component Blueprint is already registered.' );
			}
		}

		foreach ( $this->catalog->componentTypes() as $component_type ) {
			$this->dispatcher->register( $component_type, $this->renderer );
		}

		foreach ( $blueprints as $blueprint ) {
			$this->blueprints->register( $blueprint );
		}
		$this->blueprints->validateGraph();
	}
}
