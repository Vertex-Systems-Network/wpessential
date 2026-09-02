<?php

declare(strict_types=1);

namespace WPEssential\Tests\Unit\Modules\Relations;

use PHPUnit\Framework\TestCase;
use WPEssential\Contracts\MigrationStateStoreInterface;
use WPEssential\Kernel\ServiceRegistry;
use WPEssential\Modules\Relations\RelationsModule;
use WPEssential\Modules\Relations\WpdbRelationEdgeGateway;
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

final class RelationsModuleEdgePersistenceTest extends TestCase
{
    public function testNativePersistenceRegistersMigrationsAndAppliesThemDuringBoot(): void
    {
        $database = new RelationEdgeDatabaseAdapter();
        $database->rowQueue = [['Key_name' => 'scope_relation_edge']];
        $state = new class implements MigrationStateStoreInterface {
            /** @var list<string> */
            public array $ids = [];

            public function appliedIds(): array
            {
                return $this->ids;
            }

            public function markApplied(string $id): void
            {
                $this->ids[] = $id;
            }
        };
        $registry = new MigrationRegistry();
        $migrations = new MigrationCoordinator($registry, new MigrationRunner($registry, $state));
        $services = $this->services();
        $services->set('platform.database', $database);
        $services->set('platform.database.migrations', $migrations);

        $module = new RelationsModule();
        $module->register($services);

        self::assertInstanceOf(WpdbRelationEdgeGateway::class, $services->get('module.relations.edge-gateway'));
        self::assertSame([], $state->ids);
        self::assertSame([], $database->queries);

        $module->boot($services);

        self::assertSame([
            '010.create-relation-edge-persistence',
            '020.allow-non-unique-relation-edge-tuples',
        ], $state->ids);
        self::assertCount(4, $database->queries);
        self::assertStringContainsString('wp_wpe_relation_edges', $database->queries[0]);
        self::assertStringContainsString('wp_wpe_relation_edge_state', $database->queries[1]);
        self::assertStringStartsWith('SHOW INDEX FROM `wp_wpe_relation_edges`', $database->queries[2]);
        self::assertSame(
            'ALTER TABLE `wp_wpe_relation_edges` DROP INDEX `scope_relation_edge`',
            $database->queries[3],
        );

        $module->boot($services);
        self::assertCount(4, $database->queries, 'Applied edge migrations must remain idempotent.');
    }

    public function testFallbackRegistrationDoesNotRequireNativePersistence(): void
    {
        $services = $this->services();
        $module = new RelationsModule();

        $module->register($services);
        $module->boot($services);

        self::assertFalse($services->has('module.relations.edge-gateway'));
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
