<?php

declare(strict_types=1);

namespace WPEssential\Tests\Unit\Platform\Rendering;

use LogicException;
use PHPUnit\Framework\TestCase;
use stdClass;
use WPEssential\Contracts\RendererInterface;
use WPEssential\Kernel\ServiceRegistry;
use WPEssential\Platform\Assets\AssetRegistry;
use WPEssential\Platform\Auth\ExecutionContext;
use WPEssential\Platform\Auth\Principal;
use WPEssential\Platform\Components\ComponentBlueprintDescriptor;
use WPEssential\Platform\Components\ComponentBlueprintRegistry;
use WPEssential\Platform\DynamicValues\DynamicValueRouter;
use WPEssential\Platform\Rendering\BlueprintRendererDispatcher;
use WPEssential\Platform\Rendering\RenderInput;
use WPEssential\Platform\Rendering\RenderOutput;
use WPEssential\Platform\Rendering\RenderingServiceRegistrar;

final class RenderingServiceRegistrarTest extends TestCase
{
    public function testRegistersOneComposedSharedRuntimeGraph(): void
    {
        $services = new ServiceRegistry();
        (new RenderingServiceRegistrar())->register($services);

        $assets = $services->get(RenderingServiceRegistrar::SERVICE_ASSETS);
        $blueprints = $services->get(RenderingServiceRegistrar::SERVICE_BLUEPRINTS);
        $dynamicValues = $services->get(RenderingServiceRegistrar::SERVICE_DYNAMIC_VALUES);
        $renderer = $services->get(RenderingServiceRegistrar::SERVICE_RENDERER);

        self::assertInstanceOf(AssetRegistry::class, $assets);
        self::assertInstanceOf(ComponentBlueprintRegistry::class, $blueprints);
        self::assertInstanceOf(DynamicValueRouter::class, $dynamicValues);
        self::assertInstanceOf(BlueprintRendererDispatcher::class, $renderer);

        $blueprint = new ComponentBlueprintDescriptor(
            id: '11111111-1111-4111-8111-111111111111',
            revision: 1,
            ownerSurfaceId: 9,
            componentType: 'reference.card',
            bindingSchema: ['title' => 'string'],
        );
        $blueprints->register($blueprint);
        $renderer->register('reference.card', new RenderingServiceRegistrarDelegate());

        $result = $renderer->render(
            new RenderInput($blueprint->id, 1, ['title' => 'Shared']),
            new ExecutionContext(new Principal(7), 1),
        );

        self::assertTrue($result->success);
        self::assertSame('<strong>Shared</strong>', $result->html);
    }

    public function testCollisionFailsBeforeAnyPartialRegistration(): void
    {
        $services = new ServiceRegistry();
        $services->set(RenderingServiceRegistrar::SERVICE_BLUEPRINTS, new stdClass());

        try {
            (new RenderingServiceRegistrar())->register($services);
            self::fail('existing canonical rendering service must reject registrar');
        } catch (LogicException) {
            self::assertTrue(true);
        }

        self::assertFalse($services->has(RenderingServiceRegistrar::SERVICE_ASSETS));
        self::assertFalse($services->has(RenderingServiceRegistrar::SERVICE_DYNAMIC_VALUES));
        self::assertFalse($services->has(RenderingServiceRegistrar::SERVICE_RENDERER));
        self::assertInstanceOf(stdClass::class, $services->get(RenderingServiceRegistrar::SERVICE_BLUEPRINTS));
    }
}

final class RenderingServiceRegistrarDelegate implements RendererInterface
{
    public function render(RenderInput $input, ExecutionContext $context): RenderOutput
    {
        return new RenderOutput(
            true,
            '<strong>' . (string) ($input->bindings['title'] ?? '') . '</strong>',
        );
    }
}
