<?php

declare(strict_types=1);

namespace WPEssential\Tests\Unit\Modules\CustomTables\Readiness\Composition;

use InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use ReflectionClass;
use ReflectionMethod;
use WPEssential\Contracts\CapabilityCheckerInterface;
use WPEssential\Modules\CustomTables\Migration\Execution\R1AdditiveExecutionCoordinator;
use WPEssential\Modules\CustomTables\Migration\Precondition\Probe\WordPressMetadataPreconditionFactsProvider;
use WPEssential\Modules\CustomTables\Migration\Readiness\Composition\CustomTablesRuntimeCompositionFactory;
use WPEssential\Modules\CustomTables\Migration\Readiness\Composition\Persistence\WpdbMigrationExecutionConfirmationProvider;
use WPEssential\Modules\CustomTables\Migration\Readiness\Composition\RuntimeCompositionReadinessService;
use WPEssential\Modules\CustomTables\Migration\Recovery\Provider\FailClosedRecoveryVerificationProvider;
use WPEssential\Modules\CustomTables\Migration\Recovery\Provider\LocalBackupRecoveryVerificationProvider;
use WPEssential\Modules\CustomTables\Migration\Review\R1ExecutionRequestResolver;
use WPEssential\Modules\CustomTables\Migration\Review\R1MigrationRunLifecycleService;
use WPEssential\Modules\CustomTables\Migration\Review\R1MigrationRunPreparationService;
use WPEssential\Modules\CustomTables\Migration\Review\WordPressProviderCapabilityProfileResolver;
use WPEssential\Platform\Definitions\InMemoryDefinitionRepository;
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

    public function testFactoryBuildsR1CoordinatorOnlyFromFinalRepositoryIntrospector(): void
    {
        $database = new RuntimeCompositionDatabaseFake();
        $introspector = new WordPressCt1SchemaIntrospector(new RuntimeCompositionWpdbFake());
        $factory = CustomTablesRuntimeCompositionFactory::production(
            database: $database,
            policy: new PolicyEngine(self::capabilityChecker()),
            metadataFacts: new WordPressMetadataPreconditionFactsProvider($introspector),
            recoveryVerification: new FailClosedRecoveryVerificationProvider(),
            confirmations: new WpdbMigrationExecutionConfirmationProvider($database, 7),
            networkId: 2,
            siteId: 7,
        );

        self::assertInstanceOf(R1AdditiveExecutionCoordinator::class, $factory->createR1AdditiveExecutionCoordinator($introspector));
        self::assertInstanceOf(R1MigrationRunPreparationService::class, $factory->createR1MigrationRunPreparationService($introspector));
        self::assertInstanceOf(R1MigrationRunLifecycleService::class, $factory->createR1MigrationRunLifecycleService());

        $profiles = new WordPressProviderCapabilityProfileResolver(new RuntimeCompositionWpdbFake());
        self::assertInstanceOf(
            R1ExecutionRequestResolver::class,
            $factory->createR1ExecutionRequestResolver($introspector, $profiles, new InMemoryDefinitionRepository(), 'wp_'),
        );
        foreach (['schema' => WordPressCt1SchemaIntrospector::class, 'profiles' => WordPressProviderCapabilityProfileResolver::class] as $name => $class) {
            $parameter = array_values(array_filter(
                (new ReflectionMethod(CustomTablesRuntimeCompositionFactory::class, 'createR1ExecutionRequestResolver'))->getParameters(),
                static fn (\ReflectionParameter $p): bool => $p->getName() === $name,
            ))[0];
            $type = $parameter->getType();
            self::assertInstanceOf(\ReflectionNamedType::class, $type);
            self::assertSame($class, $type->getName());
            self::assertTrue((new ReflectionClass($class))->isFinal());
        }

        $preparation = (new ReflectionMethod(CustomTablesRuntimeCompositionFactory::class, 'createR1MigrationRunPreparationService'))->getParameters();
        self::assertCount(1, $preparation);
        $preparationType = $preparation[0]->getType();
        self::assertInstanceOf(\ReflectionNamedType::class, $preparationType);
        self::assertSame(WordPressCt1SchemaIntrospector::class, $preparationType->getName());

        $parameters = (new ReflectionMethod(CustomTablesRuntimeCompositionFactory::class, 'createR1AdditiveExecutionCoordinator'))->getParameters();
        self::assertCount(1, $parameters);
        $type = $parameters[0]->getType();
        self::assertInstanceOf(\ReflectionNamedType::class, $type);
        self::assertSame(WordPressCt1SchemaIntrospector::class, $type->getName());
        self::assertTrue((new ReflectionClass($type->getName()))->isFinal());
    }

    public function testCanonicalFactoryCreateDoesNotAcceptCallerSuppliedTrustProviders(): void
    {
        $create = new ReflectionMethod(CustomTablesRuntimeCompositionFactory::class, 'create');
        $constructor = (new ReflectionClass(CustomTablesRuntimeCompositionFactory::class))->getConstructor();

        self::assertSame(0, $create->getNumberOfParameters());
        self::assertNotNull($constructor);
        self::assertTrue($constructor->isPrivate());
    }

    public function testProductionFactoryAcceptsOnlyFinalRepositoryOwnedTrustProviders(): void
    {
        $production = new ReflectionMethod(CustomTablesRuntimeCompositionFactory::class, 'production');
        $expected = [
            'recoveryVerification' => [FailClosedRecoveryVerificationProvider::class, LocalBackupRecoveryVerificationProvider::class],
            'confirmations' => [WpdbMigrationExecutionConfirmationProvider::class],
        ];

        foreach ($production->getParameters() as $parameter) {
            if (!isset($expected[$parameter->getName()])) {
                continue;
            }
            $type = $parameter->getType();
            $types = $type instanceof \ReflectionUnionType ? $type->getTypes() : [$type];
            $names = [];
            foreach ($types as $named) {
                self::assertInstanceOf(\ReflectionNamedType::class, $named);
                self::assertTrue((new ReflectionClass($named->getName()))->isFinal());
                $names[] = $named->getName();
            }
            sort($names);
            $want = $expected[$parameter->getName()];
            sort($want);
            self::assertSame($want, $names);
            unset($expected[$parameter->getName()]);
        }

        self::assertSame([], $expected);
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
