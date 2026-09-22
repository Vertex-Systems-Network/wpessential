<?php

declare(strict_types=1);

namespace WPEssential\Modules\DashboardWidgets;

if (!defined('ABSPATH')) {
    exit;
}

use RuntimeException;
use WPEssential\Platform\Components\ComponentBlueprintRegistry;
use WPEssential\Platform\Rendering\BlueprintRendererDispatcher;

final readonly class DashboardWidgetComponentRegistrar
{
    private DashboardWidgetComponentBlueprintCatalog $catalog;
    private DashboardWidgetTrustedComponentRenderer $renderer;

    public function __construct(
        private ComponentBlueprintRegistry $blueprints,
        private BlueprintRendererDispatcher $dispatcher,
        ?DashboardWidgetComponentBlueprintCatalog $catalog = null,
        ?DashboardWidgetTrustedComponentRenderer $renderer = null,
    ) {
        $this->catalog = $catalog ?? new DashboardWidgetComponentBlueprintCatalog();
        $this->renderer = $renderer ?? new DashboardWidgetTrustedComponentRenderer($this->catalog);
    }

    public function register(): void
    {
        $blueprints = $this->catalog->all();

        foreach ($blueprints as $blueprint) {
            if ($this->blueprints->get($blueprint->id, $blueprint->revision) !== null) {
                throw new RuntimeException('Dashboard Widget Component Blueprint is already registered.');
            }
        }

        foreach ($this->catalog->componentTypes() as $componentType) {
            $this->dispatcher->register($componentType, $this->renderer);
        }

        foreach ($blueprints as $blueprint) {
            $this->blueprints->register($blueprint);
        }
        $this->blueprints->validateGraph();
    }
}
