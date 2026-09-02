<?php

declare(strict_types=1);

namespace WPEssential\Tests\Unit\Modules\Relations;

use PHPUnit\Framework\TestCase;
use WPEssential\Kernel\ServiceRegistry;
use WPEssential\Modules\Relations\RelationAdminController;
use WPEssential\Modules\Relations\RelationsModule;
use WPEssential\Platform\Abilities\AbilityRegistry;
use WPEssential\Platform\Auth\PolicyEngine;
use WPEssential\Platform\Definitions\InMemoryDefinitionRepository;
use WPEssential\Platform\WordPress\Abilities\WordPressAbilityBridge;
use WPEssential\Platform\WordPress\Abilities\WordPressAbilityEnvironmentInterface;
use WPEssential\Platform\WordPress\Abilities\WordPressCapabilityChecker;
use WPEssential\Platform\WordPress\Abilities\WordPressExecutionContextFactory;
use WPEssential\Platform\WordPress\Ajax\AjaxRouteRegistry;

final class RelationsModuleAdminRegistrationTest extends TestCase
{
    public function testBootRegistersNativeAdminControllerWithoutRequiringAssetBundle(): void
    {
        $environment = new class implements WordPressAbilityEnvironmentInterface {
            public function abilitiesApiAvailable(): bool { return true; }
            public function doingAction(string $hook): bool { return false; }
            public function currentUserId(): ?int { return 1; }
            public function currentSiteId(): int { return 1; }
            public function currentNetworkId(): ?int { return 1; }
            public function currentUserCan(string $capability): bool { return true; }
            public function isRestRequest(): bool { return false; }
            public function isCli(): bool { return false; }
            public function registerCategory(string $slug, array $args): bool { return true; }
            public function registerAbility(string $name, array $args): bool { return true; }
        };
        $contexts = new WordPressExecutionContextFactory($environment);
        $abilities = new AbilityRegistry(new PolicyEngine(new WordPressCapabilityChecker($environment)));
        $services = new ServiceRegistry();
        $services->set('platform.definitions', new InMemoryDefinitionRepository());
        $services->set('platform.abilities', $abilities);
        $services->set('platform.abilities.wordpress', new WordPressAbilityBridge($abilities, $environment, $contexts));
        $services->set('platform.abilities.contexts', $contexts);
        $services->set('platform.ajax.routes', new AjaxRouteRegistry());

        $module = new RelationsModule();
        $module->register($services);
        $module->boot($services);

        self::assertInstanceOf(RelationAdminController::class, $services->get('module.relations.admin'));
    }
}
