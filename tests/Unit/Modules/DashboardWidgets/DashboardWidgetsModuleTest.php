<?php

declare(strict_types=1);

namespace WPEssential\Tests\Unit\Modules\DashboardWidgets;

use InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use WPEssential\Contracts\CapabilityCheckerInterface;
use WPEssential\Contracts\QueryReadConsumerInterface;
use WPEssential\Kernel\ServiceRegistry;
use WPEssential\Modules\DashboardWidgets\DashboardWidgetComponentBlueprintCatalog;
use WPEssential\Modules\DashboardWidgets\DashboardWidgetComponentRegistrar;
use WPEssential\Modules\DashboardWidgets\DashboardWidgetContentClassCompiler;
use WPEssential\Modules\DashboardWidgets\DashboardWidgetDefinition;
use WPEssential\Modules\DashboardWidgets\DashboardWidgetQueryBindingExecutor;
use WPEssential\Modules\DashboardWidgets\DashboardWidgetRegistrationCompiler;
use WPEssential\Modules\DashboardWidgets\DashboardWidgetRuntimeRenderExecutor;
use WPEssential\Modules\DashboardWidgets\DashboardWidgetRenderSourceCompiler;
use WPEssential\Modules\DashboardWidgets\DashboardWidgetTrustedComponentRenderer;
use WPEssential\Modules\DashboardWidgets\DashboardWidgetVisibilityCompiler;
use WPEssential\Modules\DashboardWidgets\DashboardWidgetVisibilityEvaluator;
use WPEssential\Modules\DashboardWidgets\DashboardWidgetWordPressAdapter;
use WPEssential\Modules\DashboardWidgets\DashboardWidgetWordPressEnvironmentInterface;
use WPEssential\Modules\DashboardWidgets\DashboardWidgetsModule;
use WPEssential\Modules\DashboardWidgets\DashboardWidgetsReadAbilityHandler;
use WPEssential\Modules\DashboardWidgets\DashboardWidgetsReadService;
use WPEssential\Modules\Query\QueryModule;
use WPEssential\Platform\Abilities\AbilityRegistry;
use WPEssential\Platform\Auth\ExecutionChannel;
use WPEssential\Platform\Auth\ExecutionContext;
use WPEssential\Platform\Auth\PolicyEngine;
use WPEssential\Platform\Auth\Principal;
use WPEssential\Platform\Components\ComponentBlueprintRegistry;
use WPEssential\Platform\DataSources\DataSourceRegistry;
use WPEssential\Platform\Definitions\Definition;
use WPEssential\Platform\Definitions\DefinitionStatus;
use WPEssential\Platform\Definitions\InMemoryDefinitionRepository;
use WPEssential\Platform\Rendering\BlueprintRendererDispatcher;
use WPEssential\Platform\Rendering\RenderingServiceRegistrar;
use WPEssential\Platform\WordPress\Abilities\WordPressAbilityBridge;
use WPEssential\Platform\WordPress\Abilities\WordPressAbilityEnvironmentInterface;
use WPEssential\Platform\WordPress\Abilities\WordPressExecutionContextFactory;
use WPEssential\Platform\WordPress\Auth\WordPressAuthorizationServices;

final class DashboardWidgetsModuleTest extends TestCase
{
    public function testManifestRemainsProOwned(): void
    {
        $manifest = (new DashboardWidgetsModule())->manifest();

        self::assertSame('dashboard-widgets', $manifest->id);
        self::assertSame('Dashboard Widgets', $manifest->name);
        self::assertSame('pro', $manifest->edition);
    }

