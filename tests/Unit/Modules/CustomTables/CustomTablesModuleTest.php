<?php

declare(strict_types=1);

namespace WPEssential\Tests\Unit\Modules\CustomTables;

use InvalidArgumentException;
use LogicException;
use PHPUnit\Framework\TestCase;
use RuntimeException;
use WPEssential\Contracts\CapabilityCheckerInterface;
use WPEssential\Kernel\ServiceRegistry;
use WPEssential\Modules\CustomTables\AuditedMigrationAjaxHandler;
use WPEssential\Modules\CustomTables\CustomTablesAdminController;
use WPEssential\Modules\CustomTables\CustomTablesMigrationAbilityHandler;
use WPEssential\Modules\CustomTables\CustomTablesMigrationAudit;
use WPEssential\Modules\CustomTables\CustomTablesModule;
use WPEssential\Modules\CustomTables\Migration\Precondition\Probe\WordPressMetadataPreconditionFactsProvider;
use WPEssential\Modules\CustomTables\Migration\Readiness\Composition\CustomTablesRuntimeCompositionFactory;
use WPEssential\Modules\CustomTables\Migration\Readiness\Composition\Persistence\WpdbMigrationExecutionConfirmationIssuer;
use WPEssential\Modules\CustomTables\Migration\Readiness\Composition\Persistence\WpdbMigrationExecutionConfirmationProvider;
use WPEssential\Modules\CustomTables\Migration\Recovery\Provider\FailClosedRecoveryVerificationProvider;
use WPEssential\Modules\CustomTables\Migration\Review\WordPressProviderCapabilityProfileResolver;
use WPEssential\Modules\CustomTables\Schema\WordPressCt1SchemaIntrospector;
use WPEssential\Platform\Abilities\AbilityRegistry;
use WPEssential\Platform\Admin\AdminAssetManifest;
use WPEssential\Platform\Audit\AuditOutcome;
use WPEssential\Platform\Audit\AuditServices;
use WPEssential\Platform\Audit\InMemoryAuditLogger;
use WPEssential\Platform\Auth\ExecutionChannel;
use WPEssential\Platform\Auth\ExecutionContext;
use WPEssential\Platform\Auth\PolicyEngine;
use WPEssential\Platform\Auth\Principal;
use WPEssential\Platform\Database\DatabaseAdapterInterface;
use WPEssential\Platform\Definitions\Definition;
use WPEssential\Platform\Definitions\DefinitionStatus;
use WPEssential\Platform\Definitions\InMemoryDefinitionRepository;
use WPEssential\Platform\WordPress\Abilities\WordPressAbilityEnvironmentInterface;
use WPEssential\Platform\WordPress\Abilities\WordPressExecutionContextFactory;
use WPEssential\Platform\WordPress\Ajax\AjaxAuthorizationException;
use WPEssential\Platform\WordPress\Ajax\AjaxDispatcher;
use WPEssential\Platform\WordPress\Ajax\AjaxResponse;
use WPEssential\Platform\WordPress\Ajax\WordPressAjaxEnvironmentInterface;
use WPEssential\Platform\WordPress\Ajax\WordPressAjaxGateway;
use WPEssential\Platform\WordPress\Ajax\AjaxRouteRegistry;
use WPEssential\Platform\WordPress\Security\NonceEnvironmentInterface;
use WPEssential\Platform\WordPress\Security\NonceManager;
use WPEssential\Platform\WordPress\Security\NonceOperation;

final class CustomTablesModuleTest extends TestCase
{
    private const RUN_ID = '11111111-1111-4111-8111-111111111111';

    public function testRegistersSevenMutatingAbilitiesForInternalAndAdminUiOnly(): void
    {
        [$abilities] = $this->registered();

        $names = [];
        foreach (CustomTablesMigrationAbilityHandler::ACTIONS as $action) {
            $name = CustomTablesModule::abilityName($action);
            $descriptor = $abilities->descriptor($name);
            self::assertNotNull($descriptor, $name);
            self::assertSame([ExecutionChannel::Internal, ExecutionChannel::Ui], $descriptor->channels);
            foreach ([ExecutionChannel::Rest, ExecutionChannel::Cli, ExecutionChannel::Ai, ExecutionChannel::Workflow] as $channel) {
                self::assertFalse($descriptor->allows($channel), $name . ' must not allow ' . $channel->value);
            }
            self::assertSame('wpessential_apply_table_migrations', $descriptor->capability);
            self::assertSame(7, $descriptor->ownerSurfaceId);
            self::assertTrue($descriptor->mutates);
            self::assertFalse(($descriptor->inputSchema['additionalProperties'] ?? true));
            $names[] = $name;
        }

        self::assertSame([
            'wpessential/custom-tables/prepare-migration',
            'wpessential/custom-tables/submit-migration',
            'wpessential/custom-tables/approve-migration',
            'wpessential/custom-tables/revalidate-migration',
            'wpessential/custom-tables/cancel-migration',
            'wpessential/custom-tables/confirm-migration',
            'wpessential/custom-tables/execute-migration',
        ], $names);
    }

