<?php

declare(strict_types=1);

namespace WPEssential\Tests\Unit\Modules\DashboardWidgets;

use InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use WPEssential\Modules\DashboardWidgets\DashboardWidgetDefinition;
use WPEssential\Modules\DashboardWidgets\DashboardWidgetRenderSourceCompiler;
use WPEssential\Modules\DashboardWidgets\DashboardWidgetRenderSourceDescriptor;
use WPEssential\Platform\Components\ComponentBlueprintDescriptor;
use WPEssential\Platform\Components\ComponentBlueprintRegistry;
use WPEssential\Platform\Definitions\Definition;
use WPEssential\Platform\Definitions\DefinitionStatus;
use WPEssential\Platform\Rendering\RenderInput;

final class DashboardWidgetRenderSourceCompilerTest extends TestCase
{
    private const BLUEPRINT_ID = '22222222-2222-4222-8222-222222222222';

    public function testCompilesExactSurfaceOwnedBlueprintAndLiteralBindings(): void
    {
        $descriptor = (new DashboardWidgetRenderSourceCompiler($this->registry()))->compile($this->definition());

        self::assertInstanceOf(DashboardWidgetRenderSourceDescriptor::class, $descriptor);
        self::assertSame('11111111-1111-4111-8111-111111111111', $descriptor->definitionId);
        self::assertSame(3, $descriptor->definitionRevision);
        self::assertSame(self::BLUEPRINT_ID, $descriptor->blueprintId);
        self::assertSame(2, $descriptor->blueprintRevision);
        self::assertSame(['count' => 12, 'title' => 'Orders'], $descriptor->bindings);

        $renderInput = $descriptor->toRenderInput();
        self::assertInstanceOf(RenderInput::class, $renderInput);
        self::assertSame(self::BLUEPRINT_ID, $renderInput->blueprintId);
        self::assertSame(['count' => 12, 'title' => 'Orders'], $renderInput->bindings);
    }

    public function testSupportsEverySharedBlueprintBindingType(): void
    {
        $registry = new ComponentBlueprintRegistry();
        $registry->register(new ComponentBlueprintDescriptor(
            id: self::BLUEPRINT_ID,
            revision: 2,
            ownerSurfaceId: DashboardWidgetDefinition::OWNER_SURFACE_ID,
            componentType: 'dashboard.metrics',
            bindingSchema: [
                'active' => 'bool',
                'count' => 'int',
                'ids' => 'int_list',
                'labels' => 'string_list',
                'ratio' => 'float',
                'title' => 'string',
            ],
        ));

        $definition = $this->definition(bindings: [
            'active' => ['source' => 'literal', 'value' => true],
            'count' => ['source' => 'literal', 'value' => 12],
            'ids' => ['source' => 'literal', 'value' => [2, 4]],
            'labels' => ['source' => 'literal', 'value' => ['A', 'B']],
            'ratio' => ['source' => 'literal', 'value' => 1.5],
            'title' => ['source' => 'literal', 'value' => 'Orders'],
        ]);

        $descriptor = (new DashboardWidgetRenderSourceCompiler($registry))->compile($definition);

        self::assertSame(
            [
                'active' => true,
                'count' => 12,
                'ids' => [2, 4],
                'labels' => ['A', 'B'],
                'ratio' => 1.5,
                'title' => 'Orders',
            ],
            $descriptor->bindings,
        );
    }

    public function testRejectsWrongDefinitionOwnerTypeSchemaOrStatus(): void
    {
        $compiler = new DashboardWidgetRenderSourceCompiler($this->registry());

        foreach ([
            $this->definition(ownerSurfaceId: 9),
            $this->definition(type: 'listing'),
            $this->definition(schemaVersion: 2),
            $this->definition(status: DefinitionStatus::Draft),
            $this->definition(contentType: 'iframe'),
        ] as $definition) {
            try {
                $compiler->compile($definition);
                self::fail('Expected non-trusted Dashboard Widget definition to be rejected.');
            } catch (InvalidArgumentException) {
                self::assertTrue(true);
            }
        }
    }

    public function testRejectsMissingUnknownOrMalformedRenderSource(): void
    {
        $compiler = new DashboardWidgetRenderSourceCompiler($this->registry());

        try {
            $compiler->compile($this->definition(includeRenderSource: false));
            self::fail('Expected missing Dashboard Widget render source to be rejected.');
        } catch (InvalidArgumentException) {
            self::assertTrue(true);
        }

        $valid = $this->renderSource();
        foreach ([
            [],
            $valid + ['provider' => 'unsafe'],
            array_replace($valid, ['kind' => 'provider']),
            array_replace($valid, ['blueprint_id' => strtoupper(self::BLUEPRINT_ID)]),
            array_replace($valid, ['blueprint_revision' => 0]),
            array_replace($valid, ['blueprint_revision' => '2']),
            array_replace($valid, ['bindings' => ['not-an-envelope']]),
        ] as $renderSource) {
            try {
                $compiler->compile($this->definition(renderSource: $renderSource));
                self::fail('Expected malformed Dashboard Widget render source to be rejected.');
            } catch (InvalidArgumentException) {
                self::assertTrue(true);
            }
        }
    }