    public function testModuleRegistersBoundedReadOnlyAbilitiesAndTrustedComponentServices(): void
    {
        $definitions = new InMemoryDefinitionRepository();
        $services = new ServiceRegistry();
        $capabilityChecker = new class implements CapabilityCheckerInterface {
            public function can(ExecutionContext $context, string $capability): bool
            {
                return true;
            }
        };
        $abilities = new AbilityRegistry(new PolicyEngine($capabilityChecker));
        $environment = $this->environment();
        $bridge = new WordPressAbilityBridge(
            $abilities,
            $environment,
            new WordPressExecutionContextFactory($environment),
        );

        $services->set('platform.definitions', $definitions);
        $services->set('platform.abilities', $abilities);
        $services->set('platform.abilities.wordpress', $bridge);
        $services->set(WordPressAuthorizationServices::CAPABILITY_CHECKER, $capabilityChecker);
        (new RenderingServiceRegistrar())->register($services);
        $this->addQueryServices($services);

        (new DashboardWidgetsModule())->register($services);

        self::assertInstanceOf(DashboardWidgetsReadService::class, $services->get(DashboardWidgetsModule::SERVICE_READ));
        self::assertInstanceOf(DashboardWidgetContentClassCompiler::class, $services->get(DashboardWidgetsModule::SERVICE_CONTENT_CLASS_COMPILER));
        self::assertInstanceOf(DashboardWidgetRenderSourceCompiler::class, $services->get(DashboardWidgetsModule::SERVICE_RENDER_SOURCE_COMPILER));
        self::assertInstanceOf(DashboardWidgetQueryBindingExecutor::class, $services->get(DashboardWidgetsModule::SERVICE_QUERY_BINDING_EXECUTOR));
        self::assertInstanceOf(DashboardWidgetComponentBlueprintCatalog::class, $services->get(DashboardWidgetsModule::SERVICE_COMPONENT_CATALOG));
        self::assertInstanceOf(DashboardWidgetTrustedComponentRenderer::class, $services->get(DashboardWidgetsModule::SERVICE_TRUSTED_COMPONENT_RENDERER));
        self::assertInstanceOf(DashboardWidgetComponentRegistrar::class, $services->get(DashboardWidgetsModule::SERVICE_COMPONENT_REGISTRAR));
        self::assertInstanceOf(DashboardWidgetRegistrationCompiler::class, $services->get(DashboardWidgetsModule::SERVICE_REGISTRATION_COMPILER));
        self::assertInstanceOf(DashboardWidgetVisibilityCompiler::class, $services->get(DashboardWidgetsModule::SERVICE_VISIBILITY_COMPILER));
        self::assertInstanceOf(DashboardWidgetVisibilityEvaluator::class, $services->get(DashboardWidgetsModule::SERVICE_VISIBILITY_EVALUATOR));
        self::assertInstanceOf(DashboardWidgetRuntimeRenderExecutor::class, $services->get(DashboardWidgetsModule::SERVICE_RUNTIME_RENDER_EXECUTOR));
        self::assertInstanceOf(DashboardWidgetWordPressAdapter::class, $services->get(DashboardWidgetsModule::SERVICE_WORDPRESS_ADAPTER));

        $registry = $services->get(RenderingServiceRegistrar::SERVICE_BLUEPRINTS);
        self::assertInstanceOf(ComponentBlueprintRegistry::class, $registry);
        $catalog = $services->get(DashboardWidgetsModule::SERVICE_COMPONENT_CATALOG);
        self::assertInstanceOf(DashboardWidgetComponentBlueprintCatalog::class, $catalog);
        foreach ($catalog->all() as $blueprint) {
            self::assertEquals($blueprint, $registry->get($blueprint->id, $blueprint->revision));
        }

        self::assertInstanceOf(
            BlueprintRendererDispatcher::class,
            $services->get(RenderingServiceRegistrar::SERVICE_RENDERER),
        );

        $registrationCompiler = $services->get(DashboardWidgetsModule::SERVICE_REGISTRATION_COMPILER);
        self::assertInstanceOf(DashboardWidgetRegistrationCompiler::class, $registrationCompiler);

        $richText = $catalog->forContentType('rich_text');
        $kpi = $catalog->forContentType('kpi');
        self::assertNotNull($richText);
        self::assertNotNull($kpi);

        $validDefinition = new Definition(
            id: '22222222-2222-4222-8222-222222222222',
            slug: 'valid-rich-text',
            type: DashboardWidgetDefinition::TYPE,
            schemaVersion: 1,
            ownerSurfaceId: DashboardWidgetDefinition::OWNER_SURFACE_ID,
            status: DefinitionStatus::Published,
            payload: [
                'widget' => [
                    'key' => 'valid-rich-text',
                    'title' => 'Valid Rich Text',
                    'type' => 'rich_text',
                    'context' => 'normal',
                    'priority' => 'default',
                    'network_dashboard' => false,
                    'render_source' => [
                        'kind' => 'component_blueprint',
                        'blueprint_id' => $richText->id,
                        'blueprint_revision' => $richText->revision,
                        'bindings' => [
                            'content' => ['source' => 'literal', 'value' => 'Safe'],
                        ],
                    ],
                ],
            ],
            revision: 1,
            dependencies: [],
        );
        self::assertInstanceOf(
            DashboardWidgetRegistrationCompiler::class,
            $registrationCompiler,
        );
        $registrationCompiler->compile($validDefinition);

        $mismatchDefinition = new Definition(
            id: '33333333-3333-4333-8333-333333333333',
            slug: 'mismatched-rich-text',
            type: DashboardWidgetDefinition::TYPE,
            schemaVersion: 1,
            ownerSurfaceId: DashboardWidgetDefinition::OWNER_SURFACE_ID,
            status: DefinitionStatus::Published,
            payload: [
                'widget' => [
                    'key' => 'mismatched-rich-text',
                    'title' => 'Mismatched Rich Text',
                    'type' => 'rich_text',
                    'context' => 'normal',
                    'priority' => 'default',
                    'network_dashboard' => false,
                    'render_source' => [
                        'kind' => 'component_blueprint',
                        'blueprint_id' => $kpi->id,
                        'blueprint_revision' => $kpi->revision,
                        'bindings' => [
                            'label' => ['source' => 'literal', 'value' => 'Orders'],
                            'value' => ['source' => 'literal', 'value' => '12'],
                        ],
                    ],
                ],
            ],
            revision: 1,
            dependencies: [],
        );
        try {
            $registrationCompiler->compile($mismatchDefinition);
            self::fail('Expected module-wired registration compiler to reject cross-class Blueprint mismatch.');
        } catch (InvalidArgumentException) {
            self::assertTrue(true);
        }

        foreach ([DashboardWidgetsModule::ABILITY_GET, DashboardWidgetsModule::ABILITY_CATALOG] as $name) {
            $descriptor = $abilities->descriptor($name);
            self::assertNotNull($descriptor);
            self::assertFalse($descriptor->mutates);
            self::assertSame(DashboardWidgetDefinition::OWNER_SURFACE_ID, $descriptor->ownerSurfaceId);
            self::assertSame(DashboardWidgetsModule::CAPABILITY, $descriptor->capability);
            self::assertTrue($descriptor->allows(ExecutionChannel::Internal));
            self::assertTrue($descriptor->allows(ExecutionChannel::Ui));
            self::assertTrue($descriptor->allows(ExecutionChannel::Rest));
        }

        self::assertCount(2, $bridge->registerAbilities());
    }

