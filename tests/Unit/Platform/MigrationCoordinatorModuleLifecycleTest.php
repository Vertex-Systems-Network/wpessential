<?php

declare(strict_types=1);

namespace WPEssential\Tests\Unit\Platform;

use LogicException;
use PHPUnit\Framework\TestCase;
use WPEssential\Contracts\MigrationInterface;
use WPEssential\Contracts\ModuleActivationPolicyInterface;
use WPEssential\Contracts\ModuleInterface;
use WPEssential\Contracts\ServiceRegistryInterface;
use WPEssential\Kernel\Kernel;
use WPEssential\Kernel\ServiceRegistry;
use WPEssential\Platform\Database\Migrations\InMemoryMigrationStateStore;
use WPEssential\Platform\Database\Migrations\MigrationCoordinator;
use WPEssential\Platform\Database\Migrations\MigrationRegistry;
use WPEssential\Platform\Database\Migrations\MigrationRunner;
use WPEssential\Platform\Modules\ModuleManifest;

final class MigrationCoordinatorModuleLifecycleTest extends TestCase
{
    public function testAdmittedProModuleContributesDuringRegisterAndAppliesBeforeBootCompletes(): void
    {
        $registry = new MigrationRegistry();
        $coordinator = new MigrationCoordinator(
            $registry,
            new MigrationRunner($registry, new InMemoryMigrationStateStore()),
        );
        $services = new ServiceRegistry();
        $services->set('platform.database.migrations', $coordinator);
        $migration = new LifecycleMigrationFixture();
        $module = new MigrationLifecycleModuleFixture($migration);
        $policy = new class implements ModuleActivationPolicyInterface {
            public function allows(ModuleManifest $manifest): bool
            {
                return $manifest->id === 'migration-pro-fixture';
            }
        };
        $kernel = new Kernel(services: $services, moduleActivationPolicy: $policy);

        $kernel->registerModule($module);
        self::assertSame(0, $migration->applyCount);

        $kernel->boot();

        self::assertSame(1, $module->registerCalls);
        self::assertSame(1, $module->bootCalls);
        self::assertSame(1, $migration->applyCount);
        self::assertSame(['090.module-fixture'], $module->bootAppliedIds);
        self::assertSame([], $coordinator->runPending());
        self::assertSame(1, $migration->applyCount);
    }
}

final class MigrationLifecycleModuleFixture implements ModuleInterface
{
    public int $registerCalls = 0;
    public int $bootCalls = 0;

    /** @var list<string> */
    public array $bootAppliedIds = [];

    public function __construct(private readonly MigrationInterface $migration) {}

    public function manifest(): ModuleManifest
    {
        return new ModuleManifest(
            id: 'migration-pro-fixture',
            name: 'Migration Pro Fixture',
            version: '1.0.0',
            edition: 'pro',
        );
    }

    public function register(ServiceRegistryInterface $services): void
    {
        ++$this->registerCalls;
        $coordinator = $services->get('platform.database.migrations');
        if (!$coordinator instanceof MigrationCoordinator) {
            throw new LogicException('Migration coordinator fixture service is unavailable.');
        }
        $coordinator->register($this->migration);
        if ($this->migration instanceof LifecycleMigrationFixture && $this->migration->applyCount !== 0) {
            throw new LogicException('Migration must not apply during module register phase.');
        }
    }

    public function boot(ServiceRegistryInterface $services): void
    {
        ++$this->bootCalls;
        $coordinator = $services->get('platform.database.migrations');
        if (!$coordinator instanceof MigrationCoordinator) {
            throw new LogicException('Migration coordinator fixture service is unavailable.');
        }
        $this->bootAppliedIds = $coordinator->runPending();
    }
}

final class LifecycleMigrationFixture implements MigrationInterface
{
    public int $applyCount = 0;

    public function id(): string
    {
        return '090.module-fixture';
    }

    public function sequence(): int
    {
        return 900;
    }

    public function isDestructive(): bool
    {
        return false;
    }

    public function recoveryPlan(): ?string
    {
        return null;
    }

    public function apply(): void
    {
        ++$this->applyCount;
    }
}