    public function testEveryAbilityHasANonceProtectedAuditedAdminAjaxRoute(): void
    {
        [, $routes] = $this->registered();

        foreach (CustomTablesMigrationAbilityHandler::ACTIONS as $action) {
            $route = $routes->get(CustomTablesModule::routeType($action));
            self::assertNotNull($route, $action);
            self::assertSame(NonceOperation::Update, $route->operation);
            self::assertTrue($route->requiresNonce);
            self::assertFalse($route->allowGuests);
            self::assertInstanceOf(AuditedMigrationAjaxHandler::class, $route->handler);
        }
        self::assertFalse($routes->hasGuestRoutes());
    }

    public function testRestCliAndAiChannelsAreRefusedBeforeAnyHandlerRuns(): void
    {
        [$abilities] = $this->registered();

        foreach ([ExecutionChannel::Rest, ExecutionChannel::Cli, ExecutionChannel::Ai] as $channel) {
            foreach (CustomTablesMigrationAbilityHandler::ACTIONS as $action) {
                try {
                    $abilities->execute(
                        CustomTablesModule::abilityName($action),
                        ['run_id' => self::RUN_ID, 'expected_state_revision' => 1],
                        new ExecutionContext(new Principal(7), 1, $channel, networkId: 1),
                    );
                    self::fail($action . ' must be refused on ' . $channel->value);
                } catch (RuntimeException $exception) {
                    self::assertStringContainsString('channel_not_exposed', $exception->getMessage());
                }
            }
        }
    }

    public function testAdminAjaxRefusalIsAuditedAsDeniedAndRethrown(): void
    {
        $logger = new InMemoryAuditLogger();
        [, $routes] = $this->registered(userId: 8, logger: $logger);

        try {
            $routes->get(CustomTablesModule::routeType('approve'))?->handler->handle(['run_id' => self::RUN_ID, 'expected_state_revision' => 1]);
            self::fail('A non-administrator must be refused.');
        } catch (AjaxAuthorizationException) {
        }

        $records = array_values($logger->all());
        self::assertCount(1, $records);
        self::assertSame('custom-tables.migration.approve', $records[0]->action);
        self::assertSame(AuditOutcome::Denied, $records[0]->outcome);
        self::assertSame(ExecutionChannel::Ui, $records[0]->context->channel);
        self::assertSame(self::RUN_ID, $records[0]->resourceId);
    }

    public function testHandlerFailureIsAuditedAndRethrown(): void
    {
        $logger = new InMemoryAuditLogger();
        [$abilities] = $this->registered(logger: $logger);

        try {
            $abilities->execute(
                CustomTablesModule::abilityName('approve'),
                ['run_id' => self::RUN_ID, 'expected_state_revision' => 1],
                new ExecutionContext(new Principal(7), 1, networkId: 1),
            );
            self::fail('Approving a run that does not exist must fail.');
        } catch (RuntimeException) {
        }

        $records = array_values($logger->all());
        self::assertCount(1, $records);
        self::assertSame(AuditOutcome::Failed, $records[0]->outcome);
        self::assertSame('custom_table_migration_run', $records[0]->resourceType);
        self::assertSame(self::RUN_ID, $records[0]->resourceId);
    }