    public function testModuleBootRegistersWordPressDashboardHooksThroughAdapterService(): void
    {
        $dashboardEnvironment = new class implements DashboardWidgetWordPressEnvironmentInterface {
            /** @var list<string> */
            public array $hooks = [];
            /** @var list<string> */
            public array $filters = [];
            public function registerAction(string $hook, callable $callback): void { $this->hooks[] = $hook; }
            public function registerFilter(string $hook, callable $callback, int $acceptedArgs = 1): void
            {
                $this->filters[] = $hook;
            }
            public function registerDashboardWidget(string $id, string $title, callable $callback, string $context, string $priority): void {}
            public function currentUserId(): ?int { return 1; }
            public function currentSiteId(): int { return 1; }
            public function currentNetworkId(): ?int { return null; }
            public function screenId(mixed $screen): ?string
            {
                return is_object($screen) && isset($screen->id) && is_string($screen->id)
                    ? $screen->id
                    : null;
            }
            public function hasClosedPostboxPreference(string $screenId): bool { return false; }
            public function outputTrustedHtml(string $html): void {}
        };
        $services = $this->baseServices();
        (new RenderingServiceRegistrar())->register($services);
        $module = new DashboardWidgetsModule($dashboardEnvironment);

        $module->register($services);
        $module->boot($services);
        $module->boot($services);

        self::assertInstanceOf(
            DashboardWidgetWordPressAdapter::class,
            $services->get(DashboardWidgetsModule::SERVICE_WORDPRESS_ADAPTER),
        );
        self::assertSame(['wp_dashboard_setup', 'wp_network_dashboard_setup'], $dashboardEnvironment->hooks);
        self::assertSame(['default_hidden_meta_boxes'], $dashboardEnvironment->filters);
    }

    public function testModuleFailsClosedWithoutCanonicalRenderingServices(): void
    {
        $services = $this->baseServices();

        $this->expectException(\LogicException::class);
        (new DashboardWidgetsModule())->register($services);
    }

