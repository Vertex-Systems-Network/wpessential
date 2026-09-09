<?php

declare(strict_types=1);

namespace WPEssential\Tests\Unit\Modules\CustomTables\Readiness\Composition;

use InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use ReflectionClass;
use ReflectionMethod;
use WPEssential\Contracts\CapabilityCheckerInterface;
use WPEssential\Modules\CustomTables\Migration\Precondition\Probe\WordPressMetadataPreconditionFactsProvider;
use WPEssential\Modules\CustomTables\Migration\Readiness\Composition\CustomTablesRuntimeCompositionFactory;
use WPEssential\Modules\CustomTables\Migration\Readiness\Composition\Persistence\WpdbMigrationExecutionConfirmationProvider;
use WPEssential\Modules\CustomTables\Migration\Readiness\Composition\RuntimeCompositionReadinessService;
use WPEssential\Modules\CustomTables\Migration\Recovery\Provider\FailClosedRecoveryVerificationProvider;
use WPEssential\Modules\CustomTables\Schema\WordPressCt1SchemaIntrospector;
use WPEssential\Platform\Auth\ExecutionContext;
use WPEssential\Platform\Auth\PolicyEngine;
use WPEssential\Platform\Database\DatabaseAdapterInterface;

final class CustomTablesRuntimeCompositionFactoryTest extends TestCase
{
    public function testFactoryBuildsNoDispatchServiceFromProductionOwnedTrustedDependencies(): void
    {
        $database = new RuntimeCompositionDatabaseFake();
        $factory = CustomTablesRuntimeCompositionFactory::production(
            database: $database,
            policy: new PolicyEngine(self::capabilityChecker()),
            metadataFacts: new WordPressMetadataPreconditionFactsProvider(
                new WordPressCt1SchemaIntrospector(new RuntimeCompositionWpdbFake()),
            ),
            recoveryVerification: new FailClosedRecoveryVerificationProvider(),
            confirmations: new WpdbMigrationExecutionConfirmationProvider($database, 7),
            networkId: 2,
            siteId: 7,
        );

        self::assertInstanceOf(RuntimeCompositionReadinessService::class, $factory->create());
    }

    public function testCanonicalFactoryCreateDoesNotAcceptCallerSuppliedTrustProviders(): void
    {
        $create = new ReflectionMethod(CustomTablesRuntimeCompositionFactory::class, 'create');
        $constructor = (new ReflectionClass(CustomTablesRuntimeCompositionFactory::class))->getConstructor();

        self::assertSame(0, $create->getNumberOfParameters());
        self::assertNotNull($constructor);
        self::assertTrue($constructor->isPrivate());
    }

    public function testProductionFactoryRejectsInvalidScope(): void
    {
        $database = new RuntimeCompositionDatabaseFake();

        $this->expectException(InvalidArgumentException::class);
        CustomTablesRuntimeCompositionFactory::production(
            database: $database,
            policy: new PolicyEngine(self::capabilityChecker()),
            metadataFacts: new WordPressMetadataPreconditionFactsProvider(
                new WordPressCt1SchemaIntrospector(new RuntimeCompositionWpdbFake()),
            ),
            recoveryVerification: new FailClosedRecoveryVerificationProvider(),
            confirmations: new WpdbMigrationExecutionConfirmationProvider($database, 1),
            networkId: 0,
            siteId: 1,
        );
    }

    private static function capabilityChecker(): CapabilityCheckerInterface
    {
        return new class implements CapabilityCheckerInterface {
            public function can(ExecutionContext $context, string $capability): bool
            {
                return false;
            }
        };
    }
}

final class RuntimeCompositionWpdbFake
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

final class RuntimeCompositionDatabaseFake implements DatabaseAdapterInterface
{
    public function networkTablePrefix(): string { return 'wp_'; }
    public function charsetCollate(): string { return 'DEFAULT CHARACTER SET utf8mb4'; }
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
