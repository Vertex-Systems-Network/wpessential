<?php

declare(strict_types=1);

namespace WPEssential\Tests\Unit\Modules\DashboardWidgets;

use PHPUnit\Framework\TestCase;
use RuntimeException;
use WPEssential\Modules\DashboardWidgets\DashboardWidgetComponentBlueprintCatalog;
use WPEssential\Modules\DashboardWidgets\DashboardWidgetComponentRegistrar;
use WPEssential\Platform\Auth\ExecutionContext;
use WPEssential\Platform\Auth\Principal;
use WPEssential\Platform\Components\ComponentBlueprintRegistry;
use WPEssential\Platform\Rendering\BlueprintRendererDispatcher;
use WPEssential\Platform\Rendering\RenderInput;

final class DashboardWidgetComponentRegistrarTest extends TestCase
{
    public function testRegistersSevenBlueprintsAndRendererDelegates(): void
    {
        $registry = new ComponentBlueprintRegistry();
        $dispatcher = new BlueprintRendererDispatcher($registry);
        $catalog = new DashboardWidgetComponentBlueprintCatalog();

        (new DashboardWidgetComponentRegistrar($registry, $dispatcher, $catalog))->register();

        foreach ($catalog->all() as $blueprint) {
            self::assertEquals($blueprint, $registry->get($blueprint->id, $blueprint->revision));
        }

        $richText = $catalog->forContentType('rich_text');
        self::assertNotNull($richText);
        $output = $dispatcher->render(
            new RenderInput($richText->id, $richText->revision, ['content' => 'Safe']),
            new ExecutionContext(new Principal(7), 1),
        );

        self::assertTrue($output->success);
        self::assertStringContainsString('Safe', $output->html);
        self::assertSame([], $output->assetHandles);
    }

    public function testDuplicateRegistrationFailsBeforeMutatingAgain(): void
    {
        $registry = new ComponentBlueprintRegistry();
        $dispatcher = new BlueprintRendererDispatcher($registry);
        $registrar = new DashboardWidgetComponentRegistrar($registry, $dispatcher);

        $registrar->register();

        $this->expectException(RuntimeException::class);
        $registrar->register();
    }

    public function testPreexistingBlueprintCollisionFailsClosed(): void
    {
        $registry = new ComponentBlueprintRegistry();
        $dispatcher = new BlueprintRendererDispatcher($registry);
        $catalog = new DashboardWidgetComponentBlueprintCatalog();
        $blueprint = $catalog->forContentType('rich_text');
        self::assertNotNull($blueprint);
        $registry->register($blueprint);

        $this->expectException(RuntimeException::class);
        (new DashboardWidgetComponentRegistrar($registry, $dispatcher, $catalog))->register();
    }
}