    public function testModuleFailsClosedWhenRendererServiceIsMissing(): void
    {
        $services = $this->baseServices();
        $services->set(RenderingServiceRegistrar::SERVICE_BLUEPRINTS, new ComponentBlueprintRegistry());

        $this->expectException(\LogicException::class);
        (new DashboardWidgetsModule())->register($services);
    }

    public function testHandlerDelegatesGetAndCatalogWithoutMutation(): void
    {
        $definitions = new InMemoryDefinitionRepository();
        $definitions->save(new Definition(
            id: '11111111-1111-4111-8111-111111111111',
            slug: 'primary-widget',
            type: DashboardWidgetDefinition::TYPE,
            schemaVersion: 1,
            ownerSurfaceId: DashboardWidgetDefinition::OWNER_SURFACE_ID,
            status: DefinitionStatus::Published,
            payload: ['type' => 'kpi', 'context' => 'normal'],
            revision: 1,
            dependencies: [],
        ));
        $service = new DashboardWidgetsReadService($definitions);
        $context = new ExecutionContext(new Principal(1), 1);

        $get = new DashboardWidgetsReadAbilityHandler($service, DashboardWidgetsReadAbilityHandler::GET);
        $item = $get->handle(['id' => '11111111-1111-4111-8111-111111111111'], $context);
        self::assertSame('primary-widget', $item['slug']);

        $catalog = new DashboardWidgetsReadAbilityHandler($service, DashboardWidgetsReadAbilityHandler::CATALOG);
        self::assertSame(['primary-widget'], array_column($catalog->handle([], $context), 'slug'));
    }

    public function testHandlerRejectsMalformedOrMutationShapedInput(): void
    {
        $service = new DashboardWidgetsReadService(new InMemoryDefinitionRepository());
        $context = new ExecutionContext(new Principal(1), 1);

        try {
            (new DashboardWidgetsReadAbilityHandler($service, DashboardWidgetsReadAbilityHandler::GET))
                ->handle(['id' => '   '], $context);
            self::fail('Expected malformed get input to be rejected.');
        } catch (InvalidArgumentException) {
            self::assertTrue(true);
        }

        $this->expectException(InvalidArgumentException::class);
        (new DashboardWidgetsReadAbilityHandler($service, DashboardWidgetsReadAbilityHandler::CATALOG))
            ->handle(['save' => true], $context);
    }

    private function baseServices(): ServiceRegistry
    {
        $services = new ServiceRegistry();
        $capabilityChecker = new class implements CapabilityCheckerInterface {
            public function can(ExecutionContext $context, string $capability): bool
            {
                return true;
            }
        };
        $abilities = new AbilityRegistry(new PolicyEngine($capabilityChecker));
        $environment = $this->environment();

        $services->set('platform.definitions', new InMemoryDefinitionRepository());
        $services->set('platform.abilities', $abilities);
        $services->set(
            'platform.abilities.wordpress',
            new WordPressAbilityBridge($abilities, $environment, new WordPressExecutionContextFactory($environment)),
        );
        $services->set(WordPressAuthorizationServices::CAPABILITY_CHECKER, $capabilityChecker);
        $this->addQueryServices($services);

        return $services;
    }


    private function addQueryServices(ServiceRegistry $services): void
    {
        $services->set('platform.data-sources', new DataSourceRegistry());
        $services->set(QueryModule::SERVICE_READ_CONSUMER, new class implements QueryReadConsumerInterface {
            public function describe(string $sourceRef, ExecutionContext $context): array
            {
                return [];
            }

            public function read(array $request, ExecutionContext $context): array
            {
                return [
                    'contract_version' => self::CONTRACT_VERSION,
                    'ok' => false,
                    'source_ref' => '',
                    'projection' => [],
                    'rows' => [],
                    'returned' => 0,
                    'error' => null,
                ];
            }
        });
    }

    private function environment(): WordPressAbilityEnvironmentInterface
    {
        return new class implements WordPressAbilityEnvironmentInterface {
            public function abilitiesApiAvailable(): bool { return true; }
            public function doingAction(string $hook): bool { return $hook === 'wp_abilities_api_init'; }
            public function currentUserId(): ?int { return 1; }
            public function currentSiteId(): int { return 1; }
            public function currentNetworkId(): ?int { return null; }
            public function currentUserCan(string $capability): bool { return true; }
            public function isRestRequest(): bool { return false; }
            public function isCli(): bool { return false; }
            public function registerCategory(string $slug, array $args): bool { return true; }
            public function registerAbility(string $name, array $args): bool { return true; }
        };
    }
}
