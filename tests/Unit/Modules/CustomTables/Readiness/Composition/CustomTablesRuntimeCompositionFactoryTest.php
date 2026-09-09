<?php

declare(strict_types=1);

namespace WPEssential\Tests\Unit\Modules\CustomTables\Readiness\Composition;

use InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use WPEssential\Contracts\CapabilityCheckerInterface;
use WPEssential\Modules\CustomTables\Migration\Readiness\Composition\CustomTablesRuntimeCompositionFactory;
use WPEssential\Modules\CustomTables\Migration\Readiness\Composition\RuntimeCompositionReadinessService;
use WPEssential\Modules\CustomTables\Migration\Readiness\Composition\StaticMigrationExecutionConfirmationProvider;
use WPEssential\Modules\CustomTables\Migration\Recovery\Provider\StaticRecoveryVerificationProvider;
use WPEssential\Platform\Auth\ExecutionContext;
use WPEssential\Platform\Auth\PolicyEngine;
use WPEssential\Platform\Database\DatabaseAdapterInterface;

final class CustomTablesRuntimeCompositionFactoryTest extends TestCase
{
    public function testFactoryBuildsNoDispatchServiceFromCanonicalDependencies(): void
    {
        $checker = new class implements CapabilityCheckerInterface {
            public function can(ExecutionContext $context, string $capability): bool
            {
                return false;
            }
        };

        $factory = new CustomTablesRuntimeCompositionFactory(
            new RuntimeCompositionDatabaseFake(),
            new PolicyEngine($checker),
            2,
            7,
        );

        $service = $factory->create(
            new StaticRecoveryVerificationProvider(),
            new StaticMigrationExecutionConfirmationProvider(),
        );

        self::assertInstanceOf(RuntimeCompositionReadinessService::class, $service);
    }

    public function testFactoryRejectsInvalidScope(): void
    {
        $checker = new class implements CapabilityCheckerInterface {
            public function can(ExecutionContext $context, string $capability): bool
            {
                return false;
            }
        };

        $this->expectException(InvalidArgumentException::class);
        new CustomTablesRuntimeCompositionFactory(
            new RuntimeCompositionDatabaseFake(),
            new PolicyEngine($checker),
            0,
            1,
        );
    }
}

final class RuntimeCompositionDatabaseFake implements DatabaseAdapterInterface
{
    public function networkTablePrefix(): string
    {
        return 'wp_';
    }

    public function charsetCollate(): string
    {
        return 'DEFAULT CHARACTER SET utf8mb4';
    }

    public function prepare(string $query, mixed ...$args): string
    {
        return $query;
    }

    public function getRow(string $query): ?array
    {
        return null;
    }

    public function getResults(string $query): array
    {
        return [];
    }

    public function getVar(string $query): mixed
    {
        return null;
    }

    public function query(string $query): int|bool
    {
        return 0;
    }

    public function insert(string $table, array $data, array $formats = []): bool
    {
        return false;
    }

    public function lastError(): string
    {
        return '';
    }

    public function beginTransaction(): void
    {
    }

    public function commit(): void
    {
    }

    public function rollBack(): void
    {
    }
}
