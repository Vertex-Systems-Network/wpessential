<?php

declare(strict_types=1);

namespace WPEssential\Tests\Unit\Modules\DashboardWidgets;

use InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use WPEssential\Modules\DashboardWidgets\DashboardWidgetComponentBlueprintCatalog;
use WPEssential\Modules\DashboardWidgets\DashboardWidgetContentClassCompiler;
use WPEssential\Modules\DashboardWidgets\DashboardWidgetDefinition;
use WPEssential\Modules\DashboardWidgets\DashboardWidgetRegistrationCompiler;
use WPEssential\Modules\DashboardWidgets\DashboardWidgetRegistrationDescriptor;
use WPEssential\Modules\DashboardWidgets\DashboardWidgetRenderSourceCompiler;
use WPEssential\Modules\DashboardWidgets\DashboardWidgetVisibilityCompiler;
use WPEssential\Platform\Components\ComponentBlueprintRegistry;
use WPEssential\Platform\Definitions\Definition;
use WPEssential\Platform\Definitions\DefinitionStatus;

final class DashboardWidgetRegistrationCompilerTest extends TestCase
{
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
        self::assertFalse($descriptor->defaultHidden);
        self::assertFalse($descriptor->defaultCollapsed);
        self::assertNull($descriptor->siteScope);
        self::assertSame([], $descriptor->siteIds);
    }

    public function testCompilesBoundedNativeDefaultHiddenState(): void
    {
        $widget = $this->widget();
        $widget['inventory'] = ['default_hidden' => true];

        $descriptor = $this->compiler()->compile($this->definition(widget: $widget));

        self::assertTrue($descriptor->defaultHidden);

        $emptyInventory = $this->widget();
        $emptyInventory['inventory'] = [];
        self::assertFalse(
            $this->compiler()->compile($this->definition(widget: $emptyInventory))->defaultHidden,
        );
    }

    public function testRejectsMalformedNativeDefaultHiddenMetadata(): void
    {
        $valid = $this->widget();

        foreach ([
            array_replace($valid, ['inventory' => 'hidden']),
            array_replace($valid, ['inventory' => [true]]),
            array_replace($valid, ['inventory' => ['unknown' => true]]),
            array_replace($valid, ['inventory' => ['default_hidden' => 1]]),
            array_replace($valid, ['inventory' => ['default_hidden' => 'yes']]),
            array_replace($valid, ['inventory' => ['default_hidden' => null]]),
        ] as $widget) {
            try {
                $this->compiler()->compile($this->definition(widget: $widget));
                self::fail('Expected malformed Dashboard Widget default-hidden metadata to be rejected.');
            } catch (InvalidArgumentException) {
                self::assertTrue(true);
            }
        }
    }

    public function testAcceptsBoundedNativeCollapsibleCapability(): void
    {
        $widget = $this->widget();
        $widget['presentation'] = ['collapsible' => true];

        $descriptor = $this->compiler()->compile($this->definition(widget: $widget));

        self::assertFalse($descriptor->defaultCollapsed);

        $combined = $this->widget();
        $combined['presentation'] = [
            'collapsible' => true,
            'default_collapsed' => true,
        ];
        self::assertTrue(
            $this->compiler()->compile($this->definition(widget: $combined))->defaultCollapsed,
        );
    }

    public function testRejectsUnsupportedOrMalformedNativeCollapsibleMetadata(): void
    {
        $valid = $this->widget();

        foreach ([
            array_replace($valid, ['presentation' => ['collapsible' => false]]),
            array_replace($valid, ['presentation' => ['collapsible' => 1]]),
            array_replace($valid, ['presentation' => ['collapsible' => 'yes']]),
            array_replace($valid, ['presentation' => ['collapsible' => null]]),
        ] as $widget) {
            try {
                $this->compiler()->compile($this->definition(widget: $widget));
                self::fail('Expected unsupported or malformed Dashboard Widget collapsible metadata to be rejected.');
            } catch (InvalidArgumentException) {
                self::assertTrue(true);
            }
        }
    }

    public function testCompilesBoundedNativeDefaultCollapsedState(): void
    {
        $widget = $this->widget();
        $widget['presentation'] = ['default_collapsed' => true];

        $descriptor = $this->compiler()->compile($this->definition(widget: $widget));

        self::assertTrue($descriptor->defaultCollapsed);

        $emptyPresentation = $this->widget();
        $emptyPresentation['presentation'] = [];
        self::assertFalse(
            $this->compiler()->compile($this->definition(widget: $emptyPresentation))->defaultCollapsed,
        );
    }

    public function testRejectsMalformedNativeDefaultCollapsedMetadata(): void
    {
        $valid = $this->widget();

        foreach ([
            array_replace($valid, ['presentation' => 'closed']),
            array_replace($valid, ['presentation' => [true]]),
            array_replace($valid, ['presentation' => ['unknown' => true]]),
            array_replace($valid, ['presentation' => ['default_collapsed' => 1]]),
            array_replace($valid, ['presentation' => ['default_collapsed' => 'yes']]),
            array_replace($valid, ['presentation' => ['default_collapsed' => null]]),
        ] as $widget) {
            try {
                $this->compiler()->compile($this->definition(widget: $widget));
                self::fail('Expected malformed Dashboard Widget default-collapsed metadata to be rejected.');
            } catch (InvalidArgumentException) {
                self::assertTrue(true);
            }
        }
    }

    public function testCompilesAndNormalizesBoundedSiteTargeting(): void
    {
        $siteTarget = $this->widget();
        $siteTarget['network_dashboard'] = false;
        $siteTarget['target'] = [
            'scope' => 'site_ids',
            'site_ids' => [11, 3, 11, 7],
            'network_dashboard' => false,
        ];

        $descriptor = $this->compiler()->compile($this->definition(widget: $siteTarget));

        self::assertFalse($descriptor->networkDashboard);
        self::assertSame(DashboardWidgetRegistrationDescriptor::SITE_SCOPE_SITE_IDS, $descriptor->siteScope);
        self::assertSame([3, 7, 11], $descriptor->siteIds);
        self::assertTrue($descriptor->isEligibleForSite(7));
        self::assertFalse($descriptor->isEligibleForSite(9));

        $allSites = $this->widget();
        $allSites['network_dashboard'] = false;
        $allSites['target'] = ['scope' => 'all_sites'];
        $allSitesDescriptor = $this->compiler()->compile($this->definition(widget: $allSites));
        self::assertSame(DashboardWidgetRegistrationDescriptor::SITE_SCOPE_ALL_SITES, $allSitesDescriptor->siteScope);
        self::assertSame([], $allSitesDescriptor->siteIds);
        self::assertTrue($allSitesDescriptor->isEligibleForSite(999));

        $defaultSite = $this->widget();
        $defaultSite['network_dashboard'] = false;
        $defaultDescriptor = $this->compiler()->compile($this->definition(widget: $defaultSite));
        self::assertNull($defaultDescriptor->siteScope);
        self::assertSame([], $defaultDescriptor->siteIds);
        self::assertTrue($defaultDescriptor->isEligibleForSite(11));

        $emptyTarget = $defaultSite;
        $emptyTarget['target'] = [];
        $emptyTargetDescriptor = $this->compiler()->compile($this->definition(widget: $emptyTarget));
        self::assertNull($emptyTargetDescriptor->siteScope);
        self::assertSame([], $emptyTargetDescriptor->siteIds);
        self::assertTrue($emptyTargetDescriptor->isEligibleForSite(11));

        $nestedNetwork = $this->widget();
        unset($nestedNetwork['network_dashboard']);
        $nestedNetwork['target'] = ['network_dashboard' => true];
        $nestedNetworkDescriptor = $this->compiler()->compile($this->definition(widget: $nestedNetwork));
        self::assertTrue($nestedNetworkDescriptor->networkDashboard);
        self::assertNull($nestedNetworkDescriptor->siteScope);
        self::assertFalse($nestedNetworkDescriptor->isEligibleForSite(11));
    }

    public function testRejectsMalformedOrConflictingSiteTargeting(): void
    {
        $compiler = $this->compiler();
        $valid = $this->widget();
        $valid['network_dashboard'] = false;

        $overBound = array_fill(0, DashboardWidgetRegistrationDescriptor::MAX_SITE_IDS + 1, 1);
        $cases = [
            array_replace($valid, ['target' => ['unknown' => true]]),
            array_replace($valid, ['target' => ['scope' => 'current_site']]),
            array_replace($valid, ['target' => ['site_ids' => [11]]]),
            array_replace($valid, ['target' => ['scope' => 'site_ids', 'site_ids' => []]]),
            array_replace($valid, ['target' => ['scope' => 'site_ids', 'site_ids' => '11']]),
            array_replace($valid, ['target' => ['scope' => 'site_ids', 'site_ids' => [11, '12']]]),
            array_replace($valid, ['target' => ['scope' => 'site_ids', 'site_ids' => [11, 12.0]]]),
            array_replace($valid, ['target' => ['scope' => 'site_ids', 'site_ids' => [11, true]]]),
            array_replace($valid, ['target' => ['scope' => 'site_ids', 'site_ids' => [11, null]]]),
            array_replace($valid, ['target' => ['scope' => 'site_ids', 'site_ids' => [0]]]),
            array_replace($valid, ['target' => ['scope' => 'site_ids', 'site_ids' => [-1]]]),
            array_replace($valid, ['target' => ['scope' => 'site_ids', 'site_ids' => $overBound]]),
            array_replace($valid, ['target' => ['scope' => 'all_sites', 'site_ids' => []]]),
            array_replace($valid, ['target' => ['scope' => 'all_sites', 'site_ids' => [11]]]),
            array_replace($valid, ['target' => ['scope' => 'all_sites', 'network_dashboard' => true]]),
            array_replace($valid, ['network_dashboard' => true, 'target' => ['network_dashboard' => false]]),
        ];

        foreach ($cases as $widget) {
            try {
                $compiler->compile($this->definition(widget: $widget));
                self::fail('Expected malformed Dashboard Widget site targeting to be rejected.');
            } catch (InvalidArgumentException) {
                self::assertTrue(true);
            }
        }
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

    public function testRegistrationCompilationRejectsCrossClassCanonicalBlueprintMismatch(): void
    {
        $catalog = new DashboardWidgetComponentBlueprintCatalog();
        $kpi = $catalog->forContentType('kpi');
        self::assertNotNull($kpi);

        $widget = $this->widget();
        $widget['render_source'] = [
            'kind' => 'component_blueprint',
            'blueprint_id' => $kpi->id,
            'blueprint_revision' => $kpi->revision,
            'bindings' => [
                'label' => ['source' => 'literal', 'value' => 'Orders'],
                'value' => ['source' => 'literal', 'value' => '12'],
            ],
        ];

        $this->expectException(InvalidArgumentException::class);
        $this->compiler()->compile($this->definition(widget: $widget));
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
                ['bindings' => ['content' => ['source' => 'provider', 'value' => 'Orders']]],
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
        $catalog = new DashboardWidgetComponentBlueprintCatalog();
        $registry = new ComponentBlueprintRegistry();
        foreach ($catalog->all() as $blueprint) {
            $registry->register($blueprint);
        }

        $contentClassCompiler = new DashboardWidgetContentClassCompiler();

        return new DashboardWidgetRegistrationCompiler(
            new DashboardWidgetVisibilityCompiler(),
            $contentClassCompiler,
            new DashboardWidgetRenderSourceCompiler(
                $registry,
                $contentClassCompiler,
                $catalog,
            ),
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
        $blueprint = (new DashboardWidgetComponentBlueprintCatalog())->forContentType('rich_text');
        self::assertNotNull($blueprint);

        return [
            'kind' => 'component_blueprint',
            'blueprint_id' => $blueprint->id,
            'blueprint_revision' => $blueprint->revision,
            'bindings' => [
                'content' => ['source' => 'literal', 'value' => 'Orders'],
            ],
        ];
    }
}
