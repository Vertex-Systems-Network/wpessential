<?php

declare(strict_types=1);

namespace WPEssential\Tests\Unit\Modules\DashboardWidgets;

use InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use WPEssential\Modules\DashboardWidgets\DashboardWidgetContentClassCompiler;
use WPEssential\Modules\DashboardWidgets\DashboardWidgetDefinition;
use WPEssential\Modules\DashboardWidgets\DashboardWidgetRegistrationCompiler;
use WPEssential\Modules\DashboardWidgets\DashboardWidgetRegistrationDescriptor;
use WPEssential\Modules\DashboardWidgets\DashboardWidgetRenderSourceCompiler;
use WPEssential\Modules\DashboardWidgets\DashboardWidgetVisibilityCompiler;
use WPEssential\Platform\Components\ComponentBlueprintDescriptor;
use WPEssential\Platform\Components\ComponentBlueprintRegistry;
use WPEssential\Platform\Definitions\Definition;
use WPEssential\Platform\Definitions\DefinitionStatus;

final class DashboardWidgetRegistrationCompilerTest extends TestCase
{
    private const BLUEPRINT_ID = '22222222-2222-4222-8222-222222222222';

    public function testCompilesPublishedOwnedDefinitionIntoTypedDescriptor(): void
    {
        $descriptor = $this->compiler()->compile($this->definition());

        self::assertInstanceOf(DashboardWidgetRegistrationDescriptor::class, $descriptor);
        self::assertSame('11111111-1111-4111-8111-111111111111', $descriptor->definitionId);
        self::assertSame(3, $descriptor->revision);
        self::assertSame('sales-overview', $descriptor->key);
        self::assertSame('Sales Overview', $descriptor->title);
        self::assertSame('normal', $descriptor->context);
        self::assertSame('default', $descriptor->priority);
        self::assertTrue($descriptor->networkDashboard);
    }

    public function testRejectsWrongOwnerTypeOrStatus(): void
    {
        $compiler = $this->compiler();

        foreach ([
            $this->definition(ownerSurfaceId: 9),
            $this->definition(type: 'listing'),
            $this->definition(status: DefinitionStatus::Draft),
        ] as $definition) {
            try {
                $compiler->compile($definition);
                self::fail('Expected non-runtime Dashboard Widget definition to be rejected.');
            } catch (InvalidArgumentException) {
                self::assertTrue(true);
            }
        }
    }

    public function testRegistrationCompilationFailsClosedOnMalformedVisibilityMetadata(): void
    {
        $this->expectException(InvalidArgumentException::class);

        $this->compiler()->compile($this->definition(
            visibility: ['roles' => ['Bad Role']],
        ));
    }

    public function testRegistrationCompilationFailsClosedOnUntrustedContentClass(): void
    {
        $compiler = $this->compiler();
        $valid = $this->widget();
        $missing = $valid;
        unset($missing['type']);

        foreach ([
            $missing,
            array_replace($valid, ['type' => 'Rich Text']),
            array_replace($valid, ['type' => 'iframe']),
            array_replace($valid, ['type' => 'registered_provider']),
        ] as $widget) {
            try {
                $compiler->compile($this->definition(widget: $widget));
                self::fail('Expected untrusted Dashboard Widget content class to be rejected.');
            } catch (InvalidArgumentException) {
                self::assertTrue(true);
            }
        }
    }

    public function testRegistrationCompilationFailsClosedOnMissingOrMalformedRenderSource(): void
    {
        $valid = $this->widget();
        $missing = $valid;
        unset($missing['render_source']);

        foreach ([
            $missing,
            array_replace($valid, ['render_source' => ['kind' => 'provider']]),
            array_replace($valid, ['render_source' => array_replace(
                $this->renderSource(),
                ['bindings' => ['title' => ['source' => 'provider', 'value' => 'Orders']]],
            )]),
        ] as $widget) {
            try {
                $this->compiler()->compile($this->definition(widget: $widget));
                self::fail('Expected invalid Dashboard Widget render source to be rejected.');
            } catch (InvalidArgumentException) {
                self::assertTrue(true);
            }
        }
    }

    public function testRegistrationFailsClosedWhenTrustedRenderSourceCompilerIsNotBound(): void
    {
        $this->expectException(InvalidArgumentException::class);

        (new DashboardWidgetRegistrationCompiler())->compile($this->definition());
    }

    public function testRejectsMalformedRegistrationMetadata(): void
    {
        $compiler = $this->compiler();
        $valid = $this->widget();

        $cases = [
            array_replace($valid, ['key' => 'Bad Key']),
            array_replace($valid, ['title' => '<script>alert(1)</script>']),
            array_replace($valid, ['context' => 'unknown']),
            array_replace($valid, ['priority' => 'urgent']),
            array_replace($valid, ['network_dashboard' => 'yes']),
            $valid + ['callback' => 'arbitrary_php'],
        ];

        foreach ($cases as $widget) {
            try {
                $compiler->compile($this->definition(widget: $widget));
                self::fail('Expected malformed Dashboard Widget registration metadata to be rejected.');
            } catch (InvalidArgumentException) {
                self::assertTrue(true);
            }
        }
    }

    private function compiler(): DashboardWidgetRegistrationCompiler
    {
        $registry = new ComponentBlueprintRegistry();
        $registry->register(new ComponentBlueprintDescriptor(
            id: self::BLUEPRINT_ID,
            revision: 2,
            ownerSurfaceId: DashboardWidgetDefinition::OWNER_SURFACE_ID,
            componentType: 'dashboard.metrics',
            bindingSchema: ['title' => 'string', 'count' => 'int'],
        ));

        $contentClassCompiler = new DashboardWidgetContentClassCompiler();

        return new DashboardWidgetRegistrationCompiler(
            new DashboardWidgetVisibilityCompiler(),
            $contentClassCompiler,
            new DashboardWidgetRenderSourceCompiler($registry, $contentClassCompiler),
        );
    }

    /**
     * @param array<string,mixed>|null $widget
     */
    private function definition(
        int $ownerSurfaceId = DashboardWidgetDefinition::OWNER_SURFACE_ID,
        string $type = DashboardWidgetDefinition::TYPE,
        DefinitionStatus $status = DefinitionStatus::Published,
        ?array $widget = null,
        ?array $visibility = null,
    ): Definition {
        $widget = $widget ?? $this->widget();
        if ($visibility !== null) {
            $widget['visibility'] = $visibility;
        }

        return new Definition(
            id: '11111111-1111-4111-8111-111111111111',
            slug: 'sales-overview',
            type: $type,
            schemaVersion: 1,
            ownerSurfaceId: $ownerSurfaceId,
            status: $status,
            payload: ['widget' => $widget],
            revision: 3,
            dependencies: [],
        );
    }

    /** @return array<string,mixed> */
    private function widget(): array
    {
        return [
            'key' => 'sales-overview',
            'title' => 'Sales Overview',
            'type' => 'rich_text',
            'context' => 'normal',
            'priority' => 'default',
            'network_dashboard' => true,
            'render_source' => $this->renderSource(),
        ];
    }

    /** @return array<string,mixed> */
    private function renderSource(): array
    {
        return [
            'kind' => 'component_blueprint',
            'blueprint_id' => self::BLUEPRINT_ID,
            'blueprint_revision' => 2,
            'bindings' => [
                'title' => ['source' => 'literal', 'value' => 'Orders'],
                'count' => ['source' => 'literal', 'value' => 12],
            ],
        ];
    }
}
