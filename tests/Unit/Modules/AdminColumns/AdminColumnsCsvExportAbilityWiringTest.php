<?php

declare(strict_types=1);

namespace WPEssential\Tests\Unit\Modules\AdminColumns;

use PHPUnit\Framework\TestCase;
use WPEssential\Contracts\CapabilityCheckerInterface;
use WPEssential\Contracts\QueryReadConsumerInterface;
use WPEssential\Kernel\ServiceRegistry;
use WPEssential\Modules\AdminColumns\AdminColumnsCsvExportAbilityHandler;
use WPEssential\Modules\AdminColumns\AdminColumnsCsvExportService;
use WPEssential\Modules\AdminColumns\AdminColumnsModule;
use WPEssential\Platform\Abilities\AbilityRegistry;
use WPEssential\Platform\Auth\ExecutionContext;
use WPEssential\Platform\Auth\PolicyEngine;
use WPEssential\Platform\Definitions\InMemoryDefinitionRepository;
use WPEssential\Platform\WordPress\Abilities\WordPressAbilityEnvironmentInterface;
use WPEssential\Platform\WordPress\Abilities\WordPressExecutionContextFactory;
use WPEssential\Platform\WordPress\Ajax\AjaxRouteRegistry;
use WPEssential\Platform\WordPress\Security\NonceOperation;

final class AdminColumnsCsvExportAbilityWiringTest extends TestCase
{
    public function testRegistersBoundedReadOnlyExportAbilityAndApplyRoute(): void
    {
        [$services, $abilities, $routes] = $this->services();

        (new AdminColumnsModule())->register($services);

        self::assertInstanceOf(
            AdminColumnsCsvExportService::class,
            $services->get(AdminColumnsModule::SERVICE_CSV_EXPORT),
        );

        $descriptor = $abilities->descriptor(AdminColumnsCsvExportAbilityHandler::ABILITY);
        self::assertNotNull($descriptor);
        self::assertFalse($descriptor->mutates);
        self::assertSame(8, $descriptor->ownerSurfaceId);
        self::assertSame('manage_options', $descriptor->capability);
        self::assertSame(
            ['view_id', 'expected_view_revision', 'scope_request', 'controls', 'runtime'],
            $descriptor->inputSchema['required'] ?? null,
        );
        self::assertFalse($descriptor->inputSchema['additionalProperties'] ?? true);
        self::assertSame(
            ['current_page', 'selected_rows', 'all_matching'],
            $descriptor->inputSchema['properties']['scope_request']['properties']['scope']['enum'] ?? null,
        );
        self::assertSame(
            100,
            $descriptor->inputSchema['properties']['scope_request']['properties']['selected_row_ids']['maxItems'] ?? null,
        );
        self::assertSame(
            100,
            $descriptor->inputSchema['properties']['scope_request']['properties']['selected_columns']['maxItems'] ?? null,
        );
        self::assertSame(
            100,
            $descriptor->inputSchema['properties']['runtime']['properties']['page_size']['maximum'] ?? null,
        );
        self::assertSame(
            10000,
            $descriptor->inputSchema['properties']['runtime']['properties']['offset']['maximum'] ?? null,
        );
        self::assertSame(
            8388608,
            $descriptor->outputSchema['properties']['csv']['maxLength'] ?? null,
        );

        $route = $routes->get(AdminColumnsCsvExportAbilityHandler::AJAX_TYPE);
        self::assertNotNull($route);
        self::assertSame(NonceOperation::Apply, $route->operation);
        self::assertTrue($route->requiresNonce);
        self::assertFalse($route->allowGuests);
    }

    public function testHandlerDelegatesOnlyToScopedExportAndDoesNotEmitHttpTransport(): void
    {
        $path = dirname(__DIR__, 4) . '/frameworks/Modules/AdminColumns/AdminColumnsCsvExportAbilityHandler.php';
        $source = file_get_contents($path);

        self::assertIsString($source);
        self::assertStringContainsString('->exportScoped(', $source);
        self::assertStringNotContainsString('header(', $source);
        self::assertStringNotContainsString('wp_die(', $source);
        self::assertStringNotContainsString('fopen(', $source);
        self::assertStringNotContainsString('file_put_contents(', $source);
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