    public function testRejectsMissingBlueprintExactRevisionAndCrossSurfaceOwnership(): void
    {
        try {
            (new DashboardWidgetRenderSourceCompiler(new ComponentBlueprintRegistry()))->compile($this->definition());
            self::fail('Expected missing Blueprint to be rejected.');
        } catch (InvalidArgumentException) {
            self::assertTrue(true);
        }

        $wrongRevisionRegistry = new ComponentBlueprintRegistry();
        $wrongRevisionRegistry->register($this->blueprint(revision: 3));
        try {
            (new DashboardWidgetRenderSourceCompiler($wrongRevisionRegistry))->compile($this->definition());
            self::fail('Expected exact-revision lookup to reject fallback.');
        } catch (InvalidArgumentException) {
            self::assertTrue(true);
        }

        $crossSurfaceRegistry = new ComponentBlueprintRegistry();
        $crossSurfaceRegistry->register($this->blueprint(ownerSurfaceId: 9));
        $this->expectException(InvalidArgumentException::class);
        (new DashboardWidgetRenderSourceCompiler($crossSurfaceRegistry))->compile($this->definition());
    }

    public function testRejectsBindingKeySourceTypeAndExecutableMismatches(): void
    {
        $compiler = new DashboardWidgetRenderSourceCompiler($this->registry());

        $cases = [
            ['title' => ['source' => 'literal', 'value' => 'Orders']],
            [
                'title' => ['source' => 'literal', 'value' => 'Orders'],
                'count' => ['source' => 'literal', 'value' => 12],
                'extra' => ['source' => 'literal', 'value' => 'x'],
            ],
            [
                'title' => ['source' => 'provider', 'value' => 'Orders'],
                'count' => ['source' => 'literal', 'value' => 12],
            ],
            [
                'title' => ['source' => 'literal', 'value' => 'Orders', 'callback' => 'unsafe'],
                'count' => ['source' => 'literal', 'value' => 12],
            ],
            [
                'title' => ['source' => 'literal', 'value' => 12],
                'count' => ['source' => 'literal', 'value' => 12],
            ],
            [
                'title' => ['source' => 'literal', 'value' => '<script>alert(1)</script>'],
                'count' => ['source' => 'literal', 'value' => 12],
            ],
            [
                'title' => ['source' => 'literal', 'value' => 'Orders'],
                'count' => ['source' => 'literal', 'value' => null],
            ],
        ];

        foreach ($cases as $bindings) {
            try {
                $compiler->compile($this->definition(bindings: $bindings));
                self::fail('Expected unsafe or incompatible Dashboard Widget render binding to be rejected.');
            } catch (InvalidArgumentException) {
                self::assertTrue(true);
            }
        }
    }

    private function registry(): ComponentBlueprintRegistry
    {
        $registry = new ComponentBlueprintRegistry();
        $registry->register($this->blueprint());
        return $registry;
    }

    private function blueprint(
        int $ownerSurfaceId = DashboardWidgetDefinition::OWNER_SURFACE_ID,
        int $revision = 2,
    ): ComponentBlueprintDescriptor {
        return new ComponentBlueprintDescriptor(
            id: self::BLUEPRINT_ID,
            revision: $revision,
            ownerSurfaceId: $ownerSurfaceId,
            componentType: 'dashboard.metrics',
            bindingSchema: ['title' => 'string', 'count' => 'int'],
        );
    }

    /**
     * @param array<string,mixed>|null $renderSource
     * @param array<string,mixed>|null $bindings
     */
    private function definition(
        int $ownerSurfaceId = DashboardWidgetDefinition::OWNER_SURFACE_ID,
        string $type = DashboardWidgetDefinition::TYPE,
        int $schemaVersion = 1,
        DefinitionStatus $status = DefinitionStatus::Published,
        string $contentType = 'rich_text',
        ?array $renderSource = null,
        ?array $bindings = null,
        bool $includeRenderSource = true,
    ): Definition {
        $widget = ['type' => $contentType];
        if ($includeRenderSource) {
            $widget['render_source'] = $renderSource ?? $this->renderSource($bindings);
        }

        return new Definition(
            id: '11111111-1111-4111-8111-111111111111',
            slug: 'sales-overview',
            type: $type,
            schemaVersion: $schemaVersion,
            ownerSurfaceId: $ownerSurfaceId,
            status: $status,
            payload: ['widget' => $widget],
            revision: 3,
            dependencies: [],
        );
    }

    /**
     * @param array<string,mixed>|null $bindings
     * @return array<string,mixed>
     */
    private function renderSource(?array $bindings = null): array
    {
        return [
            'kind' => 'component_blueprint',
            'blueprint_id' => self::BLUEPRINT_ID,
            'blueprint_revision' => 2,
            'bindings' => $bindings ?? [
                'title' => ['source' => 'literal', 'value' => 'Orders'],
                'count' => ['source' => 'literal', 'value' => 12],
            ],
        ];
    }
}
