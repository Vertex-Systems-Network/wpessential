<?php

declare(strict_types=1);

namespace WPEssential\Tests\Unit\Modules\AdminColumns;

use PHPUnit\Framework\TestCase;
use WPEssential\Contracts\CapabilityCheckerInterface;
use WPEssential\Contracts\QueryReadConsumerInterface;
use WPEssential\Kernel\ServiceRegistry;
use WPEssential\Modules\AdminColumns\AdminColumnsModule;
use WPEssential\Modules\AdminColumns\AdminColumnsViewImportAbilityHandler;
use WPEssential\Modules\AdminColumns\AdminColumnsViewImportService;
use WPEssential\Platform\Abilities\AbilityRegistry;
use WPEssential\Platform\Auth\ExecutionContext;
use WPEssential\Platform\Auth\PolicyEngine;
use WPEssential\Platform\Definitions\InMemoryDefinitionRepository;
use WPEssential\Platform\WordPress\Abilities\WordPressAbilityEnvironmentInterface;
use WPEssential\Platform\WordPress\Abilities\WordPressExecutionContextFactory;
use WPEssential\Platform\WordPress\Ajax\AjaxRouteRegistry;
use WPEssential\Platform\WordPress\Security\NonceOperation;

final class AdminColumnsViewImportAbilityWiringTest extends TestCase
{
    public function testRegistersCreateOnlyDraftImportServiceAbilityAndUpdateRoute(): void
    {
        [$services, $abilities, $routes] = $this->services();

        (new AdminColumnsModule())->register($services);

        self::assertInstanceOf(
            AdminColumnsViewImportService::class,
            $services->get(AdminColumnsModule::SERVICE_VIEW_IMPORT),
        );

        $descriptor = $abilities->descriptor(AdminColumnsViewImportAbilityHandler::ABILITY);
        self::assertNotNull($descriptor);
        self::assertTrue($descriptor->mutates);
        self::assertSame(8, $descriptor->ownerSurfaceId);
        self::assertSame('manage_options', $descriptor->capability);
        self::assertSame(['document', 'remaps'], $descriptor->inputSchema['required'] ?? null);
        self::assertFalse($descriptor->inputSchema['additionalProperties'] ?? true);
        self::assertSame(262144, $descriptor->inputSchema['properties']['document']['maxLength'] ?? null);
        self::assertArrayNotHasKey('status', $descriptor->inputSchema['properties'] ?? []);
        self::assertFalse(
            $descriptor->inputSchema['properties']['remaps']['additionalProperties'] ?? true,
        );

        $route = $routes->get(AdminColumnsViewImportAbilityHandler::AJAX_TYPE);
        self::assertNotNull($route);
        self::assertSame(NonceOperation::Update, $route->operation);
        self::assertTrue($route->requiresNonce);
        self::assertFalse($route->allowGuests);
    }

    public function testHandlerDelegatesOnlyToCreateCommitAndHardCodesDraftStatus(): void
    {
        $source = file_get_contents(
            dirname(__DIR__, 4) . '/frameworks/Modules/AdminColumns/AdminColumnsViewImportAbilityHandler.php',
        );
        self::assertIsString($source);

        self::assertStringContainsString('->commitCreate(', $source);
        self::assertStringContainsString('DefinitionStatus::Draft', $source);
        self::assertStringNotContainsString("\$input['status']", $source);
        self::assertStringNotContainsString('->save(', $source);
        self::assertStringNotContainsString('->changeStatus(', $source);
    }

    /** @return array{ServiceRegistry,AbilityRegistry,AjaxRouteRegistry} */
    private function services(): array
    {
        $services = new ServiceRegistry();
        $environment = new class implements WordPressAbilityEnvironmentInterface {
            public function abilitiesApiAvailable(): bool { return false; }
            public function doingAction(string $hook): bool { return false; }
            public function currentUserId(): ?int { return 7; }
            public function currentSiteId(): int { return 1; }
            public function currentNetworkId(): ?int { return 1; }
            public function currentUserCan(string $capability): bool { return true; }
            public function isRestRequest(): bool { return false; }
            public function isCli(): bool { return false; }
            public function registerCategory(string $slug, array $args): bool { return true; }
            public function registerAbility(string $name, array $args): bool { return true; }
        };
        $policy = new PolicyEngine(new class implements CapabilityCheckerInterface {
            public function can(ExecutionContext $context, string $capability): bool
            {
                return true;
            }
        });
        $abilities = new AbilityRegistry($policy);
        $routes = new AjaxRouteRegistry();

        $services->set('platform.definitions', new InMemoryDefinitionRepository());
        $services->set('module.query.read-consumer', new class implements QueryReadConsumerInterface {
            public function describe(string $sourceRef, ExecutionContext $context): array
            {
                return [];
            }

            public function read(array $request, ExecutionContext $context): array
            {
                return [];
            }
        });
        $services->set('platform.abilities', $abilities);
        $services->set('platform.abilities.contexts', new WordPressExecutionContextFactory($environment));
        $services->set('platform.ajax.routes', $routes);

        return [$services, $abilities, $routes];
    }
}