    public function testMalformedInputIsRejectedAndNeverEchoedIntoTheAuditTrail(): void
    {
        $logger = new InMemoryAuditLogger();
        [$abilities] = $this->registered(logger: $logger);
        $admin = new ExecutionContext(new Principal(7), 1, networkId: 1);

        foreach ([
            ['approve', ['run_id' => 'not-a-uuid', 'expected_state_revision' => 1]],
            ['approve', ['run_id' => self::RUN_ID, 'expected_state_revision' => 0]],
            ['approve', ['run_id' => self::RUN_ID, 'expected_state_revision' => '2']],
            ['prepare', ['definition_id' => 'x']],
            ['execute', ['run_id' => self::RUN_ID, 'backup_id' => '']],
        ] as [$action, $input]) {
            try {
                $abilities->execute(CustomTablesModule::abilityName($action), $input, $admin);
                self::fail($action . ' must reject malformed input');
            } catch (InvalidArgumentException) {
            }
        }

        $records = array_values($logger->all());
        self::assertCount(5, $records);
        self::assertNull($records[0]->resourceId);
        self::assertNull($records[3]->resourceId);
    }

    public function testAdminBootstrapMintsOneNoncePerMigrationRouteAndListsOnlyTableDefinitions(): void
    {
        $definitions = new InMemoryDefinitionRepository();
        $definitions->create(new Definition(
            id: '78888888-8888-4888-8888-888888888888',
            slug: 'orders',
            type: 'table',
            schemaVersion: 1,
            ownerSurfaceId: 7,
            status: DefinitionStatus::Published,
            payload: ['label' => 'Orders'],
        ));
        [$services] = $this->services(definitions: $definitions);
        $this->module()->register($services);

        $admin = $services->get('module.custom-tables.admin');
        self::assertInstanceOf(CustomTablesAdminController::class, $admin);
        $bootstrap = $admin->bootstrap('https://example.test/wp-admin/admin-ajax.php');

        self::assertSame('custom-tables', $bootstrap['surface']);
        self::assertSame('wpessential_dispatch', $bootstrap['ajaxAction']);
        self::assertSame(CustomTablesMigrationAbilityHandler::ACTIONS, array_keys($bootstrap['routes']));
        $nonces = [];
        foreach ($bootstrap['routes'] as $action => $route) {
            self::assertSame(CustomTablesModule::routeType($action), $route['type']);
            self::assertStringContainsString(':update:', $route['nonce']);
            self::assertStringContainsString($route['type'], $route['nonce']);
            $nonces[] = $route['nonce'];
        }
        self::assertCount(7, array_unique($nonces));
        self::assertSame([[
            'definition_id' => '78888888-8888-4888-8888-888888888888',
            'key' => 'orders',
            'label' => 'Orders',
            'revision' => 1,
        ]], $bootstrap['tables']);
    }

    public function testUnknownActionIsRejected(): void
    {
        [$services] = $this->services();

        $this->expectException(InvalidArgumentException::class);
        new CustomTablesMigrationAbilityHandler(
            'drop',
            $services->get('custom-tables.runtime-composition.factory'),
            new WordPressCt1SchemaIntrospector(new ModuleWpdbFake()),
            new WordPressProviderCapabilityProfileResolver(new ModuleWpdbFake()),
            new InMemoryDefinitionRepository(),
            $services->get('custom-tables.execution-confirmation.issuer'),
            'wp_',
            new CustomTablesMigrationAudit(new InMemoryAuditLogger()),
        );
    }

    public function testModuleRequiresTheTrustedFactory(): void
    {
        [$services] = $this->services(factory: new \stdClass());

        $this->expectException(LogicException::class);
        $this->module()->register($services);
    }

    /** @return array{0: AbilityRegistry, 1: AjaxRouteRegistry} */
    private function registered(int $userId = 7, ?InMemoryAuditLogger $logger = null): array
    {
        [$services, $abilities, $routes] = $this->services($userId, $logger);
        $this->module()->register($services);

        return [$abilities, $routes];
    }

    private function module(): CustomTablesModule
    {
        $wpdb = new ModuleWpdbFake();

        return new CustomTablesModule(
            new WordPressCt1SchemaIntrospector($wpdb),
            new WordPressProviderCapabilityProfileResolver($wpdb),
            'wp_',
        );
    }

