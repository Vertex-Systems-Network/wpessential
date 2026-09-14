<?php

declare(strict_types=1);

namespace WPEssential\Tests\Unit\Modules\Settings;

use InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use WPEssential\Contracts\CapabilityCheckerInterface;
use WPEssential\Kernel\ServiceRegistry;
use WPEssential\Modules\Settings\SettingsDefinition;
use WPEssential\Modules\Settings\SettingsModule;
use WPEssential\Modules\Settings\SettingsReadAbilityHandler;
use WPEssential\Modules\Settings\SettingsReadService;
use WPEssential\Platform\Abilities\AbilityRegistry;
use WPEssential\Platform\Auth\ExecutionChannel;
use WPEssential\Platform\Auth\ExecutionContext;
use WPEssential\Platform\Auth\PolicyEngine;
use WPEssential\Platform\Auth\Principal;
use WPEssential\Platform\Definitions\InMemoryDefinitionRepository;
use WPEssential\Platform\WordPress\Abilities\WordPressAbilityBridge;
use WPEssential\Platform\WordPress\Abilities\WordPressAbilityEnvironmentInterface;
use WPEssential\Platform\WordPress\Abilities\WordPressExecutionContextFactory;

final class SettingsModuleTest extends TestCase
{
    public function testModuleRegistersBoundedReadOnlyAbilitiesAndBridgeExposure(): void
    {
        $definitions = new InMemoryDefinitionRepository();
        $services = new ServiceRegistry();
        $abilities = new AbilityRegistry(new PolicyEngine(new class implements CapabilityCheckerInterface {
            public function can(ExecutionContext $context, string $capability): bool { return true; }
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
        $bridge = new WordPressAbilityBridge($abilities, $environment, new WordPressExecutionContextFactory($environment));
        $services->set('platform.definitions', $definitions);
        $services->set('platform.abilities', $abilities);
        $services->set('platform.abilities.wordpress', $bridge);

        (new SettingsModule())->register($services);

        self::assertInstanceOf(SettingsReadService::class, $services->get(SettingsModule::SERVICE_READ));
        foreach ([SettingsModule::ABILITY_GET, SettingsModule::ABILITY_CATALOG] as $name) {
            $descriptor = $abilities->descriptor($name);
            self::assertNotNull($descriptor);
            self::assertFalse($descriptor->mutates);
            self::assertSame(SettingsDefinition::OWNER_SURFACE_ID, $descriptor->ownerSurfaceId);
            self::assertSame(SettingsModule::CAPABILITY, $descriptor->capability);
            self::assertTrue($descriptor->allows(ExecutionChannel::Internal));
            self::assertTrue($descriptor->allows(ExecutionChannel::Ui));
            self::assertTrue($descriptor->allows(ExecutionChannel::Rest));
        }
        self::assertCount(2, $bridge->registerAbilities());
    }

    public function testHandlerDelegatesReadOnlyGetAndCatalog(): void
    {
        $service = new SettingsReadService(new InMemoryDefinitionRepository());
        $context = new ExecutionContext(new Principal(1), 1);
        self::assertNull((new SettingsReadAbilityHandler($service, SettingsReadAbilityHandler::GET))->handle(['id' => 'missing-definition'], $context));
        self::assertSame([], (new SettingsReadAbilityHandler($service, SettingsReadAbilityHandler::CATALOG))->handle([], $context));
    }

    public function testHandlerRejectsMalformedOrMutationShapedInput(): void
    {
        $service = new SettingsReadService(new InMemoryDefinitionRepository());
        $context = new ExecutionContext(new Principal(1), 1);
        try {
            (new SettingsReadAbilityHandler($service, SettingsReadAbilityHandler::GET))->handle(['id' => '   '], $context);
            self::fail('Expected malformed get input to be rejected.');
        } catch (InvalidArgumentException) {
            self::assertTrue(true);
        }
        $this->expectException(InvalidArgumentException::class);
        (new SettingsReadAbilityHandler($service, SettingsReadAbilityHandler::CATALOG))->handle(['save' => true], $context);
    }
}
