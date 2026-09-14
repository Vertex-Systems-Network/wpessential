<?php

declare(strict_types=1);

namespace WPEssential\Tests\Unit\Modules\AdminMenu;

use InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use WPEssential\Contracts\CapabilityCheckerInterface;
use WPEssential\Kernel\ServiceRegistry;
use WPEssential\Modules\AdminMenu\AdminMenuDefinition;
use WPEssential\Modules\AdminMenu\AdminMenuModule;
use WPEssential\Modules\AdminMenu\AdminMenuReadAbilityHandler;
use WPEssential\Modules\AdminMenu\AdminMenuReadService;
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

final class AdminMenuModuleTest extends TestCase
{
    public function testModuleRegistersBoundedReadOnlyAbilitiesAndBridgeExposure(): void
    {
        $definitions = new InMemoryDefinitionRepository();
        $services = new ServiceRegistry();
        $abilities = new AbilityRegistry(new PolicyEngine(new class implements CapabilityCheckerInterface {
            public function can(ExecutionContext $context, string $capability): bool
            {
                return true;
            }
        }));
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

        (new AdminMenuModule())->register($services);

        self::assertInstanceOf(AdminMenuReadService::class, $services->get(AdminMenuModule::SERVICE_READ));
        foreach ([AdminMenuModule::ABILITY_GET, AdminMenuModule::ABILITY_CATALOG] as $name) {
            $descriptor = $abilities->descriptor($name);
            self::assertNotNull($descriptor);
            self::assertFalse($descriptor->mutates);
            self::assertSame(AdminMenuDefinition::OWNER_SURFACE_ID, $descriptor->ownerSurfaceId);
            self::assertSame(AdminMenuModule::CAPABILITY, $descriptor->capability);
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
            slug: 'primary-menu',
            type: AdminMenuDefinition::TYPE,
            schemaVersion: 1,
            ownerSurfaceId: AdminMenuDefinition::OWNER_SURFACE_ID,
            status: DefinitionStatus::Published,
            payload: ['scope' => 'site'],
            revision: 1,
            dependencies: [],
        ));
        $service = new AdminMenuReadService($definitions);
        $context = new ExecutionContext(new Principal(1), 1);

        $get = new AdminMenuReadAbilityHandler($service, AdminMenuReadAbilityHandler::GET);
        $item = $get->handle(['id' => '11111111-1111-4111-8111-111111111111'], $context);
        self::assertSame('primary-menu', $item['slug']);

        $catalog = new AdminMenuReadAbilityHandler($service, AdminMenuReadAbilityHandler::CATALOG);
        self::assertSame(['primary-menu'], array_column($catalog->handle([], $context), 'slug'));
    }

    public function testHandlerRejectsMalformedGetInput(): void
    {
        $handler = new AdminMenuReadAbilityHandler(
            new AdminMenuReadService(new InMemoryDefinitionRepository()),
            AdminMenuReadAbilityHandler::GET,
        );

        $this->expectException(InvalidArgumentException::class);
        $handler->handle(['id' => '   '], new ExecutionContext(new Principal(1), 1));
    }
}
