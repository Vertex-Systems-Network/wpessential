<?php

declare(strict_types=1);

namespace WPEssential\Tests\Unit\Modules\CustomTables;

use InvalidArgumentException;
use LogicException;
use PHPUnit\Framework\TestCase;
use RuntimeException;
use WPEssential\Contracts\CapabilityCheckerInterface;
use WPEssential\Kernel\ServiceRegistry;
use WPEssential\Modules\CustomTables\CustomTablesMigrationAbilityHandler;
use WPEssential\Modules\CustomTables\CustomTablesModule;
use WPEssential\Modules\CustomTables\Migration\Precondition\Probe\WordPressMetadataPreconditionFactsProvider;
use WPEssential\Modules\CustomTables\Migration\Readiness\Composition\CustomTablesRuntimeCompositionFactory;
use WPEssential\Modules\CustomTables\Migration\Readiness\Composition\Persistence\WpdbMigrationExecutionConfirmationIssuer;
use WPEssential\Modules\CustomTables\Migration\Readiness\Composition\Persistence\WpdbMigrationExecutionConfirmationProvider;
use WPEssential\Modules\CustomTables\Migration\Recovery\Provider\FailClosedRecoveryVerificationProvider;
use WPEssential\Modules\CustomTables\Migration\Review\WordPressProviderCapabilityProfileResolver;
use WPEssential\Modules\CustomTables\Schema\WordPressCt1SchemaIntrospector;
use WPEssential\Platform\Abilities\AbilityRegistry;
use WPEssential\Platform\Auth\ExecutionChannel;
use WPEssential\Platform\Auth\ExecutionContext;
use WPEssential\Platform\Auth\PolicyEngine;
use WPEssential\Platform\Auth\Principal;
use WPEssential\Platform\Database\DatabaseAdapterInterface;
use WPEssential\Platform\Definitions\InMemoryDefinitionRepository;

final class CustomTablesModuleTest extends TestCase
{
    private const RUN_ID = '11111111-1111-4111-8111-111111111111';

    public function testRegistersSevenInternalOnlyMutatingAbilities(): void
    {
        $abilities = $this->registered();

        $names = [];
        foreach (CustomTablesMigrationAbilityHandler::ACTIONS as $action) {
            $name = CustomTablesModule::abilityName($action);
            $descriptor = $abilities->descriptor($name);
            self::assertNotNull($descriptor, $name);
            self::assertSame([ExecutionChannel::Internal], $descriptor->channels);
            self::assertSame('manage_options', $descriptor->capability);
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

    public function testExternalChannelsAreRefusedBeforeAnyHandlerRuns(): void
    {
        $abilities = $this->registered();

        foreach ([ExecutionChannel::Rest, ExecutionChannel::Cli, ExecutionChannel::Ui, ExecutionChannel::Ai] as $channel) {
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

    public function testUsersWithoutManageOptionsAreRefused(): void
    {
        $abilities = $this->registered();

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('denied');
        $abilities->execute(
            CustomTablesModule::abilityName('approve'),
            ['run_id' => self::RUN_ID, 'expected_state_revision' => 1],
            new ExecutionContext(new Principal(8), 1, networkId: 1),
        );
    }

    public function testHandlerRejectsMalformedIdentifiersAndRevisions(): void
    {
        $abilities = $this->registered();
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
        self::assertTrue(true);
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
        );
    }

    public function testModuleRequiresTheTrustedFactory(): void
    {
        [, $abilities] = $this->services();
        $services = new ServiceRegistry();
        $services->set('platform.abilities', $abilities);
        $services->set('platform.definitions', new InMemoryDefinitionRepository());
        $services->set('custom-tables.runtime-composition.factory', new \stdClass());
        $services->set('custom-tables.execution-confirmation.issuer', new \stdClass());

        $this->expectException(LogicException::class);
        $this->module()->register($services);
    }

    private function registered(): AbilityRegistry
    {
        [$services, $abilities] = $this->services();
        $this->module()->register($services);

        return $abilities;
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

    /** @return array{0: ServiceRegistry, 1: AbilityRegistry} */
    private function services(): array
    {
        $policy = new PolicyEngine(new class implements CapabilityCheckerInterface {
            public function can(ExecutionContext $context, string $capability): bool
            {
                return $context->principal->userId === 7 && $capability === 'manage_options';
            }
        });
        $database = new ModuleDatabaseFake();
        $confirmations = new WpdbMigrationExecutionConfirmationProvider($database, 1);
        $abilities = new AbilityRegistry($policy);

        $services = new ServiceRegistry();
        $services->set('platform.abilities', $abilities);
        $services->set('platform.definitions', new InMemoryDefinitionRepository());
        $services->set('custom-tables.runtime-composition.factory', CustomTablesRuntimeCompositionFactory::production(
            database: $database,
            policy: $policy,
            metadataFacts: new WordPressMetadataPreconditionFactsProvider(new WordPressCt1SchemaIntrospector(new ModuleWpdbFake())),
            recoveryVerification: new FailClosedRecoveryVerificationProvider(),
            confirmations: $confirmations,
            networkId: 1,
            siteId: 1,
        ));
        $services->set('custom-tables.execution-confirmation.issuer', new WpdbMigrationExecutionConfirmationIssuer($database, $confirmations, $policy, 1));

        return [$services, $abilities];
    }
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