    /** @return array{0: ServiceRegistry, 1: AbilityRegistry, 2: AjaxRouteRegistry} */
    private function services(int $userId = 7, ?InMemoryAuditLogger $logger = null, ?object $factory = null, ?InMemoryDefinitionRepository $definitions = null): array
    {
        $policy = new PolicyEngine(new class implements CapabilityCheckerInterface {
            public function can(ExecutionContext $context, string $capability): bool
            {
                return $context->principal->userId === 7 && $capability === 'wpessential_apply_table_migrations';
            }
        });
        $database = new ModuleDatabaseFake();
        $confirmations = new WpdbMigrationExecutionConfirmationProvider($database, 1);
        $abilities = new AbilityRegistry($policy);
        $routes = new AjaxRouteRegistry();
        $dispatcher = new AjaxDispatcher(
            $routes,
            new NonceManager(new class implements NonceEnvironmentInterface {
                public function create(string $action): string
                {
                    return 'nonce:' . $action;
                }

                public function verify(string $nonce, string $action): bool
                {
                    return $nonce === 'nonce:' . $action;
                }
            }, 'wpessential-test'),
            static fn (string $capability): bool => true,
        );

        $services = new ServiceRegistry();
        $services->set('platform.abilities', $abilities);
        $services->set('platform.definitions', $definitions ?? new InMemoryDefinitionRepository());
        $services->set('platform.ajax.dispatcher', $dispatcher);
        $services->set('platform.ajax.gateway', new WordPressAjaxGateway('wpessential_dispatch', $dispatcher, new ModuleAjaxEnvironmentFake()));
        $services->set('platform.admin.assets', new AdminAssetManifest(sys_get_temp_dir(), 'https://example.test/wp-content/plugins/wpessential'));
        $services->set('platform.abilities.contexts', new WordPressExecutionContextFactory(new ModuleEnvironmentFake($userId)));
        $services->set('platform.ajax.routes', $routes);
        $services->set(AuditServices::LOGGER, $logger ?? new InMemoryAuditLogger());
        $services->set('custom-tables.runtime-composition.factory', $factory ?? CustomTablesRuntimeCompositionFactory::production(
            database: $database,
            policy: $policy,
            metadataFacts: new WordPressMetadataPreconditionFactsProvider(new WordPressCt1SchemaIntrospector(new ModuleWpdbFake())),
            recoveryVerification: new FailClosedRecoveryVerificationProvider(),
            confirmations: $confirmations,
            networkId: 1,
            siteId: 1,
        ));
        $services->set('custom-tables.execution-confirmation.issuer', new WpdbMigrationExecutionConfirmationIssuer($database, $confirmations, $policy, 1));

        return [$services, $abilities, $routes];
    }
}

final class ModuleEnvironmentFake implements WordPressAbilityEnvironmentInterface
{
    public function __construct(private int $userId)
    {
    }

    public function abilitiesApiAvailable(): bool { return true; }
    public function doingAction(string $hook): bool { return false; }
    public function currentUserId(): ?int { return $this->userId; }
    public function currentSiteId(): int { return 1; }
    public function currentNetworkId(): ?int { return 1; }
    public function currentUserCan(string $capability): bool { return $this->userId === 7; }
    public function isRestRequest(): bool { return false; }
    public function isCli(): bool { return false; }
    public function registerCategory(string $slug, array $args): bool { return true; }
    public function registerAbility(string $name, array $args): bool { return true; }
}

final class ModuleWpdbFake
{
    public string $prefix = 'wp_';
    public string $collate = 'utf8mb4_unicode_ci';
    public string $last_error = '';

    public function prepare(string $query, mixed ...$args): string
    {
        return $query;
    }

    /** @return list<array<string,mixed>> */
    public function get_results(string $query, string $output): array
    {
        return [];
    }

    public function db_server_info(): string
    {
        return '8.0.36';
    }
}

final class ModuleDatabaseFake implements DatabaseAdapterInterface
{
    public function networkTablePrefix(): string { return 'wp_'; }
    public function charsetCollate(): string { return ''; }
    public function prepare(string $query, mixed ...$args): string { return $query; }
    public function getRow(string $query): ?array { return null; }
    public function getResults(string $query): array { return []; }
    public function getVar(string $query): mixed { return null; }
    public function query(string $query): int|bool { return 0; }
    public function insert(string $table, array $data, array $formats = []): bool { return false; }
    public function lastError(): string { return ''; }
    public function beginTransaction(): void {}
    public function commit(): void {}
    public function rollBack(): void {}
}

final class ModuleAjaxEnvironmentFake implements WordPressAjaxEnvironmentInterface
{
    public function registerAction(string $hook, callable $callback): void {}
    public function request(): array { return []; }
    public function isAuthenticated(): bool { return true; }
    public function currentUserCan(string $capability): bool { return true; }
    public function respond(AjaxResponse $response): void {}
}
