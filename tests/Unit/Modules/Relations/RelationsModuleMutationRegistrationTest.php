<?php

declare(strict_types=1);

namespace WPEssential\Tests\Unit\Modules\Relations;

use PHPUnit\Framework\TestCase;
use WPEssential\Contracts\MigrationStateStoreInterface;
use WPEssential\Kernel\ServiceRegistry;
use WPEssential\Modules\Relations\RelationEdgeMutationService;
use WPEssential\Modules\Relations\RelationsModule;
use WPEssential\Platform\Abilities\AbilityRegistry;
use WPEssential\Platform\Auth\PolicyEngine;
use WPEssential\Platform\Database\Migrations\MigrationCoordinator;
use WPEssential\Platform\Database\Migrations\MigrationRegistry;
use WPEssential\Platform\Database\Migrations\MigrationRunner;
use WPEssential\Platform\Definitions\InMemoryDefinitionRepository;
use WPEssential\Platform\WordPress\Abilities\WordPressAbilityBridge;
use WPEssential\Platform\WordPress\Abilities\WordPressAbilityEnvironmentInterface;
use WPEssential\Platform\WordPress\Abilities\WordPressCapabilityChecker;
use WPEssential\Platform\WordPress\Abilities\WordPressExecutionContextFactory;
use WPEssential\Platform\WordPress\Ajax\AjaxRouteRegistry;
use WPEssential\Platform\WordPress\Security\NonceOperation;

final class RelationsModuleMutationRegistrationTest extends TestCase
{
    public function testNativePersistenceRegistersTransactionalMutationAbilitiesAndRoutes(): void
    {
        $services = $this->services();
        $services->set('platform.database', new RelationEdgeDatabaseAdapter());
        $services->set('platform.database.migrations', $this->migrations());

        (new RelationsModule())->register($services);

        self::assertInstanceOf(
            RelationEdgeMutationService::class,
            $services->get('module.relations.edge-mutations'),
        );

        $abilities = $services->get('platform.abilities');
        self::assertInstanceOf(AbilityRegistry::class, $abilities);
        foreach (['connect', 'disconnect'] as $action) {
            $descriptor = $abilities->descriptor('wpessential/relations/' . $action);
            self::assertNotNull($descriptor);
            self::assertSame(4, $descriptor->ownerSurfaceId);
            self::assertTrue($descriptor->mutates);
            self::assertSame(
                ['relation_definition_id', 'from_object_id', 'to_object_id'],
                $descriptor->inputSchema['required'] ?? null,
            );
        }

        $routes = $services->get('platform.ajax.routes');
        self::assertInstanceOf(AjaxRouteRegistry::class, $routes);
        foreach (['relations.connect', 'relations.disconnect'] as $type) {
            $route = $routes->get($type);
            self::assertNotNull($route);
            self::assertSame(NonceOperation::Update, $route->operation);
            self::assertTrue($route->requiresNonce);
            self::assertFalse($route->allowGuests);
        }
    }

    public function testWithoutNativePersistenceMutationAbilitiesRemainUnavailable(): void
    {
        $services = $this->services();

        (new RelationsModule())->register($services);

        self::assertFalse($services->has('module.relations.edge-mutations'));
        $abilities = $services->get('platform.abilities');
        self::assertInstanceOf(AbilityRegistry::class, $abilities);
        self::assertNull($abilities->descriptor('wpessential/relations/connect'));
        self::assertNull($abilities->descriptor('wpessential/relations/disconnect'));

        $routes = $services->get('platform.ajax.routes');
        self::assertInstanceOf(AjaxRouteRegistry::class, $routes);
        self::assertNull($routes->get('relations.connect'));
        self::assertNull($routes->get('relations.disconnect'));
    }

    private function migrations(): MigrationCoordinator
    {
        $state = new class implements MigrationStateStoreInterface {
            public function appliedIds(): array
            {
                return [];
            }

            public function markApplied(string $id): void {}
        };
        $registry = new MigrationRegistry();
        return new MigrationCoordinator($registry, new MigrationRunner($registry, $state));
    }

    private function services(): ServiceRegistry
    {
        $environment = new class implements WordPressAbilityEnvironmentInterface {
            public function abilitiesApiAvailable(): bool
            {
                return true;
            }

            public function doingAction(string $hook): bool
            {
                return false;
            }

            public function currentUserId(): ?int
            {
                return 1;
            }

            public function currentSiteId(): int
            {
                return 1;
            }

            public function currentNetworkId(): ?int
            {
                return 1;
            }

            public function currentUserCan(string $capability): bool
            {
                return true;
            }

            public function isRestRequest(): bool
            {
                return false;
            }

            public function isCli(): bool
            {
                return false;
            }

            public function registerCategory(string $slug, array $args): bool
            {
                return true;
            }

            public function registerAbility(string $name, array $args): bool
            {
                return true;
            }
        };

        $contexts = new WordPressExecutionContextFactory($environment);
        $abilities = new AbilityRegistry(new PolicyEngine(new WordPressCapabilityChecker($environment)));
        $bridge = new WordPressAbilityBridge($abilities, $environment, $contexts);
        $services = new ServiceRegistry();
        $services->set('platform.definitions', new InMemoryDefinitionRepository());
        $services->set('platform.abilities', $abilities);
        $services->set('platform.abilities.wordpress', $bridge);
        $services->set('platform.abilities.contexts', $contexts);
        $services->set('platform.ajax.routes', new AjaxRouteRegistry());
        return $services;
    }
}
