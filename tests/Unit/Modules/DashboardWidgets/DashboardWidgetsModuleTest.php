<?php

declare(strict_types=1);

namespace WPEssential\Tests\Unit\Modules\DashboardWidgets;

use InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use WPEssential\Contracts\CapabilityCheckerInterface;
use WPEssential\Kernel\ServiceRegistry;
use WPEssential\Modules\DashboardWidgets\DashboardWidgetDefinition;
use WPEssential\Modules\DashboardWidgets\DashboardWidgetRegistrationCompiler;
use WPEssential\Modules\DashboardWidgets\DashboardWidgetVisibilityCompiler;
use WPEssential\Modules\DashboardWidgets\DashboardWidgetVisibilityEvaluator;
use WPEssential\Modules\DashboardWidgets\DashboardWidgetsModule;
use WPEssential\Modules\DashboardWidgets\DashboardWidgetsReadAbilityHandler;
use WPEssential\Modules\DashboardWidgets\DashboardWidgetsReadService;
use WPEssential\Platform\Abilities\AbilityRegistry;
use WPEssential\Platform\Auth\ExecutionChannel;
use WPEssential\Platform\Auth\ExecutionContext;
use WPEssential\Platform\Auth\PolicyEngine;
use WPEssential\Platform\Auth\Principal;
use WPEssential\Platform\Definitions\Definition;
use WPEssential\Platform\Definitions\DefinitionStatus;
use WPEssential\Platform\Definitions\InMemoryDefinitionRepository;
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

    public function testModuleRegistersBoundedReadOnlyAbilitiesAndBridgeExposure(): void
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
        $environment = new class implements WordPressAbilityEnvironmentInterface {
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
        $bridge = new WordPressAbilityBridge(
            $abilities,
            $environment,
            new WordPressExecutionContextFactory($environment),
        );

        $services->set('platform.definitions', $definitions);
        $services->set('platform.abilities', $abilities);
        $services->set('platform.abilities.wordpress', $bridge);
        $services->set(WordPressAuthorizationServices::CAPABILITY_CHECKER, $capabilityChecker);

        (new DashboardWidgetsModule())->register($services);

        self::assertInstanceOf(
            DashboardWidgetsReadService::class,
            $services->get(DashboardWidgetsModule::SERVICE_READ),
        );

        self::assertInstanceOf(
            DashboardWidgetRegistrationCompiler::class,
            $services->get(DashboardWidgetsModule::SERVICE_REGISTRATION_COMPILER),
        );

        self::assertInstanceOf(
            DashboardWidgetVisibilityCompiler::class,
            $services->get(DashboardWidgetsModule::SERVICE_VISIBILITY_COMPILER),
        );

        self::assertInstanceOf(
            DashboardWidgetVisibilityEvaluator::class,
            $services->get(DashboardWidgetsModule::SERVICE_VISIBILITY_EVALUATOR),
        );

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
}
